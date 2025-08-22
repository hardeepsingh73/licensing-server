<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <title>{{ setting('site_name', config('app.name')) }}</title>

    <!-- Your CSS and JS via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @isset($style)
        {{ $style }}
    @endisset
    @stack('styles')
</head>

<body class="font-sans bg-light">

    <div class="d-flex min-vh-100 flex-column">

        @include('layouts.navigation')

        <div class="d-flex flex-grow-1">
            <main class="flex-grow-1">
                {{ $slot }}
            </main>
        </div>

        @include('layouts.footer')
    </div>
    @isset($script)
        {{ $script }}
    @endisset
    @stack('scripts')

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            $(document).ready(function() {
                // Toggle password visibility for both fields
                $('.toggle-password').on('click', function() {
                    const $input = $(this).closest('.input-group').find('input');
                    const $icon = $(this).find('i');

                    if ($input.attr('type') === 'password') {
                        $input.attr('type', 'text');
                        $icon.removeClass('bi-eye').addClass('bi-eye-slash');
                    } else {
                        $input.attr('type', 'password');
                        $icon.removeClass('bi-eye-slash').addClass('bi-eye');
                    }
                });
            });
        });
    </script>
</body>

</html>
