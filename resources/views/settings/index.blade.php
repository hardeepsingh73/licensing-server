<x-app-layout>
    <!-- Page Header -->
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="fw-semibold fs-4 text-dark mb-0">
                <i class="bi bi-person-badge-fill me-2"></i>
                Settings
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
        <li class="breadcrumb-item active" aria-current="page">System Settings</li>
    </x-slot>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">System Settings</h5>
                <p class="text-muted mb-0 small">Manage application configuration</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="collapse"
                    data-bs-target="#listSearchForm" aria-expanded="false" aria-controls="listSearchForm">
                    <i class="bi bi-search me-1"></i> Search
                </button>
                @can('create settings')
                    <x-add-button :href="route('settings.create')" class="btn-primary btn-sm">
                        <i class="bi bi-plus-lg me-1"></i> {{ __('Add New Setting') }}
                    </x-add-button>
                @endcan
            </div>
        </div>

        <div class="card-body">
            <!-- Search Form -->
            <div class="collapse mb-4 border-bottom pb-3" id="listSearchForm">
                <form action="{{ route('settings.index') }}" method="POST" class="form-horizontal">
                    @csrf
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label">Setting Key</label>
                            <input type="text" name="key" class="form-control" placeholder="Enter setting key"
                                value="{{ request('key') }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Group</label>
                            <select name="group" class="form-select">
                                <option value="">All Groups</option>
                                @foreach ($groups as $group)
                                    <option value="{{ $group }}"
                                        {{ request('group') == $group ? 'selected' : '' }}>
                                        {{ ucfirst($group) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search me-1"></i> Search
                            </button>
                            <a href="{{ url()->current() }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">#</th>
                            <th>Key</th>
                            <th>Value</th>
                            <th>Group</th>
                            <th>Type</th>
                            <th width="15%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($settings as $setting)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $setting->key }}</td>
                                <td>
                                    @if ($setting->type === consthelper('Setting::TYPE_BOOLEAN'))
                                        <span class="badge bg-{{ $setting->value ? 'success' : 'danger' }}">
                                            {{ $setting->value ? 'Yes' : 'No' }}
                                        </span>
                                    @elseif($setting->type === consthelper('Setting::TYPE_IMAGE'))
                                        @if ($setting->value)
                                            <a href="{{ Storage::url($setting->value) }}" target="_blank"
                                                data-bs-toggle="tooltip" title="View Full Image">
                                                <img src="{{ Storage::url($setting->value) }}"
                                                    alt="{{ $setting->key }}" class="img-thumbnail"
                                                    style="max-width: 50px; max-height: 50px;">
                                            </a>
                                        @else
                                            <span class="text-muted">No image</span>
                                        @endif
                                    @else
                                        {{ Str::limit($setting->value, 50) }}
                                    @endif
                                </td>
                                <td><span class="badge bg-info">{{ $setting->group }}</span></td>
                                <td><span class="badge bg-secondary">{{ $setting->type }}</span></td>
                                <td class="text-nowrap">
                                    <div class="d-flex gap-2">
                                        @can('edit settings')
                                            <x-edit-button :href="route('settings.edit', $setting)" class="me-1">
                                                <i class="bi bi-pencil"></i>
                                            </x-edit-button>
                                        @endcan
                                        @can('delete settings')
                                            <form action="{{ route('settings.destroy', $setting->id) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button"
                                                    class="btn btn-sm btn-outline-danger delete-entry delete-setting"
                                                    data-id="{{ $setting->id }}" data-bs-toggle="tooltip" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="bi bi-gear fs-1 text-muted mb-2"></i>
                                        <p class="mb-0">No settings found</p>
                                        @can('create settings')
                                            <a href="{{ route('settings.create') }}" class="btn btn-sm btn-primary mt-2">
                                                <i class="bi bi-plus-lg me-1"></i> Create New Setting
                                            </a>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($settings->hasPages())
                <div class="mt-4">
                    <x-pagination :paginator="$settings" />
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
