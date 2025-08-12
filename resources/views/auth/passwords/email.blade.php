<!-- filepath: c:\Users\Deva\Desktop\Kodingan\Bisnis\DewataVillaManagement\Backend\resources\views\auth\passwords\email.blade.php -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss/dist/tailwind.min.css">
</head>

<body class="bg-gray-100">
    <div class="mx-auto mt-10 max-w-md rounded bg-white p-6 shadow">
        <h2 class="mb-4 text-xl font-bold">Reset Password</h2>
        @if (session('status'))
            <div class="mb-4 text-green-600">{{ session('status') }}</div>
        @endif
        <form method="POST" action="{{ route('password.email') }}">
            @csrf
            <div class="mb-4">
                <label for="email" class="block text-gray-700">Email</label>
                <input id="email" type="email" name="email" required autofocus
                    class="mt-1 w-full rounded border px-3 py-2">
                @error('email')
                    <span class="text-sm text-red-500">{{ $message }}</span>
                @enderror
            </div>
            <button type="submit" class="w-full rounded bg-blue-600 py-2 text-white hover:bg-blue-700">
                Kirim Link Reset Password
            </button>
        </form>
    </div>
</body>

</html>
