<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de seguimiento - N° {{ $message->code }}</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/html2pdf.js@0.10.1/dist/html2pdf.bundle.min.js"></script>

    <style>
        body { background: #fff; }
        .report-header { border-bottom: 2px solid #e9ecef; margin-bottom: 1rem; }
        .small-text { font-size: 0.9rem; }
        .table-sm td, .table-sm th { padding: .4rem; }
        .meta { color: #6c757d; }
        .badge-code { font-size: .9rem; }
        @media print {
            .no-print { display: none !important; }
            a[href]:after { content: ""; }
        }
    </style>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        // Logs en orden cronológico (antiguo -> reciente)
        $logs = $message->logs->sortBy('created_at');
    @endphp
</head>
<body>
<div class="container my-3">
    <div class="d-flex justify-content-between align-items-start report-header">
        <div>
            <h5 class="mb-1">Reporte de seguimiento del documento</h5>
            <div class="meta">Generado: {{ now()->format('d/m/Y H:i') }}</div>
        </div>
        <div class="text-end">
            <span class="badge bg-secondary badge-code" data-code="{{ $message->code }}">
                N° de expediente: {{ $message->code }}
            </span>
        </div>
    </div>

    <div class="no-print mb-3 d-flex gap-2">
        <button id="btnDownload" class="btn btn-primary btn-sm">
            <i class="fas fa-file-download"></i> Descargar PDF
        </button>
        <button class="btn btn-outline-secondary btn-sm" onclick="window.print()">
            <i class="fas fa-print"></i> Imprimir
        </button>
    </div>

    <div id="report-content">
        <div class="card mb-3">
            <div class="card-body">
                <div class="row small-text">
                    <div class="col-md-6">
                        <div><strong>De:</strong> {{ $message->sender->name }} {{ $message->sender->apellidos }}</div>
                        <div><strong>Email:</strong> {{ $message->sender->email }}</div>
                        <div><strong>Cargo:</strong> {{ $message->sender->cargo ?? 'No especificado' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div><strong>Para:</strong> {{ $message->receiver->name }} {{ $message->receiver->apellidos }}</div>
                        <div><strong>Email:</strong> {{ $message->receiver->email }}</div>
                        <div><strong>Cargo:</strong> {{ $message->receiver->cargo ?? 'No especificado' }}</div>
                    </div>
                </div>
                <div class="row small-text mt-2">
                    <div class="col-md-6"><strong>Fecha:</strong> {{ $message->created_at->format('d/m/Y H:i') }}</div>
                    <div class="col-md-6"><strong>Asunto:</strong> {{ $message->subject }}</div>
                </div>
                @if($message->file_path)
                    <div class="small-text mt-2">
                        <strong>Archivo adjunto:</strong> {{ $message->file_name }}
                        <span class="meta">
                            (Tipo: {{ $message->file_type }}, Tamaño: {{ number_format($message->file_size / 1024, 2) }} KB)
                        </span>
                    </div>
                @endif
                <div class="mt-3 small-text">
                    <strong>Mensaje:</strong>
                    <div class="border rounded p-2 bg-light">{!! nl2br(e($message->message)) !!}</div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-light py-2"><strong>Seguimiento del documento</strong></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Área</th>
                                <th>Nombre</th>
                                <th>Acción</th>
                                <th class="text-center" style="width: 110px;">Fecha</th>
                                <th class="text-center" style="width: 80px;">Hora</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($logs as $log)
                            @php
                                $details = [];
                                if (is_string($log->details)) {
                                    $decoded = json_decode($log->details, true);
                                    $details = is_array($decoded) ? $decoded : [];
                                } elseif (is_array($log->details)) {
                                    $details = $log->details;
                                }

                                $accion = match($log->action) {
                                    'sent'       => 'Enviado',
                                    'read'       => 'Leído / Recibido',
                                    'downloaded' => 'Archivo descargado',
                                    'forwarded'  => 'Asignado' . (isset($details['new_receiver_name']) ? ' a: ' . $details['new_receiver_name'] : ''),
                                    'in_review'  => 'En revisión',
                                    'approved'   => 'Aprobado',
                                    'observed'   => 'Observado',
                                    'finalized'  => 'Finalizado',
                                    'archived'   => 'Archivado',
                                    'cancelled'  => 'Anulado',
                                    'reply'      => 'Respuesta',
                                    default      => ucfirst($log->action),
                                };

                                $rol = $log->user && $log->user->role ? $log->user->role->role : null;
                            @endphp
                            <tr>
                                <td>{{ $rol ?? '—' }}</td>
                                <td>{{ $log->user->name ?? '' }} {{ $log->user->apellidos ?? '' }}</td>
                                <td>
                                    <strong>{{ $accion }}</strong>
                                    @if($log->action === 'forwarded' && (!empty($details['new_receiver_name']) || !empty($details['new_receiver_role'])))
                                        <div class="meta">
                                            {{ $details['new_receiver_name'] ?? '' }}
                                            @if(!empty($details['new_receiver_role']))
                                                ({{ $details['new_receiver_role'] }})
                                            @endif
                                        </div>
                                    @endif
                                    @if(!empty($details['file_name']) && $log->action === 'downloaded')
                                        <div class="meta">{{ $details['file_name'] }}</div>
                                    @endif
                                </td>
                                <td class="text-center">{{ \Carbon\Carbon::parse($log->created_at)->format('d/m/Y') }}</td>
                                <td class="text-center">{{ \Carbon\Carbon::parse($log->created_at)->format('H:i') }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js" defer></script>
<script>
    // Ajustar título del documento
    (function () {
        try {
            var badge = document.querySelector('.badge-code');
            var code = badge ? badge.getAttribute('data-code') || '' : '';
            document.title = 'Reporte de seguimiento - N° ' + code;
        } catch (e) {}
    })();

    function downloadAsPdf() {
        const element = document.getElementById('report-content');
        const opt = {
            margin: 10,
            filename: 'Reporte_Expediente_{{ $message->code }}.pdf',
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { scale: 2, useCORS: true },
            jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
        };
        window.html2pdf().set(opt).from(element).save();
    }

    function ensureHtml2PdfReady(callback) {
        if (typeof window.html2pdf !== 'undefined') {
            callback();
            return;
        }
        const fallbackSrc = 'https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js';
        if (!document.getElementById('html2pdf-fallback')) {
            const s = document.createElement('script');
            s.src = fallbackSrc;
            s.id = 'html2pdf-fallback';
            document.head.appendChild(s);
        }
        const start = Date.now();
        const timer = setInterval(() => {
            if (typeof window.html2pdf !== 'undefined') {
                clearInterval(timer);
                callback();
            } else if (Date.now() - start > 5000) {
                clearInterval(timer);
                alert('No se pudo cargar el generador de PDF. Intenta usar Imprimir > Guardar como PDF.');
            }
        }, 150);
    }

    document.getElementById('btnDownload').addEventListener('click', function () {
        ensureHtml2PdfReady(downloadAsPdf);
    });

    // Auto-descarga si viene con ?auto=1
    const params = new URLSearchParams(window.location.search);
    if (params.get('auto') === '1') {
        window.addEventListener('load', function () {
            ensureHtml2PdfReady(downloadAsPdf);
        });
    }
</script>
</body>
</html>
