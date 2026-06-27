<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Library Seat System') - UTAR Library</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        :root {
            --primary: #4F46E5;
            --primary-dark: #4338CA;
            --primary-light: #818CF8;
            --secondary: #0EA5E9;
            --success: #10B981;
            --warning: #F59E0B;
            --danger: #EF4444;
            --gray-50: #F8FAFC;
            --gray-100: #F1F5F9;
            --gray-200: #E2E8F0;
            --gray-300: #CBD5E1;
            --gray-400: #94A3B8;
            --gray-500: #64748B;
            --gray-600: #475569;
            --gray-700: #334155;
            --gray-800: #1E293B;
            --gray-900: #0F172A;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: var(--gray-50);
            color: var(--gray-800);
        }

        .navbar-custom {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            box-shadow: 0 4px 20px rgba(79, 70, 229, 0.3);
        }

        .navbar-custom .navbar-brand {
            font-weight: 700;
            font-size: 1.25rem;
            color: white !important;
        }

        .navbar-custom .nav-link {
            color: rgba(255, 255, 255, 0.85) !important;
            transition: all 0.2s;
            font-weight: 500;
        }

        .navbar-custom .nav-link:hover {
            color: white !important;
            transform: translateY(-1px);
        }

        .navbar-custom .nav-link.active {
            color: white !important;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 8px;
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border: none;
            color: white;
            padding: 10px 24px;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s;
            box-shadow: 0 4px 14px rgba(79, 70, 229, 0.35);
        }

        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(79, 70, 229, 0.45);
            color: white;
        }

        .btn-outline-primary-custom {
            border: 2px solid var(--primary);
            color: var(--primary);
            padding: 8px 20px;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s;
            background: transparent;
        }

        .btn-outline-primary-custom:hover {
            background: var(--primary);
            color: white;
            transform: translateY(-2px);
        }

        .card-custom {
            background: white;
            border-radius: 16px;
            border: none;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
            transition: all 0.3s;
            overflow: hidden;
        }

        .card-custom:hover {
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.10);
        }

        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 20px 24px;
            border-left: 4px solid var(--primary);
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05);
            transition: all 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 6px 24px rgba(0, 0, 0, 0.10);
        }

        .stat-card .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .seat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(60px, 1fr));
            gap: 8px;
        }

        .seat-item {
            width: 100%;
            aspect-ratio: 1;
            border-radius: 8px;
            border: 2px solid var(--gray-200);
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.6rem;
            font-weight: 600;
            color: var(--gray-500);
            cursor: pointer;
            transition: all 0.2s;
        }

        .seat-item:hover:not(.occupied):not(.disabled) {
            border-color: var(--primary);
            background: var(--gray-50);
            transform: scale(1.05);
        }

        .seat-item.available {
            border-color: var(--success);
            color: var(--success);
        }

        .seat-item.available:hover {
            background: var(--success);
            color: white;
        }

        .seat-item.occupied {
            border-color: var(--danger);
            background: #FEF2F2;
            color: var(--danger);
            cursor: not-allowed;
        }

        .seat-item.selected {
            border-color: var(--primary);
            background: var(--primary);
            color: white;
        }

        .seat-item.disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }

        .seat-item .seat-label {
            font-size: 0.55rem;
            line-height: 1;
        }

        .area-card {
            cursor: pointer;
            transition: all 0.3s;
            border: 2px solid transparent;
        }

        .area-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
        }

        .area-card.selected {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.15);
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .status-badge.pending { background: #FEF3C7; color: #92400E; }
        .status-badge.confirmed { background: #DBEAFE; color: #1E40AF; }
        .status-badge.checked_in { background: #D1FAE5; color: #065F46; }
        .status-badge.temporary_leave { background: #FED7AA; color: #9A3412; }
        .status-badge.completed { background: #E5E7EB; color: #374151; }
        .status-badge.cancelled { background: #FEE2E2; color: #991B1B; }
        .status-badge.no_show { background: #FEE2E2; color: #991B1B; }

        .footer {
            background: white;
            border-top: 1px solid var(--gray-200);
            padding: 20px 0;
            margin-top: 60px;
        }

        .page-header {
            padding: 24px 0 8px 0;
        }

        .page-header h1 {
            font-weight: 700;
            font-size: 1.75rem;
            color: var(--gray-900);
        }

        .page-header .breadcrumb {
            background: transparent;
            padding: 0;
            margin: 0;
        }

        @media (max-width: 768px) {
            .seat-grid {
                grid-template-columns: repeat(auto-fill, minmax(44px, 1fr));
                gap: 4px;
            }
            .seat-item .seat-label {
                font-size: 0.45rem;
            }
        }

        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050;
        }

        .avatar-circle {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 0.9rem;
        }
    </style>

    @stack('styles')
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom py-3">
        <div class="container">
            <a class="navbar-brand" href="{{ route('dashboard') }}">
                <i class="bi bi-book me-2"></i>UTAR Library Seats
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                            <i class="bi bi-grid-3x3-gap-fill me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('areas.*') ? 'active' : '' }}" href="{{ route('areas.index') }}">
                            <i class="bi bi-layers me-1"></i> Areas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('reservations.*') ? 'active' : '' }}" href="{{ route('reservations.index') }}">
                            <i class="bi bi-calendar-check me-1"></i> My Reservations
                        </a>
                    </li>
                    @auth
                        @if(auth()->user()->isAdmin())
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.*') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                                    <i class="bi bi-speedometer2 me-1"></i> Admin
                                </a>
                            </li>
                        @endif
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('turnstile.simulator') }}">
                                <i class="bi bi-qr-code-scan me-1"></i> Simulate Scan
                            </a>
                        </li>
                    @endauth
                </ul>
                <ul class="navbar-nav">
                    @auth
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                <span class="avatar-circle me-2">{{ substr(auth()->user()->name, 0, 2) }}</span>
                                <span>{{ auth()->user()->name }}</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i>Profile</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            <i class="bi bi-box-arrow-right me-2"></i>Logout
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register') }}">Register</a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="py-4">
        <div class="container">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error') || $errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-circle-fill me-2"></i>
                    @if(session('error'))
                        {{ session('error') }}
                    @else
                        {{ $errors->first() }}
                    @endif
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0 text-muted small">
                        <i class="bi bi-book me-1"></i> UTAR Library Seat Management System
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0 text-muted small">
                        &copy; {{ date('Y') }} UTAR Sg Long. All rights reserved.
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery (for AJAX) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    @stack('scripts')

    <script>
        // Auto-dismiss alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                document.querySelectorAll('.alert').forEach(function(alert) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 5000);
        });
    </script>
</body>
</html>