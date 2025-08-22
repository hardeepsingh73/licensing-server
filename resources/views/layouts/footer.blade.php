<footer class="bg-primary text-white mt-auto">
    <div class="container py-3 d-flex flex-column flex-md-row align-items-center justify-content-between">
        <span class="mb-2 mb-md-0">
            &copy; {{ date('Y') }} {{ setting('site_name', config('app.name')) }}. All rights reserved.
        </span>
        <div class="footer-links">
            <a href="{{ url('/privacy-policy') }}" class="text-white text-decoration-none me-3">
                <i class="bi bi-shield-lock-fill me-1"></i> Privacy Policy
            </a>
            <a href="{{ url('/terms') }}" class="text-white text-decoration-none">
                <i class="bi bi-file-earmark-text-fill me-1"></i> Terms
            </a>
        </div>
    </div>
</footer>
