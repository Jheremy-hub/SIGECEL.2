<!-- resources/views/sige/partials/inbox.blade.php (UTF-8) -->

<!-- Filtros -->
<div class="card-body border-bottom bg-light py-2">
    <div class="row g-2 align-items-center">
        <div class="col-md-2">
            <input type="text" class="form-control form-control-sm" id="searchCode" placeholder="Filtrar por N° de expediente...">
        </div>
        <div class="col-md-3">
            <input type="text" class="form-control form-control-sm" id="searchSender" placeholder="Filtrar por remitente...">
        </div>
        <div class="col-md-3">
            <input type="text" class="form-control form-control-sm" id="searchSubject" placeholder="Filtrar por contenido...">
        </div>
        <div class="col-md-2">
            <label for="filterDate" class="form-label visually-hidden">Filtrar por fecha</label>
            <input type="date" class="form-control form-control-sm" id="filterDate" aria-label="Filtrar por fecha" title="Filtrar por fecha" placeholder="dd/mm/aaaa">
        </div>
    </div>
</div>

<div class="card-body p-0">
    @if($inboxMessages->count() > 0)
        <div class="table-responsive" style="max-height: 500px;">
            <table class="table table-sm table-hover mb-0 table-grid" id="messagesTable">
                <thead class="table-light sticky-top">
                    <tr>
                        <th width="90" class="text-center py-1">N° de expediente</th>
                        <th width="140" class="py-1">Área</th>
                        <th width="180" class="py-1">Remitente</th>
                        <th class="py-1">Contenido / Urgencia</th>
                        <th width="110" class="text-center py-1">Fecha</th>
                        <th width="80" class="text-center py-1">Revisar</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($inboxMessages as $message)
                    @php
                        $codeDisplay = $message->code;
                        $hasReplyForMe = $message->logs->contains(function($log) {
                            if ($log->action !== 'reply') {
                                return false;
                            }
                            $details = is_string($log->details)
                                ? json_decode($log->details, true)
                                : (is_array($log->details) ? $log->details : []);
                            $toId = $details['to_user_id'] ?? null;
                            return $toId && (int) $toId === (int) Auth::id();
                        });
                        if ($hasReplyForMe && $codeDisplay) {
                            $parts = explode('-', $codeDisplay);
                            if (count($parts) === 2) {
                                $codeDisplay = $parts[0] . 'R-' . $parts[1];
                            } else {
                                $codeDisplay = $codeDisplay . 'R';
                            }
                        }
                    @endphp
                    <tr class="message-row {{ !$message->is_read ? 'unread-row' : '' }}" data-message-id="{{ $message->id }}" data-view-url="{{ url('messages/'.$message->id) }}" data-date="{{ $message->created_at->format('Y-m-d') }}" style="cursor: pointer; font-size: 0.85rem;">
                        <td class="text-center py-1"><span class="fw-semibold text-dark">{{ $codeDisplay }}</span></td>
                        <td class="py-1">
                            <div class="text-truncate" title="{{ $message->sender->role->role ?? ($message->sender->cargo ?? 'Sin área') }}">
                                {{ $message->sender->role->role ?? ($message->sender->cargo ?? 'Sin área') }}
                            </div>
                        </td>
                        <td class="py-1">
                            <div class="d-flex align-items-center">
                                @if(!$message->is_read)
                                    <span class="unread-indicator bg-primary me-1"></span>
                                @endif
                                <div>
                                    <div class="fw-bold">{{ $message->sender->name }}</div>
                                    <small class="text-muted">{{ $message->sender->apellidos }}</small>
                                </div>
                            </div>
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
                            <a class="btn btn-sm btn-outline-primary" href="{{ url('messages/'.$message->id) }}" onclick="event.stopPropagation()" title="Ver detalle">
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
            <div class="empty-state-icon mb-2"><i class="fas fa-inbox fa-3x text-muted"></i></div>
            <h6 class="text-muted mb-2">No hay mensajes en la bandeja de entrada</h6>
            <button class="btn btn-sm btn-primary" onclick="switchToCompose()"><i class="fas fa-plus me-1"></i> Escribir Mensaje</button>
        </div>
    @endif
</div>

<style>
.unread-row { background-color: #f0f8ff !important; border-left: 3px solid #007bff; }
.unread-indicator { width: 6px; height: 6px; border-radius: 50%; display: inline-block; }
.message-row:hover { background-color: #f8f9fa !important; }
.table th { font-weight: 600; font-size: 0.8rem; background-color: #f8f9fa; }
.table td { vertical-align: middle; border-bottom: 1px solid #f0f0f0; }
.sticky-top { position: sticky; top: 0; z-index: 10; }
.table-grid { border-collapse: collapse !important; }
.table-grid th, .table-grid td { border: 1px solid #dee2e6 !important; }
.table-grid thead th { border-bottom-width: 2px !important; }
</style>

<script>
function filterMessages() {
    const codeFilter = (document.getElementById('searchCode')?.value || '').toLowerCase();
    const senderFilter = (document.getElementById('searchSender')?.value || '').toLowerCase();
    const subjectFilter = (document.getElementById('searchSubject')?.value || '').toLowerCase();
    const dateFilter = document.getElementById('filterDate')?.value || '';

    document.querySelectorAll('#messagesTable tbody tr').forEach(row => {
        const code = row.cells[0].textContent.toLowerCase();
        const sender = row.cells[2].textContent.toLowerCase();
        const subject = row.cells[3].textContent.toLowerCase();
        const dateAttr = row.getAttribute('data-date') || '';

        let show = true;
        if (codeFilter && !code.includes(codeFilter)) show = false;
        if (senderFilter && !sender.includes(senderFilter)) show = false;
        if (subjectFilter && !subject.includes(subjectFilter)) show = false;
        if (dateFilter && dateAttr !== dateFilter) show = false;
        row.style.display = show ? '' : 'none';
    });
}

['searchCode','searchSender','searchSubject'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.addEventListener('input', filterMessages);
});
document.getElementById('filterDate')?.addEventListener('change', filterMessages);

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.message-row').forEach(row => {
        row.addEventListener('click', function() {
            const url = this.getAttribute('data-view-url');
            if (url) window.location.href = url;
        });
    });
});
</script>
