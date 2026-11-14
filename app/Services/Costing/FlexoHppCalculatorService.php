<?php

namespace App\Services\Costing;

use App\Support\MoneyHelper;

class FlexoHppCalculatorService
{
    /**
     * Default constants untuk Mesin Flexo Zonten TTC (Satuan Dimensi: CM)
     */
    public const DEFAULT_MESIN = 'Flexo Zonten';
    public const DEFAULT_REPEAT_CYLINDER = 41.28;  // cm (standar zonten 412.75 mm = 41.28 cm)
    public const DEFAULT_ALLOWANCE_PANJANG = 0.3;   // cm (3 mm)
    public const DEFAULT_ALLOWANCE_LEBAR = 0.3;     // cm (3 mm)
    public const DEFAULT_ALLOWANCE_WEB = 2.0;       // cm (20 mm)
    public const DEFAULT_WASTE_SETTING = 40.0;      // meter
    public const DEFAULT_WASTE_PERSEN = 10.0;       // %
    public const DEFAULT_KECEPATAN_CETAK = 100.0;   // meter/menit
    public const DEFAULT_WAKTU_SETUP = 60.0;        // menit
    public const DEFAULT_JUMLAH_WARNA = 8;
    public const DEFAULT_GRAMATUR = 230.0;          // gsm
    public const DEFAULT_HARGA_BAHAN = 10000.0;     // Rp/kg
    public const DEFAULT_HARGA_BAHAN_PER_METER = 0.0; // Rp/m

