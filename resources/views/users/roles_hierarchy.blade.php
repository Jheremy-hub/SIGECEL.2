@extends('layouts.app')

@section('title', 'Jerarquia de roles')
@section('page-title', 'Jerarquia de roles')

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-sitemap me-2"></i> Jerarquia de roles</h5>
        <div class="d-flex gap-2">
            <a href="{{ route('users.roles') }}" class="btn btn-outline-light btn-sm">
                <i class="fas fa-user-shield"></i> Roles
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
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Rol</th>
                        <th>Usuario</th>
                        <th>Jefe (rol padre)</th>
                        <th style="width: 160px;">Accion</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($roles as $r)
                        <tr>
                            <td>{{ $r->role }}</td>
                            <td>
                                @if($r->user)
                                    <a href="{{ route('users.edit', $r->user->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-user"></i> {{ $r->user->name }} {{ $r->user->apellidos }}
                                    </a>
                                @else
                                    <span class="text-muted">Sin usuario</span>
                                @endif
                            </td>
                            <td>
                                <form class="d-flex align-items-center gap-2" method="POST" action="{{ route('users.roles.hierarchy.update') }}">
                                    @csrf
                                    <input type="hidden" name="role_id" value="{{ $r->id }}">
                                    <div class="w-100">
                                        <input type="text"
                                               class="form-control form-control-sm mb-1 parent-filter-input"
                                               placeholder="Buscar rol o usuario..."
                                               data-target="parentSelect-{{ $r->id }}">
                                        <select id="parentSelect-{{ $r->id }}" name="parent_role_id" class="form-select form-select-sm">
                                            <option value="">Sin jefe</option>
                                            @foreach($roles as $opt)
                                                @if($opt->id !== $r->id)
                                                    <option value="{{ $opt->id }}" {{ $r->parent_role_id == $opt->id ? 'selected' : '' }}>
                                                        {{ $opt->role }}@if($opt->user) - {{ $opt->user->name }} {{ $opt->user->apellidos }} @endif
                                                    </option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-sm btn-primary">
                                        <i class="fas fa-save"></i>
                                    </button>
                                </form>
                            </td>
                            <td class="text-muted small">
                                @if($r->parent_role_id)
                                    Padre: {{ optional($roles->firstWhere('id', $r->parent_role_id))->role }}
                                @else
                                    Sin padre
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@parent
<script>
document.addEventListener('input', function (e) {
    if (!e.target.classList.contains('parent-filter-input')) return;
    const targetId = e.target.getAttribute('data-target');
    const select = document.getElementById(targetId);
    if (!select) return;
    const term = (e.target.value || '').toLowerCase();
    Array.from(select.options).forEach(function (opt) {
        if (!opt.value) { opt.hidden = false; return; }
        const text = (opt.textContent || '').toLowerCase();
        opt.hidden = term && !text.includes(term);
    });
});
</script>
@endsection
