<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
</head>
<body style="margin:0;padding:20px;background-color:#f4f4f4;font-family:Arial,Helvetica,sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:650px;margin:0 auto;background-color:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 2px 10px rgba(0,0,0,0.1);">
        <tr>
            <td align="center" style="padding:0;background-color:#2c3e50;">
                {{-- Usar CID attachment con $message->embed() para mejor compatibilidad con clientes de correo --}}
                @if($useCustomImage && $customImagePath && file_exists($customImagePath))
                    <img src="{{ $message->embed($customImagePath) }}" 
                         alt="Saludo de cumpleaños"
                         style="display:block;width:100%;max-height:600px;object-fit:cover;">
                @else
                    <img src="{{ $message->embed(public_path('Backend/Style/Ima.Cumple.jpg')) }}" 
                         alt="Saludo de cumpleaños"
                         style="display:block;width:100%;max-height:600px;object-fit:cover;">
                @endif
            </td>
        </tr>
        <tr>
            <td style="background-color:#f8f9fa;padding:20px;text-align:center;">
                <p style="margin:0;color:#7f8c8d;font-size:14px;">
                    Atentamente,<br>
                    <strong style="color:#2c3e50;font-weight:bold;">Colegio de Economistas de Lima</strong>
                </p>
            </td>
        </tr>
    </table>
</body>
</html>

