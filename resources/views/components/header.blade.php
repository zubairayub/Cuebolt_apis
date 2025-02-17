<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CueBolt - Trading Marketplace</title>
    <link rel="stylesheet" href="{{ asset(path: 'css/tailwind.min.css') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#FFD700',
                        secondary: '#1A1A1A',
                        dark: '#0D0D0D'
                    }
                }
            }
        }
    </script>
    <link href="https://unpkg.com/lucide-css" rel="stylesheet">
</head>