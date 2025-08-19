<x-app-layout>
    <!-- Page Header -->
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="fw-semibold fs-4 text-dark mb-0">
                <i class="bi bi-eye me-2"></i>
                {{ __('API Log Details') }}
            </h2>
        </div>
    </x-slot>

    <!-- Breadcrumbs -->
    <x-slot name="breadcrumbs">
        <li class="breadcrumb-item">
            <a class="btn-link text-decoration-none" href="{{ route('dashboard') }}">
                <i class="bi bi-house-door me-1"></i> Dashboard
            </a>
        </li>
        <li class="breadcrumb-item">
            <a class="btn-link text-decoration-none" href="{{ route('api-logs.index') }}">
                <i class="bi bi-activity me-1"></i> API Logs
            </a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">
            Log #{{ $apiLog->id }}
        </li>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="card border-0 shadow-lg rounded-lg overflow-hidden">

                <!-- Header -->
                <div class="card-header bg-white py-4 d-flex justify-content-between align-items-center border-bottom">
                    <div>
                        <h5 class="mb-0 text-primary">
                            <i class="bi bi-eye me-2"></i>
                            API Log Details #{{ $apiLog->id }}
                        </h5>
                        <p class="text-muted mb-0 small">Detailed information for this API log entry</p>
                    </div>
                    <div>
                        <a href="{{ route('api-logs.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-left me-1"></i> Back to Logs
                        </a>
                    </div>
                </div>

                <!-- Body -->
                <div class="card-body">

                    <!-- Basic Info -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <strong class="text-muted d-block small">Log ID:</strong>
                            <span class="text-dark">{{ $apiLog->id }}</span>
                        </div>
                        <div class="col-md-3">
                            <strong class="text-muted d-block small">User:</strong>
                            @if ($apiLog->user)
                                <span class="text-dark">{{ $apiLog->user->name }} <small class="text-muted">({{ $apiLog->user->email }})</small></span>
                            @else
                                <span class="text-muted fst-italic">Guest / N/A</span>
                            @endif
                        </div>
                        <div class="col-md-3">
                            <strong class="text-muted d-block small">Status Code:</strong>
                            <span class="badge bg-{{ $apiLog->response_status >= 200 && $apiLog->response_status < 300 ? 'success' : 'danger' }}">
                                {{ $apiLog->response_status }}
                            </span>
                        </div>
                        <div class="col-md-3">
                            <strong class="text-muted d-block small">Date & Time:</strong>
                            <span class="text-dark">
                                {{ $apiLog->created_at->format('Y-m-d H:i:s') }}
                                ({{ $apiLog->created_at->diffForHumans() }})
                            </span>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Technical Info -->
                    <div class="row mb-4">
                        <div class="col-md-2">
                            <strong class="text-muted d-block small">HTTP Method:</strong>
                            <span class="badge bg-dark">{{ strtoupper($apiLog->method ?? '-') }}</span>
                        </div>
                        <div class="col-md-5">
                            <strong class="text-muted d-block small">Endpoint:</strong>
                            <span class="text-dark" style="word-break: break-all;">{{ $apiLog->endpoint ?? '-' }}</span>
                        </div>
                        <div class="col-md-3">
                            <strong class="text-muted d-block small">IP Address:</strong>
                            <span class="text-dark">{{ $apiLog->ip_address ?? 'N/A' }}</span>
                        </div>
                        <div class="col-md-2">
                            <strong class="text-muted d-block small">Execution Time (s):</strong>
                            <span class="text-dark">{{ number_format($apiLog->execution_time, 4) }}</span>
                        </div>
                    </div>

                    @if ($apiLog->request_headers)
                        <div class="mb-4">
                            <strong class="text-muted d-block small mb-1">Request Headers:</strong>
                            <div class="bg-light p-3 rounded" style="max-height: 300px; overflow-y: auto;">
                                <pre class="mb-0 small">{{ json_encode($apiLog->request_headers, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
                            </div>
                        </div>
                    @endif

                    <hr class="my-4">

                    <!-- Request Data -->
                    <div class="mb-4">
                        <strong class="text-muted d-block small mb-1">Request Data:</strong>
                        @if ($apiLog->request_body)
                            <div class="bg-light p-3 rounded" style="max-height: 400px; overflow-y: auto;">
                                <pre class="mb-0 small">{{ json_encode($apiLog->request_body, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
                            </div>
                        @else
                            <p class="text-muted fst-italic">No request data recorded.</p>
                        @endif
                    </div>

                    <!-- Response Data -->
                    <div class="mb-4">
                        <strong class="text-muted d-block small mb-1">Response Data:</strong>
                        @if ($apiLog->response_body)
                            <div class="bg-light p-3 rounded" style="max-height: 400px; overflow-y: auto;">
                                <pre class="mb-0 small">{{ json_encode($apiLog->response_body, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
                            </div>
                        @else
                            <p class="text-muted fst-italic">No response data recorded.</p>
                        @endif
                    </div>
                </div>

                <!-- Footer -->
                <div class="card-footer bg-white text-end py-4">
                    <a href="{{ route('api-logs.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i> Back to Logs
                    </a>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
