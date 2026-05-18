<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Mundial 26 Predict') }}</title>
    </head>
    <body>
        <script>
            window.localStorage.setItem('mundial_token', @json($token));
            window.location.replace(@json($redirectTo));
        </script>
    </body>
</html>
