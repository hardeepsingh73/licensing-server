<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="description" content="{{ config('app.description', 'Laravel Application') }}" />

    <title>{{ isset($title) ? "$title | " : '' }}{{ setting('site_name', 'Laravel') }}</title>

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon" />
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">

    <!-- Bootstrap & Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @isset($style)
        {{ $style }}
    @endisset
    @stack('style')
</head>

<body class="font-sans bg-light min-vh-100 d-flex flex-column">

    <div class="d-flex flex-column flex-grow-1">

        {{-- Top Navigation --}}
        @include('layouts.navigation')

        <div class="d-flex flex-grow-1">

            {{-- Sidebar --}}
            @include('layouts.sidebar')

            {{-- Main Content --}}
            <main id="main-content" class="flex-grow-1 p-4 bg-white shadow-sm rounded-3 overflow-auto" tabindex="-1">

                {{-- Page Header --}}
                @isset($header)
                    <header class="border-bottom pb-3 mb-4">
                        <div
                            class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
                            <h1 class="h4 mb-2 mb-md-0 fw-bold text-dark">{{ $header }}</h1>
                            @isset($breadcrumbs)
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb small mb-0">
                                        {{ $breadcrumbs }}
                                    </ol>
                                </nav>
                            @endisset
                        </div>
                    </header>
                @endisset

                {{-- Flash Messages --}}
                <div class="container-fluid px-0">
                    <div class="row justify-content-center">
                        <div class="col-xxl-10">
                            {{-- Page Slot Content --}}
                            {{ $slot }}
                        </div>
                    </div>
                </div>
            </main>
        </div>

        {{-- Footer --}}
        @include('layouts.footer')
    </div>
    @isset($script)
        {{ $script }}
    @endisset
    @stack('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            //  Success Alert
            @if (session()->has('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: "{{ session('success') }}",
                    showConfirmButton: true,
                    timer: 3000
                });
            @endif

            //  Error Alert (single messages from session)
            @if (session()->has('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: "{{ session('error') }}",
                    showConfirmButton: true
                });
            @endif

            // ️ Warning Alert
            @if (session()->has('warning'))
                Swal.fire({
                    icon: 'warning',
                    title: 'Warning',
                    text: "{{ session('warning') }}",
                    showConfirmButton: true
                });
            @endif

            //  Validation Errors (from $errors)
            @if ($errors->any())
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    html: `{!! implode('<br>', $errors->all()) !!}`,
                    showConfirmButton: true
                });
            @endif

            $(document).ready(function() {
                // Initialize tooltips
                $('[data-bs-toggle="tooltip"]').each(function() {
                    new bootstrap.Tooltip(this);
                });

                // Show Filters if search params exist
                @if (request()->hasAny([
                        'user_id',
                        'login_at',
                        'endpoint',
                        'method',
                        'ip_address',
                        'key',
                        'group',
                        'name',
                        'email',
                        'role',
                    ]))
                    new bootstrap.Collapse($('#listSearchForm')[0]).show();
                @endif
            });
        });
    </script>
</body>

</html>
