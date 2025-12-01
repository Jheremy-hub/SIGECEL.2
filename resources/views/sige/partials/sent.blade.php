<!-- resources/views/sige/partials/sent.blade.php (UTF-8) -->
<!-- Diseño unificado con Recibidos: filtros simples y cuadrícula -->

<!-- Filtros -->
<div class="card-body border-bottom bg-light py-2">
    <div class="row g-2 align-items-center">
        <div class="col-md-2">
            <input type="text" class="form-control form-control-sm" id="sentSearchCode" placeholder="Filtrar por N° de expediente...">
        </div>
        <div class="col-md-3">
            <input type="text" class="form-control form-control-sm" id="sentSearchReceiver" placeholder="Filtrar por destinatario...">
        </div>
        <div class="col-md-3">
            <input type="text" class="form-control form-control-sm" id="sentSearchContent" placeholder="Filtrar por contenido...">
        </div>
        <div class="col-md-2">
            <label for="sentFilterDate" class="form-label visually-hidden">Filtrar por fecha</label>
            <input type="date" class="form-control form-control-sm" id="sentFilterDate" aria-label="Filtrar por fecha" title="Filtrar por fecha" placeholder="dd/mm/aaaa">
        </div>
    </div>
    </div>

<div class="card-body p-0">
    @if($sentMessages->count() > 0)
        <div class="table-responsive" style="max-height: 500px;">
            <table class="table table-sm table-hover mb-0 table-grid" id="sentMessagesTable">
                <thead class="table-light sticky-top">
                    <tr>
                        <th width="90" class="text-center py-1">N° de expediente</th>
                        <th width="140" class="py-1">Área</th>
                        <th width="180" class="py-1">Destinatario</th>
                        <th class="py-1">Contenido / Urgencia</th>
                        <th width="110" class="text-center py-1">Fecha</th>
                        <th width="80" class="text-center py-1">Revisar</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sentMessages as $message)
                    <tr style="font-size: 0.85rem;" data-message-id="{{ $message->id }}" data-view-url="{{ url('messages/'.$message->id) }}" data-date="{{ $message->created_at->format('Y-m-d') }}">
                        <td class="text-center py-1"><span class="fw-semibold text-dark">{{ $message->code }}</span></td>
                        <td class="py-1">
                            <div class="text-truncate" title="{{ $message->receiver->role->role ?? ($message->receiver->cargo ?? 'Sin área') }}">
                                {{ $message->receiver->role->role ?? ($message->receiver->cargo ?? 'Sin área') }}
                            </div>
                        </td>
                        <td class="py-1">
                            <div class="fw-bold">{{ $message->receiver->name }}</div>
                            <small class="text-muted">{{ $message->receiver->apellidos }}</small>
                        </td>
                        <td class="py-1">
                            <div class="mb-1">
                                <span class="badge {{ $message->urgency_badge_class }}">
                                    {{ $message->urgency_label }}
                                </span>
                            </div>
                            <div class="text-muted text-truncate d-block" style="max-width: 320px;">
                                {{ Str::limit($message->message, 90) }}
                            </div>
                            @if($message->file_path)
                                <small class="text-success"><i class="fas fa-paperclip"></i> Adjunto</small>
                            @endif
                        </td>
                        <td class="text-center py-1">
                            <small class="text-muted">
                                <div>{{ $message->created_at->format('d/m/Y') }}</div>
                                <div class="text-primary">{{ $message->created_at->format('H:i') }}</div>
                            </small>
                        </td>
                        <td class="text-center py-1">
                            <a class="btn btn-sm btn-outline-primary" href="{{ url('messages/'.$message->id) }}" title="Ver detalle">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="text-center py-4">
            <div class="empty-state-icon mb-2"><i class="fas fa-paper-plane fa-3x text-muted"></i></div>
            <h6 class="text-muted mb-2">No has enviado ningún mensaje</h6>
            <button class="btn btn-sm btn-primary" onclick="switchToCompose()"><i class="fas fa-plus me-1"></i> Escribir Mensaje</button>
        </div>
    @endif
</div>

<style>
.table-grid { border-collapse: collapse !important; }
.table-grid th, .table-grid td { border: 1px solid #dee2e6 !important; }
.table-grid thead th { border-bottom-width: 2px !important; }
</style>

<script>
function filterSent() {
    const code = (document.getElementById('sentSearchCode')?.value || '').toLowerCase();
    const receiver = (document.getElementById('sentSearchReceiver')?.value || '').toLowerCase();
    const content = (document.getElementById('sentSearchContent')?.value || '').toLowerCase();
    const date = document.getElementById('sentFilterDate')?.value || '';

    document.querySelectorAll('#sentMessagesTable tbody tr').forEach(row => {
        const codeText = row.cells[0].textContent.toLowerCase();
        const receiverText = row.cells[2].textContent.toLowerCase();
        const contentText = row.cells[3].textContent.toLowerCase();
        const rowDate = row.getAttribute('data-date') || '';

        let show = true;
        if (code && !codeText.includes(code)) show = false;
        if (receiver && !receiverText.includes(receiver)) show = false;
        if (content && !contentText.includes(content)) show = false;
        if (date && rowDate !== date) show = false;
        row.style.display = show ? '' : 'none';
    });
}

['sentSearchCode','sentSearchReceiver','sentSearchContent'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.addEventListener('input', filterSent);
});
document.getElementById('sentFilterDate')?.addEventListener('change', filterSent);

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('#sentMessagesTable tbody tr').forEach(row => {
        row.addEventListener('click', function(e){
            // Avoid triggering when clicking on links/buttons which already navigate
            if (e.target.closest('a,button')) return;
            const url = this.getAttribute('data-view-url');
            if (url) window.location.href = url;
        });
    });
});
</script>
