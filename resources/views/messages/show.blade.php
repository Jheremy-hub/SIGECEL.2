<div class="container-fluid">
    <div class="card">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h6 class="card-title mb-0">{{ $message->subject }}</h6>
            <a class="btn btn-sm btn-primary" href="{{ route('messages.report', $message->id) }}" target="_blank">
                <i class="fas fa-file-download"></i> Crear reporte
            </a>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>De:</strong> {{ optional($message->sender)->name }} {{ optional($message->sender)->apellidos }}<br>
                    <strong>Email:</strong> {{ optional($message->sender)->email ?? 'No especificado' }}<br>
                    <strong>Cargo:</strong> {{ optional($message->sender)->cargo ?? 'No especificado' }}
                </div>
                <div class="col-md-6">
                    <strong>Para:</strong> {{ optional($message->receiver)->name }} {{ optional($message->receiver)->apellidos }}<br>
                    <strong>Email:</strong> {{ optional($message->receiver)->email ?? 'No especificado' }}<br>
                    <strong>Cargo:</strong> {{ optional($message->receiver)->cargo ?? 'No especificado' }}
                </div>
            </div>

            <div class="mb-3">
                <strong>Fecha:</strong> {{ $message->created_at->format('d/m/Y H:i') }}
            </div>

            @php
                $urgency      = $message->urgency;
                $urgencyLabel = $message->urgency_label;
                $barClass = match($urgency) {
                    'critica' => 'urgency-bar-critical',
                    'alta'    => 'urgency-bar-high',
                    default   => 'urgency-bar-normal',
                };
            @endphp
            <div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <strong>Código del documento:</strong>
                    {{ $message->code ?? $message->id }}
                </div>
                <div class="flex-grow-1">
                    <div class="urgency-bar {{ $barClass }}">
                        Nivel de urgencia: {{ $urgencyLabel }}
                    </div>
                </div>
            </div>

            <div class="mb-3 d-flex align-items-center gap-3 flex-wrap">
                <div>
                    <strong>Estado:</strong>
                    <span class="badge {{ $message->status_badge_class }}">{{ $message->status_label }}</span>
                </div>
                @if($message->status === 'archivado_por_jefe')
                    <span class="text-warning">Archivado por jefe.</span>
                @endif
                @if($message->status === 'pendiente_aprobacion_jefe' && $message->receiver_id === Auth::id() && Auth::id() === ($message->approver_id))
                    <div class="d-flex gap-2 align-items-center">
                        <button class="btn btn-sm btn-success" id="bossApproveBtn" data-approve-url="{{ route('messages.boss.approve', $message->id) }}">
                            <i class="fas fa-check"></i> Aprobar
                        </button>
                        <button class="btn btn-sm btn-outline-danger" id="bossArchiveBtn" data-archive-url="{{ route('messages.boss.archive', $message->id) }}">
                            <i class="fas fa-box-archive"></i> Archivar
                        </button>
                        <small class="text-muted">Acciones solo para jefe.</small>
                    </div>
                @endif
            </div>

            <div class="mb-3">
                <strong>Mensaje:</strong>
                <div class="border p-3 bg-light rounded mt-1">{!! nl2br(e($message->message)) !!}</div>
            </div>

            @if($message->file_path)
                <div class="mb-3">
                    <strong>Archivo adjunto:</strong><br>
                    <div class="d-flex align-items-center mt-2 p-2 border rounded">
                        <i class="fas fa-paperclip fa-2x text-muted me-3"></i>
                        <div class="flex-grow-1">
                            <div class="fw-bold">{{ $message->file_name }}</div>
                            <small class="text-muted">
                                Tipo: {{ $message->file_type }} |
                                Tamaño: {{ number_format($message->file_size / 1024, 2) }} KB
                            </small>
                        </div>
                        <a href="{{ route('messages.download', $message->id) }}" class="btn btn-success btn-sm">
                            <i class="fas fa-download"></i> Descargar
                        </a>
                    </div>
                </div>
            @endif

            @php
                $logs = $message->logs->sortBy('created_at')->values();

                $hasFinalized = $logs->contains(fn($l) => $l->action === 'finalized');
                $hasArchived  = $logs->contains(fn($l) => $l->action === 'archived');
                $hasCancelled = $logs->contains(fn($l) => $l->action === 'cancelled');
                $isClosed     = $hasArchived || $hasCancelled;

                $segments = [];
                if ($logs->count()) {
                    $firstLog = $logs->first();
                    $sentLog  = $logs->firstWhere('action', 'sent');

                    $holderId = null;
                    if ($sentLog) {
                        $sd = is_string($sentLog->details)
                            ? json_decode($sentLog->details, true)
                            : (is_array($sentLog->details) ? $sentLog->details : []);
                        $holderId = $sd['to'] ?? $message->receiver_id;
                    } else {
                        $holderId = $message->receiver_id;
                    }

                    $start = $firstLog->created_at ?? $message->created_at;

                    foreach ($logs as $lg) {
                        if ($lg->action === 'forwarded') {
                            $segments[] = [
                                'holder_id' => $holderId,
                                'from'      => $start,
                                'to'        => $lg->created_at,
                            ];

                            $d = is_string($lg->details)
                                ? json_decode($lg->details, true)
                                : (is_array($lg->details) ? $lg->details : []);

                            $holderId = $d['new_receiver_id'] ?? $holderId;
                            $start    = $lg->created_at;
                        }
                    }

                    $segments[] = [
                        'holder_id' => $holderId,
                        'from'      => $start,
                        'to'        => ($logs->last()->created_at ?? now()),
                    ];

                    foreach ($segments as &$seg) {
                        $u = \App\Models\User::with('role')->find($seg['holder_id']);
                        $seg['holder'] = $u ? trim(($u->name ?? '').' '.($u->apellidos ?? '')) : '-';
                        $seg['role']   = $u?->role?->role;
                    }
                    unset($seg);
                }
            @endphp

            @if(!empty($segments))
                <h6 class="mt-3 mb-2">Historial de acciones</h6>
                @foreach($segments as $seg)
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <div>
                                <strong>Acciones de {{ $seg['holder'] }}</strong>
                                @if(!empty($seg['role']))
                                    <small class="text-muted">({{ $seg['role'] }})</small>
                                @endif
                            </div>
                            <small class="text-muted">
                                {{ \Carbon\Carbon::parse($seg['from'])->format('d/m/Y H:i') }}
                                -
                                {{ \Carbon\Carbon::parse($seg['to'])->format('d/m/Y H:i') }}
                            </small>
                        </div>
                        <div class="card-body">
                            @php
                                $actions = $logs->filter(function($l) use ($seg) {
                                    return \Carbon\Carbon::parse($l->created_at) >= \Carbon\Carbon::parse($seg['from'])
                                        && \Carbon\Carbon::parse($l->created_at) <= \Carbon\Carbon::parse($seg['to']);
                                });
                            @endphp
                            @if($actions->count())
                                <ul class="list-group list-group-flush">
                                    @foreach($actions as $log)
                                        @php
                                            $d = is_string($log->details)
                                                ? json_decode($log->details, true)
                                                : (is_array($log->details) ? $log->details : []);
                                        @endphp
                                        <li class="list-group-item d-flex justify-content-between align-items-start">
                                            <div>
                                                @switch($log->action)
                                                    @case('sent')
                                                        <strong>Enviado</strong>
                                                        @break
                                                    @case('read')
                                                        <strong>Recibido</strong>
                                                        @break
                                                    @case('downloaded')
                                                        <span class="text-muted">Descargado</span>
                                                        @break
                                                    @case('in_review')
                                                        <strong>En revisión</strong>
                                                        @break
                                                    @case('approved')
                                                        <strong>Aprobado</strong>
                                                        @break
                                                    @case('observed')
                                                        <strong>Observado</strong>
                                                        @break
                                                    @case('finalized')
                                                        <strong>Finalizado</strong>
                                                        @break
                                                    @case('archived')
                                                        <strong>Archivado</strong>
                                                        @break
                                                    @case('cancelled')
                                                        <strong>Anulado</strong>
                                                        @break
                                                    @case('forwarded')
                                                        <strong>Asignado a:</strong>
                                                        {{ $d['new_receiver_name'] ?? '-' }}
                                                        @if(!empty($d['new_receiver_role']))
                                                            <small class="text-muted">({{ $d['new_receiver_role'] }})</small>
                                                        @endif
                                                        @break
                                                    @case('reply')
                                                        <strong>Respuesta</strong>
                                                        @if(!empty($d['to_user_name']))
                                                            a: <span class="fw-semibold">{{ $d['to_user_name'] }}</span>
                                                        @endif
                                                        <span class="text-muted">{{ $d['text'] ?? '' }}</span>
                                                        @if(!empty($d['file']['path'] ?? null))
                                                            <a href="{{ route('messages.reply.download', [$message->id, $log->id]) }}" class="ms-2">
                                                                Descargar adjunto
                                                            </a>
                                                        @endif
                                                        @break
                                                    @case('approved_by_boss')
                                                        <strong>Aprobado por jefe</strong>
                                                        @break
                                                    @case('archived_by_boss')
                                                        <strong>Archivado por jefe</strong>
                                                        @if(!empty($d['note'])) <small class="text-muted">{{ $d['note'] }}</small> @endif
                                                        @break
                                                    @default
                                                        <span class="text-muted">{{ ucfirst($log->action) }}</span>
                                                @endswitch
                                            </div>
                                            <small class="text-muted">{{ $log->created_at->format('d/m/Y H:i') }}</small>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                @endforeach
            @endif

            <div class="mt-3">
                <div id="message-vars" class="d-none"
                     data-id="{{ $message->id }}"
                     data-status="{{ route('messages.status', $message->id) }}"></div>

                @php
                    $participantIds = collect([$message->sender_id, $message->receiver_id]);
                    foreach ($message->logs as $log) {
                        if (!empty($log->user_id)) {
                            $participantIds->push((int) $log->user_id);
                        }
                        $details = is_string($log->details)
                            ? json_decode($log->details, true)
                            : (is_array($log->details) ? $log->details : []);
                        if (is_array($details)) {
                            foreach (['previous_receiver_id', 'new_receiver_id', 'assigned_to_id', 'to_user_id', 'to'] as $key) {
                                if (!empty($details[$key])) {
                                    $participantIds->push((int) $details[$key]);
                                }
                            }
                        }
                    }
                    $participantIds = $participantIds->filter()->unique()->values();
                    $participants = $participantIds->isEmpty()
                        ? collect()
                        : \App\Models\User::whereIn('id', $participantIds)->with('role')->get();
                @endphp
                @if(isset($participants))
                    <div id="message-participants"
                         data-current-user="{{ Auth::id() }}"
                         data-participants='{!! $participants->map(function($u){
                                return [
                                    "id"   => $u->id,
                                    "name" => trim(($u->name ?? "")." ".($u->apellidos ?? "")),
                                    "role" => optional($u->role)->role,
                                ];
                         })->values()->toJson() !!}'
                         class="d-none"></div>
                @endif

                @if(Auth::id() === $message->receiver_id && !$isClosed)
                    <div class="mb-3">
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-secondary dropdown-toggle"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                Actualizar estado
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                @if(!$hasFinalized)
                                    <li><a class="dropdown-item seg-status" href="#" data-action="in_review">En revisión</a></li>
                                    <li><a class="dropdown-item seg-status" href="#" data-action="approved">Aprobado</a></li>
                                    <li><a class="dropdown-item seg-status" href="#" data-action="observed">Observado</a></li>
                                    <li><a class="dropdown-item seg-status" href="#" data-action="finalized">Finalizado</a></li>
                                @endif
                                <li><a class="dropdown-item seg-status" href="#" data-action="archived">Archivado</a></li>
                                <li><a class="dropdown-item seg-status" href="#" data-action="cancelled">Anulado</a></li>
                            </ul>
                        </div>
                    </div>
                @endif

                @if(Auth::id() === $message->receiver_id)
                    <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#forwardModal">
                        <i class="fas fa-share"></i> Reenviar
                    </button>
                @endif

                <button class="btn btn-outline-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#replyBox"
                        aria-expanded="false">
                    <i class="fas fa-reply"></i> Responder / Adjuntar
                </button>

                <div id="replyBox" class="collapse mt-2">
                    <form id="replyForm" method="POST" action="{{ route('messages.reply', $message->id) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="row g-2 mb-2">
                            <div class="col-md-6">
                                <label class="form-label small">Responder a</label>
                                <select name="reply_to_id" id="reply_to_id" class="form-select form-select-sm">
                                    <option value="">Seleccionar destinatario...</option>
                                    @if($message->sender_id && $message->sender_id != Auth::id())
                                        <option value="{{ $message->sender_id }}">
                                            Remitente: {{ optional($message->sender)->name }} {{ optional($message->sender)->apellidos }}
                                        </option>
                                    @endif
                                    @if($message->receiver_id && $message->receiver_id != Auth::id())
                                        <option value="{{ $message->receiver_id }}">
                                            Destinatario: {{ optional($message->receiver)->name }} {{ optional($message->receiver)->apellidos }}
                                        </option>
                                    @endif
                                    @foreach($participants as $u)
                                        @if($u->id != Auth::id() && $u->id != $message->sender_id && $u->id != $message->receiver_id)
                                            <option value="{{ $u->id }}">
                                                {{ $u->name }} {{ $u->apellidos }}
                                                @if(optional($u->role)->role) ({{ optional($u->role)->role }}) @endif
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="mb-2">
                            <textarea name="reply_text" class="form-control" rows="3"
                                      placeholder="Escribe tu respuesta o indicaciones"></textarea>
                        </div>
                        <div class="mb-2">
                            <label for="reply_file" class="form-label small">Adjuntar archivo</label>
                            <input type="file" id="reply_file" name="reply_file"
                                   class="form-control form-control-sm">
                            <small class="text-muted">PDF, JPG, PNG, DOC, DOCX, TXT - Máx 10MB</small>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-paperclip"></i> Enviar respuesta
                        </button>
                    </form>
                </div>

                <!-- Modal Reenviar -->
                <div class="modal fade" id="forwardModal" tabindex="-1" aria-labelledby="forwardModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header bg-warning">
                                <h5 class="modal-title" id="forwardModalLabel">Reenviar / Asignar documento</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                            </div>
                            <form id="forwardForm" method="POST" action="{{ route('messages.forward', $message->id) }}">
                                @csrf
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label">Nuevo destinatario *</label>
                                        <select class="form-control" name="new_receiver_id" required>
                                            <option value="">Seleccionar...</option>
                                            @foreach($participants as $user)
                                                @if($user->id !== Auth::id())
                                                    <option value="{{ $user->id }}">
                                                        {{ $user->name }} {{ $user->apellidos }}
                                                        - {{ optional($user->role)->role ?? 'Usuario' }}
                                                    </option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Nota (opcional)</label>
                                        <textarea class="form-control" name="forward_note" rows="3"
                                                  placeholder="Agrega una nota si lo deseas..."></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-warning">Reenviar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const holder = document.getElementById('message-participants');
    const select = document.getElementById('reply_to_id');
    if (holder && select) {
        try {
            const raw = holder.getAttribute('data-participants') || '[]';
            const currentId = parseInt(holder.getAttribute('data-current-user') || '0', 10);
            const list = JSON.parse(raw);
            while (select.firstChild) { select.removeChild(select.firstChild); }
            const optEmpty = document.createElement('option');
            optEmpty.value = '';
            optEmpty.textContent = 'Seleccionar destinatario...';
            select.appendChild(optEmpty);
            if (Array.isArray(list) && list.length) {
                list.forEach(function (p) {
                    if (!p || !p.id || p.id === currentId) return;
                    const opt = document.createElement('option');
                    opt.value = String(p.id);
                    const nombre = (p.name || '').trim();
                    const rol = (p.role || '').trim();
                    opt.textContent = nombre + (rol ? ' (' + rol + ')' : '');
                    select.appendChild(opt);
                });
            } else {
                const opt = document.createElement('option');
                opt.value = '';
                opt.disabled = true;
                opt.textContent = 'No hay participantes disponibles';
                select.appendChild(opt);
            }
        } catch (e) {
            console.error('No se pudo poblar el combo de respuesta', e);
        }
    }

    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const approveBtn = document.getElementById('bossApproveBtn');
    const archiveBtn = document.getElementById('bossArchiveBtn');

    function postJson(url, data) {
        return fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json', 'Content-Type': 'application/json' },
            body: JSON.stringify(data || {})
        }).then(r => r.json());
    }

    if (approveBtn) {
        approveBtn.addEventListener('click', function() {
            const url = this.getAttribute('data-approve-url');
            if (!url) return;
            postJson(url, {}).then(res => {
                if (res && res.success) { location.reload(); }
                else { alert((res && res.message) || 'No se pudo aprobar'); }
            }).catch(() => alert('Error de red'));
        });
    }

    if (archiveBtn) {
        archiveBtn.addEventListener('click', function() {
            const url = this.getAttribute('data-archive-url');
            if (!url) return;
            const note = prompt('Escribe la observación para archivar:');
            if (note === null || note.trim() === '') return;
            postJson(url, { note: note.trim() }).then(res => {
                if (res && res.success) { location.reload(); }
                else { alert((res && res.message) || 'No se pudo archivar'); }
            }).catch(() => alert('Error de red'));
        });
    }
});
</script>

<style>
.urgency-bar {
    border-radius: 4px;
    padding: 4px 10px;
    font-size: 0.8rem;
    font-weight: 600;
    color: #fff;
    text-align: right;
}
.urgency-bar-normal { background-color: #6c757d; }
.urgency-bar-high   { background-color: #fd7e14; }
.urgency-bar-critical { background-color: #dc3545; }
</style>
