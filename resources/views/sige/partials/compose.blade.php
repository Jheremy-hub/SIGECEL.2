<!-- resources/views/sige/partials/compose.blade.php -->
<form id="composeForm" onsubmit="sendMessage(event)">
    @csrf
    <input type="hidden" id="receiver_id" name="receiver_id" required>

    <div id="composeErrors" class="alert alert-danger d-none" role="alert"></div>

    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label fw-bold small">Área</label>
            <select class="form-select form-select-sm mb-2" id="areaSelect">
                <option value="">Seleccionar área...</option>
                @php
                    $areas = isset($roleOptions) && $roleOptions->count()
                        ? $roleOptions
                        : collect($users)
                            ->map(function ($u) { return optional($u->role)->role; })
                            ->filter(function ($area) {
                                return $area && !in_array($area, ['Sin área', 'Usuario de Prueba'], true);
                            })
                            ->unique()
                            ->sort()
                            ->values();
                @endphp
                @foreach($areas as $area)
                    <option value="{{ $area }}">{{ $area }}</option>
                @endforeach
            </select>

            <label class="form-label fw-bold small">Personas del área (Destinatario) *</label>
            <input type="text" class="form-control form-control-sm mb-1" id="userSearch" placeholder="Buscar...">
            <select class="form-control form-control-sm" id="userSelect" size="6">
                <option value="">Seleccionar...</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}"
                            data-name="{{ $user->name }} {{ $user->apellidos }}"
                            data-email="{{ $user->email }}"
                            data-cargo="{{ $user->cargo ?? 'Sin cargo' }}"
                            data-area="{{ optional($user->role)->role ?? 'Sin área' }}">
                        {{ $user->name }} {{ $user->apellidos }} - {{ $user->email }}
                    </option>
                @endforeach
            </select>

            <div id="selectedUserInfo" class="mt-1 p-2 bg-light rounded d-none">
                <small>
                    <strong id="selectedUserName"></strong><br>
                    <span id="selectedUserEmail" class="text-muted"></span><br>
                    <span id="selectedUserCargo" class="text-muted"></span>
                </small>
            </div>
        </div>

        <div class="col-md-6">
            <div class="mb-2">
                <label for="subject" class="form-label fw-bold small">Asunto *</label>
                @php
                    $rolActual = optional(Auth::user()->role)->role;
                    $usaAsuntosPredefinidos = in_array($rolActual, ['Secretaría', 'Recepción']);
                @endphp
                @php
                    $rolActual = $rolActual ?? '';
                    $usaAsuntosPredefinidos = \Illuminate\Support\Str::contains($rolActual, [
                        'Secretaria', 'Secretaría', 'Recepcion', 'Recepción'
                    ]);
                @endphp
                @if($usaAsuntosPredefinidos)
                    <select class="form-control form-control-sm" id="subject" name="subject" required>
                        <option value="">Seleccionar asunto...</option>
                        <option value="Fiscalización de Diploma">Fiscalización de Diploma</option>
                        <option value="Diplomas Colegiados">Diplomas Colegiados</option>
                        <option value="Cartas Notariales">Cartas Notariales</option>
                        <option value="Invitaciones a eventos">Invitaciones a eventos</option>
                    </select>
                @else
                    <input type="text"
                           class="form-control form-control-sm"
                           id="subject"
                           name="subject"
                           placeholder="Escribe el asunto..."
                           required>
                @endif
            </div>

            <div class="mb-2">
                <label for="urgency" class="form-label fw-bold small">Nivel de urgencia *</label>
                <select class="form-control form-control-sm" id="urgency" name="urgency" required>
                    <option value="normal" selected>Normal</option>
                    <option value="alta">Alta</option>
                    <option value="critica">Muy urgente</option>
                </select>
            </div>
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label fw-bold small">Descripción</label>
        <textarea class="form-control form-control-sm" id="message" name="message" rows="5" required></textarea>
    </div>

    <div class="mb-3">
        <label class="form-label fw-bold small"><i class="fas fa-paperclip"></i> Archivo adjunto</label>
        <input type="file" class="form-control form-control-sm" id="file" name="file">
        <div class="form-text small">PDF, JPG, PNG, DOC, TXT - Máx 10MB</div>
    </div>

    <div class="d-flex justify-content-between align-items-center border-top pt-2">
        <button type="submit" class="btn btn-sm btn-primary text-dark">
            <i class="fas fa-paper-plane me-1"></i> Enviar
        </button>
        <small class="text-muted">* Campos obligatorios</small>
    </div>
