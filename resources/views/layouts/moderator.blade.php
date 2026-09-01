<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="base-url" content="{{ rtrim(config('app.url'), '/') }}">

    <title>@yield('title', 'Moderator Panel - DiscussHub')</title>

    <!-- Bootstrap 5 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Custom Design System -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    @stack('styles')
</head>
<body>
    <!-- Moderator Top Navbar -->
    <nav class="dg-navbar navbar navbar-expand-lg py-2">
        <div class="container-fluid px-4">
            <a class="dg-brand text-decoration-none" href="{{ route('moderator.dashboard') }}">
                <div class="d-inline-flex align-items-center justify-content-center bg-warning text-dark rounded p-1" style="width: 32px; height: 32px;">
                    <i class="bi bi-shield-half fs-5"></i>
                </div>
                <span>Discuss<span class="text-primary">Hub</span></span>
                <span class="badge bg-warning-subtle text-warning border border-warning small ms-1">Moderator Panel</span>
            </a>

            <div class="ms-auto d-flex align-items-center gap-3">
                <button class="btn btn-link text-secondary text-decoration-none p-1" id="theme-toggle-btn" title="Toggle Theme">
                    <i id="theme-toggle-icon" class="bi bi-moon-stars-fill fs-5"></i>
                </button>

                <a href="{{ route('home') }}" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Live Site
                </a>

                <div class="dropdown">
                    <button class="btn btn-light rounded-pill border d-flex align-items-center gap-2 py-1 px-3" data-bs-toggle="dropdown">
                        <i class="bi bi-shield-half text-warning"></i>
                        <span class="fw-semibold small">{{ auth()->user()->user_name }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border mt-2">
                        <li><h6 class="dropdown-header">{{ auth()->user()->user_name }}</h6></li>
                        <li><a class="dropdown-item small" href="{{ route('profile.show') }}"><i class="bi bi-person me-2"></i> My Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item small text-danger">
                                    <i class="bi bi-box-arrow-right me-2"></i> Sign Out
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Moderator Content Grid -->
    <div class="container-fluid px-4 py-4">
        <div class="row g-4">
            <!-- Sidebar Navigation -->
            <div class="col-lg-3 col-xl-2">
                <div class="dg-card p-3 sticky-top" style="top: 80px;">
                    <div class="small fw-bold text-muted text-uppercase mb-2 px-2" style="font-size: 0.7rem;">Overview</div>
                    <a href="{{ route('moderator.dashboard') }}" class="admin-nav-link {{ request()->routeIs('moderator.dashboard') ? 'active' : '' }}">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>

                    <div class="small fw-bold text-muted text-uppercase mt-3 mb-2 px-2" style="font-size: 0.7rem;">Moderation Queues</div>
                    <a href="{{ route('moderator.ai-queue') }}" class="admin-nav-link {{ request()->routeIs('moderator.ai-queue') ? 'active' : '' }}">
                        <i class="bi bi-robot text-primary"></i> AI Flagged Content
                    </a>
                    <a href="{{ route('moderator.report-queue') }}" class="admin-nav-link {{ request()->routeIs('moderator.report-queue') ? 'active' : '' }}">
                        <i class="bi bi-flag text-danger"></i> User Reports Queue
                    </a>

                    <div class="small fw-bold text-muted text-uppercase mt-3 mb-2 px-2" style="font-size: 0.7rem;">My Activity</div>
                    <a href="{{ route('moderator.history') }}" class="admin-nav-link {{ request()->routeIs('moderator.history') ? 'active' : '' }}">
                        <i class="bi bi-clock-history"></i> Action History
                    </a>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="col-lg-9 col-xl-10">
                <!-- Global Alerts -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show border d-flex align-items-center gap-2 mb-4">
                        <i class="bi bi-check-circle-fill fs-5"></i>
                        <div>{{ session('success') }}</div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('info'))
                    <div class="alert alert-info alert-dismissible fade show border d-flex align-items-center gap-2 mb-4">
                        <i class="bi bi-info-circle-fill fs-5"></i>
                        <div>{{ session('info') }}</div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('warning'))
                    <div class="alert alert-warning alert-dismissible fade show border d-flex align-items-center gap-2 mb-4">
                        <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                        <div>{{ session('warning') }}</div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show border d-flex align-items-center gap-2 mb-4">
                        <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                        <div>{{ session('error') }}</div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @yield('content')
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/app.js') }}"></script>

    @stack('scripts')
</body>
</html>
