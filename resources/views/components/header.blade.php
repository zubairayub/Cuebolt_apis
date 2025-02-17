<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CueBolt - Trading Marketplace</title>
    <link rel="stylesheet" href="{{ asset(path: 'css/tailwind.min.css') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>

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
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        lucide.createIcons();
    });
</script>

    <style>
/* Mobile menu styles */
      .mobile-menu {
        transform: translateX(-100%);
        transition: transform 0.3s ease-in-out;
      }
      .mobile-menu.show {
        transform: translateX(0);
      }
    </style>
    <link href="https://unpkg.com/lucide-css" rel="stylesheet">
    
</head>