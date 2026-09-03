<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="base-url" content="{{ rtrim(config('app.url'), '/') }}">

    <title>@yield('title', 'Admin Console - DiscussHub')</title>

    <!-- Bootstrap 5 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Favicon & Touch Icons -->
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.png') }}">

    <!-- Custom Design System -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    @stack('styles')
</head>
<body>
    @include('layouts.partials.preloader')

    <!-- Admin Top Navbar -->
    <nav class="dg-navbar navbar navbar-expand-lg py-2">
        <div class="container-fluid px-4">
            <a class="dg-brand text-decoration-none d-inline-flex align-items-center gap-2" href="{{ route('admin.dashboard') }}">
                <img src="{{ asset('logo.png') }}" alt="DiscussHub" class="dg-brand-logo">
                <span class="badge bg-danger-subtle text-danger border border-danger small">Admin Console</span>
            </a>

            <div class="ms-auto d-flex align-items-center gap-2">
                <button class="dg-nav-icon-btn" id="theme-toggle-btn" title="Toggle Theme">
                    <i id="theme-toggle-icon" class="bi bi-moon-stars-fill fs-5"></i>
                </button>

                <a href="{{ route('home') }}" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-medium d-inline-flex align-items-center gap-1">
                    <i class="bi bi-box-arrow-up-right"></i> Live Site
                </a>

                <div class="dropdown">
                    <button class="dg-user-pill border-0 btn" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle text-danger fs-5"></i>
                        <span class="fw-semibold small">{{ session('admin_name', 'Administrator') }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border mt-2">
                        <li>
                            <form action="{{ route('admin.logout') }}" method="POST">
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

    <!-- Admin Content Grid -->
    <div class="container-fluid px-4 py-4">
        <div class="row g-4">
            <!-- Sidebar Navigation -->
            <div class="col-lg-3 col-xl-2">
                <div class="dg-card p-3 sticky-top" style="top: 80px;">
                    <div class="small fw-bold text-muted text-uppercase mb-2 px-2" style="font-size: 0.7rem;">Overview</div>
                    <a href="{{ route('admin.dashboard') }}" class="admin-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                    <a href="{{ route('admin.analytics.index') }}" class="admin-nav-link {{ request()->routeIs('admin.analytics.*') ? 'active' : '' }}">
                        <i class="bi bi-graph-up-arrow"></i> Analytics & Trends
                    </a>
                    <a href="{{ route('admin.ai-center.index') }}" class="admin-nav-link {{ request()->routeIs('admin.ai-center.*') ? 'active' : '' }}">
                        <i class="bi bi-cpu text-primary"></i> Intelligence Center
                    </a>

                    <div class="small fw-bold text-muted text-uppercase mt-3 mb-2 px-2" style="font-size: 0.7rem;">Moderation & Safety</div>
                    <a href="{{ route('admin.reports.index') }}" class="admin-nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                        <i class="bi bi-flag text-danger"></i> Reports Queue
                    </a>
                    <a href="{{ route('admin.contact.index') }}" class="admin-nav-link {{ request()->routeIs('admin.contact.*') ? 'active' : '' }}">
                        <i class="bi bi-envelope"></i> Inquiries & Feedback
                    </a>
                    <a href="{{ route('admin.audit-logs.index') }}" class="admin-nav-link {{ request()->routeIs('admin.audit-logs.*') ? 'active' : '' }}">
                        <i class="bi bi-journal-text"></i> Audit Logs
                    </a>

                    <div class="small fw-bold text-muted text-uppercase mt-3 mb-2 px-2" style="font-size: 0.7rem;">Content Management</div>
                    <a href="{{ route('admin.users.index') }}" class="admin-nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        <i class="bi bi-people"></i> Users
                    </a>
                    <a href="{{ route('admin.questions.index') }}" class="admin-nav-link {{ request()->routeIs('admin.questions.*') ? 'active' : '' }}">
                        <i class="bi bi-question-circle"></i> Questions
                    </a>
                    <a href="{{ route('admin.answers.index') }}" class="admin-nav-link {{ request()->routeIs('admin.answers.*') ? 'active' : '' }}">
                        <i class="bi bi-chat-dots"></i> Answers
                    </a>
                    <a href="{{ route('admin.categories.index') }}" class="admin-nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                        <i class="bi bi-folder"></i> Categories
                    </a>
                    <a href="{{ route('admin.tags.index') }}" class="admin-nav-link {{ request()->routeIs('admin.tags.*') ? 'active' : '' }}">
                        <i class="bi bi-tags"></i> Tags & Merging
                    </a>
                    <a href="{{ route('admin.badges.index') }}" class="admin-nav-link {{ request()->routeIs('admin.badges.*') ? 'active' : '' }}">
                        <i class="bi bi-award text-warning"></i> Badges
                    </a>
                    <a href="{{ route('admin.moderators.index') }}" class="admin-nav-link {{ request()->routeIs('admin.moderators.*') ? 'active' : '' }}">
                        <i class="bi bi-shield-half text-warning"></i> Moderators
                    </a>

                    <div class="small fw-bold text-muted text-uppercase mt-3 mb-2 px-2" style="font-size: 0.7rem;">Configuration</div>
                    <a href="{{ route('admin.reputation-settings.index') }}" class="admin-nav-link {{ request()->routeIs('admin.reputation-settings.*') ? 'active' : '' }}">
                        <i class="bi bi-stars text-warning"></i> Reputation Points
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
