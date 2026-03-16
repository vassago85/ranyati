<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login — Ranyati</title>
    <link rel="icon" href="{{ asset('ranyati-icon.png') }}" type="image/png">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet" />
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: #0a0f1a;
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            padding: 24px;
        }
        .login-card {
            width: 100%; max-width: 380px;
            background: linear-gradient(180deg, rgba(15,25,50,0.9) 0%, rgba(10,18,35,0.95) 100%);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: 16px; padding: 40px 32px;
        }
        .logo { text-align: center; margin-bottom: 32px; }
        .logo img { height: 28px; width: auto; opacity: 0.7; }
        .logo p { margin-top: 8px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.15em; color: rgba(255,255,255,0.2); }
        label { display: block; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; color: rgba(255,255,255,0.4); margin-bottom: 6px; }
        input[type="email"], input[type="password"] {
            width: 100%; padding: 12px 14px; border-radius: 10px;
            border: 1px solid rgba(255,255,255,0.1); background: rgba(255,255,255,0.04);
            color: #fff; font-size: 14px; font-family: 'Inter', system-ui, sans-serif;
            outline: none; transition: border-color 0.2s, box-shadow 0.2s;
        }
        input:focus { border-color: rgba(245,130,32,0.5); box-shadow: 0 0 0 3px rgba(245,130,32,0.1); }
        input::placeholder { color: rgba(255,255,255,0.2); }
        .form-group { margin-bottom: 20px; }
        .remember { display: flex; align-items: center; gap: 8px; margin-bottom: 24px; }
        .remember input[type="checkbox"] { width: 16px; height: 16px; accent-color: #F58220; }
        .remember label { margin: 0; font-size: 13px; text-transform: none; letter-spacing: 0; color: rgba(255,255,255,0.4); cursor: pointer; }
        button {
            width: 100%; padding: 12px; border-radius: 10px; border: none;
            background: linear-gradient(135deg, #F58220 0%, #d46f16 100%);
            color: #fff; font-size: 14px; font-weight: 700; font-family: 'Inter', system-ui, sans-serif;
            cursor: pointer; transition: all 0.2s;
            box-shadow: 0 2px 12px -2px rgba(245,130,32,0.4);
        }
        button:hover { transform: translateY(-1px); box-shadow: 0 6px 20px -4px rgba(245,130,32,0.5); }
        .error {
            margin-bottom: 20px; padding: 10px 14px; border-radius: 8px;
            background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.2);
            color: #ef4444; font-size: 13px; text-align: center;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="logo">
            <img src="{{ asset('logo-ranyatigroup-white_text.png') }}" alt="Ranyati Group" />
            <p>Admin Panel</p>
        </div>

        @if($errors->any())
            <div class="error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('admin.login.submit') }}">
            @csrf
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="you@ranyati.co.za" required autofocus value="{{ old('email') }}" />
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required />
            </div>
            <div class="remember">
                <input type="checkbox" id="remember" name="remember" />
                <label for="remember">Remember me</label>
            </div>
            <button type="submit">Sign In</button>
        </form>
    </div>
</body>
</html>
