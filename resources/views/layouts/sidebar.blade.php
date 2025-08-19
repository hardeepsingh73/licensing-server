<div id="sidebar" class="bg-white border-end d-none d-lg-block shadow-sm" style="width: 250px;">
    <div class="list-group list-group-flush vh-100 overflow-auto">
        <ul class="side-menu list-unstyled mb-0">

            <!-- Dashboard -->
            <li class="sidebar-layout {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <a href="{{ route('dashboard') }}"
                    class="list-group-item list-group-item-action d-flex align-items-center {{ request()->routeIs('dashboard') ? 'active fw-semibold' : '' }}">
                    <i class="bi bi-house-door me-2 fs-5"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <!-- ===================== MANAGEMENT ===================== -->
            <li class="px-3 pt-3 pb-2">
                <span class="text-uppercase small fw-bold text-muted">Management</span>
            </li>

            <!-- Staff Management (Collapsible) -->
            @canany(['view users', 'view roles', 'view permissions', 'delete permissions'])
                @php
                    $staffActive =
                        request()->routeIs('users.*') ||
                        request()->routeIs('roles.*') ||
                        request()->routeIs('permissions.*');
                @endphp
                <li class="sidebar-layout {{ $staffActive ? 'active' : '' }}">
                    <a href="#staff-nav"
                        class="list-group-item list-group-item-action d-flex align-items-center {{ $staffActive ? '' : 'collapsed' }}"
                        data-bs-toggle="collapse" aria-expanded="{{ $staffActive ? 'true' : 'false' }}"
                        aria-controls="staff-nav">
                        <i class="bi bi-shield-lock me-2 fs-5"></i>
                        <span>Staff Management</span>
                        <i class="bi bi-chevron-down ms-auto"></i>
                    </a>
                    <ul id="staff-nav" class="submenu collapse {{ $staffActive ? 'show' : '' }} list-unstyled ps-4"
                        data-bs-parent="#sidebar">
                        @can('view users')
                            <li class="{{ request()->routeIs('users.*') ? 'active' : '' }}">
                                <a href="{{ route('users.index') }}"
                                    class="list-group-item list-group-item-action d-flex align-items-center {{ request()->routeIs('users.*') ? 'active fw-semibold' : '' }}">
                                    <i class="bi bi-people me-2"></i>
                                    <span>Staff Users</span>
                                </a>
                            </li>
                        @endcan

                        @can('view permissions')
                            <li class="{{ request()->routeIs('permissions.*') ? 'active' : '' }}">
                                <a href="{{ route('permissions.index') }}"
                                    class="list-group-item list-group-item-action d-flex align-items-center {{ request()->routeIs('permissions.*') ? 'active fw-semibold' : '' }}">
                                    <i class="bi bi-key me-2"></i>
                                    <span>Permissions</span>
                                </a>
                            </li>
                        @endcan

                        @can('view roles')
                            <li class="{{ request()->routeIs('roles.*') ? 'active' : '' }}">
                                <a href="{{ route('roles.index') }}"
                                    class="list-group-item list-group-item-action d-flex align-items-center {{ request()->routeIs('roles.*') ? 'active fw-semibold' : '' }}">
                                    <i class="bi bi-person-badge me-2"></i>
                                    <span>Roles</span>
                                </a>
                            </li>
                        @endcan
                    </ul>
                </li>
            @endcanany

            <!-- Settings -->
            @can('edit settings')
                <li class="{{ request()->routeIs('settings.*') ? 'active' : '' }}">
                    <a href="{{ route('settings.index') }}"
                        class="list-group-item list-group-item-action d-flex align-items-center {{ request()->routeIs('settings.*') ? 'active fw-semibold' : '' }}">
                        <i class="bi bi-gear me-2 fs-5"></i>
                        <span>Settings</span>
                    </a>
                </li>
            @endcan
            
            <!-- License -->
            @can('view licenses')
                <li class="{{ request()->routeIs('licenses.*') ? 'active' : '' }}">
                    <a href="{{ route('licenses.index') }}"
                        class="list-group-item list-group-item-action d-flex align-items-center {{ request()->routeIs('licenses.*') ? 'active fw-semibold' : '' }}">
                        <i class="bi bi-key me-2 fs-5"></i>
                        <span>License</span>
                    </a>
                </li>
            @endcan
            <!-- ===================== LOGS ===================== -->
            <li class="px-3 pt-3 pb-2">
                <span class="text-uppercase small fw-bold text-muted">Logs</span>
            </li>

            @can('view activity logs')
                @if (setting('activity_logs', true))
                    <li class="{{ request()->routeIs('activity-logs.*') ? 'active' : '' }}">
                        <a href="{{ route('activity-logs.index') }}"
                            class="list-group-item list-group-item-action d-flex align-items-center {{ request()->routeIs('activity-logs.*') ? 'active fw-semibold' : '' }}">
                            <i class="bi bi-list-check me-2 fs-5"></i>
                            <span>Activity Logs</span>
                        </a>
                    </li>
                @endif
            @endcan

            @can('view api logs')
                @if (setting('api_logs', true))
                    <li class="{{ request()->routeIs('api-logs.*') ? 'active' : '' }}">
                        <a href="{{ route('api-logs.index') }}"
                            class="list-group-item list-group-item-action d-flex align-items-center {{ request()->routeIs('api-logs.*') ? 'active fw-semibold' : '' }}">
                            <i class="bi bi-activity me-2 fs-5"></i>
                            <span>API Logs</span>
                        </a>
                    </li>
                @endif
            @endcan

            @can('view email logs')
                @if (setting('email_logs', true))
                    <li class="{{ request()->routeIs('email-logs.*') ? 'active' : '' }}">
                        <a href="{{ route('email-logs.index') }}"
                            class="list-group-item list-group-item-action d-flex align-items-center {{ request()->routeIs('email-logs.*') ? 'active fw-semibold' : '' }}">
                            <i class="bi bi-envelope me-2 fs-5"></i>
                            <span>Email Logs</span>
                        </a>
                    </li>
                @endif
            @endcan

            @can('view error logs')
                @if (setting('error_logs', true))
                    <li class="{{ request()->routeIs('error-logs.*') ? 'active' : '' }}">
                        <a href="{{ route('error-logs.index') }}"
                            class="list-group-item list-group-item-action d-flex align-items-center {{ request()->routeIs('error-logs.*') ? 'active fw-semibold' : '' }}">
                            <i class="bi bi-exclamation-triangle me-2 fs-5"></i>
                            <span>Error Logs</span>
                        </a>
                    </li>
                @endif
            @endcan

            @can('view login history')
                @if (setting('login_history', true))
                    <li class="{{ request()->routeIs('login-history.*') ? 'active' : '' }}">
                        <a href="{{ route('login-history.index') }}"
                            class="list-group-item list-group-item-action d-flex align-items-center {{ request()->routeIs('login-history.*') ? 'active fw-semibold' : '' }}">
                            <i class="bi bi-clock-history me-2 fs-5"></i>
                            <span>Login History</span>
                        </a>
                    </li>
                @endif
            @endcan
        </ul>
    </div>
</div>
