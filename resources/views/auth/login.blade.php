<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="mb-5">
        <h1 class="text-lg font-semibold text-gray-900">Pilih jenis akun</h1>
        <p class="mt-1 text-sm text-gray-600">Pilih panel sesuai dengan role akun Anda.</p>
    </div>

    <nav class="space-y-3" aria-label="Pilih panel login">
        <a href="{{ route('login.admin') }}" class="flex w-full items-center justify-between rounded-md bg-gray-800 px-4 py-3 text-sm font-semibold text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
            <span>Login Admin</span>
            <span aria-hidden="true">&rarr;</span>
        </a>

        <a href="{{ route('login.karyawan') }}" class="flex w-full items-center justify-between rounded-md border border-gray-300 bg-white px-4 py-3 text-sm font-semibold text-gray-800 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
            <span>Login Karyawan</span>
            <span aria-hidden="true">&rarr;</span>
        </a>
    </nav>

    <p class="text-center text-sm text-gray-600 mt-6">
        Belum punya akun?
        <a class="underline font-semibold text-indigo-600 hover:text-indigo-800 rounded focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('register') }}">
            Daftar di sini
        </a>
    </p>
</x-guest-layout>
