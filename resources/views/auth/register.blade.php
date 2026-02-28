<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-green-100 via-blue-100 to-green-50">
        <div class="w-full max-w-md bg-white rounded-xl shadow-xl border border-gray-200">

            <!-- HEADER -->
            <div class="px-6 py-6 text-center border-b">
                <img src="{{ asset('images/ss.png') }}"
                     alt="Logo Perusahaan"
                     class="h-16 mx-auto mb-3">

                <h1 class="text-xl font-bold text-gray-800">
                    📝 Sistem Informasi PT TTC
                </h1>

                <p class="text-sm text-gray-500 mt-1">
                    Registrasi akun pengguna
                </p>
            </div>

            <!-- FORM -->
            <form method="POST" action="{{ route('register') }}" class="px-6 py-6 space-y-4">
                @csrf

                <!-- NAMA -->
                <div class="grid grid-cols-3 items-center gap-3">
                    <label class="text-sm font-medium text-gray-700">Nama</label>
                    <input type="text" name="name" required autofocus
                        value="{{ old('name') }}"
                        class="col-span-2 rounded-md border-gray-300 focus:border-green-500 focus:ring-green-200">
                </div>

                <!-- EMAIL -->
                <div class="grid grid-cols-3 items-center gap-3">
                    <label class="text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="email" required
                        value="{{ old('email') }}"
                        class="col-span-2 rounded-md border-gray-300 focus:border-green-500 focus:ring-green-200">
                </div>

                <!-- ROLE -->
                <div class="grid grid-cols-3 items-center gap-3">
                    <label class="text-sm font-medium text-gray-700">Role</label>
                    <select name="role" required
                        class="col-span-2 rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-200">
                        <option value="">-- Pilih Role --</option>
                        <option value="admin">Admin</option>
                        <option value="manajer">Manajer</option>
                        <option value="operator">Operator</option>
                    </select>
                </div>

                <!-- PASSWORD -->
                <div class="grid grid-cols-3 items-center gap-3">
                    <label class="text-sm font-medium text-gray-700">Password</label>
                    <input type="password" name="password" required
                        class="col-span-2 rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-200">
                </div>

                <!-- KONFIRMASI PASSWORD -->
                <div class="grid grid-cols-3 items-center gap-3">
                    <label class="text-sm font-medium text-gray-700">Konfirmasi</label>
                    <input type="password" name="password_confirmation" required
                        class="col-span-2 rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-200">
                </div>

                <!-- ERROR -->
                @if ($errors->any())
                    <div class="text-sm text-red-600">
                        {{ $errors->first() }}
                    </div>
                @endif

                <!-- BUTTON -->
                <button type="submit"
                    class="w-full bg-gradient-to-r from-green-600 to-blue-600 hover:from-green-700 hover:to-blue-700
                           text-white font-semibold py-2 rounded-md transition">
                    REGISTER
                </button>

                <!-- LINK LOGIN -->
                <p class="text-sm text-center text-gray-600">
                    Sudah punya akun?
                    <a href="{{ route('login') }}" class="text-blue-600 hover:underline">
                        Login
                    </a>
                </p>
            </form>

            <!-- FOOTER -->
            <div class="px-6 py-3 bg-gray-50 text-center text-xs text-gray-500 rounded-b-xl">
                © {{ date('Y') }} PT TTC • Sistem Manajemen Terpadu
            </div>

        </div>
    </div>
</x-guest-layout>