</form>

<script>
function filterUsers() {
    const area = (document.getElementById('areaSelect')?.value || '').toLowerCase();
    const searchTerm = (document.getElementById('userSearch')?.value || '').toLowerCase();
    const select = document.getElementById('userSelect');
    if (!select) return;
    const options = select.getElementsByTagName('option');
    for (let i = 1; i < options.length; i++) {
        const option = options[i];
        const name = (option.getAttribute('data-name') || '').toLowerCase();
        const email = (option.getAttribute('data-email') || '').toLowerCase();
        const cargo = (option.getAttribute('data-cargo') || '').toLowerCase();
        const optArea = (option.getAttribute('data-area') || '').toLowerCase();
        const inArea = !area || optArea === area;
        const matches = !searchTerm || name.includes(searchTerm) || email.includes(searchTerm) || cargo.includes(searchTerm);
        option.style.display = (inArea && matches) ? '' : 'none';
    }
}

function updateSelectedUserInfo(){
    const select = document.getElementById("userSelect");
    const hidden = document.getElementById("receiver_id");
    const opt = select && select.options[select.selectedIndex];
    hidden.value = (opt && opt.value) ? opt.value : "";

    const infoBox = document.getElementById('selectedUserInfo');
    const nameEl  = document.getElementById('selectedUserName');
    const emailEl = document.getElementById('selectedUserEmail');
    const cargoEl = document.getElementById('selectedUserCargo');

    if (opt && opt.value) {
        nameEl.textContent  = opt.getAttribute('data-name') || '';
        emailEl.textContent = opt.getAttribute('data-email') || '';
        cargoEl.textContent = opt.getAttribute('data-cargo') || '';
        infoBox.classList.remove('d-none');
    } else if (infoBox) {
        infoBox.classList.add('d-none');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const userSelect = document.getElementById('userSelect');
    const userSearch = document.getElementById('userSearch');
    const areaSelect = document.getElementById('areaSelect');

    if (userSelect) {
        userSelect.addEventListener('change', updateSelectedUserInfo);
    }
    if (userSearch) {
        userSearch.addEventListener('input', filterUsers);
    }
    if (areaSelect) {
        areaSelect.addEventListener('change', function(){
            if (userSelect) userSelect.value = '';
            const hidden = document.getElementById('receiver_id');
            if (hidden) hidden.value = '';
            const infoBox = document.getElementById('selectedUserInfo');
            if (infoBox) infoBox.classList.add('d-none');
            filterUsers();
        });
    }

    filterUsers();
});

function resetForm() {
    const form = document.getElementById('composeForm');
    if (form && typeof form.reset === 'function') {
        try { form.reset(); } catch(e) {}
    }

    const receiver = document.getElementById('receiver_id');
    if (receiver) receiver.value = '';

    const selectedInfo = document.getElementById('selectedUserInfo');
    if (selectedInfo && selectedInfo.classList) selectedInfo.classList.add('d-none');

    const userSearchEl = document.getElementById('userSearch');
    if (userSearchEl) userSearchEl.value = '';

    const userSelectEl = document.getElementById('userSelect');
    if (userSelectEl) {
        const options = userSelectEl.getElementsByTagName('option');
        for (let i = 0; i < options.length; i++) {
            options[i].style.display = '';
        }
    }
}
</script>

<style>
#userSelect {
    max-height: 180px;
    overflow-y: auto;
    border: 1px solid #ced4da;
    border-radius: 0.375rem;
    font-size: 0.85rem;
}
#userSelect option {
    padding: 6px 8px;
    border-bottom: 1px solid #f0f0f0;
    font-size: 0.85rem;
}
#userSelect option:hover {
    background-color: #e9ecef;
}
#selectedUserInfo {
    border-left: 3px solid #007bff;
    background: #f8f9fa;
    font-size: 0.8rem;
}
</style>
