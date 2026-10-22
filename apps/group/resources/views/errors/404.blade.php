<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Page not found</title>
    <meta name="robots" content="noindex">
    <style>
        body { margin: 0; font-family: Inter, system-ui, sans-serif; background: #020810; color: #fff; }
        main { max-width: 32rem; margin: 0 auto; padding: 64px 20px; }
        a { color: #fff; min-height: 44px; display: inline-flex; align-items: center; }
    </style>
</head>
<body>
    <main>
        @if(str_starts_with(request()->getHost(), 'arms.'))
            <h1>We can't find that page</h1>
            <p>The firearm or page you asked for is not here. Browse the current stock instead.</p>
            <a href="{{ url('/') }}">Back to stock</a>
        @else
            <h1>Page not found</h1>
            <p>That page does not exist.</p>
            <a href="{{ url('/') }}">Back to the home page</a>
        @endif
    </main>
</body>
</html>
