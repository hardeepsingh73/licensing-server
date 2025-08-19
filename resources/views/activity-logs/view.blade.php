<x-app-layout>
    <!-- Page Header -->
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="fw-semibold fs-4 text-dark mb-0">
                <i class="bi bi-journal-text me-2"></i>
                {{ __('Activity Log Details') }}
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
            <a class="btn-link text-decoration-none" href="{{ route('activity-logs.index') }}">
                <i class="bi bi-journal-text me-1"></i> Activity Logs
            </a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">
            Log #{{ $activityLog->id }}
        </li>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="card border-0 shadow-lg rounded-lg overflow-hidden">

                <!-- Header -->
                <div class="card-header bg-white py-4 d-flex justify-content-between align-items-center border-bottom">
                    <div>
                        <h5 class="mb-0 text-primary">
                            <i class="bi bi-journal-text me-2"></i>
                            Activity Log Details #{{ $activityLog->id }}
                        </h5>
                        <p class="text-muted mb-0 small">Detailed information for this log entry</p>
                    </div>
                    <div>
                        <a href="{{ route('activity-logs.index') }}" class="btn btn-outline-secondary btn-sm">
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
                            <span class="text-dark">{{ $activityLog->id }}</span>
                        </div>
                        <div class="col-md-3">
                            <strong class="text-muted d-block small">Event:</strong>
                            <span class="badge bg-primary text-white rounded-pill">
                                {{ ucfirst($activityLog->event ?: 'N/A') }}
                            </span>
                        </div>
                        <div class="col-md-3">
                            <strong class="text-muted d-block small">Log Name:</strong>
                            <span class="text-dark">{{ $activityLog->log_name }}</span>
                        </div>
                        <div class="col-md-3">
                            <strong class="text-muted d-block small">Date & Time:</strong>
                            <span class="text-dark">
                                {{ $activityLog->created_at->format('Y-m-d H:i:s') }}
                                ({{ $activityLog->created_at->diffForHumans() }})
                            </span>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Description -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <strong class="text-muted d-block small">Description:</strong>
                            <p class="mb-0 text-dark">{{ $activityLog->description }}</p>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Subject / Restore Button -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <strong class="text-muted d-block small">Subject:</strong>
                            {{-- Subject Display + Restore Button --}}
                            @if ($activityLog->subject_type && $activityLog->subject_id)
                                @php
                                    $subjectClass = $activityLog->subject_type;
                                    $subject = null;

                                    if (
                                        class_exists($subjectClass) &&
                                        in_array(
                                            Illuminate\Database\Eloquent\SoftDeletes::class,
                                            class_uses_recursive($subjectClass),
                                        )
                                    ) {
                                        $subject = $subjectClass::withTrashed()->find($activityLog->subject_id);
                                    } elseif (class_exists($subjectClass)) {
                                        $subject = $subjectClass::find($activityLog->subject_id);
                                    }
                                @endphp

                                @if ($subject)
                                    <span class="text-dark">
                                        {{ class_basename($activityLog->subject_type) }}
                                        #{{ $activityLog->subject_id }}
                                    </span>

                                    {{-- Deleted → Restore --}}
                                    @if (
                                        $activityLog->event === consthelper('ActivityLog::EVENT_DELETED') &&
                                            method_exists($subject, 'trashed') &&
                                            $subject->trashed())
                                        <form action="{{ route('activity-logs.restore', $activityLog->id) }}"
                                            method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-success ms-2">
                                                <i class="bi bi-arrow-counterclockwise"></i> Restore
                                            </button>
                                        </form>
                                    @endif

                                    {{-- Updated → Revert --}}
                                    @if ($activityLog->event === consthelper('ActivityLog::EVENT_UPDATED'))
                                        <form action="{{ route('activity-logs.restore', $activityLog->id) }}"
                                            method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-warning ms-2">
                                                <i class="bi bi-arrow-counterclockwise"></i> Revert to Old
                                            </button>
                                        </form>
                                    @endif
                                @else
                                    <span class="text-muted fst-italic">Record not found</span>
                                @endif
                            @else
                                <span class="text-muted fst-italic">No specific subject</span>
                            @endif
                        </div>

                        <!-- Causer -->
                        <div class="col-md-4">
                            <strong class="text-muted d-block small">Causer:</strong>
                            @if ($activityLog->causer_type && $activityLog->causer_id)
                                <span class="text-dark">
                                    {{ class_basename($activityLog->causer_type) }} #{{ $activityLog->causer_id }}
                                    @if ($activityLog->causer)
                                        ({{ $activityLog->causer->name ?? ($activityLog->causer->email ?? 'Unknown Name') }})
                                    @endif
                                </span>
                            @else
                                <span class="text-muted fst-italic">System / Guest</span>
                            @endif
                        </div>

                        <!-- Batch UUID -->
                        <div class="col-md-4">
                            <strong class="text-muted d-block small">Batch UUID:</strong>
                            <span class="text-dark">{{ $activityLog->batch_uuid ?: 'N/A' }}</span>
                        </div>
                    </div>

                    <hr class="my-4">

                    @php
                        $ipAddress = $activityLog->ip_address ?? 'N/A';
                        $userAgent = $activityLog->user_agent ?? 'N/A';
                        $url = $activityLog->url ?? 'N/A';
                        $method = $activityLog->method ?? 'N/A';
                    @endphp

                    <!-- Technical Info -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <strong class="text-muted d-block small">IP Address:</strong>
                            <span class="text-dark">{{ $ipAddress }}</span>
                        </div>
                        <div class="col-md-3">
                            <strong class="text-muted d-block small">HTTP Method:</strong>
                            <span class="text-dark">{{ strtoupper($method) }}</span>
                        </div>
                        <div class="col-md-6">
                            <strong class="text-muted d-block small">Accessed URL:</strong>
                            <span class="text-dark" style="word-break: break-all;">{{ $url }}</span>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-12">
                            <strong class="text-muted d-block small">User Agent:</strong>
                            <span class="small text-muted">{{ $userAgent }}</span>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Additional Properties -->
                    <div class="row">
                        <div class="col-md-12">
                            <strong class="text-muted d-block small mb-1">Additional Properties:</strong>
                            @if ($activityLog->properties)
                                <div class="bg-light p-3 rounded" style="max-height: 400px; overflow-y: auto;">
                                    <pre class="mb-0 small">{{ json_encode($activityLog->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
                                </div>
                            @else
                                <p class="text-muted fst-italic">No additional properties recorded.</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="card-footer bg-white text-end py-4">
                    <a href="{{ route('activity-logs.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i> Back to Logs
                    </a>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
