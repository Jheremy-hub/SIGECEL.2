@extends('layouts.app')
@section('title', 'Detalles del Documento')
@section('page-title', 'Detalles del Documento')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="text-primary mb-0">
                    <i class="fas fa-file-alt me-2"></i> Detalles del {{ $document->document_type }}
                </h5>
                <a href="{{ route('documents.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Volver
                </a>
            </div>

            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="card-title mb-0">{{ $document->subject }}</h6>
                </div>
                <div class="card-body">
                    @php
                    // Normalizar el campo meta para evitar errores
                    $meta = is_string($document->meta) ? json_decode($document->meta, true) : [];
                    $meta = is_array($meta) ? $meta : [];
                    @endphp

                    @if($document->document_type === 'Oficio')
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Tipo de Oficio:</strong> {{ $meta['mode'] ?? '—' }}<br>
                            <strong>Enviado Por:</strong> {{ $meta['sent_by'] ?? $document->sender }}<br>
                            <strong>Institución:</strong> {{ $document->institution }}
                        </div>
                        <div class="col-md-6">
                            <strong>Fecha de Aceptación:</strong> {{ \Carbon\Carbon::parse($meta['acceptance_date'] ?? null)?->format('d/m/Y') ?: '—' }}<br>
                            <strong>Código:</strong> {{ $document->document_code }}<br>
                            <strong>Fecha de Creación:</strong> {{ $document->created_at->format('d/m/Y H:i') }}
                        </div>
                    </div>
                    @elseif($document->document_type === 'Memo')
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>De:</strong> {{ $document->sender }}<br>
                            <strong>Para:</strong> {{ $meta['destination'] ?? '—' }}<br>
                            <strong>Área / Departamento:</strong> {{ $document->institution }}
                        </div>
                        <div class="col-md-6">
                            <strong>Código:</strong> {{ $document->document_code }}<br>
                            <strong>Fecha:</strong> {{ $document->created_at->format('d/m/Y H:i') }}
                        </div>
                    </div>
                    @elseif($document->document_type === 'Carta')
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Remitente:</strong> {{ $document->sender }}<br>
                            <strong>Destinatario:</strong> {{ $meta['destination'] ?? '—' }}<br>
                            <strong>Institución Destinataria:</strong> {{ $document->institution }}
                        </div>
                        <div class="col-md-6">
                            <strong>Referencia:</strong> {{ $meta['reference'] ?? '—' }}<br>
                            <strong>Código:</strong> {{ $document->document_code }}<br>
                            <strong>Fecha:</strong> {{ $document->created_at->format('d/m/Y H:i') }}
                        </div>
                    </div>
                    @endif

                    <!-- Contenido -->
                    @if($document->content)
                    <div class="mb-3">
                        <strong>Contenido:</strong>
                        <div class="border p-3 bg-light rounded mt-1">
                            {!! nl2br(e($document->content)) !!}
                        </div>
                    </div>
                    @endif

                    <!-- Archivo Adjunto -->
                    @if($document->file_path)
                    <div class="mb-3">
                        <strong>Archivo Adjunto:</strong><br>
                        <div class="d-flex align-items-center mt-2 p-2 border rounded">
                            <i class="fas fa-paperclip fa-2x text-muted me-3"></i>
                            <div class="flex-grow-1">
                                <div class="fw-bold">{{ $document->file_name }}</div>
                                <small class="text-muted"> Tipo: {{ $document->file_type }} | Tamaño: {{ number_format($document->file_size / 1024, 2) }} KB </small>
                            </div>
                            <a href="{{ route('documents.download', $document->id) }}" class="btn btn-success btn-sm">
                                <i class="fas fa-download"></i> Descargar
                            </a>
                        </div>
                    </div>
                    @endif

                    <!-- Acciones -->
                    <div class="mt-4 border-top pt-3">
                        <a href="{{ route('documents.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Volver
                        </a>
                        <form action="{{ route('documents.destroy', $document->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" onclick="return confirm('¿Estás seguro de eliminar este documento?')">
                                <i class="fas fa-trash me-1"></i> Eliminar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection