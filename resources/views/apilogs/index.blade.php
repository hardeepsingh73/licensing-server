<x-app-layout>
    {{-- Header --}}
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="fw-semibold fs-4 text-dark mb-0">
                <i class="bi bi-activity me-2"></i> {{ __('API Logs') }}
            </h2>
        </div>
    </x-slot>

    {{-- Breadcrumbs --}}
    <x-slot name="breadcrumbs">
        <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}" class="btn-link text-decoration-none">
                <i class="bi bi-house-door me-1"></i> Dashboard
            </a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">API Logs</li>
    </x-slot>

    {{-- API Logs Card --}}
    <div class="card border-0 shadow-sm">
        {{-- Card Header --}}
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">API Logs</h5>
                <p class="text-muted mb-0 small">Track all API requests and responses</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                @can('clear api logs')
                    <form id="clear-api-logs-form" action="{{ route('api-logs.clear') }}" method="POST"
                        class="mb-0 clearLogs">
                        @csrf
                        <button type="submit" class="btn btn-danger btn-sm">
                            <i class="bi bi-trash3-fill me-1"></i> Clear Logs
                        </button>
                    </form>
                @endcan

                <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="collapse"
                    data-bs-target="#listSearchForm" aria-expanded="false" aria-controls="listSearchForm">
                    <i class="bi bi-search me-1"></i> Search
                </button>
            </div>
        </div>

        {{-- Card Body --}}
        <div class="card-body">

            {{-- Search Form --}}
            <div id="listSearchForm" class="collapse mb-4 border-bottom pb-3">
                <form action="{{ route('api-logs.index') }}" method="POST" class="row g-3">
                    @csrf

                    {{-- User --}}
                    <div class="col-md-2">
                        <label for="user_id" class="form-label">User</label>
                        <select id="user_id" name="user_id" class="form-select">
                            <option value="">All Users</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}"
                                    {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Method --}}
                    <div class="col-md-2">
                        <label for="method" class="form-label">Method</label>
                        <input type="text" id="method" name="method" value="{{ request('method') }}"
                            placeholder="GET / POST" class="form-control">
                    </div>

                    {{-- Endpoint --}}
                    <div class="col-md-3">
                        <label for="endpoint" class="form-label">Endpoint</label>
                        <input type="text" id="endpoint" name="endpoint" value="{{ request('endpoint') }}"
                            placeholder="Enter Endpoint" class="form-control">
                    </div>

                    {{-- IP Address --}}
                    <div class="col-md-3">
                        <label for="ip_address" class="form-label">IP Address</label>
                        <input type="text" id="ip_address" name="ip_address" value="{{ request('ip_address') }}"
                            placeholder="Enter IP address" class="form-control">
                    </div>

                    {{-- Search / Reset Buttons --}}
                    <div class="col-md-2 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary w-50">
                            <i class="bi bi-search me-1"></i> Search
                        </button>
                        <a href="{{ route('api-logs.index') }}" class="btn btn-outline-secondary w-50">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                        </a>
                    </div>
                </form>
            </div>


            {{-- Logs Table --}}
            <div class="table-responsive">
                <table class="table table-hover align-middle table-striped mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>User</th>
                            <th>Endpoint</th>
                            <th>Method</th>
                            <th>Status</th>
                            <th>IP Address</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($apiLogs as $log)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    @if ($log->user)
                                        <span class="text-dark">{{ $log->user->name }} <small
                                                class="text-muted">({{ $log->user->email }})</small></span>
                                    @else
                                        <span class="text-muted fst-italic">Guest / N/A</span>
                                    @endif
                                </td>
                                <td>{{ $log->endpoint ?? '-' }}</td>
                                <td>{{ $log->method ?? '-' }}</td>
                                <td>
                                    <span
                                        class="badge bg-{{ $log->response_status >= 200 && $log->response_status < 300 ? 'success' : 'danger' }}">
                                        {{ $log->response_status }}
                                    </span>
                                </td>
                                <td>{{ $log->ip_address }}</td>
                                <td>{{ $log->created_at->format('d M Y H:i') }}</td>
                                <td>
                                    <a href="{{ route('api-logs.show', $log->id) }}" class="btn btn-info btn-sm">
                                        <i class="bi bi-eye-fill"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i class="bi bi-inbox fs-3 text-muted mb-2"></i>
                                    <p class="mb-0 text-muted">No API logs found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($apiLogs->hasPages())
                <div class="mt-4">
                    <x-pagination :paginator="$apiLogs" />
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
