<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SIGE - Sistema Integrado de Gestión')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        :root {
            --sidebar-width: 280px;
            --sidebar-bg: #2c3e50;
            --sidebar-color: #ecf0f1;
            --sidebar-active: #3498db;
            --header-height: 70px;
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            overflow-x: hidden;
            font-size: 14px;
        }

        /* Layout Principal */
        .app-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            color: var(--sidebar-color);
            position: fixed;
            height: 100vh;
            transition: all 0.3s;
            z-index: 1000;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
        }

        .sidebar-header {
            padding: 25px 20px;
            text-align: center;
            border-bottom: 2px solid #34495e;
            background: rgba(0, 0, 0, 0.1);
        }

        .sidebar-header h3 {
            margin: 0;
            font-size: 1.4rem;
            font-weight: 700;
        }

        .sidebar-header .logo {
            font-size: 2.5rem;
            margin-bottom: 15px;
            color: #3498db;
        }

        .sidebar-menu {
            padding: 20px 0;
        }

        .nav-item {
            margin-bottom: 2px;
        }

        .nav-link {
            color: var(--sidebar-color);
            padding: 15px 25px;
            display: flex;
            align-items: center;
            text-decoration: none;
            transition: all 0.3s;
            border-left: 4px solid transparent;
            font-weight: 500;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border-left-color: var(--sidebar-active);
        }

        .nav-link.active {
            background: rgba(52, 152, 219, 0.2);
            color: white;
            border-left-color: var(--sidebar-active);
        }

        .nav-link i {
            width: 25px;
            text-align: center;
            margin-right: 15px;
            font-size: 1.2rem;
        }

        .nav-link .badge {
            margin-left: auto;
            font-size: 0.7rem;
        }

        /* Contenido Principal */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            transition: all 0.3s;
            min-height: 100vh;
        }

        /* Header */
        .main-header {
            background: white;
            border-bottom: 2px solid #e9ecef;
            height: var(--header-height);
            padding: 0 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 999;
        }

        .header-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin: 0;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-avatar {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1rem;
        }

        /* Contenido de la página */
        .page-content {
            padding: 30px;
            margin-top: 0;
        }

        /* Botón toggle sidebar para móviles */
        .sidebar-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.4rem;
            color: #2c3e50;
            cursor: pointer;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.mobile-open {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .sidebar-toggle {
                display: block;
            }

            .sidebar-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.5);
                z-index: 999;
            }

            .sidebar-overlay.active {
                display: block;
            }

            .page-content {
                padding: 20px 15px;
            }
        }

        /* Tablas Estilo Formal */
        .table-formal {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .table-formal thead {
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            color: white;
        }

        .table-formal th {
            border: none;
            padding: 15px 12px;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .table-formal td {
            padding: 12px;
            vertical-align: middle;
            border-bottom: 1px solid #e9ecef;
        }

        .table-formal tbody tr:hover {
            background-color: #f8f9fa;
        }

        .status-badge {
            font-size: 0.75rem;
            padding: 4px 8px;
            border-radius: 4px;
        }

        .action-btn {
            padding: 6px 12px;
            font-size: 0.8rem;
            border-radius: 4px;
        }
    </style>
</head>

<body>
    <div class="app-container">
        <!-- Sidebar -->
        <div class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <i class="fas fa-university"></i>
                </div>
                <h3>SIGE</h3>
                <small class="text-muted">Sistema Integrado de Gestión</small>
            </div>

            <div class="sidebar-menu">
                @php
                    $user = Auth::user();
                    $userRoleName = optional(optional($user)->role)->role ?? '';
                    $normalizedUserRole = $user ? \Illuminate\Support\Str::ascii(\Illuminate\Support\Str::lower($userRoleName)) : '';
                    $canManageUsers = $user && in_array($normalizedUserRole, ['tecnologias de informacion', 'tecnologia de informacion'], true);
                @endphp
                <nav class="nav flex-column">
                    <!-- Dashboard (solo TI) -->
                    @if($canManageUsers)
                        <div class="nav-item">
                            <a href="{{ route('dashboard') }}" class="nav-link {{ Request::is('dashboard') ? 'active' : '' }}">
                                <i class="fas fa-tachometer-alt"></i>
                                <span>Dashboard</span>
                            </a>
                        </div>
                    @endif

                    <!-- Regresar al Sistema Principal -->
                    <div class="nav-item">
                        <a href="https://administrativo.cel.org.pe/admin" class="nav-link" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white !important; font-weight: 600; border-left: 4px solid #28a745;">
                            <i class="fas fa-arrow-left"></i>
                            <span>Volver a CEL Principal</span>
                        </a>
                    </div>

                    <!-- Reporte Cumpleaños (solo TI) -->
                    @if($canManageUsers)
                        <div class="nav-item">
                            <a href="{{ route('reports.birthdays') }}" class="nav-link {{ Request::is('reporte-cumpleanos*') ? 'active' : '' }}">
                                <i class="fas fa-birthday-cake"></i>
                                <span>Reporte Cumpleaños</span>
                            </a>
                        </div>
                    @endif

                    <!-- Busqueda de Archivo -->
                    <div class="nav-item">
                        <a href="{{ route('tracking.public') }}" class="nav-link {{ Request::is('seguimiento*') ? 'active' : '' }}">
                            <i class="fas fa-search"></i>
                            <span>Busqueda de Archivo</span>
                        </a>
                    </div>

                    <!-- SIGE -->
                    <div class="nav-item">
                        <a href="{{ route('sige.index') }}" class="nav-link {{ Request::is('sige*') ? 'active' : '' }}">
                            <i class="fas fa-comments"></i>
                            <span>SIGECEL
                            </span>
                            @php
                            $unreadCount = \App\Models\UserMessage::where('receiver_id', Auth::id())->where('is_read', 0)->count();
                            @endphp
                            @if($unreadCount > 0)
                            <span class="badge bg-danger">{{ $unreadCount }}</span>
                            @endif
                        </a>
                    </div>
                    @if($canManageUsers)
                        <!-- Gestión de Usuarios -->
                        <div class="nav-item">
                            <a href="#" class="nav-link" data-bs-toggle="collapse" data-bs-target="#usuariosMenu">
                                <i class="fas fa-users"></i>
                                <span>Gestión de Usuarios</span>
                                <i class="fas fa-chevron-down ms-auto"></i>
                            </a>
                            <div class="collapse show" id="usuariosMenu">
                                <div class="nav flex-column ms-3">
                                    <a href="{{ route('users.list') }}" class="nav-link {{ Request::is('users') ? 'active' : '' }}">
                                        <i class="fas fa-list"></i>
                                        <span>Lista de Usuarios</span>
                                    </a>
                                    <a href="{{ route('register.form') }}" class="nav-link {{ Request::is('users/register') ? 'active' : '' }}">
                                        <i class="fas fa-user-plus"></i>
                                        <span>Registrar Usuario</span>
                                    </a>
                                    <a href="{{ route('users.roles') }}" class="nav-link {{ Request::is('users/roles') ? 'active' : '' }}">
                                        <i class="fas fa-user-shield"></i>
                                        <span>Roles</span>
                                    </a>
                                    <a href="{{ route('users.roles.hierarchy') }}" class="nav-link {{ Request::is('users/roles/hierarchy') ? 'active' : '' }}">
                                        <i class="fas fa-sitemap"></i>
                                        <span>Jerarquía por rol</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Separador -->
                    <div class="nav-item mt-4">
                        <hr style="border-color: #34495e; margin: 10px 25px;">
                    </div>

                    <!-- Cerrar Sesión -->
                    <div class="nav-item">
                        <form method="POST" action="{{ route('logout') }}" id="logout-form" class="d-inline w-100">
                            @csrf
                            <button type="submit" class="nav-link text-start w-100 border-0 bg-transparent">
                                <i class="fas fa-sign-out-alt"></i>
                                <span>Cerrar Sesión</span>
                            </button>
                        </form>
                    </div>
                </nav>
            </div>
        </div>

        <!-- Overlay para móviles -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <!-- Contenido Principal -->
        <div class="main-content" id="mainContent">
            <!-- Header -->
            <header class="main-header">
                <div class="d-flex align-items-center">
                    <button class="sidebar-toggle" id="sidebarToggle" aria-label="Abrir o cerrar menú lateral" title="Abrir o cerrar menú lateral">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h4 class="header-title ms-3">@yield('page-title', 'Dashboard')</h4>
                </div>

                <div class="user-info">
                    <div class="user-details text-end">
                        @if($user)
                            <div class="fw-bold">{{ $user->name }} {{ $user->apellidos }}</div>
                            <small class="text-muted">{{ optional($user->role)->role ?? 'Usuario' }}</small>
                        @else
                            <div class="fw-bold">Invitado</div>
                            <small class="text-muted">Sesión no iniciada</small>
                        @endif
                    </div>
                    <div class="user-avatar">
                        @if($user)
                            {{ strtoupper(substr($user->name ?? '', 0, 1) . substr($user->apellidos ?? '', 0, 1)) }}
                        @else
                            ?
                        @endif
                    </div>
                </div>
            </header>

            <!-- Contenido de la Página -->
            <main class="page-content">
                @yield('content')
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle sidebar en móviles
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.toggle('mobile-open');
            overlay.classList.toggle('active');
        });

        // Cerrar sidebar al hacer clic en el overlay
        document.getElementById('sidebarOverlay').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.remove('mobile-open');
            overlay.classList.remove('active');
        });

        // Cerrar sidebar al hacer clic en un enlace (en móviles)
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 768) {
                    const sidebar = document.getElementById('sidebar');
                    const overlay = document.getElementById('sidebarOverlay');
                    sidebar.classList.remove('mobile-open');
                    overlay.classList.remove('active');
                }
            });
        });
    </script>

    @yield('scripts')
</body>

</html>
