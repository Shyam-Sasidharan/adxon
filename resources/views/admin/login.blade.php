<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Adxon CMS Login</title>
    <link rel="icon" href="{{ asset('assets/brand/adxon-mark-dark.png') }}">
    <link rel="stylesheet" href="{{ asset('assets/styles.css') }}">
</head>
<body class="admin-body login-body">
    <main class="login-card">
        <a class="brand" href="{{ route('home') }}"><img class="brand-logo" src="{{ asset('assets/brand/adxon-full-light.png') }}" alt="Adxon CMS"></a>
        <h1>Admin Login</h1>
        @error('login')
            <p class="admin-alert">{{ $message }}</p>
        @enderror
        <form method="post" action="{{ route('admin.login.post') }}" class="admin-form">
            @csrf
            <label>Username <input name="username" required autofocus></label>
            <label>Password <input name="password" type="password" required></label>
            <button class="button button-primary" type="submit">Sign In</button>
        </form>
        <p class="login-hint">Default: admin / adxon@123</p>
    </main>
</body>
</html>
