<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 50px;
        }
        h1 {
            color: #333;
        }
    </style>
</head>
<body>
    <h1 class="text-4xl font-bold mb-4">{{ $title ?? '404 - Page Not Found' }}</h1>
    <p class="mb-8">{{ $message ?? 'Sorry, the page you are looking for could not be found.' }}</p>
    <a href="{{ url('/') }}">Return to Home</a>
</body>
</html> 