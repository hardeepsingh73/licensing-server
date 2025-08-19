<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="fw-semibold fs-4 text-dark mb-0">
                <i class="bi bi-gear-wide-connected me-2"></i>
                {{ isset($setting) ? 'Edit' : 'Create' }} Settings
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
            <a class="btn-link text-decoration-none" href="{{ route('settings.index') }}">
                <i class="bi bi-gear-wide-connected me-1"></i> Settings
            </a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">
            {{ isset($setting) ? 'Edit' : 'Create' }}
        </li>
    </x-slot>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">{{ isset($setting) ? 'Edit Setting' : 'Create New Setting' }}</h5>
                <p class="text-muted mb-0 small">
                    {{ isset($setting) ? 'Update setting details' : 'Add a new system setting' }}
                </p>
            </div>
            <div>
                <a href="{{ route('settings.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to Settings
                </a>
            </div>
        </div>

        <div class="card-body">
            <form method="POST"
                action="{{ isset($setting) ? route('settings.update', $setting->id) : route('settings.store') }}"
                enctype="multipart/form-data">
                @csrf
                @if (isset($setting))
                    @method('PUT')
                @endif

                <div class="row">
                    <!-- Setting Key -->
                    <div class="col-md-6 mb-3">
                        <label for="key" class="form-label">Setting Key <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('key') is-invalid @enderror" id="key"
                            name="key" value="{{ old('key', $setting->key ?? '') }}" placeholder="e.g. site_name">
                        @error('key')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Unique identifier for this setting (snake_case recommended)</small>
                    </div>

                    <!-- Group Selection -->
                    <div class="col-md-6 mb-3">
                        <label for="group" class="form-label">Group <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <select class="form-control @error('group') is-invalid @enderror" id="group-select"
                                name="group">
                                <option value="">Select Group</option>
                                @foreach ($groups as $group)
                                    <option value="{{ $group }}"
                                        {{ old('group', $setting->group ?? '') == $group ? 'selected' : '' }}>
                                        {{ ucfirst($group) }}
                                    </option>
                                @endforeach
                                <option value="__new__">+ Create New Group</option>
                            </select>
                            <input type="text" class="form-control d-none @error('group') is-invalid @enderror"
                                id="group-input" name="group-input" placeholder="Enter new group name">
                            <button class="btn btn-outline-secondary" type="button" id="toggle-group">
                                <i class="bi bi-pencil"></i>
                            </button>
                        </div>
                        @error('group')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Data Type -->
                    <div class="col-md-6 mb-3">
                        <label for="type" class="form-label">Data Type <span class="text-danger">*</span></label>
                        <select class="form-control @error('type') is-invalid @enderror" id="type" name="type">
                            <option value="">Select Type</option>
                            @foreach (consthelper('Setting::$types') as $type)
                                <option value="{{ $type }}"
                                    {{ old('type', $setting->type ?? '') == $type ? 'selected' : '' }}>
                                    {{ $type }}
                                </option>
                            @endforeach
                        </select>
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Value: dynamic field, rendered via JS after initial page load -->
                    <div class="col-md-6 mb-3" id="value-container">
                        <!-- JavaScript will render the field content here based on type -->
                    </div>

                    <!-- Description -->
                    <div class="col-12 mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description"
                            rows="2">{{ old('description', $setting->description ?? '') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Optional description of what this setting controls</small>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> {{ isset($setting) ? 'Update Setting' : 'Create Setting' }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <x-slot name="script">
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                $(function() {
                    // Group selection toggle
                    $('#group-select').change(function() {
                        if ($(this).val() === '__new__') {
                            $('#group-input').removeClass('d-none').attr('name', 'group');
                            $('#group-select').addClass('d-none').removeAttr('name');
                        }
                    });

                    $('#toggle-group').click(function() {
                        const $groupInput = $('#group-input');
                        const $groupSelect = $('#group-select');

                        if ($groupInput.hasClass('d-none')) {
                            $groupInput.removeClass('d-none').attr('name', 'group');
                            $groupSelect.addClass('d-none').removeAttr('name');
                        } else {
                            $groupInput.addClass('d-none').removeAttr('name');
                            $groupSelect.removeClass('d-none').attr('name', 'group');
                        }
                    });

                    // Setup dynamic value field based on selected type
                    function renderValueField() {
                        const type = $('#type').val();
                        const currentValue = `{{ old('value', $setting->value ?? '') }}`;
                        let html =
                            '<label for="value" class="form-label">Value <span class="text-danger">*</span></label>';

                        switch (type) {
                            case 'boolean':
                                html += `<select name="value" id="value" class="form-control" >
                    <option value="1" ${currentValue == '1' ? 'selected' : ''}>Yes</option>
                    <option value="0" ${currentValue == '0' ? 'selected' : ''}>No</option>
                </select>`;
                                break;

                            case 'text':
                                html +=
                                    `<textarea name="value" id="value" class="form-control" rows="3" >${currentValue}</textarea>`;
                                break;

                            case '{{ consthelper('Setting::TYPE_IMAGE') }}':
                                html += `<div>
                    <input type="file" name="value" id="value" class="form-control" >
                    @if (isset($setting) && $setting->type === consthelper('Setting::TYPE_IMAGE') && $setting->value)
                        <div class="mt-2">
                            <img src="{{ Storage::url($setting->value) }}" alt="Current image" style="max-height: 100px;">
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" name="remove_image" id="remove_image" value="1">
                                <label class="form-check-label" for="remove_image">Remove current image</label>
                            </div>
                        </div>
                    @endif
                </div>`;
                                break;

                            case '{{ consthelper('Setting::TYPE_ARRAY') }}':
                            case '{{ consthelper('Setting::TYPE_JSON') }}':
                                html += `<textarea name="value" id="value" class="form-control" rows="3" >${currentValue}</textarea>
                    <small class="text-muted">
                        ${type === '{{ consthelper('Setting::TYPE_ARRAY') }}' ? 'Comma-separated, e.g. value1,value2,value3' : 'Valid JSON string'}
                    </small>`;
                                break;

                            default: // string, integer, float
                                const inputType = (type === '{{ consthelper('Setting::TYPE_INTEGER') }}' ||
                                        type === '{{ consthelper('Setting::TYPE_FLOAT') }}') ? 'number' :
                                    'text';
                                const step = (type === '{{ consthelper('Setting::TYPE_FLOAT') }}') ? 'any' :
                                    '1';
                                html += `<input type="${inputType}" name="value" id="value" class="form-control"
                    value="${currentValue}" ${inputType === 'number' ? `step="${step}"` : ''} >`;
                        }

                        $('#value-container').html(html);
                    }

                    // Initialize
                    $('#type').change(renderValueField);
                    renderValueField();
                });
            });
        </script>
    </x-slot>
</x-app-layout>
