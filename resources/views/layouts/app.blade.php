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

    <!-- Custom Design System -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    @stack('styles')
</head>
<body>
    <!-- Top Navigation Bar -->
    <nav class="dg-navbar navbar navbar-expand-lg py-2">
        <div class="container">
            <a class="dg-brand text-decoration-none" href="{{ route('home') }}">
                <div class="d-inline-flex align-items-center justify-content-center bg-primary text-white rounded p-1" style="width: 32px; height: 32px;">
                    <i class="bi bi-chat-square-quote-fill fs-5"></i>
                </div>
                <span>Discuss<span class="text-primary">Hub</span></span>
                <span class="brand-badge">AI 2.0</span>
            </a>

            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarContent">
                <!-- Search bar -->
                <form action="{{ route('search') }}" method="GET" class="dg-search-box mx-lg-4 my-2 my-lg-0">
                    <i class="bi bi-search dg-search-icon"></i>
                    <input type="text" name="q" class="dg-search-input" placeholder="Search questions, topics, answers, tags..." value="{{ request('q') }}">
                </form>

                <ul class="navbar-nav me-auto mb-2 mb-lg-0 gap-1">
                    <li class="nav-item">
                        <a class="nav-link px-3 {{ request()->routeIs('home') ? 'active fw-semibold text-primary' : 'text-secondary' }}" href="{{ route('home') }}">
                            <i class="bi bi-house me-1"></i> Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-3 {{ request()->routeIs('questions.*') ? 'active fw-semibold text-primary' : 'text-secondary' }}" href="{{ route('questions.index') }}">
                            <i class="bi bi-chat-left-text me-1"></i> Discussions
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-3 {{ request()->routeIs('categories.*') ? 'active fw-semibold text-primary' : 'text-secondary' }}" href="{{ route('categories.index') }}">
                            <i class="bi bi-grid me-1"></i> Topics
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-3 {{ request()->routeIs('tags.*') ? 'active fw-semibold text-primary' : 'text-secondary' }}" href="{{ route('tags.index') }}">
                            <i class="bi bi-tags me-1"></i> Tags
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-3 {{ request()->routeIs('contact.*') ? 'active fw-semibold text-primary' : 'text-secondary' }}" href="{{ route('contact.show') }}">
                            <i class="bi bi-envelope me-1"></i> Contact Us
                        </a>
                    </li>
                </ul>

                <!-- Right Action Items -->
                <div class="d-flex align-items-center gap-3">
                    <!-- Theme Toggle Button -->
                    <button class="btn btn-link text-secondary text-decoration-none p-1" id="theme-toggle-btn" title="Toggle Light/Dark Theme">
                        <i id="theme-toggle-icon" class="bi bi-moon-stars-fill fs-5"></i>
                    </button>

                    @auth
                        <!-- Bookmarks -->
                        <a href="{{ route('bookmarks.index') }}" class="btn btn-link text-secondary text-decoration-none p-1 position-relative" title="Saved Discussions">
                            <i class="bi bi-bookmark fs-5"></i>
                        </a>

                        <!-- Notifications Bell -->
                        @php
                            $unreadNotificationsCount = auth()->user()->unreadNotificationsCount();
                        @endphp
                        <a href="{{ route('notifications.index') }}" class="btn btn-link text-secondary text-decoration-none p-1 position-relative" title="Notifications">
                            <i class="bi bi-bell fs-5"></i>
                            @if($unreadNotificationsCount > 0)
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
                                    {{ $unreadNotificationsCount }}
                                </span>
                            @endif
                        </a>

                        <!-- User Profile Dropdown -->
                        <div class="dropdown">
                            <button class="btn btn-light rounded-pill d-flex align-items-center gap-2 py-1 px-3 border" type="button" data-bs-toggle="dropdown">
                                @if(auth()->user()->profile_image && auth()->user()->profile_image !== 'default_profile.png')
                                    <img src="{{ asset('profiles/' . auth()->user()->profile_image) }}" class="rounded-circle object-fit-cover" width="26" height="26" alt="avatar">
                                @else
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 26px; height: 26px; font-size: 0.75rem;">
                                        {{ strtoupper(substr(auth()->user()->user_name, 0, 1)) }}
                                    </div>
                                @endif
                                <span class="fw-semibold small d-none d-md-inline">{{ auth()->user()->user_name }}</span>
                                <span class="reputation-badge py-0 px-2" title="Reputation">
                                    <i class="bi bi-stars"></i> {{ auth()->user()->reputation }}
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
                        <a href="{{ route('questions.create') }}" class="btn btn-primary btn-sm rounded-pill px-3 fw-semibold d-inline-flex align-items-center gap-1">
                            <i class="bi bi-plus-lg"></i> Ask Question
                        </a>

                        @if(auth()->user()->role === 'moderator')
                            <a href="{{ route('moderator.dashboard') }}" class="btn btn-warning btn-sm rounded-pill px-3 fw-semibold d-inline-flex align-items-center gap-1">
                                <i class="bi bi-shield-half"></i> Mod Panel
                            </a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="btn btn-outline-secondary btn-sm fw-medium px-3 rounded-pill">Sign In</a>
                        <a href="{{ route('register') }}" class="btn btn-primary btn-sm fw-semibold px-3 rounded-pill">Join Community</a>
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
                    <div class="d-flex align-items-center gap-2 fw-bold fs-5 text-primary mb-2">
                        <i class="bi bi-chat-square-quote-fill"></i> DiscussHub
                    </div>
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
