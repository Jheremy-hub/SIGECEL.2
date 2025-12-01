@extends('layouts.app')

@section('title', 'Roles')
@section('page-title', 'Roles registrados')

@section('content')
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center bg-info text-white">
        <h5 class="mb-0">
            <i class="fas fa-user-shield me-2"></i> Roles registrados
        </h5>
        <div class="d-flex gap-2">
            <a href="{{ route('users.roles.hierarchy') }}" class="btn btn-outline-light btn-sm">
                <i class="fas fa-sitemap"></i> Jerarquía por rol
            </a>
            <a href="{{ route('users.list') }}" class="btn btn-outline-light btn-sm">
                <i class="fas fa-users"></i> Usuarios
            </a>
        </div>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form class="row g-2 mb-3" method="POST" action="{{ route('users.roles.store') }}">
            @csrf
            <div class="col-md-6">
                <label class="form-label">Nombre de rol *</label>
                <input name="role" class="form-control" list="roleOptions" placeholder="Ej. Jefe de Archivo" required>
                <datalist id="roleOptions">
                    @foreach($roles as $opt)
                        <option value="{{ $opt->role }}"></option>
                    @endforeach
                </datalist>
            </div>
            <div class="col-md-3">
                <label class="form-label">Nivel jerárquico (1-10)</label>
                <input name="hierarchy_level" type="number" min="1" max="10" class="form-control" value="5">
                <small class="text-muted">Opcional, default 5.</small>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button class="btn btn-primary w-100">
                    <i class="fas fa-plus"></i> Agregar rol
                </button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Rol</th>
                        <th>Nivel</th>
                        <th>Última asignación</th>
                        <th class="text-center" style="width: 110px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($roles as $r)
                        <tr>
                            <td>{{ $r->role }}</td>
                            <td>{{ $r->hierarchy_level ?? '—' }}</td>
                            <td>{{ $r->assigned_at ? \Carbon\Carbon::parse($r->assigned_at)->format('d/m/Y H:i') : '—' }}</td>
                            <td class="text-center">
                                <form action="{{ route('users.roles.delete') }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar el rol {{ $r->role }}? Se quitará de la lista, pero no cambia los usuarios existentes.');">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="role" value="{{ $r->role }}">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">Sin roles registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
