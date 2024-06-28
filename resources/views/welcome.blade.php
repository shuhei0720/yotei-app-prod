<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Family Schedule App</title>
    @vite('resources/css/app.css')
</head>
<body>
    <div class="min-h-screen flex items-center justify-center">
        <div class="text-center p-4">
            <h1 class="text-3xl sm:text-5xl font-bold mb-8">Welcome to the Family Schedule App</h1>
            <div class="flex flex-col sm:flex-row justify-center space-y-4 sm:space-y-0 sm:space-x-4">
                <a href="{{ route('login') }}" class="bg-blue-500 text-white px-4 py-2 rounded">Login</a>
                <a href="{{ route('register') }}" class="bg-green-500 text-white px-4 py-2 rounded">Register</a>
            </div>
        </div>
    </div>
</body>
</html>