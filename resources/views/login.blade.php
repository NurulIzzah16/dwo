<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>

    @vite('resources/css/app.css')

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
    </style>
</head>

<body class="bg-gray-100 font-[Poppins] min-h-screen flex items-center justify-center text-gray-800">

    <div class="w-full max-w-md bg-white rounded-xl shadow-sm p-8">

        {{-- Header --}}
        <div class="text-center mb-6">
            <h2 class="text-2xl font-semibold">Login Dashboard</h2>
        </div>

        {{-- Error --}}
        @if ($errors->any())
            <div class="mb-4 text-sm text-red-600 bg-red-50 px-4 py-2 rounded">
                {{ $errors->first() }}
            </div>
        @endif

        {{-- Form --}}
        <form method="POST" action="/login" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium mb-1">Email</label>
                <input
                    type="email"
                    name="email"
                    required
                    class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-[#1b2691]"
                >
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Password</label>
                <input
                    type="password"
                    name="password"
                    required
                    class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-[#1b2691]"
                >
            </div>

            <button
                type="submit"
                class="w-full bg-[#1b2691] text-white py-2 rounded-md font-medium hover:bg-[#141d6f] transition">
                Login
            </button>
        </form>

        {{-- Footer --}}
        <p class="text-xs text-center text-gray-500 mt-6">
            KELOMPOK 6 · {{ date('Y') }}
        </p>
    </div>

</body>
</html>
