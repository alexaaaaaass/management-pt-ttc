<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-green-100 via-blue-100 to-green-50">
        <div class="w-full max-w-md bg-white rounded-xl shadow-xl border border-gray-200">
            
            <!-- HEADER -->
            <div class="px-6 py-6 text-center border-b">
                <img src="{{ asset('images/ss.png') }}" 
                     alt="Logo Perusahaan"
                     class="h-16 mx-auto mb-3">
              <h1 class="text-xl font-bold text-gray-800">
    🔐 Sistem Informasi PT TTC
</h1>

                <p class="text-sm text-gray-500 mt-1">
                    Login untuk mengakses sistem
                    <br>
                    PT Teknografi Tri Cawanaska
                </p>
            </div>

            <!-- FORM -->

            <form method="POST" action="{{ route('login') }}" class="px-6 py-6 space-y-4">
                @csrf
                @if ($errors->any())
    <div class="mb-4 rounded-md bg-red-50 border border-red-200 p-3 text-sm text-red-700">
        {{ $errors->first() }}
    </div>
@endif


                <!-- EMAIL -->
                <div class="grid grid-cols-3 items-center gap-3">
                    <label class="text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="email" required autofocus
       value="{{ old('email') }}"
       class="col-span-2 rounded-md border-gray-300 focus:border-green-500 focus:ring-green-200">

                </div>

                <!-- PASSWORD -->
                <div class="grid grid-cols-3 items-center gap-3">
                    <label class="text-sm font-medium text-gray-700">Password</label>
                    <input type="password" name="password" required
                        class="col-span-2 rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-200">
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

                <!-- REMEMBER -->
                <div class="flex items-center justify-between text-sm">
                    <label class="flex items-center gap-2 text-gray-600">
                        <input type="checkbox" name="remember" class="rounded">
                        Remember me
                    </label>

                    <a href="{{ route('password.request') }}"
                       class="text-blue-600 hover:underline">
                        Lupa password?
                    </a>
                </div>

                <!-- BUTTON -->
                <button type="submit"
                    class="w-full bg-gradient-to-r from-green-600 to-blue-600 hover:from-green-700 hover:to-blue-700
                           text-white font-semibold py-2 rounded-md transition">
                    LOGIN
                </button>
            </form>

            <!-- FOOTER -->
            <div class="px-6 py-3 bg-gray-50 text-center text-xs text-gray-500 rounded-b-xl">
                © {{ date('Y') }} PT Teknografi Tri Cawanaska • Sistem Manajemen 
            </div>
        </div>
    </div>
</x-guest-layout>
