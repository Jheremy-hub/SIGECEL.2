@extends('layouts.app')
@section('title', 'Mis Documentos')
@section('page-title', 'Mis Documentos')
@section('content')
<style>
    /* Paleta de colores negro y blanco */
    .btn-black {
        background-color: #000;
        border-color: #000;
        color: #fff;
        font-weight: 600;
    }
    .btn-black:hover {
        background-color: #333;
        border-color: #333;
        color: #fff;
    }
    .btn-white {
        background-color: #fff;
        border-color: #ddd;
        color: #000;
        font-weight: 600;
    }
    .btn-white:hover {
        background-color: #f8f9fa;
        border-color: #ccc;
        color: #000;
    }
    .card-header.black {
        background-color: #000;
        color: #fff;
    }
    .card-header.white {
        background-color: #fff;
        color: #000;
        border-bottom: 2px solid #ddd;
    }
    .text-black {
        color: #000;
    }
    .text-white {
        color: #fff;
    }
    .bg-light-gray {
        background-color: #f8f9fa;
    }
    .border-dark {
        border: 1px solid #333;
    }
    .table thead {
        background-color: #f8f9fa;
        border-bottom: 2px solid #ddd;
    }
    .table th {
        font-weight: 600;
        color: #000;
    }
    .table td {
        border-top: 1px solid #eee;
    }
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Título -->
            <h5 class="text-primary mb-4">
                <i class="fas fa-folder-open me-2"></i> Mis Documentos
            </h5>

            <!-- Botones de selección de tipo -->
            <div class="d-flex flex-wrap gap-2 mb-4">
                <button type="button" class="btn btn-lg btn-white flex-fill" onclick="loadDocuments('Oficio')">
                    <i class="fas fa-file-alt me-2"></i> Oficio
                </button>
                <button type="button" class="btn btn-lg btn-white flex-fill" onclick="loadDocuments('Memo')">
                    <i class="fas fa-sticky-note me-2"></i> Memo
                </button>
                <button type="button" class="btn btn-lg btn-white flex-fill" onclick="loadDocuments('Carta')">
                    <i class="fas fa-envelope me-2"></i> Carta
                </button>
            </div>

            <!-- Área donde se carga la tabla dinámicamente -->
            <div id="documentsArea" class="mt-3">
                <div class="text-center py-5">
                    <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                    <p class="lead">Selecciona un tipo de documento para ver su lista.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function loadDocuments(type) {
    const url = '{{ route("documents.getDocumentsByType") }}';
    const formData = new FormData();
    formData.append('type', type);

    // Mostrar spinner
    document.getElementById('documentsArea').innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando ${type}...</span>
            </div>
            <p class="mt-2">Cargando ${type}s...</p>
        </div>
    `;

    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('documentsArea').innerHTML = data.html;
        } else {
            document.getElementById('documentsArea').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> Error al cargar los documentos: ${data.error || 'Desconocido'}
                </div>
            `;
        }
    })
    .catch(error => {
        document.getElementById('documentsArea').innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i> Error de red: ${error.message}
            </div>
        `;
    });
}

document.addEventListener('click', function(e) {
    if (e.target.closest('.reset-filters-btn')) {
        const button = e.target.closest('.reset-filters-btn');
        const type = button.getAttribute('data-type');
        const form = document.getElementById(`filterForm_${type}`);
        if (form) {
            form.reset();
            // Vuelve a cargar los documentos del tipo
            loadDocuments(type.charAt(0).toUpperCase() + type.slice(1));
        }
    }
});
</script>
@endsection