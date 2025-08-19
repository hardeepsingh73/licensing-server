<x-app-layout>
    <!-- Page Header -->
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-0 fw-semibold">
                <i class="bi bi-people-fill me-2"></i> User Management
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
            <i class="bi bi-people me-1"></i> Users
        </li>
    </x-slot>

    <div class="card shadow-sm border-0 rounded-3 overflow-hidden">

        <!-- Card Header -->
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
            <h2 class="h5 mb-0 fw-semibold">All System Users</h2>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="collapse"
                    data-bs-target="#listSearchForm" aria-expanded="false">
                    <i class="bi bi-funnel me-1"></i> Filter
                </button>
                @can('create users')
                    <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-lg me-1"></i> Add User
                    </a>
                @endcan
            </div>
        </div>

        <!-- Filter Section -->
        <div class="collapse" id="listSearchForm">
            <div class="p-3 border-bottom">
                <form method="POST" action="{{ route('users.index') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small">Name</label>
                            <input type="text" name="name" class="form-control" placeholder="Search by name"
                                value="{{ request('name') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Email</label>
                            <input type="text" name="email" class="form-control" placeholder="Search by email"
                                value="{{ request('email') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Role</label>
                            <select name="role" class="form-select">
                                <option value="">All Roles</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}"
                                        {{ request('role') == $role->id ? 'selected' : '' }}>
                                        {{ $role->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 d-flex justify-content-end gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search me-1"></i> Search
                            </button>
                            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
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
                            <th class="ps-4">User</th>
                            <th>Email</th>
                            <th>Role(s)</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td class="ps-4"> {{ $user->name }}</td>
                                <td class="text-truncate" style="max-width:200px;">{{ $user->email }}</td>
                                <td>
                                    @foreach ($user->roles as $role)
                                        <span class="badge bg-primary ">
                                            {{ $role->name }}
                                        </span>
                                    @endforeach
                                </td>
                                <td>
                                    @if ($user->email_verified_at)
                                        <span class="badge rounded-pill bg-success">
                                            <i class="bi bi-patch-check-fill me-1"></i> Verified
                                        </span>
                                    @else
                                        <span class="badge rounded-pill bg-warning">
                                            <i class="bi bi-exclamation-circle-fill me-1"></i> Unverified
                                        </span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-flex justify-content-end gap-2">
                                        @can('is_superadmin')
                                            <a href="{{ route('shadow.login', $user->id) }}"
                                                class="btn btn-sm btn-outline-warning"
                                                title="Login as {{ $user->name }}">
                                                <i class="bi bi-incognito"></i>
                                            </a>
                                        @endcan
                                        @can('edit users')
                                            <a href="{{ route('users.edit', $user->id) }}"
                                                class="btn btn-sm btn-outline-secondary" data-bs-toggle="tooltip"
                                                title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        @endcan
                                        @can('delete users')
                                            <form action="{{ route('users.destroy', $user->id) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button"
                                                    class="btn btn-sm btn-outline-danger delete-entry delete-user"
                                                    data-id="{{ $user->id }}" data-bs-toggle="tooltip" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <i class="bi bi-people fs-1 text-muted"></i>
                                    <h5 class="mt-3">No users found</h5>
                                    <p class="text-muted">Try adjusting your search filters</p>
                                    @can('create users')
                                        <a href="{{ route('users.create') }}" class="btn btn-primary mt-3">
                                            <i class="bi bi-plus-lg me-2"></i> Create New User
                                        </a>
                                    @endcan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($users->hasPages())
                <div class="mt-4">
                    <x-pagination :paginator="$users" />
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
