<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - FrameX</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            overflow-x: hidden;
        }

        .sidebar {
            min-height: 100vh;
            background: #343a40;
            color: white;
            position: fixed;
            width: 250px;
            z-index: 1000;
            display: flex;
            flex-direction: column;
        }

        .sidebar-header {
            padding: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, .1); 
            flex-shrink: 0;
        }

        .sidebar-content {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, .8);
            padding: 1rem;
            display: flex;
            align-items: center;
        }

        .sidebar .nav-link:hover {
            color: white;
            background: rgba(255, 255, 255, .1);
        }

        .sidebar .nav-link.active {
            color: white;
            background: rgba(255, 255, 255, .2);
        }

        .sidebar .nav-link i {
            width: 25px;
            text-align: center;
            margin-right: 10px;
        }

        .main-content {
            margin-left: 250px;
            padding: 20px;
            min-height: 100vh;
        }

        .sidebar-header h4 {
            margin: 0;
            color: white;
        }

        .sidebar-header p {
            margin: 0;
            color: rgba(255, 255, 255, .5);
            font-size: 0.9rem;
        }

        .nav-section {
            padding: 1rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, .1);
        }

        .nav-section-title {
            padding: 0.5rem 1rem;
            color: rgba(255, 255, 255, .5);
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }

        /* Custom scrollbar for sidebar */
        .sidebar-content::-webkit-scrollbar {
            width: 5px;
        }

        .sidebar-content::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, .1);
        }

        .sidebar-content::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, .2);
            border-radius: 3px;
        }

        .sidebar-content::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, .3);
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                position: relative;
            }

            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h4>FrameX Admin</h4>
            <p>Welcome to Admin Panel</p>
        </div>

        <div class="sidebar-content">
            <div class="nav-section">
                <div class="nav-section-title">Main</div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                            href="{{ route('admin.dashboard') }}">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                </ul>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">Frame Management</div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.frames.index') ? 'active' : '' }}"
                            href="{{ route('admin.frames.index') }}">
                            <i class="fas fa-images"></i> All Frames
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.frames.create') ? 'active' : '' }}"
                            href="{{ route('admin.frames.create') }}">
                            <i class="fas fa-plus-circle"></i> Add New Frame
                        </a>
                    </li>
                </ul>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">Categories</div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.frames.index') && request('category') == 'birthday' ? 'active' : '' }}"
                            href="{{ route('admin.frames.index', ['category' => 'birthday']) }}">
                            <i class="fas fa-birthday-cake"></i> Birthday Frames
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.frames.index') && request('category') == 'wedding' ? 'active' : '' }}"
                            href="{{ route('admin.frames.index', ['category' => 'wedding']) }}">
                            <i class="fas fa-heart"></i> Wedding Frames
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.frames.index') && request('category') == 'general' ? 'active' : '' }}"
                            href="{{ route('admin.frames.index', ['category' => 'general']) }}">
                            <i class="fas fa-image"></i> General Frames
                        </a>
                    </li>
                </ul>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">Account</div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('profile.edit') }}">
                            <i class="fas fa-user"></i> Profile
                        </a>
                    </li>
                    <li class="nav-item">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <a class="nav-link" href="{{ route('logout') }}"
                                onclick="event.preventDefault(); this.closest('form').submit();">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
