<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard - QR Attendance')</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"/>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Figtree', sans-serif;
            background-color: #f8f9fa;
        }

        .container {
            display: flex;
            min-height: 100vh;
        }

        nav {
            width: 250px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 2rem 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }

        .navbar {
            list-style: none;
            padding: 0;
        }

        .navbar li {
            margin: 0.5rem 0;
        }

        .navbar li a {
            display: flex;
            align-items: center;
            padding: 1rem 2rem;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }

        .navbar li a:hover,
        .navbar li a.active {
            background: rgba(255,255,255,0.1);
            border-left-color: white;
            color: white;
        }

        .navbar li a i {
            margin-right: 1rem;
            width: 20px;
            text-align: center;
        }

        .main {
            flex: 1;
            margin-left: 250px;
            padding: 2rem;
        }

        .main-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding: 1.5rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }

        .main-top h1 {
            color: #333;
            font-size: 2rem;
            font-weight: 600;
        }

        .main-top i {
            font-size: 2rem;
            color: #667eea;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            text-align: center;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }

        .stat-card i {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .stat-card h3 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .stat-card p {
            color: #666;
            font-size: 1.1rem;
        }

        .stat-card.employees i { color: #28a745; }
        .stat-card.employees h3 { color: #28a745; }

        .stat-card.admins i { color: #007bff; }
        .stat-card.admins h3 { color: #007bff; }

        .stat-card.attendance i { color: #ffc107; }
        .stat-card.attendance h3 { color: #ffc107; }

        .stat-card.present i { color: #17a2b8; }
        .stat-card.present h3 { color: #17a2b8; }

        .card {
            background: white;
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-bottom: 2rem;
        }

        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 1.5rem;
            border: none;
        }

        .card-body {
            padding: 2rem;
        }

        .table {
            margin-bottom: 0;
        }

        .table th {
            border-top: none;
            font-weight: 600;
            color: #333;
            background: #f8f9fa;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .logout {
            color: #dc3545 !important;
        }

        .logout:hover {
            background: rgba(220, 53, 69, 0.1) !important;
            border-left-color: #dc3545 !important;
        }

        footer {
            background: #343a40;
            color: white;
            text-align: center;
            padding: 1rem;
            margin-top: 2rem;
        }

        .alert {
            border-radius: 10px;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <div class="container">
        <nav>
            <ul class="navbar">
                <li>
                    <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class="fas fa-tachometer-alt"></i>
                        <span class="nav-item">Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.admins') }}" class="{{ request()->routeIs('admin.admins') ? 'active' : '' }}">
                        <i class="fas fa-user-shield"></i>
                        <span class="nav-item">Admins</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        <i class="fas fa-users-cog"></i>
                        <span class="nav-item">User Management</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.employees.index') }}" class="{{ request()->routeIs('admin.employees.*') ? 'active' : '' }}">
                        <i class="fas fa-users"></i>
                        <span class="nav-item">Employees</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.attendance.index') }}" class="{{ request()->routeIs('admin.attendance.index') ? 'active' : '' }}">
                        <i class="fas fa-chart-bar"></i>
                        <span class="nav-item">Attendance</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.attendance.scanner') }}" class="{{ request()->routeIs('admin.attendance.scanner') ? 'active' : '' }}">
                        <i class="fas fa-qrcode"></i>
                        <span class="nav-item">QR Scanner</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.leave.index') }}" class="{{ request()->routeIs('admin.leave.*') ? 'active' : '' }}">
                        <i class="fas fa-calendar-alt"></i>
                        <span class="nav-item">Leave Management</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.reports.index') }}" class="{{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                        <i class="fas fa-chart-bar"></i>
                        <span class="nav-item">Reports & Analytics</span>
                    </a>
                </li>
                <li>
                    <form method="POST" action="{{ route('admin.logout') }}" style="margin: 0;">
                        @csrf
                        <a href="#" onclick="event.preventDefault(); this.closest('form').submit();" class="logout">
                            <i class="fas fa-sign-out-alt"></i>
                            <span class="nav-item">Logout</span>
                        </a>
                    </form>
                </li>
            </ul>
        </nav>

        <section class="main">
            <div class="main-top">
                <h1>@yield('page-title', 'Dashboard')</h1>
                <div>
                    <i class="fas fa-user-cog"></i>
                    <span class="ms-2">{{ auth('admin')->user()->adminname ?? 'Admin' }}</span>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </section>
    </div>

    <footer>
        &copy; Copyright {{ date('Y') }} TcheumaniSinakJusto - All Rights Reserved
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    @stack('scripts')
</body>
</html>
