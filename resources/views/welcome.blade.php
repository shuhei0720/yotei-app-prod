<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yoteiapp</title>
    @vite('resources/css/app.css')

    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#000000">
    <link rel="icon" type="image/png" sizes="192x192" href="/path/to/icon-192x192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="/path/to/icon-512x512.png">
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Raleway:wght@300;400;700&display=swap" rel="stylesheet">

    <style>
        body {
            background-color: #f0f4f8;
            font-family: 'Raleway', sans-serif;
            overflow: hidden;
        }
        .logo {
            width: 150px;
            height: 150px;
            margin-bottom: 20px;
        }
        .welcome-text {
            font-size: 3rem;
            font-weight: bold;
            margin-bottom: 40px;
            color: #4a90e2;
            font-family: 'Pacifico', cursive;
        }
        .welcome-text span {
            color: #e94e77;
        }
        .button-container {
            display: flex;
            justify-content: center;
            gap: 20px;
        }
        .button {
            background-color: #4a90e2;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 1.25rem;
            transition: background-color 0.3s;
        }
        .button:hover {
            background-color: #357ab7;
        }
        .button.register {
            background-color: #5cb85c;
        }
        .button.register:hover {
            background-color: #4cae4c;
        }
        .navbar-nav {
            margin-top: 40px;
            list-style-type: none;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }
        .nav-item {
            display: block;
        }
        .nav-link {
            color: #4a90e2;
            text-decoration: none;
            font-size: 1rem;
            transition: color 0.3s;
        }
        .nav-link:hover {
            color: #357ab7;
        }
    </style>
</head>
<body>
    <div class="min-h-screen flex flex-col items-center justify-center text-center p-4">
        <img src="/path/to/icon-192x192.png" alt="App Icon" class="logo">
        <div class="welcome-text">Welcome to <span>Yoteiapp</span></div>
        <div class="flex items-center justify-end mt-4">
            <a href="{{ route('auth.line') }}" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-full flex items-center justify-center transition duration-300 ease-in-out w-full" id="line-login">
                <img src="{{ asset('img/line.png') }}" alt="LINE Logo" class="w-6 h-6 mr-2">
                {{ __('LINEアカウントでログイン') }}
            </a>
        </div>
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" href="{{ route('privacy.policy') }}">プライバシーポリシー</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('terms.service') }}">利用規約</a>
            </li>
        </ul>
    </div>

    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/service-worker.js').then(function(registration) {
                    console.log('ServiceWorker registration successful with scope: ', registration.scope);
                }, function(error) {
                    console.log('ServiceWorker registration failed: ', error);
                });
            });
        }

        window.addEventListener('load', function() {
            if (window.location.hash.includes('callback=')) {
                const intendedUrl = decodeURIComponent(window.location.hash.split('callback=')[1]);
                window.location.hash = '';
                window.location.href = intendedUrl;
            }
        });
    </script>
</body>
</html>