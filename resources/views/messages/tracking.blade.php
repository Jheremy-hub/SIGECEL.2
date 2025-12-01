@extends('layouts.app')
@php
    $pageTitle = $pageTitle ?? 'Seguimiento de documentos';
    $pageSubtitle = $pageSubtitle ?? 'Consulta el estado de tu documento';
@endphp
@section('title', $pageTitle)
@section('page-title', $pageTitle)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            {{-- Buscador --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="fas fa-search-location me-2"></i> Búsqueda de archivo
                    </h5>
                    <form method="GET"
                          action="{{ (isset($pageTitle) && $pageTitle === 'SIGECEL') ? route('sige.index') : route('tracking.public') }}"
                          class="row g-3 align-items-end">
                        <div class="col-md-5 col-lg-4">
                            <label for="code" class="form-label">Código del documento</label>
                            <input type="text"
                                   class="form-control"
                                   id="code"
                                   name="code"
                                   value="{{ old('code', $code) }}"
                                   placeholder="Ejemplo: 00025-25 o 25">
                        </div>
                        <div class="col-md-3 col-lg-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-1"></i> Buscar
                            </button>
                        </div>
                        <div class="col-12">
                            <small class="text-muted">
                                Ingrese el código que aparece en su documento para ver en qué área se encuentra.
                            </small>
                        </div>
                    </form>
                    @if($error)
                        <div class="alert alert-danger mt-3 mb-0">
                            {{ $error }}
                        </div>
                    @endif
                </div>
            </div>

            @if($message)
                @php
                    $currentSegment = !empty($segments) ? end($segments) : null;
                    $currentArea    = $currentSegment['role'] ?? null;
                    $senderArea     = optional(optional($message->sender)->role)->role;
                @endphp

                {{-- Resultado en una fila horizontal --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-light">
                        <strong>Resultado de la búsqueda</strong>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm mb-0 align-middle tracking-result-table">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 60px;">Nro</th>
                                        <th style="width: 140px;">Código</th>
                                        <th>Asunto</th>
                                        <th style="width: 220px;">Área remitente</th>
                                        <th style="width: 220px;">Área actual</th>
                                        <th style="width: 180px;">Fecha de registro</th>
                                        <th style="width: 110px;" class="text-center">Detalle</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>1</td>
                                        <td>{{ $message->code }}</td>
                                        <td>{{ $message->subject }}</td>
                                        <td>{{ $senderArea ?? 'No registrado' }}</td>
                                        <td>{{ $currentArea ?? 'No disponible' }}</td>
                                        <td>{{ $message->created_at->format('d/m/Y H:i') }}</td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-outline-primary"
                                                    type="button"
                                                    data-bs-toggle="collapse"
                                                    data-bs-target="#trackingDetail"
                                                    aria-expanded="true"
                                                    aria-controls="trackingDetail">
                                                <i class="fas fa-eye"></i> Ver detalle
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Detalle: recorrido por áreas, todo en horizontal --}}
                <div id="trackingDetail" class="collapse show">
                    {{-- Recorrido del documento por áreas (una tarjeta por área, con color arriba) --}}
                    @if(!empty($areaSegments))
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-light">
                                <strong>Recorrido del documento por áreas</strong>
                            </div>
                            <div class="card-body">
                                <div class="d-flex flex-wrap gap-2 tracking-actions-row">
                                    @foreach($areaSegments as $seg)
                                        @php
                                            $status = $seg['status_action'] ?? null;
                                            $normalizedStatus = in_array($status, ['in_review','observed','approved','finalized','archived','cancelled'], true)
                                                ? $status
                                                : 'in_review';
                                            $colorClass = match($normalizedStatus) {
                                                'in_review' => 'trk-estado-enrevision',
                                                'observed'  => 'trk-estado-observado',
                                                'approved'  => 'trk-estado-aprobado',
                                                'finalized' => 'trk-estado-finalizado',
                                                'archived'  => 'trk-estado-archivado',
                                                'cancelled' => 'trk-estado-anulado',
                                                default     => 'trk-estado-neutro',
                                            };
                                        @endphp
                                        <div class="tracking-card {{ $colorClass }}">
                                            <div class="tracking-card-area">
                                                {{ $seg['role'] ?? 'Área no definida' }}
                                                @if($loop->first)
                                                    <span class="badge bg-light text-muted ms-1" style="font-size: 0.7rem;">
                                                        Remitente
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="tracking-card-date text-muted">
                                                {{ \Carbon\Carbon::parse($seg['from'])->format('d/m/Y') }}
                                                ·
                                                {{ \Carbon\Carbon::parse($seg['from'])->format('H:i') }}
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="mt-3 small">
                                    <span class="tracking-legend-dot trk-estado-enrevision"></span> En revisión
                                    <span class="tracking-legend-dot trk-estado-observado ms-3"></span> Observado
                                    <span class="tracking-legend-dot trk-estado-aprobado ms-3"></span> Aprobado
                                    <span class="tracking-legend-dot trk-estado-finalizado ms-3"></span> Finalizado
                                    <span class="tracking-legend-dot trk-estado-archivado ms-3"></span> Archivado
                                    <span class="tracking-legend-dot trk-estado-anulado ms-3"></span> Anulado
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

<style>
    .tracking-result-table th,
    .tracking-result-table td {
        font-size: 0.85rem;
    }

    .tracking-actions-row {
        align-items: stretch;
    }

    .tracking-card {
        min-width: 180px;
        max-width: 220px;
        border-radius: 4px;
        border: 1px solid #dee2e6;
        padding: 8px 10px;
        font-size: 0.85rem;
        background-color: #ffffff;
        border-top-width: 5px;
    }

    .tracking-card-area {
        font-weight: 600;
        margin-bottom: 2px;
    }

    .tracking-card-date {
        font-size: 0.78rem;
    }

    /* Colores por estado */
    .trk-estado-enrevision { border-top-color: #ffc107; } /* amarillo */
    .trk-estado-observado  { border-top-color: #fd7e14; } /* naranja */
    .trk-estado-aprobado   { border-top-color: #198754; } /* verde */
    .trk-estado-finalizado { border-top-color: #dc3545; } /* rojo */
    .trk-estado-archivado  { border-top-color: #0d6efd; } /* azul */
    .trk-estado-anulado    { border-top-color: #6c757d; } /* gris oscuro */
    .trk-estado-neutro     { border-top-color: #adb5bd; } /* gris claro */

    .tracking-legend-dot {
        display: inline-block;
        width: 14px;
        height: 14px;
        border-radius: 50%;
        margin-right: 4px;
        vertical-align: middle;
    }

    .tracking-legend-dot.trk-estado-enrevision { background-color: #ffc107; }
    .tracking-legend-dot.trk-estado-observado  { background-color: #fd7e14; }
    .tracking-legend-dot.trk-estado-aprobado   { background-color: #198754; }
    .tracking-legend-dot.trk-estado-finalizado { background-color: #dc3545; }
    .tracking-legend-dot.trk-estado-archivado  { background-color: #0d6efd; }
    .tracking-legend-dot.trk-estado-anulado    { background-color: #6c757d; }
</style>
@endsection
