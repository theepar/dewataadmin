<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atur Ulang Kata Sandi - Dewata Property</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        /* Menggunakan font Inter sebagai default */
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-gray-900">

    <!-- Container Utama -->
    <div class="flex min-h-screen items-center justify-center p-4">

        <!-- Kartu Form -->
        <div class="w-full max-w-md rounded-2xl bg-gray-800 p-8 shadow-2xl shadow-yellow-500/10 md:p-10">

            <!-- Header -->
            <div class="mb-8 text-center">
                <h1 class="text-3xl font-bold text-white">Dewata Property</h1>
                <p class="mt-2 text-gray-400">Buat kata sandi baru Anda</p>
            </div>

            <!-- Form -->
            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <!-- Pesan Error Validasi -->
                @if ($errors->any())
                    <div class="mb-4 rounded-lg bg-red-900/50 p-4 text-sm text-red-400" role="alert">
                        <span class="font-medium">Oops! Terjadi kesalahan.</span>
                        <ul class="mt-1.5 list-inside list-disc">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Pesan Status -->
                @if (session('status'))
                    <div class="mb-4 rounded-lg bg-green-900/50 p-4 text-sm text-green-400" role="alert">
                        <span class="font-medium">Sukses!</span> {{ session('status') }}
                    </div>
                @endif

                <!-- Input Email -->
                <div class="mb-4">
                    <label for="email" class="mb-2 block text-sm font-medium text-gray-300">Alamat Email</label>
                    <input id="email" type="email" name="email" value="{{ $email ?? old('email') }}" required
                        autofocus placeholder="anda@email.com"
                        class="block w-full rounded-lg border border-gray-600 bg-gray-700 p-3 text-white placeholder-gray-400 transition focus:border-yellow-500 focus:outline-none focus:ring-2 focus:ring-yellow-500">
                </div>

                <!-- Input Password Baru -->
                <div class="mb-4">
                    <label for="password" class="mb-2 block text-sm font-medium text-gray-300">Kata Sandi Baru</label>
                    <input id="password" type="password" name="password" required placeholder="••••••••"
                        class="block w-full rounded-lg border border-gray-600 bg-gray-700 p-3 text-white placeholder-gray-400 transition focus:border-yellow-500 focus:outline-none focus:ring-2 focus:ring-yellow-500">
                </div>

                <!-- Input Konfirmasi Password -->
                <div class="mb-4">
                    <label for="password_confirmation" class="mb-2 block text-sm font-medium text-gray-300">Konfirmasi
                        Kata Sandi</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required
                        placeholder="••••••••"
                        class="block w-full rounded-lg border border-gray-600 bg-gray-700 p-3 text-white placeholder-gray-400 transition focus:border-yellow-500 focus:outline-none focus:ring-2 focus:ring-yellow-500">
                </div>

                <!-- Tombol Submit -->
                <div class="mt-6">
                    <button type="submit"
                        class="w-full rounded-lg bg-yellow-500 px-5 py-3 text-center text-base font-bold text-gray-900 shadow-md transition-transform duration-150 ease-in-out hover:scale-[1.02] hover:bg-yellow-600 focus:outline-none focus:ring-4 focus:ring-yellow-300">
                        Atur Ulang Kata Sandi
                    </button>
                </div>
            </form>

        </div>
    </div>
</body>

</html>
