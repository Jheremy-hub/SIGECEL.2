<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Usuarios - Sistema</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-users"></i> Gestión de Usuarios
            </a>
            <div class="navbar-nav ms-auto">
                <a href="{{ route('register.form') }}" class="btn btn-success btn-sm">
                    <i class="fas fa-user-plus"></i> Nuevo Usuario
                </a>
                <a href="{{ route('users.roles') }}" class="btn btn-outline-light btn-sm ms-2">
                    <i class="fas fa-user-shield"></i> Roles
                </a>
                <a href="{{ route('dashboard') }}" class="btn btn-outline-light btn-sm ms-2">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-list"></i> Lista de Usuarios Registrados
                </h5>
            </div>
            <div class="card-body">
                @if(isset($rolesList) && $rolesList->count())
                <div class="alert alert-secondary py-2 mb-3">
                    <strong>Roles registrados:</strong>
                    {{ $rolesList->implode(', ') }}
                </div>
                @endif
                @if(session('success'))
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                </div>
                @endif

                @if(session('error'))
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                </div>
                @endif

                <form class="row g-2 mb-3 align-items-end" method="GET" action="{{ route('users.list') }}">
                    <div class="col-md-3">
                        <input type="text" name="name" value="{{ request('name') }}" class="form-control form-control-sm" placeholder="Buscar por nombre/apellidos">
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="email" value="{{ request('email') }}" class="form-control form-control-sm" placeholder="Buscar por email">
                    </div>
                    <div class="col-md-2">
                        <select name="role" class="form-select form-select-sm">
                            <option value="">Todos los roles</option>
                            @foreach($rolesList as $role)
                            <option value="{{ $role }}" {{ request('role') === $role ? 'selected' : '' }}>{{ $role }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="estado" class="form-select form-select-sm">
                            <option value="">Todos los estados</option>
                            <option value="1" {{ request('estado') === '1' ? 'selected' : '' }}>Activo</option>
                            <option value="0" {{ request('estado') === '0' ? 'selected' : '' }}>Inactivo</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" value="1" id="showAll" name="all" {{ (request('all') || (isset($showAll)&&$showAll)) ? 'checked' : '' }}>
                            <label class="form-check-label small" for="showAll">Mostrar todos</label>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-sm btn-primary w-100">Filtrar</button>
                            <a href="{{ route('users.list') }}" class="btn btn-sm btn-secondary">Limpiar</a>
                        </div>
                    </div>
                </form>

                @if($users->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="usersTable">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Nombre Completo</th>
                                <th>Email</th>
                                <th>Cargo</th>
                                <th>Rol</th>
                                <th>Estado</th>
                                <th>Celular</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                            <tr>
                                <td>{{ $user->id }}</td>
                                <td>
                                    <strong>{{ $user->name }} {{ $user->apellidos }}</strong>
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->cargo }}</td>
                                <td>
                                    <span class="badge bg-primary">
                                        {{ $user->role->role ?? 'Sin rol' }}
                                    </span>
                                </td>
                                <td>
                                    @if($user->id_estado == 1)
                                    <span class="badge bg-success">Activo</span>
                                    @else
                                    <span class="badge bg-danger">Inactivo</span>
                                    @endif
                                </td>
                                <td>{{ $user->celular ?? 'No especificado' }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('users.edit', $user->id) }}"
                                            class="btn btn-warning btn-sm"
                                            title="Editar usuario">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                </td>

                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if(!(isset($showAll) && $showAll))
                <div class="mt-3 d-flex justify-content-center">
                    {{ $users->withQueryString()->links('pagination::bootstrap-5') }}
                </div>
                @endif
                @else
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle fa-2x mb-3"></i>
                    <h4>No hay usuarios registrados</h4>
                    <p>Comienza registrando el primer usuario en el sistema.</p>
                    <a href="{{ route('register.form') }}" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i> Registrar Primer Usuario
                    </a>
                </div>
                @endif
            </div>
            <div class="card-footer text-muted">
                @php
                $totalUsers = ($users instanceof \Illuminate\Pagination\LengthAwarePaginator) ? $users->total() : $users->count();
                @endphp
                Total de usuarios: <strong>{{ $totalUsers }}</strong>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>