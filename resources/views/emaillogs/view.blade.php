<x-app-layout>
    <!-- Page Header -->
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="fw-semibold fs-4 text-dark mb-0">
                <i class="bi bi-envelope-open me-2"></i> {{ __('Email Log Details') }}
            </h2>
            <a href="{{ route('email-logs.index') }}" class="btn btn-outline-secondary btn-sm ms-2">
                <i class="bi bi-arrow-left me-1"></i> Back to Logs
            </a>
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
            <a class="btn-link text-decoration-none" href="{{ route('email-logs.index') }}">
                Email Logs
            </a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">
            Log #{{ $emailLog->id }}
        </li>
    </x-slot>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Email Details</h5>
            <p class="text-muted mb-0 small">Detailed view of sent email record</p>
        </div>

        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="mb-3">
                        <h6 class="text-muted small mb-1">Recipient</h6>
                        <p class="mb-0">{{ $emailLog->to }}</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <h6 class="text-muted small mb-1">Subject</h6>
                        <p class="mb-0">{{ $emailLog->subject }}</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <h6 class="text-muted small mb-1">Status</h6>
                        <span class="badge bg-{{ $emailLog->status === consthelper('EmailLog::STATUS_SENT') ? 'success' : 'danger' }}">
                            {{ ucfirst($emailLog->status) }}
                        </span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <h6 class="text-muted small mb-1">Sent At</h6>
                        <p class="mb-0">{{ $emailLog->created_at->format('d M Y, H:i') }}</p>
                    </div>
                </div>
            </div>

            <hr>

            <div class="mb-3">
                <h6 class="text-muted small mb-2">Email Content</h6>
                <div class="border p-3 bg-light rounded" style="white-space: pre-wrap;">
                    {!! $emailLog->body !!}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>