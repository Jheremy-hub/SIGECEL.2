<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bandeja de Entrada</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-inbox"></i> Bandeja de Entrada
            </a>
            <div class="navbar-nav ms-auto">
                <a href="{{ route('messages.create') }}" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-plus"></i> Nuevo Mensaje
                </a>
                <a href="{{ route('messages.sent') }}" class="btn btn-outline-light btn-sm ms-2">
                    <i class="fas fa-paper-plane"></i> Mensajes Enviados
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
                    <div class="card-header bg-info text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-envelope"></i> Mensajes Recibidos
                        </h5>
                    </div>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if($messages->count() > 0)
                            <div class="list-group">
                                @foreach($messages as $message)
                                    <a href="{{ route('messages.show', $message->id) }}" 
                                       class="list-group-item list-group-item-action {{ !$message->is_read ? 'list-group-item-warning' : '' }}">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1">
                                                @if(!$message->is_read)
                                                    <i class="fas fa-envelope text-primary"></i>
                                                @else
                                                    <i class="fas fa-envelope-open text-muted"></i>
                                                @endif
                                                De: {{ $message->sender->name }} {{ $message->sender->apellidos }}
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
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> No tienes mensajes en tu bandeja de entrada.
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