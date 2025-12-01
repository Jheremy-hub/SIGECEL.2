<!-- resources/views/documents/partials/document-table.blade.php -->
<style>
    .card-header.simple { background-color: #fff; border-bottom: 1px solid #e5e7eb; }
    .filters .form-control, .filters .btn { height: 36px; }
    .btn-action { padding: 4px 10px; }
</style>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-header simple">
        <h6 class="mb-0">
            <i class="fas fa-{{ $type == 'Oficio' ? 'file-alt' : ($type == 'Memo' ? 'sticky-note' : 'envelope') }} me-2"></i>
            {{ $type }}s
        </h6>
    </div>
    <div class="card-body">
        <!-- Filtros -->
        <form method="GET" action="#" id="filterForm_{{ strtolower($type) }}" class="mb-3 filters">
            <input type="hidden" name="type" value="{{ $type }}">
            <div class="row g-2">
                <div class="col-md-2">
                    <input type="text" name="code" class="form-control form-control-sm" placeholder="Código" value="{{ request('code') }}">
                </div>
                <div class="col-md-2">
                    <input type="text" name="subject" class="form-control form-control-sm" placeholder="Asunto" value="{{ request('subject') }}">
                </div>
                <div class="col-md-2">
                    <input type="text" name="sender" class="form-control form-control-sm" placeholder="Remitente" value="{{ request('sender') }}">
                </div>
                <div class="col-md-2">
                    <input type="text" name="institution" class="form-control form-control-sm" placeholder="Institución" value="{{ request('institution') }}">
                </div>
                <div class="col-md-2">
                    <input type="date" name="date" class="form-control form-control-sm" value="{{ request('date') }}">
                </div>
                <div class="col-md-2">
                    <div class="d-flex gap-1">
                        <button type="submit" class="btn btn-sm btn-dark w-100">
                            <i class="fas fa-filter me-1"></i> Filtrar
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary reset-filters-btn" data-type="{{ strtolower($type) }}" title="Restablecer">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <!-- Tabla -->
        @if($documents->count() > 0)
            <div class="table-responsive mt-3">
                <table class="table table-sm table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Código</th>
                            @if(in_array($type, ['Oficio','Memo','Carta']))
                                <th>Tipo</th>
                            @endif
                            <th>Remitente</th>
                            <th>Institución</th>
                            <th>Asunto</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($documents as $doc)
                            <tr>
                                <td>{{ $doc->document_code }}</td>
                                @if(in_array($type, ['Oficio','Memo','Carta']))
                                    <td>
                                        @php $meta = $doc->meta ? json_decode($doc->meta, true) : []; @endphp
                                        {{ $meta['mode'] ?? '—' }}
                                    </td>
                                @endif
                                <td>{{ $doc->sender }}</td>
                                <td>{{ $doc->institution }}</td>
                                <td>{{ $doc->subject }}</td>
                                <td>{{ $doc->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <a href="{{ route('documents.show', $doc->id) }}" class="btn btn-sm btn-outline-primary btn-action">
                                        <i class="fas fa-eye"></i> Ver
                                    </a>
                                    <a href="{{ route('documents.download', $doc->id) }}" class="btn btn-sm btn-outline-success btn-action">
                                        <i class="fas fa-download"></i> Descargar
                                    </a>
                                    <form action="{{ route('documents.destroy', $doc->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger btn-action" onclick="return confirm('¿Estás seguro de eliminar este documento?')">
                                            <i class="fas fa-trash"></i> Eliminar
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-3 text-muted">No hay {{ strtolower($type) }}s.</div>
        @endif

        <!-- Botón "Subir Nuevo Documento" -->
        <div class="mt-4">
            <a href="{{ route('documents.create', ['type' => $type]) }}" class="btn btn-dark">
                <i class="fas fa-plus me-1"></i> Subir Nuevo {{ $type }}
            </a>
        </div>
    </div>
</div>

<script>
document.addEventListener('click', function(e) {
    if (e.target.closest('.reset-filters-btn')) {
        const button = e.target.closest('.reset-filters-btn');
        const type = button.getAttribute('data-type');
        const form = document.getElementById(`filterForm_${type}`);
        if (form) {
            form.reset();
            loadDocuments(type.charAt(0).toUpperCase() + type.slice(1));
        }
    }
});
</script>