    /**
     * Hitung kalkulasi layout, kebutuhan bahan kertas, biaya proses flexo, dan HPP lengkap.
     * Satuan dimensi menggunakan centimeter (cm).
     *
     * @param array $data Input parameter kalkulasi
     * @param float $targetOrder Target jumlah pesanan (pcs)
     * @return array Hasil kalkulasi komprehensif
     */
    public function calculate(array $data, float $targetOrder): array
    {
        $targetOrder = max(1, (float) $targetOrder);

        // =====================================================================
        // A. SPESIFIKASI ORDER & LAYOUT (Satuan: CM)
        // =====================================================================
        $panjangEtiket = max(0, (float) ($data['panjang_etiket_jadi'] ?? 0));
        $lebarEtiket = max(0, (float) ($data['lebar_etiket_jadi'] ?? 0));
        $allowancePanjang = isset($data['lebihan_cetak_panjang']) ? (float) $data['lebihan_cetak_panjang'] : self::DEFAULT_ALLOWANCE_PANJANG;
        $allowanceLebar = isset($data['lebihan_cetak_lebar']) ? (float) $data['lebihan_cetak_lebar'] : self::DEFAULT_ALLOWANCE_LEBAR;
        $allowanceWeb = isset($data['lebihan_lebar_bahan']) ? (float) $data['lebihan_lebar_bahan'] : self::DEFAULT_ALLOWANCE_WEB;
        $repeatCylinder = (float) ($data['repeat_cylinder'] ?? self::DEFAULT_REPEAT_CYLINDER);

        // Auto-normalize jika nilai input masih berupa mm (data lama)
        if ($panjangEtiket > 50) {
            $panjangEtiket /= 10.0;
        }
        if ($lebarEtiket > 50) {
            $lebarEtiket /= 10.0;
        }
        if ($repeatCylinder > 100) {
            $repeatCylinder /= 10.0;
        }
        if ($allowancePanjang > 1.5) {
            $allowancePanjang /= 10.0;
        }
        if ($allowanceLebar > 1.5) {
            $allowanceLebar /= 10.0;
        }
        if ($allowanceWeb > 10) {
            $allowanceWeb /= 10.0;
        }

        if ($repeatCylinder <= 0) {
            $repeatCylinder = self::DEFAULT_REPEAT_CYLINDER;
        }

        $etiketSepanjangRepeat = max(1, (int) ($data['jumlah_etiket_sepanjang_repeat'] ?? 2));
        $jalurMelintang = max(1, (int) ($data['jumlah_etiket_sepanjang_lebar'] ?? 2));

        // Rumus Layout (semua dalam cm)
        $lebarBahan = ($lebarEtiket + $allowanceLebar) * $jalurMelintang + $allowanceWeb; // cm
        $panjangCetakPitch = $panjangEtiket + $allowancePanjang; // cm
        $panjangKebutuhanRepeat = $etiketSepanjangRepeat * $panjangCetakPitch; // cm
        $sisaRepeat = $repeatCylinder - $panjangKebutuhanRepeat; // cm
        $statusPanjangRepeat = ($sisaRepeat >= 0) ? 'MUAT SECARA MATEMATIS' : 'TIDAK MUAT (OVERSIZE)';

        // =====================================================================
        // B. KEBUTUHAN BAHAN
        // =====================================================================
        $etiketPerRepeat = $etiketSepanjangRepeat * $jalurMelintang;
        $repeatDibutuhkan = (int) ceil($targetOrder / max(1, $etiketPerRepeat));
        // Panjang produksi teoritis (meter) = repeat * repeatCylinder (cm) / 100
        $panjangProduksiTeoritis = ($repeatDibutuhkan * $repeatCylinder) / 100.0; // meter

        $wasteSetting = isset($data['waste_setting']) ? (float) $data['waste_setting'] : self::DEFAULT_WASTE_SETTING;
        $wastePersenRaw = isset($data['waste_tambahan_persen']) ? (float) $data['waste_tambahan_persen'] : self::DEFAULT_WASTE_PERSEN;
        $wastePersenRatio = ($wastePersenRaw > 1) ? ($wastePersenRaw / 100.0) : $wastePersenRaw;
        $wasteTambahanMeter = $panjangProduksiTeoritis * $wastePersenRatio;

        $totalPanjangBahan = $panjangProduksiTeoritis + $wasteSetting + $wasteTambahanMeter; // meter
        $gramatur = isset($data['gramatur_kertas']) ? (float) $data['gramatur_kertas'] : self::DEFAULT_GRAMATUR;

        // Berat bahan = panjang (m) * lebar (m) * gsm / 1000
        // Karena $lebarBahan dalam cm, diubah ke meter: $lebarBahan / 100.0
        $beratBahanTeoritis = $totalPanjangBahan * ($lebarBahan / 100.0) * ($gramatur / 1000.0); // kg

        // Konversi Berat per Meter Linear (kg/meter):
        $kgPerMeter = ($lebarBahan / 100.0) * ($gramatur / 1000.0); // kg/meter

        $hargaBahanPerKg = isset($data['harga_bahan_per_kg']) ? (float) $data['harga_bahan_per_kg'] : 0;
        $hargaBahanPerMeter = isset($data['harga_bahan_per_meter']) ? (float) $data['harga_bahan_per_meter'] : 0;
        $basisHarga = $data['basis_harga_bahan'] ?? ($hargaBahanPerMeter > 0 && $hargaBahanPerKg <= 0 ? 'meter' : 'kg');

        if ($basisHarga === 'meter' && $hargaBahanPerMeter > 0) {
            $biayaBahan = $totalPanjangBahan * $hargaBahanPerMeter;
            $hargaBahanPerKg = $kgPerMeter > 0 ? ($hargaBahanPerMeter / $kgPerMeter) : 0;
        } else {
            if ($hargaBahanPerKg <= 0 && $hargaBahanPerMeter <= 0) {
                $hargaBahanPerKg = self::DEFAULT_HARGA_BAHAN;
            }
            if ($hargaBahanPerKg > 0) {
                $biayaBahan = $beratBahanTeoritis * $hargaBahanPerKg;
                $hargaBahanPerMeter = $kgPerMeter * $hargaBahanPerKg;
            } else {
                $biayaBahan = $totalPanjangBahan * $hargaBahanPerMeter;
                $hargaBahanPerKg = $kgPerMeter > 0 ? ($hargaBahanPerMeter / $kgPerMeter) : 0;
            }
        }

        $biayaBahanPerEtiket = $targetOrder > 0 ? ($biayaBahan / $targetOrder) : 0;

        // =====================================================================
        // C. BIAYA PROSES CETAK FLEXO
        // =====================================================================
        $kecepatanCetak = (float) ($data['kecepatan_cetak'] ?? self::DEFAULT_KECEPATAN_CETAK);
        if ($kecepatanCetak <= 0) {
            $kecepatanCetak = self::DEFAULT_KECEPATAN_CETAK;
        }
        $waktuSetup = isset($data['waktu_setup']) ? (float) $data['waktu_setup'] : self::DEFAULT_WAKTU_SETUP;
        $waktuCetak = $totalPanjangBahan / $kecepatanCetak; // menit
        $totalWaktuMesin = $waktuSetup + $waktuCetak;      // menit

        $tarifMesinPerJam = (float) ($data['tarif_mesin_per_jam'] ?? 0);
        $biayaMesin = ($totalWaktuMesin / 60.0) * $tarifMesinPerJam;
        $biayaTintaTotal = (float) ($data['biaya_tinta_total'] ?? 0);
        $biayaPlate = (float) ($data['biaya_plate'] ?? 0);
        $biayaTenagaKerja = (float) ($data['biaya_tenaga_kerja'] ?? 0);

        $biayaProsesCetakTotal = $biayaMesin + $biayaTintaTotal + $biayaPlate + $biayaTenagaKerja;
        $biayaProsesPerEtiket = $targetOrder > 0 ? ($biayaProsesCetakTotal / $targetOrder) : 0;

        // =====================================================================
        // D. RINGKASAN HPP & MARGIN
        // =====================================================================
        $biayaOverhead = (float) ($data['biaya_overhead'] ?? 0);
        $totalBiayaOrder = $biayaBahan + $biayaProsesCetakTotal + $biayaOverhead;
        $hppPerEtiket = $targetOrder > 0 ? ($totalBiayaOrder / $targetOrder) : 0;

        $marginPersenRaw = (float) ($data['margin_persen'] ?? 0);
        $marginRatio = ($marginPersenRaw > 1) ? ($marginPersenRaw / 100.0) : $marginPersenRaw;
        // Sistem Markup: HPP * (1 + margin%)
        $hargaJualIndikatif = $hppPerEtiket * (1 + $marginRatio);

        return [
            // Section A: Layout (Satuan CM)
            'panjang_etiket_jadi' => round($panjangEtiket, 2),
            'lebar_etiket_jadi' => round($lebarEtiket, 2),
            'lebihan_cetak_panjang' => round($allowancePanjang, 2),
            'lebihan_cetak_lebar' => round($allowanceLebar, 2),
            'lebihan_lebar_bahan' => round($allowanceWeb, 2),
            'jumlah_etiket_sepanjang_repeat' => $etiketSepanjangRepeat,
            'repeat_cylinder' => round($repeatCylinder, 2),
            'jumlah_etiket_sepanjang_lebar' => $jalurMelintang,
            'lebar_bahan' => round($lebarBahan, 2),
            'panjang_cetak_pitch' => round($panjangCetakPitch, 2),
            'panjang_kebutuhan_per_repeat' => round($panjangKebutuhanRepeat, 2),
            'sisa_repeat' => round($sisaRepeat, 2),
            'status_panjang_repeat' => $statusPanjangRepeat,

            // Section B: Bahan
            'target_order' => (int) $targetOrder,
            'etiket_per_repeat' => $etiketPerRepeat,
            'repeat_yang_dibutuhkan' => $repeatDibutuhkan,
            'panjang_produksi_teoritis' => round($panjangProduksiTeoritis, 2),
            'waste_setting' => round($wasteSetting, 2),
            'waste_tambahan_persen' => round($wastePersenRaw, 2),
            'waste_tambahan_meter' => round($wasteTambahanMeter, 2),
            'total_panjang_bahan' => round($totalPanjangBahan, 2),
            'total_panjang_kebutuhan_bahan' => round($totalPanjangBahan, 2),
            'gramatur_kertas' => round($gramatur, 2),
            'berat_bahan_teoritis' => round($beratBahanTeoritis, 3),
            'harga_bahan_per_kg' => round($hargaBahanPerKg, 2),
            'harga_bahan_per_meter' => round($hargaBahanPerMeter, 2),
            'basis_harga_bahan' => $basisHarga,
            'biaya_bahan' => round($biayaBahan, 2),
            'biaya_bahan_per_etiket' => round($biayaBahanPerEtiket, 4),

            // Section C: Proses Cetak Flexo
            'kecepatan_cetak' => round($kecepatanCetak, 2),
            'waktu_setup' => round($waktuSetup, 2),
            'waktu_cetak' => round($waktuCetak, 2),
            'total_waktu_mesin' => round($totalWaktuMesin, 2),
            'tarif_mesin_per_jam' => round($tarifMesinPerJam, 2),
            'biaya_mesin' => round($biayaMesin, 2),
            'biaya_tinta_total' => round($biayaTintaTotal, 2),
            'biaya_plate' => round($biayaPlate, 2),
            'biaya_tenaga_kerja' => round($biayaTenagaKerja, 2),
            'biaya_proses_cetak_total' => round($biayaProsesCetakTotal, 2),
            'biaya_proses_per_etiket' => round($biayaProsesPerEtiket, 4),

            // Section D: Ringkasan HPP & Margin
            'biaya_overhead' => round($biayaOverhead, 2),
            'total_biaya_order' => round($totalBiayaOrder, 2),
            'terbilang_total_biaya' => MoneyHelper::terbilang($totalBiayaOrder),
            'hpp_per_etiket' => round($hppPerEtiket, 2),
            'margin_persen' => round($marginPersenRaw, 2),
            'harga_jual_indikatif' => round($hargaJualIndikatif, 2),
        ];
    }
}
