<x-app-layout>
    <!-- Page Header -->
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="fw-semibold fs-4 text-dark mb-0">
                <i class="bi bi-key me-2"></i>
                {{ isset($license) ? 'Edit License' : 'Create New License' }}
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
            {{ isset($license) ? 'Edit' : 'Create' }}
        </li>
    </x-slot>

    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center bg-white py-3">
            <div class="header-title">
                <h4 class="card-title mb-0">
                    {{ isset($license) ? 'Edit License' : 'Create License' }}
                </h4>
                <p class="text-muted mb-0 small">
                    {{ isset($license) ? 'Update license details' : 'Fill license details' }}
                </p>
            </div>
            <div class="header-action">
                <x-back-button :href="route('licenses.index')" class="btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> {{ __('Back to Licenses') }}
                </x-back-button>
            </div>
        </div>

        <div class="card-body">
            <form action="{{ isset($license) ? route('licenses.update', $license) : route('licenses.store') }}"
                method="POST" class="needs-validation" novalidate>
                @csrf
                @if (isset($license))
                    @method('PUT')
                @endif

                <!-- License Key -->
                <div class="form-group row mb-4">
                    <label for="key" class="col-sm-3 col-form-label">License Key <x-star /></label>
                    <div class="col-sm-9">

                        <div class="input-group">
                            <input type="text" name="key" id="key"
                                class="form-control @error('key') is-invalid @enderror" placeholder="Enter license key"
                                value="{{ old('key', $license->key ?? '') }}" readonly>
                            @if (!isset($license))
                                <button type="button" class="btn btn-outline-secondary" id="generateKey">
                                    <i class="bi bi-lightning me-1"></i> Generate
                                </button>
                            @endif
                        </div>
                        @error('key')
                            <x-error-msg>{{ $message }}</x-error-msg>
                        @enderror
                        @if (isset($license))
                            <small class="form-text text-muted">License key cannot be changed once created.</small>
                        @endif
                    </div>
                </div>

                <!-- Status -->
                <div class="form-group row mb-4">
                    <label for="status" class="col-sm-3 col-form-label">Status <x-star /></label>
                    <div class="col-sm-9">
                        <select name="status" id="status" class="form-select @error('status') is-invalid @enderror">
                            @foreach (consthelper('LicenseKey::$statuses') as $id => $status)
                                <option value="{{ $id }}"
                                    {{ old('status', $license->status ?? '') == $id ? 'selected' : '' }}>
                                    {{ $status }}
                                </option>
                            @endforeach
                        </select>
                        @error('status')
                            <x-error-msg>{{ $message }}</x-error-msg>
                        @enderror
                    </div>
                </div>

                <!-- Activation Limit -->
                <div class="form-group row mb-4">
                    <label for="activation_limit" class="col-sm-3 col-form-label">Activation Limit <x-star /></label>
                    <div class="col-sm-9">
                        <input type="number" name="activation_limit" id="activation_limit"
                            class="form-control @error('activation_limit') is-invalid @enderror"
                            placeholder="Enter activation limit" min="1"
                            value="{{ old('activation_limit', $license->activation_limit ?? 1) }}">
                        @error('activation_limit')
                            <x-error-msg>{{ $message }}</x-error-msg>
                        @enderror
                    </div>
                </div>

                <!-- Expiry Date -->
                <div class="form-group row mb-4">
                    <label for="expires_at" class="col-sm-3 col-form-label">Expiry Date</label>
                    <div class="col-sm-9">
                        <input type="date" name="expires_at" id="expires_at"
                            class="form-control @error('expires_at') is-invalid @enderror"
                            value="{{ old('expires_at', isset($license->expires_at) ? $license->expires_at->format('Y-m-d') : '') }}">
                        @error('expires_at')
                            <x-error-msg>{{ $message }}</x-error-msg>
                        @enderror
                        <small class="form-text text-muted">Leave blank for no expiry</small>
                    </div>
                </div>

                <!-- Actions -->
                <div class="form-group row">
                    <div class="col-sm-9 offset-sm-3">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-save me-1"></i> {{ isset($license) ? 'Update License' : 'Create License' }}
                        </button>
                        <button type="reset" class="btn btn-outline-secondary ms-2">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @push('scripts')
        <script>
            function randomStr(length) {
                const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
                let result = '';
                for (let i = 0; i < length; i++) {
                    result += chars.charAt(Math.floor(Math.random() * chars.length));
                }
                return result;
            }

            function generateLicenseKey() {
                return 'IPTV-' +
                    randomStr(4) + '-' +
                    randomStr(4) + '-' +
                    randomStr(4);
            }

            function checkKeyUnique(key, cb) {
                $.ajax({
                    url: "{{ route('licenses.checkKey') }}",
                    method: "POST",
                    data: {
                        key: key,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        cb(response.unique);
                    }
                });
            }
            document.addEventListener('DOMContentLoaded', function() {
                $(document).ready(function() {
                    $('#generateKey').on('click', function() {
                        $("#key-error").hide();
                        let tryCount = 0;

                        function tryGenerate() {
                            if (tryCount > 10) {
                                $("#key-error").text(
                                    "Could not generate a unique key, please try again.").show();
                                return;
                            }
                            tryCount++;
                            const key = generateLicenseKey();
                            checkKeyUnique(key, function(isUnique) {
                                if (isUnique) {
                                    $("#key").val(key);
                                } else {
                                    tryGenerate();
                                }
                            });
                        }
                        tryGenerate();
                    });
                });
            });
        </script>
    @endpush
</x-app-layout>
