<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Adxon CMS Login</title>
    <link rel="icon" href="{{ asset('assets/brand/adxon-mark-dark.png') }}">
    <link rel="stylesheet" href="{{ asset('assets/styles.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/platform.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/workspace.css') }}">
</head>
<body class="admin-body light-mode login-body">
    <main class="login-card">
        <a class="brand" href="{{ route('home') }}"><img class="brand-logo" src="{{ asset('assets/brand/adxon-full-dark.png') }}" alt="Adxon"></a>
        <h1>Welcome back.</h1>
        <p>Sign in to your Adxon workspace.</p>
        @error('login')
            <p class="admin-alert">{{ $message }}</p>
        @enderror
        <form method="post" action="{{ route('admin.login.post') }}" class="admin-form">
            @csrf
            <label>Username or email <input name="username" value="{{ old('username') }}" autocomplete="username" required autofocus></label>
            <label>Password <input name="password" type="password" autocomplete="current-password" required></label>
            <button class="button button-primary" type="submit">Sign In</button>
        </form>
    </main>
</body>
</html>
