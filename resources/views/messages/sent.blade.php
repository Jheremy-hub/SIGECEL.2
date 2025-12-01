<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mensajes Enviados</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-paper-plane"></i> Mensajes Enviados
            </a>
            <div class="navbar-nav ms-auto">
                <a href="{{ route('messages.create') }}" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-plus"></i> Nuevo Mensaje
                </a>
                <a href="{{ route('messages.inbox') }}" class="btn btn-outline-light btn-sm ms-2">
                    <i class="fas fa-inbox"></i> Bandeja de Entrada
                </a>
                <a href="{{ route('dashboard') }}" class="btn btn-outline-light btn-sm ms-2">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-paper-plane"></i> Mensajes Enviados
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($messages->count() > 0)
                            <div class="list-group">
                                @foreach($messages as $message)
                                    <div class="list-group-item">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1">
                                                <i class="fas fa-share"></i>
                                                Para: {{ $message->receiver->name }} {{ $message->receiver->apellidos }}
                                            </h6>
                                            <small>{{ $message->created_at->diffForHumans() }}</small>
                                        </div>
                                        <p class="mb-1"><strong>{{ $message->subject }}</strong></p>
                                        <p class="mb-1 text-muted">{{ Str::limit($message->message, 100) }}</p>
                                        @if($message->file_path)
                                            <small class="text-success">
                                                <i class="fas fa-paperclip"></i> Archivo adjunto: {{ $message->file_name }}
                                            </small>
                                        @endif
                                        <div class="mt-2">
                                            <a href="{{ route('messages.show', $message->id) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i> Ver
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> No has enviado ningún mensaje.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>