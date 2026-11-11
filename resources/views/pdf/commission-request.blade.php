<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Commission Request - {{ $comm_no ?? $commission_no ?? '-' }}</title>
    <style>
        @page {
            margin: 28px 36px 28px 36px;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10.5pt;
            color: #000000;
            line-height: 1.35;
        }

        /* HEADER */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }

        .header-table td {
            vertical-align: top;
            padding: 0;
        }

        .company-name {
            font-size: 11pt;
            font-weight: bold;
            color: #000000;
            margin-bottom: 2px;
        }

        .company-address {
            font-size: 9.5pt;
            color: #111111;
            line-height: 1.4;
        }

        /* METADATA */
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
            font-size: 10.5pt;
        }

        .meta-table td {
            padding: 2px 0;
            vertical-align: middle;
        }

        .meta-label-left {
            width: 14%;
            color: #000;
        }

        .meta-val-left {
            width: 44%;
            font-weight: bold;
            color: #000;
        }

        .meta-label-right {
            width: 18%;
            color: #000;
        }

        .meta-val-right {
            width: 24%;
            font-weight: bold;
            color: #000;
        }

        /* TITLE BANNER */
        .title-box {
            width: 100%;
            border: 1.5px solid #000000;
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            letter-spacing: 0.3px;
            padding: 6px 0;
            margin-bottom: 22px;
        }

        /* CALCULATION TABLE (Courier New matching Excel DOC_Comm) */
        .calc-wrapper {
            width: 66%;
            margin: 0 auto 24px auto;
            border-collapse: collapse;
            font-family: "Courier New", Courier, monospace;
            font-size: 11pt;
        }

        .calc-wrapper td {
            padding: 3px 4px;
            vertical-align: middle;
        }

        .calc-wrapper tr.underline-row td {
            border-bottom: 1.5px solid #000000;
            padding-bottom: 4px;
        }

        .calc-wrapper tr.after-line td {
            padding-top: 5px;
        }

        .calc-bold {
            font-weight: bold;
        }

        /* SIGNATURE BLOCK */
        .sig-top-line {
            width: 100%;
            border-top: 1.5px solid #000000;
            margin-top: 18px;
        }

        .sig-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10.5pt;
            margin-top: 3px;
            margin-bottom: 58px;
        }

        .sig-table td {
            width: 33.33%;
            vertical-align: top;
            padding: 0;
        }

        .double-bottom-line {
            width: 100%;
            border-top: 1px solid #000000;
            border-bottom: 1.5px solid #000000;
            height: 3px;
        }
    </style>
</head>
<body>

    <!-- TOP HEADER -->
    <table class="header-table">
        <tr>
            <td style="width: 70%;">
                <div class="company-name">PT Teknografi Tri Cawanaska</div>
                <div class="company-address">
                    Pergudangan Sentral Singosari<br>
                    Ardimulyo, Singosari, Malang<br>
                    teknografitricawanaska@gmail.com<br>
                    +62812 3339 0799
                </div>
            </td>
            <td style="width: 30%; text-align: right;">
                @if(!empty($logoBase64))
                    <img src="{{ $logoBase64 }}" style="max-height: 75px; width: auto;" alt="TTC Logo">
                @elseif(file_exists(public_path('images/logo.png')))
                    <img src="{{ public_path('images/logo.png') }}" style="max-height: 75px; width: auto;" alt="TTC Logo">
                @endif
            </td>
        </tr>
    </table>

    <!-- METADATA -->
    <table class="meta-table">
        <tr>
            <td class="meta-label-left">Order ID</td>
            <td class="meta-val-left">{{ $order_id ?? '-' }}</td>
            <td class="meta-label-right">Commission No.</td>
            <td class="meta-val-right">{{ $comm_no ?? $commission_no ?? '-' }}</td>
        </tr>
        <tr>
            <td class="meta-label-left">Order Date</td>
            <td class="meta-val-left">{{ $order_date ?? '-' }}</td>
            <td class="meta-label-right">Request No.</td>
            <td class="meta-val-right">{{ $request_date ?? '1 Oct 2026' }}</td>
        </tr>
        <tr>
            <td class="meta-label-left">Customer</td>
            <td class="meta-val-left">{{ $customer ?? '-' }}</td>
            <td class="meta-label-right">Order Summary</td>
            <td class="meta-val-right">{{ $summary ?? $order_summary ?? '-' }}</td>
        </tr>
    </table>

    <!-- TITLE BANNER -->
    <div class="title-box">
        COMMISSION REQUEST
    </div>

    @php
        $valRevenue = (float) ($revenue ?? 0);
        $valRevComm = (float) ($revenue_commission ?? $revenue_comm ?? 0);
        $valTaxComm = (float) ($tax_commission ?? $tax_comm ?? 0);
        $valRefComm = (float) ($referral_commission ?? $referral_comm ?? 0);
        $valRevAfAdj = (float) ($revenue_after_adj ?? $rev_after_adj ?? 0);
        $valSalesComm = (float) ($sales_commission ?? $sales_comm ?? 0);
        $valTotalComm = (float) ($total_commission ?? $total_comm ?? 0);
    @endphp

    <!-- CALCULATION BODY -->
    <table class="calc-wrapper">
        <tr>
            <td style="width: 62%; text-align: left;">Revenue</td>
            <td style="width: 38%; text-align: right;">{{ number_format($valRevenue, 0, '.', ',') }}</td>
        </tr>
        <tr>
            <td style="text-align: left;">Revenue Commission</td>
            <td style="text-align: right;">{{ $valRevComm > 0 ? number_format($valRevComm, 0, '.', ',') : '-' }}</td>
        </tr>
        <tr class="underline-row">
            <td style="text-align: left;">Tax Commission (11%)</td>
            <td style="text-align: right;">{{ $valTaxComm > 0 ? number_format($valTaxComm, 0, '.', ',') : '' }}</td>
        </tr>
        <tr class="after-line">
            <td style="text-align: left;">Referral Commission (Rp)</td>
            <td style="text-align: right;" class="calc-bold">{{ $valRefComm > 0 ? number_format($valRefComm, 0, '.', ',') : '-' }}</td>
        </tr>

        <tr>
            <td colspan="2" style="height: 22px;">&nbsp;</td>
        </tr>

        <tr>
            <td style="text-align: left;">Revenue After Adjustment</td>
            <td style="text-align: right;">{{ number_format($valRevAfAdj, 0, '.', ',') }}</td>
        </tr>
        <tr class="underline-row">
            <td style="text-align: left;">Sales Commission (2%)</td>
            <td style="text-align: right;" class="calc-bold">{{ number_format($valSalesComm, 0, '.', ',') }}</td>
        </tr>
        <tr class="after-line">
            <td style="text-align: left;">Total Commission (Rp)</td>
            <td style="text-align: right;">{{ number_format($valTotalComm, 0, '.', ',') }}</td>
        </tr>
    </table>

    <!-- SIGNATURE BLOCK -->
    <div class="sig-top-line"></div>
    <table class="sig-table">
        <tr>
            <td style="text-align: left;">(Prepared by)</td>
            <td style="text-align: center;">(Checked by)</td>
            <td style="text-align: right; padding-right: 28px;">(Approved by)</td>
        </tr>
    </table>
    <div class="double-bottom-line"></div>

</body>
</html>

