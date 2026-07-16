<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OurClass - Splash Screen</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <!-- Redirect to login after 2 seconds -->
    <meta http-equiv="refresh" content="2;url={{ route('login') }}">
</head>
<body>

    <div class="splash-container">
        <div class="splash-logo-wrapper">
            <img src="{{ asset('images/logo.png') }}" class="splash-logo" alt="OurClass Logo">
            <h1 class="splash-title">OurClass</h1>
            <span class="splash-loader"></span>
        </div>
    </div>

    <script>
        // Check user preferences for dark theme
        const currentTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', currentTheme);
    </script>
</body>
</html>
