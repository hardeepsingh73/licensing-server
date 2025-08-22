<x-app-layout>

    <!-- Page Header -->
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="fw-semibold fs-4 text-dark mb-0">
                <i class="bi bi-people me-2"></i>
                {{ isset($user) ? 'Edit User' : 'Create New User' }}
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
            <a class="btn-link text-decoration-none" href="{{ route('users.index') }}">
                <i class="bi bi-people me-1"></i> Users
            </a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">
            {{ isset($user) ? 'Edit' : 'Create' }}
        </li>
    </x-slot>

    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center bg-white py-3">
            <div class="header-title">
                <h4 class="card-title mb-0">
                    {{ isset($user) ? 'Edit User' : 'Create User' }}
                </h4>
                <p class="text-muted mb-0 small">
                    {{ isset($user) ? 'Update user details and permissions' : 'Fill user details and assign roles' }}
                </p>
            </div>
            <div class="header-action">
                <x-back-button :href="route('users.index')" class="btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> {{ __('Back to Users') }}
                </x-back-button>
            </div>
        </div>

        <div class="card-body">
            <form action="{{ isset($user) ? route('users.update', $user) : route('users.store') }}" method="POST"
                class="needs-validation" novalidate>
                @csrf
                @if (isset($user))
                    @method('PUT')
                @endif

                <!-- Name -->
                <div class="form-group row mb-4">
                    <label class="col-sm-3 col-form-label" for="name">
                        Full Name <x-star />
                    </label>
                    <div class="col-sm-9">
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                            id="name" placeholder="Enter full name" value="{{ old('name', $user->name ?? '') }}">
                        @error('name')
                            <x-error-msg>{{ $message }}</x-error-msg>
                        @enderror
                    </div>
                </div>

                <!-- Email -->
                <div class="form-group row mb-4">
                    <label class="col-sm-3 col-form-label" for="email">
                        Email Address <x-star />
                    </label>
                    <div class="col-sm-9">
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                            id="email" placeholder="Enter email" value="{{ old('email', $user->email ?? '') }}">
                        @error('email')
                            <x-error-msg>{{ $message }}</x-error-msg>
                        @enderror
                    </div>
                </div>

                <!-- Password -->
                <div class="form-group row mb-4">
                    <label class="col-sm-3 col-form-label" for="password">
                        {{ isset($user) ? 'New Password' : 'Password' }}
                        @if (!isset($user))
                            <x-star />
                        @endif
                    </label>
                    <div class="col-sm-9">
                        <div class="input-group">
                            <input type="password" name="password"
                                class="form-control @error('password') is-invalid @enderror" id="password"
                                placeholder="{{ isset($user) ? 'Leave blank to keep current password' : 'Minimum 8 characters' }}">
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword" tabindex="-1"
                                aria-label="Show/hide password">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        @error('password')
                            <x-error-msg>{{ $message }}</x-error-msg>
                        @enderror
                    </div>
                </div>

                <!-- Password Confirmation -->
                <div class="form-group row mb-4">
                    <label class="col-sm-3 col-form-label" for="password_confirmation">
                        Confirm Password
                        @if (!isset($user))
                            <x-star />
                        @endif
                    </label>
                    <div class="col-sm-9">
                        <input type="password" name="password_confirmation" id="password_confirmation"
                            class="form-control @error('password_confirmation') is-invalid @enderror"
                            placeholder="Re-enter password to confirm">
                        @error('password_confirmation')
                            <x-error-msg>{{ $message }}</x-error-msg>
                        @enderror
                    </div>
                </div>

                <!-- Roles -->
                @can('edit users')
                    <div class="form-group row mb-4">
                        <label class="col-sm-3 col-form-label pt-0">Roles</label>
                        <div class="col-sm-9">
                            @if ($roles->isNotEmpty())
                                <div class="row">
                                    @foreach ($roles as $role)
                                        <div class="col-md-4 mb-2">
                                            <div class="form-check">
                                                <input type="radio" class="form-check-input"
                                                    id="role-{{ $role->id }}" name="roles"
                                                    value="{{ $role->name }}"
                                                    {{ (isset($user) && $user->roles->contains($role->id)) || old('roles') == $role->name ? 'checked' : '' }}>
                                                <label class="form-check-label" for="role-{{ $role->id }}">
                                                    {{ $role->name }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="alert alert-warning p-2 mb-0"><i class="bi bi-exclamation-triangle me-1"></i> No
                                    roles available</div>
                            @endif
                            @error('roles')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                @endcan

                <!-- Actions -->
                <div class="form-group row">
                    <div class="col-sm-9 offset-sm-3">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-save me-1"></i> {{ isset($user) ? 'Update User' : 'Create User' }}
                        </button>
                        <button type="reset" class="btn btn-outline-secondary ms-2">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Password toggle and tooltips -->
    <x-slot name="script">
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                $('#togglePassword').click(function() {
                    const $passwordInput = $('#password');
                    const $icon = $(this).find('i');

                    if ($passwordInput.attr('type') === 'password') {
                        $passwordInput.attr('type', 'text');
                        $icon.removeClass('bi-eye').addClass('bi-eye-slash');
                    } else {
                        $passwordInput.attr('type', 'password');
                        $icon.removeClass('bi-eye-slash').addClass('bi-eye');
                    }
                });
            });
        </script>
    </x-slot>
</x-app-layout>
