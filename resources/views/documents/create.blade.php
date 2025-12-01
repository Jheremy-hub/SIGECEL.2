@extends('layouts.app')
@section('title', 'Subir Oficio')
@section('page-title', 'Subir Nuevo Oficio')
@section('content')
<style>
    /* Estilos para el formulario de Oficio */
    .formal-officio {
        background-color: #000;
        border-color: #000;
        color: #fff;
    }

    .formal-officio:hover {
        background-color: #333;
        border-color: #333;
        color: #fff;
    }

    .card-header.formal-officio {
        background-color: #000;
        color: #fff;
    }
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header formal-officio">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-file-alt me-2"></i> Subir Nuevo Oficio
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('documents.store') }}" enctype="multipart/form-data" id="documentForm">
                        @csrf

                        <!-- Campo oculto para tipo -->
                        <input type="hidden" name="document_type" id="document_type" value="Oficio">

                        <!-- Campos específicos para Oficio -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Tipo de Oficio *</label>
                                <div>
                                    <!-- Tipo de Oficio -->
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="oficio_mode" id="simple" value="Simple" required>
                                        <label class="form-check-label" for="simple">Simple</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="oficio_mode" id="multiple" value="Múltiple" required>
                                        <label class="form-check-label" for="multiple">Múltiple</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="sent_by" class="form-label">Enviado Por *</label>
                                <input type="text" class="form-control" id="sent_by" name="sent_by" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="acceptance_date" class="form-label">Fecha de Aceptación *</label>
                                <input type="date" class="form-control" id="acceptance_date" name="acceptance_date" required>
                            </div>
                            <div class="col-md-6">
                                <label for="web_upload_date" class="form-label">Fecha de Subido a la Web *</label>
                                <input type="date" class="form-control" id="web_upload_date" name="web_upload_date" required>
                            </div>
                        </div>

                        <!-- Campos comunes -->
                        <div class="mb-3">
                            <label for="institution" class="form-label">Institución *</label>
                            <input type="text" class="form-control" id="institution" name="institution" required>
                        </div>
                        <div class="mb-3">
                            <label for="subject" class="form-label">Asunto *</label>
                            <input type="text" class="form-control" id="subject" name="subject" required>
                        </div>
                        <div class="mb-3">
                            <label for="content" class="form-label">Contenido (opcional)</label>
                            <textarea class="form-control" id="content" name="content" rows="5"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="file" class="form-label">Archivo Adjunto *</label>
                            <input type="file" class="form-control" id="file" name="file" required>
                            <div class="form-text">Formatos permitidos: PDF, JPG, JPEG, PNG, DOC, DOCX, TXT - Máximo 10MB</div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-upload me-1"></i> Subir Oficio
                            </button>
                            <a href="{{ route('documents.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i> Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Si estamos en modo Oficio, llenar campos con fechas actuales por defecto
        if (document.getElementById('acceptance_date')) {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('acceptance_date').value = today;
            document.getElementById('web_upload_date').value = today;
        }
    });
</script>
@endsection