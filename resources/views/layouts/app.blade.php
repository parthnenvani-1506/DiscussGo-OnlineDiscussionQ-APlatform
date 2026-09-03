<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="base-url" content="{{ rtrim(config('app.url'), '/') }}">

    <title>@yield('title', 'DiscussHub - Open Knowledge Sharing & Community Q&A')</title>
    <meta name="description" content="@yield('meta_description', 'DiscussHub is a modern discussion and Q&A platform where curious minds discover real answers, share expertise, and explore multifaceted perspectives.')">

    <!-- Bootstrap 5 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- Highlight.js for Code Highlighting (Optional for technical topics) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/atom-one-dark.min.css">

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

    <!-- Top Navigation Bar -->
    <nav class="dg-navbar navbar navbar-expand-lg py-2">
        <div class="container-fluid px-4" style="max-width: 1440px;">
            <a class="dg-brand text-decoration-none d-inline-flex align-items-center" href="{{ route('home') }}">
                <img src="{{ asset('logo.png') }}" alt="DiscussHub" class="dg-brand-logo">
            </a>

            <!-- Mobile Quick Actions & Animated Hamburger -->
            <div class="d-flex align-items-center gap-2 d-lg-none">
                <button class="dg-nav-icon-btn p-0" id="theme-toggle-btn-mobile" title="Toggle Light/Dark Theme" onclick="document.getElementById('theme-toggle-btn')?.click()">
                    <i class="bi bi-moon-stars-fill fs-5"></i>
                </button>
                <button class="dg-hamburger-btn collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="dg-hamburger-line"></span>
                    <span class="dg-hamburger-line"></span>
                    <span class="dg-hamburger-line"></span>
                </button>
            </div>

            <div class="collapse navbar-collapse" id="navbarContent">
                <!-- Search bar with Keyboard Shortcut -->
                <form action="{{ route('search') }}" method="GET" class="dg-search-box ms-lg-2 me-lg-3 my-2 my-lg-0">
                    <i class="bi bi-search dg-search-icon"></i>
                    <input type="text" name="q" class="dg-search-input" placeholder="Search discussions..." value="{{ request('q') }}">
                    <kbd class="dg-search-kbd d-none d-xl-inline-block">Ctrl K</kbd>
                </form>

                <ul class="navbar-nav me-auto mb-2 mb-lg-0 gap-1">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('home') ? 'active' : 'text-secondary' }}" href="{{ route('home') }}">
                            Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('questions.*') ? 'active' : 'text-secondary' }}" href="{{ route('questions.index') }}">
                            Discussions
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('categories.*') ? 'active' : 'text-secondary' }}" href="{{ route('categories.index') }}">
                            Topics
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('tags.*') ? 'active' : 'text-secondary' }}" href="{{ route('tags.index') }}">
                            Tags
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('contact.*') ? 'active' : 'text-secondary' }}" href="{{ route('contact.show') }}">
                            Contact Us
                        </a>
                    </li>
                </ul>

                <!-- Right Action Items -->
                <div class="dg-nav-right-actions d-flex align-items-center gap-2">
                    <!-- Desktop Theme Toggle Button -->
                    <button class="dg-nav-icon-btn d-none d-lg-inline-flex" id="theme-toggle-btn" title="Toggle Light/Dark Theme">
                        <i id="theme-toggle-icon" class="bi bi-moon-stars-fill fs-5"></i>
                    </button>

                    @auth
                        <!-- Mobile Quick Actions: Bookmarks & Notifications in cohesive row -->
                        <div class="dg-mobile-quick-actions d-flex align-items-center gap-2">
                            <!-- Bookmarks -->
                            <a href="{{ route('bookmarks.index') }}" class="dg-nav-icon-btn dg-mobile-action-pill" title="Saved Discussions">
                                <i class="bi bi-bookmark fs-5"></i>
                                <span class="d-lg-none ms-2 fw-medium">Saved</span>
                            </a>

                            <!-- Notifications Bell -->
                            @php
                                $unreadNotificationsCount = auth()->user()->unreadNotificationsCount();
                            @endphp
                            <a href="{{ route('notifications.index') }}" class="dg-nav-icon-btn position-relative dg-mobile-action-pill" title="Notifications">
                                <i class="bi bi-bell fs-5"></i>
                                <span class="d-lg-none ms-2 fw-medium">Notifications</span>
                                @if($unreadNotificationsCount > 0)
                                    <span class="dg-badge-radar badge rounded-pill">
                                        {{ $unreadNotificationsCount }}
                                    </span>
                                @endif
                            </a>
                        </div>

                        <!-- User Profile Dropdown -->
                        <div class="dropdown w-100 w-lg-auto">
                            <button class="dg-user-pill border-0 btn w-100 justify-content-between justify-content-lg-start" type="button" data-bs-toggle="dropdown">
                                <div class="d-flex align-items-center gap-2">
                                    @if(auth()->user()->profile_image && auth()->user()->profile_image !== 'default_profile.png')
                                        <img src="{{ asset('profiles/' . auth()->user()->profile_image) }}" class="rounded-circle object-fit-cover" width="28" height="28" alt="avatar">
                                    @else
                                        <div class="dg-user-avatar-initial">
                                            {{ strtoupper(substr(auth()->user()->user_name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <span class="fw-semibold small text-truncate dg-user-name">{{ auth()->user()->user_name }}</span>
                                </div>
                                <span class="reputation-badge rep-badge-fmt ms-auto ms-lg-0"
                                    data-rep="{{ auth()->user()->reputation }}"
                                    data-bs-toggle="tooltip"
                                    data-bs-placement="bottom"
                                    title="{{ number_format(auth()->user()->reputation) }} reputation points">
                                    <i class="bi bi-stars"></i> <span class="rep-value">{{ auth()->user()->reputation }}</span>
                                </span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border mt-2">
                                <li><h6 class="dropdown-header">{{ auth()->user()->user_name }} ({{ ucfirst(auth()->user()->level ?? 'newcomer') }})</h6></li>
                                <li><a class="dropdown-item small" href="{{ route('profile.show') }}"><i class="bi bi-person me-2"></i> Profile & Achievements</a></li>
                                <li><a class="dropdown-item small" href="{{ route('profile.edit') }}"><i class="bi bi-gear me-2"></i> Account Settings</a></li>
                                <li><a class="dropdown-item small" href="{{ route('bookmarks.index') }}"><i class="bi bi-bookmark me-2"></i> Saved Discussions</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="dropdown-item small text-danger"><i class="bi bi-box-arrow-right me-2"></i> Logout</button>
                                    </form>
                                </li>
                            </ul>
                        </div>

                        <!-- Ask Question CTA -->
                        <a href="{{ route('questions.create') }}" class="dg-btn-cta w-100 w-lg-auto justify-content-center">
                            <i class="bi bi-plus-lg"></i> Ask Question
                        </a>

                        @if(auth()->user()->role === 'moderator')
                            <a href="{{ route('moderator.dashboard') }}" class="btn btn-warning btn-sm rounded-pill px-3 fw-semibold d-inline-flex align-items-center justify-content-center gap-1 w-100 w-lg-auto">
                                <i class="bi bi-shield-half"></i> Mod Panel
                            </a>
                        @endif
                    @else
                        <div class="d-flex align-items-center gap-2 w-100 w-lg-auto">
                            <a href="{{ route('login') }}" class="dg-btn-outline flex-fill text-center justify-content-center">Sign In</a>
                            <a href="{{ route('register') }}" class="dg-btn-cta flex-fill text-center justify-content-center">Join Community</a>
                        </div>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Container -->
    <main class="py-4 flex-grow-1">
        <div class="container">
            <!-- Global Flash Messages -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show border d-flex align-items-center gap-2 mb-4" role="alert">
                    <i class="bi bi-check-circle-fill fs-5"></i>
                    <div>{{ session('success') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('info'))
                <div class="alert alert-info alert-dismissible fade show border d-flex align-items-center gap-2 mb-4" role="alert">
                    <i class="bi bi-info-circle-fill fs-5"></i>
                    <div>{{ session('info') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show border d-flex align-items-center gap-2 mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                    <div>{{ session('error') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <!-- Footer -->
    <footer class="dg-footer">
        <div class="container">
            <div class="row g-4 mb-4">
                <div class="col-lg-4">
                    <a href="{{ route('home') }}" class="text-decoration-none d-inline-block mb-3">
                        <img src="{{ asset('logo.png') }}" alt="DiscussHub" class="dg-footer-logo">
                    </a>
                    <p class="small text-secondary mb-3">
                        Open knowledge sharing and Q&amp;A platform empowering curious minds to ask questions, share perspectives, and discover verified answers.
                    </p>
                    <div class="d-flex gap-3 text-secondary">
                        <span class="small"><i class="bi bi-globe text-primary me-1"></i> Open Knowledge</span>
                        <span class="small"><i class="bi bi-shield-check text-success me-1"></i> Verified Solutions</span>
                    </div>
                </div>
                <div class="col-6 col-lg-2">
                    <h6 class="fw-bold mb-3 small text-uppercase">Discover</h6>
                    <ul class="list-unstyled small d-flex flex-column gap-2 mb-0">
                        <li><a href="{{ route('questions.index') }}" class="text-secondary text-decoration-none">All Discussions</a></li>
                        <li><a href="{{ route('categories.index') }}" class="text-secondary text-decoration-none">Topics</a></li>
                        <li><a href="{{ route('tags.index') }}" class="text-secondary text-decoration-none">Tags</a></li>
                        <li><a href="{{ route('search') }}" class="text-secondary text-decoration-none">Search</a></li>
                    </ul>
                </div>
                <div class="col-6 col-lg-3">
                    <h6 class="fw-bold mb-3 small text-uppercase">Intelligent Features</h6>
                    <ul class="list-unstyled small d-flex flex-column gap-2 mb-0">
                        <li><span class="text-secondary">Multi-Answer Consensus</span></li>
                        <li><span class="text-secondary">Semantic Search Matching</span></li>
                        <li><span class="text-secondary">Taxonomy Tag Extractor</span></li>
                        <li><span class="text-secondary">Community Reputation System</span></li>
                    </ul>
                </div>
                <div class="col-lg-3">
                    <h6 class="fw-bold mb-3 small text-uppercase">Support & Inquiries</h6>
                    <p class="small text-secondary mb-2">Have a question or feedback? Reach out to our community team.</p>
                    <a href="{{ route('contact.show') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">Contact Team</a>
                </div>
            </div>
            <hr class="border-secondary opacity-25">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 small text-secondary">
                <div>&copy; {{ date('Y') }} DiscussHub. A platform for curious minds.</div>
                <div>Built with Laravel & Bootstrap.</div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Highlight.js JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>

    <!-- Marked.js Markdown Parser -->
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>

    <!-- App JavaScript -->
    <script src="{{ asset('js/app.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Render markdown content blocks
            document.querySelectorAll('.render-markdown').forEach(el => {
                const raw = el.getAttribute('data-raw') || el.innerText || el.textContent;
                if (window.marked && raw) {
                    el.innerHTML = marked.parse(raw);
                }
            });

            // Trigger code syntax highlighting if any code blocks exist
            if (window.hljs) {
                document.querySelectorAll('pre code').forEach(block => {
                    hljs.highlightElement(block);
                });
            }
        });
    </script>

    @stack('scripts')
</body>
</html>
