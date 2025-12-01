<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>{{ $userMessage->subject ?: 'Nuevo documento en SIGE' }}</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
            background-color: #f4f4f4;
            color: #333333;
        }

        .wrapper {
            width: 100%;
            padding: 20px 0;
        }

        .card {
            max-width: 650px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        /* Encabezado */
        .card-header {
            padding: 16px 20px;
        }

        .header-top {
            width: 100%;
        }

        .header-title {
            display: inline-block;
            font-size: 20px;
            font-weight: bold;
        }

        .header-urgency {
            float: right;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 600;
            background-color: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.7);
        }

        .header-normal {
            background: linear-gradient(90deg, #0d6efd, #0b5ed7);
            color: #ffffff;
        }

        .header-alta {
            background: linear-gradient(90deg, #ffc107, #ffb000);
            color: #212529;
        }

        .header-critica {
            background: linear-gradient(90deg, #dc3545, #bb2d3b);
            color: #ffffff;
        }

        .card-header p {
            clear: both;
            margin: 6px 0 0;
            font-size: 13px;
            opacity: 0.9;
        }

        /* Cuerpo */
        .card-body {
            padding: 20px;
            font-size: 14px;
            line-height: 1.5;
        }

        .section-title {
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #6c757d;
            margin: 0 0 6px;
        }

        .info-row {
            margin-bottom: 8px;
        }

        .label {
            font-weight: bold;
        }

        /* Badge de urgencia */
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 600;
            color: #ffffff;
        }

        .badge-normal {
            background-color: #6c757d;
        }

        .badge-alta {
            background-color: #ffc107;
            color: #212529;
        }

        .badge-critica {
            background-color: #dc3545;
        }

        /* Mensaje */
        .message-box {
            margin-top: 10px;
            padding: 12px 14px;
            background-color: #f8f9fa;
            border-radius: 6px;
            border: 1px solid #e9ecef;
            white-space: pre-wrap;
        }

        /* Footer y botón */
        .footer {
            padding: 14px 20px 18px;
            background-color: #f8f9fa;
            text-align: center;
            font-size: 12px;
            color: #6c757d;
        }

        .btn {
            display: inline-block;
            padding: 8px 16px;
            margin-top: 4px;
            border-radius: 4px;
            background-color: #0d6efd;
            color: #ffffff !important;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
        }
    </style>
</head>

<body>
    @php
    $urgency = method_exists($userMessage, 'getUrgencyAttribute')
    ? $userMessage->urgency
    : 'normal';

    $urgencyLabel = method_exists($userMessage, 'getUrgencyLabelAttribute')
    ? $userMessage->urgency_label
    : 'Normal';

    $badgeClass = 'badge-normal';
    if ($urgency === 'alta') {
    $badgeClass = 'badge-alta';
    } elseif ($urgency === 'critica') {
    $badgeClass = 'badge-critica';
    }

   // Clase de encabezado según urgencia
if ($urgency === 'critica') {
    $headerClass = 'header-critica';
} elseif ($urgency === 'alta') {
    $headerClass = 'header-alta';
} else {
    $headerClass = 'header-normal';
}


    $senderName = trim((optional($userMessage->sender)->name ?? '') . ' ' . (optional($userMessage->sender)->apellidos ?? ''));
    @endphp

    <div class="wrapper">
    <div class="card">
        <div class="card-header {{ $headerClass }}">
            <div class="header-top">
                <span class="header-title">{{ $userMessage->subject ?: 'Nuevo documento en SIGE' }}</span>
                <span class="header-urgency">
                    Nivel de urgencia: {{ $urgencyLabel }}
                </span>
            </div>
            <p>Te ha llegado un nuevo documento en el Sistema Integrado de Gestión.</p>
        </div>
            <div class="card-body">
                <p class="section-title">Detalles del documento</p>
                <div class="info-row">
                    <span class="label">Código:</span>
                    <span>{{ $userMessage->code ?? ('#'.$userMessage->id) }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Fecha:</span>
                    <span>{{ optional($userMessage->created_at)->format('d/m/Y H:i') }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Nivel de urgencia:</span>
                    <span class="badge {{ $badgeClass }}">{{ $urgencyLabel }}</span>
                </div>

                <hr style="border:none;border-top:1px solid #e9ecef;margin:14px 0;">

                <p class="section-title">Remitente</p>
                <div class="info-row">
                    <span class="label">Nombre:</span>
                    <span>{{ $senderName ?: 'No especificado' }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Email:</span>
                    <span>{{ optional($userMessage->sender)->email ?? 'No especificado' }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Cargo:</span>
                    <span>{{ optional($userMessage->sender)->cargo ?? 'No especificado' }}</span>
                </div>

                @if(!empty($userMessage->message))
                <hr style="border:none;border-top:1px solid #e9ecef;margin:14px 0;">
                <p class="section-title">Mensaje</p>
                <div class="message-box">
                    {!! nl2br(e($userMessage->message)) !!}
                </div>
                @endif

                @if(!empty($url))
                <p style="margin-top:16px;">
                    <a href="{{ $url }}" class="btn">Abrir documento en SIGE</a>
                </p>
                @endif
            </div>

            <div class="footer">
                <div>Colegio de Economistas de Lima</div>
                <div>Sistema Integrado de Gestión – SIGECEL</div>
            </div>
        </div>
    </div>
</body>

</html>