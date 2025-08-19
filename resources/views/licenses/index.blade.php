<x-app-layout>
    <!-- Page Header -->
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-0 fw-semibold">
                <i class="bi bi-key-fill me-2"></i> License Management
            </h1>
        </div>
    </x-slot>
    <!-- Breadcrumbs -->
    <x-slot name="breadcrumbs">
        <li class="breadcrumb-item">
            <a class="btn-link text-decoration-none" href="{{ route('dashboard') }}">
                <i class="bi bi-house-door me-1"></i> Dashboard
            </a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">
            <i class="bi bi-key me-1"></i> Licenses
        </li>
    </x-slot>

    <div class="card shadow-sm border-0 rounded-3 overflow-hidden">
        <!-- Card Header -->
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
            <h2 class="h5 mb-0 fw-semibold">All Licenses</h2>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="collapse"
                    data-bs-target="#listSearchForm" aria-expanded="false">
                    <i class="bi bi-funnel me-1"></i> Filter
                </button>
                @can('create licenses')
                    <a href="{{ route('licenses.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-lg me-1"></i> Add License
                    </a>
                @endcan
            </div>
        </div>

        <!-- Filter Section -->
        <div class="collapse" id="listSearchForm">
            <div class="p-3 border-bottom">
                <form method="POST" action="{{ route('licenses.index') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label small">License Key</label>
                            <input type="text" name="key" class="form-control" placeholder="Search by key"
                                value="{{ request('key') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">User</label>
                            <select name="user_id" class="form-select">
                                <option value="">All Users</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}"
                                        {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ $user->email }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All Statuses</option>
                                @foreach (consthelper('LicenseKey::$statuses') as $id => $status)
                                    <option value="{{ $id }}"
                                        {{ request('status') == $id ? 'selected' : '' }}>
                                        {{ $status }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Expiry At</label>
                            <input type="date" name="expires_at" class="form-control"
                                value="{{ request('expires_at') }}">
                        </div>
                        <div class="col-12 d-flex justify-content-end gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search me-1"></i> Search
                            </button>
                            <a href="{{ route('licenses.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Table -->
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">License Key</th>
                            <th>User</th>
                            <th>Status</th>
                            <th>Activation Limit</th>
                            <th>Activations Used</th>
                            <th>Expires At</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($licenses as $license)
                            <tr>
                                <td class="ps-4">
                                    <div>
                                        <h6 class="mb-0 text-break">{{ $license->key }}</h6>
                                    </div>
                                </td>
                                <td>
                                    @if ($license->user)
                                        <span class="fw-semibold">{{ $license->user->name }}</span>
                                        <br>
                                        <small class="text-muted">{{ $license->user->email }}</small>
                                    @else
                                        <span class="text-muted">Unassigned</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $license->status_badge_class }}">
                                        {{ $license->status_label }}
                                    </span>
                                </td>
                                <td>{{ $license->activation_limit }}</td>
                                <td>{{ $license->activations }}</td>
                                <td>
                                    @if ($license->expires_at)
                                        {{ \Carbon\Carbon::parse($license->expires_at)->format('Y-m-d') }}
                                    @else
                                        <span class="text-muted">Never</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-flex justify-content-end gap-2">
                                        @can('view licenses')
                                            <a href="{{ route('licenses.show', $license->id) }}"
                                                class="btn btn-sm btn-outline-primary" title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        @endcan
                                        @can('edit licenses')
                                            <a href="{{ route('licenses.edit', $license->id) }}"
                                                class="btn btn-sm btn-outline-secondary" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        @endcan
                                        @can('delete licenses')
                                            <form action="{{ route('licenses.destroy', $license->id) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('Are you sure you want to delete this license?');"
                                                    title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <i class="bi bi-key fs-1 text-muted"></i>
                                    <h5 class="mt-3">No licenses found</h5>
                                    <p class="text-muted">Try adjusting your search filters</p>
                                    @can('create licenses')
                                        <a href="{{ route('licenses.create') }}" class="btn btn-primary mt-3">
                                            <i class="bi bi-plus-lg me-2"></i> Create New License
                                        </a>
                                    @endcan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <!-- Pagination -->
            @if ($licenses->hasPages())
                <div class="mt-4">
                    <x-pagination :paginator="$licenses" />
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                $(document).ready(function() {
                    // Initialize tooltips
                    $('[data-bs-toggle="tooltip"]').each(function() {
                        new bootstrap.Tooltip(this);
                    });

                    // Show Filters if search params exist
                    @if (request()->hasAny(['key', 'status', 'expires_at']))
                        new bootstrap.Collapse($('#licenseFilters')[0]).show();
                    @endif
                });
            });
        </script>
    @endpush
</x-app-layout>
