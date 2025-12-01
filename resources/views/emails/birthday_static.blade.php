<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Saludo de cumpleaños</title>
</head>
<body style="margin:0;padding:0;background-color:#f4f4f4;">
@php
    // Ruta relativa dentro de public/ que llega desde el Mailable
    $bgPath = isset($backgroundPath) && $backgroundPath ? $backgroundPath : 'Backend/Style/Ima.Cumple.jpg';
    $cid = null;

    // En correos: incrustar la imagen como embebida (cid) para que no dependa de la URL 127.0.0.1
    if (isset($message) && method_exists($message, 'embed')) {
        $full = public_path($bgPath);
        if (file_exists($full)) {
            $cid = $message->embed($full);
        }
    }
@endphp
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
           style="background-color:#f4f4f4;">
        <tr>
            <td align="center" style="padding:20px 10px;">
                @if($cid)
                    <img src="{{ $cid }}"
                         alt="Saludo de cumpleaños"
                         width="800"
                         style="display:block;border:0;max-width:100%;height:auto;">
                @endif

                {{-- Una sola línea con el nombre del colegiado --}}
                <p style="margin-top:15px;
                          font-family:Arial,Helvetica,sans-serif;
                          font-size:20px;
                          font-weight:bold;
                          color:#000000;">
                    {{ $name }}
                </p>
            </td>
        </tr>
    </table>
</body>
</html>

