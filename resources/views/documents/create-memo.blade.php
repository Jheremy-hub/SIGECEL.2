@extends('layouts.app')
@section('title', 'Subir Memo')
@section('page-title', 'Subir Nuevo Memo')
@section('content')
<style>
    .memo-header { background-color: #000; color: #fff; }
    .btn-memo { background-color: #000; border-color: #000; color: #fff; }
    .btn-memo:hover { background-color: #333; border-color: #333; color: #fff; }
    .inline-radios .form-check { display: inline-block; margin-right: 10px; }
    .form-text-muted { font-size: .85rem; color: #6c757d; }
</style>

<div class="card">
    <div class="card-header memo-header">
        <h5 class="mb-0"><i class="fas fa-sticky-note me-2"></i> Subir Nuevo Memo</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('documents.store') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="document_type" value="Memo">

            <!-- Tipo de Memo: Simple o Múltiple -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Tipo de Memo *</label>
                    <div class="inline-radios">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="memo_mode" id="memo_simple" value="Simple" required>
                            <label class="form-check-label" for="memo_simple">Simple</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="memo_mode" id="memo_multiple" value="Múltiple" required>
                            <label class="form-check-label" for="memo_multiple">Múltiple</label>
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
            </div>

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
                <textarea class="form-control" id="content" name="content" rows="6"></textarea>
            </div>
            <div class="mb-3">
                <label for="file" class="form-label">Archivo Adjunto *</label>
                <input type="file" class="form-control" id="file" name="file" required>
                <div class="form-text-muted">Formatos permitidos: PDF, JPG, JPEG, PNG, DOC, DOCX, TXT - Máximo 10MB</div>
            </div>

            <div class="d-flex justify-content-between">
                <button type="submit" class="btn btn-memo">
                    <i class="fas fa-paper-plane me-1"></i> Enviar Memo
                </button>
                <a href="{{ route('documents.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times me-1"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('acceptance_date');
    if (input && !input.value) {
        input.value = new Date().toISOString().split('T')[0];
    }
});
</script>
@endsection

