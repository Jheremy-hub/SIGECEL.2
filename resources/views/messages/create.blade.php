<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enviar Mensaje</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-envelope"></i> Enviar Mensaje
            </a>
            <div class="navbar-nav ms-auto">
                <a href="{{ route('messages.inbox') }}" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-inbox"></i> Bandeja de Entrada
                </a>
                <a href="{{ route('dashboard') }}" class="btn btn-outline-light btn-sm ms-2">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-paper-plane"></i> Nuevo Mensaje
                        </h5>
                    </div>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('messages.store') }}" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label for="receiver_id" class="form-label">Destinatario</label>
                                <select class="form-control" id="receiver_id" name="receiver_id" required>
                                    <option value="">Seleccionar usuario...</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">
                                            {{ $user->name }} {{ $user->apellidos }} - {{ $user->email }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="subject" class="form-label">Asunto</label>
                                <input type="text" class="form-control" id="subject" name="subject" required>
                            </div>

                            <div class="mb-3">
                                <label for="message" class="form-label">Mensaje</label>
                                <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="file" class="form-label">Archivo Adjunto (PDF, imágenes, documentos - Máx 10MB)</label>
                                <input type="file" class="form-control" id="file" name="file">
                                <div class="form-text">Formatos permitidos: PDF, JPG, JPEG, PNG, DOC, DOCX, TXT</div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane"></i> Enviar Mensaje
                                </button>
                                <a href="{{ route('messages.inbox') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancelar
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>