@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
    <!-- Tarjeta de bienvenida -->
    <div class="card bg-gradient-primary text-white mb-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="card-title">
                        <i class="fas fa-user-circle"></i> 
                        Bienvenido, {{ $user->name }} {{ $user->apellidos }}
                    </h1>
                    <p class="card-text mb-0">Sistema de gestión de documentos y mensajería</p>
                    <small>
                        <i class="fas fa-calendar"></i> 
                        {{ now()->format('d/m/Y H:i') }}
                    </small>
                </div>
                <div class="col-md-4 text-center">
                    <div class="user-avatar mx-auto" style="width: 80px; height: 80px; font-size: 1.5rem;">
                        {{ substr($user->name, 0, 1) }}{{ substr($user->apellidos, 0, 1) }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tarjetas de estadísticas -->
    <div class="row">
        <div class="col-md-3">
            <div class="card stats-card border-success">
                <div class="card-body text-center">
                    <i class="fas fa-file-alt fa-3x text-success mb-3"></i>
                    <h3>{{ $stats['documents_count'] }}</h3>
                    <p class="card-text">Documentos</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stats-card border-info">
                <div class="card-body text-center">
                    <i class="fas fa-envelope fa-3x text-info mb-3"></i>
                    <h3>{{ $stats['unread_messages'] }}</h3>
                    <p class="card-text">Mensajes No Leídos</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stats-card border-warning">
                <div class="card-body text-center">
                    <i class="fas fa-users fa-3x text-warning mb-3"></i>
                    <h3>{{ $stats['total_users'] }}</h3>
                    <p class="card-text">Usuarios Activos</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stats-card border-primary">
                <div class="card-body text-center">
                    <i class="fas fa-paper-plane fa-3x text-primary mb-3"></i>
                    <h3>{{ $stats['sent_messages'] }}</h3>
                    <p class="card-text">Mensajes Enviados</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <!-- Información del usuario -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-id-card"></i> Información del Usuario
                    </h5>
                    <span class="badge bg-light text-dark">
                        ID: {{ $user->id }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6 mb-3">
                            <strong><i class="fas fa-user text-primary"></i> Nombre:</strong><br>
                            {{ $user->name }}
                        </div>
                        <div class="col-6 mb-3">
                            <strong><i class="fas fa-users text-primary"></i> Apellidos:</strong><br>
                            {{ $user->apellidos }}
                        </div>
                        <div class="col-6 mb-3">
                            <strong><i class="fas fa-envelope text-primary"></i> Email:</strong><br>
                            {{ $user->email }}
                        </div>
                        <div class="col-6 mb-3">
                            <strong><i class="fas fa-briefcase text-primary"></i> Cargo:</strong><br>
                            {{ $user->cargo ?? 'No especificado' }}
                        </div>
                        <div class="col-6 mb-3">
                            <strong><i class="fas fa-user-tag text-primary"></i> Rol:</strong><br>
                            <span class="badge bg-primary">
                                {{ $user->role->role ?? 'No asignado' }}
                            </span>
                        </div>
                        <div class="col-6 mb-3">
                            <strong><i class="fas fa-sort-numeric-up text-primary"></i> Nivel:</strong><br>
                            <span class="badge bg-info">
                                {{ $user->role->hierarchy_level ?? 'No asignado' }}
                            </span>
                        </div>
                        <div class="col-6 mb-3">
                            <strong><i class="fas fa-mobile-alt text-primary"></i> Celular:</strong><br>
                            {{ $user->celular ?? 'No especificado' }}
                        </div>
                        <div class="col-6 mb-3">
                            <strong><i class="fas fa-toggle-on text-primary"></i> Estado:</strong><br>
                            @if($user->id_estado == 1)
                                <span class="badge bg-success">Activo</span>
                            @else
                                <span class="badge bg-danger">Inactivo</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Documentos -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-file-alt"></i> Documentos
                    </h5>
                    <span class="badge bg-light text-dark">
                        {{ $user->documents->count() }}
                    </span>
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    @if($user->documents->count() > 0)
                        <div class="list-group">
                            @foreach($user->documents as $document)
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <h6 class="mb-0 text-primary">{{ $document->document_name }}</h6>
                                                <span class="badge bg-secondary badge-status">
                                                    {{ $document->document_type ?? 'No especificado' }}
                                                </span>
                                            </div>
                                            <p class="mb-2 small text-muted">
                                                {{ $document->document_description ?? 'Sin descripción' }}
                                            </p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="text-muted">
                                                    <i class="fas fa-calendar"></i>
                                                    {{ $document->uploaded_at ? \Carbon\Carbon::parse($document->uploaded_at)->format('d/m/Y H:i') : 'Fecha no disponible' }}
                                                </small>
                                                @if($document->document_path)
                                                <a href="{{ asset('storage/' . $document->document_path) }}" 
                                                   class="btn btn-primary btn-sm" 
                                                   target="_blank"
                                                   title="Ver documento">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No se encontraron documentos.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-bolt"></i> Acciones Rápidas
                </h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3">
                        <a href="{{ route('messages.create') }}" class="btn btn-primary btn-lg w-100 mb-2 p-3">
                            <i class="fas fa-plus fa-2x mb-2"></i><br>
                            Nuevo Mensaje
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('register.form') }}" class="btn btn-success btn-lg w-100 mb-2 p-3">
                            <i class="fas fa-user-plus fa-2x mb-2"></i><br>
                            Nuevo Usuario
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('messages.inbox') }}" class="btn btn-info btn-lg w-100 mb-2 p-3">
                            <i class="fas fa-inbox fa-2x mb-2"></i><br>
                            Ver Mensajes
                        </a>
                    </div>
                    <!-- ✅ BOTÓN PARA MIS DOCUMENTOS -->
                    <div class="col-md-3">
                        <a href="{{ route('documents.index') }}" class="btn btn-warning btn-lg w-100 mb-2 p-3">
                            <i class="fas fa-folder-open fa-2x mb-2"></i><br>
                            Mis Documentos
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection