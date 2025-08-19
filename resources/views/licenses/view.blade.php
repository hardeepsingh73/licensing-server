<x-app-layout>
    <!-- Page Header -->
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="fw-semibold fs-4 text-dark mb-0">
                <i class="bi bi-key me-2"></i>
                License Details
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
            <a class="btn-link text-decoration-none" href="{{ route('licenses.index') }}">
                <i class="bi bi-key me-1"></i> Licenses
            </a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">
            View License
        </li>
    </x-slot>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h5 class="card-title mb-3">License Key: <strong>{{ $license->key }}</strong></h5>

            <div class="row mb-3">
                <div class="col-md-4">
                    <div>
                        <strong>Status:</strong>
                        <span class="badge {{ $license->status_badge_class }}">
                            {{ $license->status_label }}
                        </span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div><strong>Activation Limit:</strong> {{ $license->activation_limit }}</div>
                </div>
                <div class="col-md-4">
                    <div><strong>Activations Used:</strong> {{ $license->activations }}</div>
                </div>
            </div>

            {{-- Assigned User --}}
            <div class="row mb-3">
                <div class="col-md-4">
                    <div>
                        <strong>Assigned User:</strong>
                        @if ($license->user)
                            <span class="fw-semibold">{{ $license->user->name }}</span><br>
                            <small class="text-muted">{{ $license->user->email }}</small>
                        @else
                            <span class="text-muted">Unassigned</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <div><strong>Expiry Date:</strong>
                        @if ($license->expires_at)
                            {{ \Carbon\Carbon::parse($license->expires_at)->format('Y-m-d') }}
                        @else
                            <span class="text-muted">Never</span>
                        @endif
                    </div>
                </div>
                <div class="col-md-4">
                    <div><strong>Created At:</strong> {{ $license->created_at->format('Y-m-d H:i') }}</div>
                </div>
                <div class="col-md-4">
                    <div><strong>Last Updated:</strong> {{ $license->updated_at->format('Y-m-d H:i') }}</div>
                </div>
            </div>

            @can('edit licenses')
                <a href="{{ route('licenses.edit', $license) }}" class="btn btn-primary">
                    <i class="bi bi-pencil me-1"></i> Edit License
                </a>
            @endcan

            @can('delete licenses')
                @if ($license->status !== \App\Models\LicenseKey::STATUS_REISSUE)
                    <form action="{{ route('licenses.reissue', $license) }}" method="POST" id="reissue-form"
                        class="d-inline">
                        @csrf
                        <button type="button" class="btn btn-danger" id="reissue-btn">
                            <i class="bi bi-slash-circle me-1"></i> Reissue License
                        </button>
                    </form>
                @endif
            @endcan
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Activated Devices</h5>
        </div>
        <div class="card-body p-0">
            @if ($devices->isEmpty())
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-hdd-rack fs-1"></i>
                    <p class="mt-3 mb-0">No activations found for this license.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Device ID</th>
                                <th>IP Address</th>
                                <th>User Agent</th>
                                <th>Activated At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($devices as $device)
                                <tr>
                                    <td class="text-break">{{ $device->device_id }}</td>
                                    <td>{{ $device->ip_address ?? '-' }}</td>
                                    <td class="text-truncate" style="max-width: 250px;">
                                        {{ $device->user_agent ?? '-' }}
                                    </td>
                                    <td>{{ $device->created_at->format('Y-m-d H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($devices->hasPages())
                    <div class="mt-3 px-3">
                        <x-pagination :paginator="$devices" />
                    </div>
                @endif
            @endif
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                $(document).ready(function() {
                    $('#reissue-btn').on('click', function(e) {
                        e.preventDefault();
                        Swal.fire({
                            title: 'Are you sure?',
                            text: "This will reissue the license and remove all activations!",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Yes, reissue it!',
                            cancelButtonText: 'Cancel',
                            reverseButtons: true,
                            focusCancel: true
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $('#reissue-form').submit();
                            }
                        });
                    });
                });
            });
        </script>
    @endpush
</x-app-layout>
