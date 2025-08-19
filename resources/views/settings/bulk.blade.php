<x-app-layout>
    <!-- Page Header -->
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="fw-semibold fs-4 text-dark mb-0">
                <i class="bi bi-gear-wide-connected me-2"></i>
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
                <p class="text-muted mb-0 small">Manage all system configuration settings</p>
            </div>
            <div>
                <a href="{{ route('settings.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-2"></i> Add New Setting
                </a>
            </div>
        </div>

        <div class="card-body">
            <form method="POST" action="{{ route('settings.bulk-update') }}" enctype="multipart/form-data">
                @csrf

                <ul class="nav nav-tabs mb-4" id="settingsTabs" role="tablist">
                    @foreach ($groups as $group)
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $loop->first ? 'active' : '' }}" id="{{ Str::slug($group) }}-tab"
                                data-bs-toggle="tab" data-bs-target="#{{ Str::slug($group) }}" type="button"
                                role="tab" aria-controls="{{ Str::slug($group) }}"
                                aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                                {{ ucfirst($group) }}
                            </button>
                        </li>
                    @endforeach
                </ul>

                <div class="tab-content" id="settingsTabContent">
                    @foreach ($settings as $group => $groupSettings)
                        <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="{{ Str::slug($group) }}"
                            role="tabpanel" aria-labelledby="{{ Str::slug($group) }}-tab">
                            <div class="card border-0 shadow-none mb-3">
                                <div class="card-body">
                                    @foreach ($groupSettings as $setting)
                                        <div class="row mb-3">
                                            <label for="setting-{{ $setting->key }}" class="col-md-3 col-form-label">
                                                {{ Str::title(str_replace('_', ' ', $setting->key)) }}
                                                @if ($setting->description)
                                                    <small
                                                        class="text-muted d-block">{{ $setting->description }}</small>
                                                @endif
                                            </label>
                                            <div class="col-md-9">
                                                @if ($setting->type === consthelper('Setting::TYPE_TEXT'))
                                                    <textarea class="form-control" id="setting-{{ $setting->key }}" name="settings[{{ $setting->key }}]" rows="3">{{ old('settings.' . $setting->key, $setting->value) }}</textarea>
                                                @elseif ($setting->type === consthelper('Setting::TYPE_BOOLEAN'))
                                                    <div class="form-check form-switch">
                                                        <input type="hidden" name="settings[{{ $setting->key }}]"
                                                            value="0">
                                                        <input class="form-check-input" type="checkbox"
                                                            id="setting-{{ $setting->key }}"
                                                            name="settings[{{ $setting->key }}]" value="1"
                                                            {{ $setting->value ? 'checked' : '' }}>
                                                        <label class="form-check-label"
                                                            for="setting-{{ $setting->key }}">
                                                            {{ $setting->value ? 'Enabled' : 'Disabled' }}
                                                        </label>
                                                    </div>
                                                @elseif ($setting->type === consthelper('Setting::TYPE_IMAGE'))
                                                    <div class="image-upload-container">
                                                        <input type="file" class="form-control mb-2"
                                                            id="setting-{{ $setting->key }}"
                                                            name="settings[{{ $setting->key }}]" accept="image/*">

                                                        @if ($setting->value)
                                                            <div class="current-image-container mt-2">
                                                                <p class="small text-muted mb-1">Current Image:</p>
                                                                <img src="{{ Storage::url($setting->value) }}"
                                                                    alt="{{ $setting->key }}"
                                                                    class="img-thumbnail mb-2"
                                                                    style="max-height: 100px;">
                                                            </div>
                                                        @endif
                                                    </div>
                                                @else
                                                    <input
                                                        type="{{ $setting->type === consthelper('Setting::TYPE_INTEGER') ? 'number' : 'text' }}"
                                                        class="form-control" id="setting-{{ $setting->key }}"
                                                        name="settings[{{ $setting->key }}]"
                                                        value="{{ old('settings.' . $setting->key, $setting->value) }}"
                                                        @if ($setting->type === consthelper('Setting::TYPE_INTEGER')) step="1" @endif>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-2"></i> Save All Settings
                    </button>
                </div>
            </form>
        </div>
    </div>

    <x-slot name="script">
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                $(function() {
                    // Initialize Bootstrap tabs (jQuery version)
                    $('button[data-bs-toggle="tab"]').click(function() {
                        const tabTrigger = new bootstrap.Tab(this);
                        tabTrigger.show();
                    });

                    // Toggle boolean labels on change
                    $('.form-check-input[type="checkbox"]').change(function() {
                        if ($(this).attr('name')?.startsWith('settings[')) {
                            $(this).next('label').text($(this).is(':checked') ? 'Enabled' : 'Disabled');
                        }
                    });

                    // Preview image before upload
                    $('input[type="file"][accept="image/*"]').change(function() {
                        const file = this.files[0];
                        if (!file) return;

                        const reader = new FileReader();
                        const container = $(this).closest('.image-upload-container');

                        reader.onload = function(e) {
                            container.find('.image-preview').remove();
                            container.append(`
                <div class="image-preview mt-2">
                    <p class="small text-muted mb-1">New Image Preview:</p>
                    <img src="${e.target.result}" alt="Preview" class="img-thumbnail mb-2" style="max-height: 100px;">
                </div>
            `);
                        };

                        reader.readAsDataURL(file);
                    });
                });
            });
        </script>
    </x-slot>
</x-app-layout>
