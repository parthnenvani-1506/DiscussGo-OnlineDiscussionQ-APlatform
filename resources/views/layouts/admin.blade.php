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

    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Custom Design System -->
    <link href="{{ asset('css/app.css') }}?v=5.2" rel="stylesheet">
    <link href="{{ asset('css/admin.css') }}?v=2.6" rel="stylesheet">

    <!-- Prevent Flash of Uncollapsed Sidebar -->
    <script>
        (function() {
            if (localStorage.getItem('adminSidebarCollapsed') === '1') {
                document.documentElement.classList.add('sidebar-collapsed');
            }
        })();
    </script>

    @stack('styles')
</head>
<body class="admin-body">
    @include('layouts.partials.preloader')

    @php
        try {
            $pendingReportsCount = \App\Models\Report::where('status', 'pending')->count();
            $unreadContactsCount = \App\Models\ContactMessage::where('is_read', false)->count();
        } catch (\Throwable $e) {
            $pendingReportsCount = 0;
            $unreadContactsCount = 0;
        }
    @endphp

    <!-- Mobile Sidebar Backdrop Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Floating App Sidebar -->
    <aside class="app-sidebar" id="appSidebar">
        <!-- Sidebar Brand -->
        <div class="sidebar-brand">
            <a href="{{ route('admin.dashboard') }}" class="d-inline-flex align-items-center text-decoration-none">
                <img src="{{ asset('logo.png') }}" alt="DiscussHub" class="sidebar-logo">
            </a>
            <div class="sidebar-logo-small" id="sidebarLogoSmall">
                <img src="{{ asset('favicon.png') }}" alt="DiscussHub">
            </div>
            <button type="button" id="sidebarCloseBtn" aria-label="Close Sidebar">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <!-- Sidebar Navigation -->
        <ul class="sidebar-nav">
            <!-- Overview -->
            <li class="sidebar-heading">Overview</li>
            <li class="nav-item">
                <a href="{{ route('admin.dashboard') }}" class="admin-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" data-tooltip="Dashboard">
                    <span class="nav-icon"><i class="bi bi-speedometer2"></i></span>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.analytics.index') }}" class="admin-nav-link {{ request()->routeIs('admin.analytics.*') ? 'active' : '' }}" data-tooltip="Analytics & Trends">
                    <span class="nav-icon"><i class="bi bi-graph-up-arrow"></i></span>
                    <span class="nav-text">Analytics & Trends</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.ai-center.index') }}" class="admin-nav-link {{ request()->routeIs('admin.ai-center.*') ? 'active' : '' }}" data-tooltip="Intelligence Center">
                    <span class="nav-icon"><i class="bi bi-cpu"></i></span>
                    <span class="nav-text">Intelligence Center</span>
                </a>
            </li>

            <!-- Moderation & Safety -->
            <li class="sidebar-heading">Moderation & Safety</li>
            <li class="nav-item">
                <a href="{{ route('admin.reports.index') }}" class="admin-nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}" data-tooltip="Reports Queue">
                    <span class="nav-icon"><i class="bi bi-flag"></i></span>
                    <span class="nav-text">Reports Queue</span>
                    @if($pendingReportsCount > 0)
                        <span class="nav-badge">{{ $pendingReportsCount }}</span>
                    @endif
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.contact.index') }}" class="admin-nav-link {{ request()->routeIs('admin.contact.*') ? 'active' : '' }}" data-tooltip="Inquiries & Feedback">
                    <span class="nav-icon"><i class="bi bi-envelope"></i></span>
                    <span class="nav-text">Inquiries & Feedback</span>
                    @if($unreadContactsCount > 0)
                        <span class="nav-badge">{{ $unreadContactsCount }}</span>
                    @endif
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.audit-logs.index') }}" class="admin-nav-link {{ request()->routeIs('admin.audit-logs.*') ? 'active' : '' }}" data-tooltip="Audit Logs">
                    <span class="nav-icon"><i class="bi bi-journal-text"></i></span>
                    <span class="nav-text">Audit Logs</span>
                </a>
            </li>

            <!-- Content Management -->
            <li class="sidebar-heading">Content Management</li>
            <li class="nav-item">
                <a href="{{ route('admin.users.index') }}" class="admin-nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" data-tooltip="Users">
                    <span class="nav-icon"><i class="bi bi-people"></i></span>
                    <span class="nav-text">Users</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.questions.index') }}" class="admin-nav-link {{ request()->routeIs('admin.questions.*') ? 'active' : '' }}" data-tooltip="Questions">
                    <span class="nav-icon"><i class="bi bi-question-circle"></i></span>
                    <span class="nav-text">Questions</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.answers.index') }}" class="admin-nav-link {{ request()->routeIs('admin.answers.*') ? 'active' : '' }}" data-tooltip="Answers">
                    <span class="nav-icon"><i class="bi bi-chat-dots"></i></span>
                    <span class="nav-text">Answers</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.categories.index') }}" class="admin-nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}" data-tooltip="Categories">
                    <span class="nav-icon"><i class="bi bi-folder"></i></span>
                    <span class="nav-text">Categories</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.tags.index') }}" class="admin-nav-link {{ request()->routeIs('admin.tags.*') ? 'active' : '' }}" data-tooltip="Tags & Merging">
                    <span class="nav-icon"><i class="bi bi-tags"></i></span>
                    <span class="nav-text">Tags & Merging</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.badges.index') }}" class="admin-nav-link {{ request()->routeIs('admin.badges.*') ? 'active' : '' }}" data-tooltip="Badges">
                    <span class="nav-icon"><i class="bi bi-award"></i></span>
                    <span class="nav-text">Badges</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.moderators.index') }}" class="admin-nav-link {{ request()->routeIs('admin.moderators.*') ? 'active' : '' }}" data-tooltip="Moderators">
                    <span class="nav-icon"><i class="bi bi-shield-shaded"></i></span>
                    <span class="nav-text">Moderators</span>
                </a>
            </li>

            <!-- Configuration -->
            <li class="sidebar-heading">Configuration</li>
            <li class="nav-item">
                <a href="{{ route('admin.reputation-settings.index') }}" class="admin-nav-link {{ request()->routeIs('admin.reputation-settings.*') ? 'active' : '' }}" data-tooltip="Reputation Points">
                    <span class="nav-icon"><i class="bi bi-stars"></i></span>
                    <span class="nav-text">Reputation Points</span>
                </a>
            </li>
        </ul>

        <!-- Sidebar Footer -->
        <div class="sidebar-footer">
            <div class="footer-full">
                <span>DiscussHub</span> <strong>Admin</strong>
            </div>
            <div class="footer-short">
                DH
            </div>
            <button type="button" class="sidebar-collapse-btn" id="sidebarCollapseBtn" title="Toggle Sidebar">
                <i class="bi bi-chevron-left" id="collapseIcon"></i>
                <span class="collapse-btn-text">Collapse</span>
            </button>
        </div>
    </aside>

    <!-- Floating Top Navbar -->
    <nav class="app-navbar">
        <div class="d-flex align-items-center">
            <button class="hamburger-btn" id="hamburgerBtn" type="button" aria-label="Open Sidebar">
                <i class="bi bi-list"></i>
            </button>
            <h1 class="navbar-page-title mb-0">
                @php
                    $rawTitle = html_entity_decode(trim($__env->yieldContent('title')), ENT_QUOTES, 'UTF-8');
                    $displayTitle = explode(' - ', $rawTitle)[0] ?? 'Admin Console';
                    if (empty($displayTitle)) $displayTitle = 'Admin Dashboard';
                @endphp
                {{ $displayTitle }}
            </h1>
        </div>

        <div class="navbar-actions">
            <!-- Theme Toggle -->
            <button class="dg-nav-icon-btn" id="theme-toggle-btn" title="Toggle Light/Dark Theme" type="button">
                <i id="theme-toggle-icon" class="bi bi-moon-stars-fill fs-5"></i>
            </button>

            <!-- Admin User Pill Dropdown -->
            <div class="dropdown">
                <button class="dg-admin-user-pill border-0 btn" data-bs-toggle="dropdown" type="button" aria-expanded="false">
                    <div class="dg-admin-avatar">
                        {{ strtoupper(substr(session('admin_name', 'Administrator'), 0, 1)) }}
                    </div>
                    <div class="dg-admin-info">
                        <div class="name">{{ session('admin_name', 'Administrator') }}</div>
                        <div class="role">Platform Admin</div>
                    </div>
                    <i class="bi bi-chevron-down text-muted small ms-1 d-none d-sm-inline"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border mt-2">
                    <li><h6 class="dropdown-header">{{ session('admin_name', 'Administrator') }}</h6></li>
                    <li><a class="dropdown-item small" href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2 me-2 text-primary"></i> Dashboard</a></li>
                    <li><a class="dropdown-item small" href="{{ route('home') }}" target="_blank"><i class="bi bi-box-arrow-up-right me-2 text-secondary"></i> View Live Site</a></li>
                    <li><hr class="dropdown-divider"></li>
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
    </nav>

    <!-- Main Content Grid -->
    <div class="app-main" id="appMain">
        <div class="app-content">
            <!-- Global Alerts -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show border d-flex align-items-center gap-2 mb-4 rounded-3 shadow-sm">
                    <i class="bi bi-check-circle-fill fs-5 text-success"></i>
                    <div class="fw-medium">{{ session('success') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show border d-flex align-items-center gap-2 mb-4 rounded-3 shadow-sm">
                    <i class="bi bi-exclamation-triangle-fill fs-5 text-danger"></i>
                    <div class="fw-medium">{{ session('error') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/app.js') }}?v=1.3"></script>

    <!-- Admin Interactive Behaviors (Sidebar Collapse, Mobile Drawer) -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const appSidebar = document.getElementById('appSidebar');
            const sidebarOverlay = document.getElementById('sidebarOverlay');
            const hamburgerBtn = document.getElementById('hamburgerBtn');
            const collapseBtn = document.getElementById('sidebarCollapseBtn');
            const closeBtn = document.getElementById('sidebarCloseBtn');

            // Restore collapsed state
            if (localStorage.getItem('adminSidebarCollapsed') === '1' && appSidebar) {
                appSidebar.classList.add('collapsed');
                document.documentElement.classList.add('sidebar-collapsed');
            }

            // Desktop Collapse Toggle
            if (collapseBtn && appSidebar) {
                collapseBtn.addEventListener('click', function() {
                    const isCollapsed = appSidebar.classList.toggle('collapsed');
                    document.documentElement.classList.toggle('sidebar-collapsed', isCollapsed);
                    localStorage.setItem('adminSidebarCollapsed', isCollapsed ? '1' : '0');
                });
            }

            // Mobile Open Sidebar
            if (hamburgerBtn && appSidebar && sidebarOverlay) {
                hamburgerBtn.addEventListener('click', function() {
                    appSidebar.classList.add('open');
                    sidebarOverlay.classList.add('show');
                    document.body.style.overflow = 'hidden';
                });
            }

            // Mobile Close Sidebar
            function closeMobileSidebar() {
                if (appSidebar) appSidebar.classList.remove('open');
                if (sidebarOverlay) sidebarOverlay.classList.remove('show');
                document.body.style.overflow = '';
            }

            if (closeBtn) closeBtn.addEventListener('click', closeMobileSidebar);
            if (sidebarOverlay) sidebarOverlay.addEventListener('click', closeMobileSidebar);
        });
    </script>

    @stack('scripts')
</body>
</html>
