@extends('layouts.app')

@section('title', 'SIGE - Mensajería')
@section('page-title', 'SIGECEL-Documentación')

@section('content')
@php
    $rolActual = optional($user->role)->role ?? '';
    $esRecepcionOSecretaria = \Illuminate\Support\Str::contains($rolActual, [
        'Secretaria', 'Secretaría', 'Recepcion', 'Recepción'
    ]);
    $rolNormalizado = \Illuminate\Support\Str::ascii(\Illuminate\Support\Str::lower($rolActual));
    $puedeGestionarRoles = in_array($rolNormalizado, ['tecnologias de informacion', 'tecnologia de informacion'], true);
@endphp
<!-- Estadísticas Rápidas -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <h4 class="mb-1">{{ $inboxMessages->count() }}</h4>
                <p class="text-muted mb-0 small">Documentos Recibidos</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <h4 class="mb-1">{{ $sentMessages->count() }}</h4>
                <p class="text-muted mb-0 small">Documentos Enviados</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <h4 class="mb-1">{{ $inboxMessages->where('is_read', 0)->count() }}</h4>
                <p class="text-muted mb-0 small">No Leidos</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <h4 class="mb-1">{{ $user->documents->count() }}</h4>
                <p class="text-muted mb-0 small">Mis Documentos</p>
            </div>
        </div>
    </div>
</div>

    <!-- Panel de Navegación -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <ul class="nav nav-pills" id="sigeTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="inbox-tab" data-bs-toggle="tab" data-bs-target="#inbox" type="button" role="tab">
                        <i class="fas fa-inbox me-2"></i>Bandeja de Documentos
                        @if($inboxMessages->where('is_read', 0)->count() > 0)
                            <span class="badge bg-danger ms-1" id="unread-badge">{{ $inboxMessages->where('is_read', 0)->count() }}</span>
                        @endif
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="sent-tab" data-bs-toggle="tab" data-bs-target="#sent" type="button" role="tab">
                        <i class="fas fa-paper-plane me-2"></i>Documentos Enviados
                    </button>
                </li>
                <!-- ✅ PESTAÑA MIS DOCUMENTOS - ACTIVADA Y FUNCIONAL -->
                <li class="nav-item" role="presentation">
                    <a class="nav-link" href="{{ route('documents.index') }}" type="button" role="tab">
                        <i class="fas fa-folder-open me-2"></i>Mis Documentos
                    </a>
                </li>
            </ul>
            <div class="d-flex align-items-center gap-2">
                @if($puedeGestionarRoles)
                    <a class="btn btn-outline-secondary" href="{{ route('users.roles') }}">
                        <i class="fas fa-user-shield me-2"></i>Gestionar roles
                    </a>
                    <a class="btn btn-outline-secondary" href="{{ route('users.roles.hierarchy') }}">
                        <i class="fas fa-sitemap me-2"></i>Jerarquia por rol
                    </a>
                @endif
                @if(isset($pendingApprovals) && $pendingApprovals->count() > 0)
                    <button class="btn btn-warning text-dark" id="bossPendingBtn" data-first-pending="{{ $pendingApprovals->first()->id }}">
                        <i class="fas fa-gavel me-2"></i>Pendientes de aprobación ({{ $pendingApprovals->count() }})
                    </button>
                @endif
                <!-- Botón Nuevo Mensaje -->
                <button class="btn btn-primary" id="newMessageBtn">
                    <i class="fas fa-edit me-2"></i>Nuevo Documento
                </button>
            </div>
        </div>
    </div>
</div>
    <!-- Contenido de las Pestañas -->
    <div class="tab-content" id="sigeTabsContent">
        <!-- Bandeja de Entrada -->
        <div class="tab-pane fade show active" id="inbox" role="tabpanel" aria-labelledby="inbox-tab">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-inbox text-primary me-2"></i>Documentos Recibidos
                        </h5>
                        <button class="btn btn-sm btn-outline-primary" onclick="SigeSystem.refreshInbox()">
                            <i class="fas fa-sync-alt me-1"></i>Actualizar
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div id="inbox-content">
                        @include('sige.partials.inbox')
                    </div>
                </div>
            </div>
        </div>
        

        <!-- Mensajes Enviados -->
        <div class="tab-pane fade" id="sent" role="tabpanel" aria-labelledby="sent-tab">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-paper-plane text-success me-2"></i>Documentos Enviados
                        </h5>
                        <button class="btn btn-sm btn-outline-success" onclick="SigeSystem.refreshSent()">
                            <i class="fas fa-sync-alt me-1"></i>Actualizar
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div id="sent-content">
                        @include('sige.partials.sent')
                    </div>
                </div>
            </div>
        </div>

    </div>
    
</div>


<!-- Modal para Ver Mensaje -->
<div class="modal fade" id="messageModal" tabindex="-1" role="dialog" aria-labelledby="messageModalLabel" aria-modal="true" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="messageModalLabel">
                    <i class="fas fa-envelope me-2"></i>Detalles del Documento
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" id="messageModalBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando documento...</span>
                    </div>
                    <p class="mt-2">Cargando documento...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Redactar Nuevo Mensaje -->
<div class="modal fade" id="composeModal" tabindex="-1" role="dialog" aria-labelledby="composeModalLabel" aria-modal="true" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="composeModalLabel">
                    <i class="fas fa-edit me-2"></i>Redactar Nuevo Documento
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" id="composeModalBody">
                <!-- El contenido del formulario se cargará aquí dinámicamente -->
                <div class="text-center py-4">
                    <div class="spinner-border text-info" role="status">
                        <span class="visually-hidden">Cargando formulario...</span>
                    </div>
                    <p class="mt-2">Cargando formulario...</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
if (csrfToken && window.$) {
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': csrfToken } });
}
const SigeSystem = {
    init() {
        $('#newMessageBtn').on('click', () => this.openComposeModal());
        $('#inbox-tab').on('click', () => this.refreshInbox());
        $('#sent-tab').on('click', () => this.refreshSent());
        this.loadComposeForm();
    },
    loadComposeForm() {
        $('#composeModalBody').html(`<div class="text-center py-4"><div class="spinner-border text-info" role="status"><span class="visually-hidden">Cargando formulario...</span></div><p class="mt-2">Cargando formulario...</p></div>`);
        $.get('{{ route("sige.compose.form") }}')
            .done((html) => { $('#composeModalBody').html(html); this.initComposeForm(); })
            .fail(() => this.showAlert('Error al cargar el formulario', 'error'));
    },
    initComposeForm() {
        const userSelect = document.getElementById('userSelect');
        const userSearch = document.getElementById('userSearch');
        const areaSelect = document.getElementById('areaSelect');
        const hidden = document.getElementById('receiver_id');
        const infoBox = document.getElementById('selectedUserInfo');
        const nameEl  = document.getElementById('selectedUserName');
        const emailEl = document.getElementById('selectedUserEmail');
        const cargoEl = document.getElementById('selectedUserCargo');

        const applyFilter = () => {
            const area = (areaSelect?.value || '').toLowerCase();
            const search = (userSearch?.value || '').toLowerCase();
            if (!userSelect) return;
            const opts = userSelect.getElementsByTagName('option');
            for (let i = 1; i < opts.length; i++) {
                const opt = opts[i];
                const name = (opt.getAttribute('data-name') || '').toLowerCase();
                const email = (opt.getAttribute('data-email') || '').toLowerCase();
                const cargo = (opt.getAttribute('data-cargo') || '').toLowerCase();
                const optArea = (opt.getAttribute('data-area') || '').toLowerCase();
                const inArea = !area || optArea === area;
                const match = !search || name.includes(search) || email.includes(search) || cargo.includes(search);
                opt.style.display = (inArea && match) ? '' : 'none';
            }
        };

        const updateSelected = () => {
            const opt = userSelect?.options[userSelect.selectedIndex];
            const val = opt && opt.value ? opt.value : '';
            if (hidden) hidden.value = val;
            if (val && infoBox && nameEl && emailEl && cargoEl) {
                nameEl.textContent  = opt.getAttribute('data-name') || '';
                emailEl.textContent = opt.getAttribute('data-email') || '';
                cargoEl.textContent = opt.getAttribute('data-cargo') || '';
                infoBox.classList.remove('d-none');
            } else if (infoBox) {
                infoBox.classList.add('d-none');
            }
        };

        userSelect?.addEventListener('change', updateSelected);
        userSearch?.addEventListener('input', applyFilter);
        areaSelect?.addEventListener('change', () => {
            if (userSelect) userSelect.value = '';
            if (hidden) hidden.value = '';
            infoBox?.classList.add('d-none');
            applyFilter();
        });
        applyFilter();
    },
    openComposeModal() {
        this.loadComposeForm();
        $('#composeModal').modal('show');
    },
    refreshInbox() {
        $('#inbox-content').html(`<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando...</span></div><p class="mt-2">Actualizando mensajes...</p></div>`);
        $.get('{{ route("sige.index") }}')
            .done((data) => { $('#inbox-content').html($(data).find('#inbox-content').html()); })
            .fail(() => this.showAlert('Error al actualizar la bandeja de entrada', 'error'));
    },
    refreshSent() {
        $('#sent-content').html(`<div class="text-center py-4"><div class="spinner-border text-success" role="status"><span class="visually-hidden">Cargando...</span></div><p class="mt-2">Actualizando mensajes...</p></div>`);
        $.get('{{ route("sige.index") }}')
            .done((data) => { $('#sent-content').html($(data).find('#sent-content').html()); })
            .fail(() => this.showAlert('Error al actualizar mensajes enviados', 'error'));
    },
    loadMessage(id) {
        if (!id) { this.showAlert('ID de mensaje no válido', 'error'); return; }
        $('#messageModalBody').html(`<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando mensaje...</span></div><p class="mt-2">Cargando mensaje...</p></div>`);
        $.get(`/messages/${id}`)
            .done((data) => { $('#messageModalBody').html(data); this.markAsRead(id); })
            .fail((xhr) => {
                let msg = 'Error al cargar el mensaje';
                if (xhr.status === 403) msg = 'No tienes permiso para ver este mensaje.';
                else if (xhr.status === 404) msg = 'El mensaje no fue encontrado.';
                $('#messageModalBody').html(`<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> ${msg}</div>`);
            });
    },
    markAsRead(id) {
        if (!id) return;
        if ($('#sigeTabs .nav-link.active').attr('id') !== 'inbox-tab') return;
        $.post(`/messages/${id}/read`).done((res) => {
            if (res.success) { this.updateMessageRow(id); this.updateUnreadCount(); }
        });
    },
    updateMessageRow(id) {
        const row = $(`tr[data-message-id="${id}"]`);
        if (row.length) {
            row.removeClass('unread-row');
            row.find('.unread-indicator').remove();
            row.find('.badge-warning').replaceWith('<span class="badge bg-success">Leído</span>');
        }
    },
    updateUnreadCount() {
        const unread = $('.unread-row').length;
        const badge = $('#unread-badge');
        if (unread > 0) {
            if (badge.length) badge.text(unread);
            else $('#inbox-tab').append(`<span class="badge bg-danger ms-1" id="unread-badge">${unread}</span>`);
        } else { badge.remove(); }
    },
    sendMessage(event) {
        event.preventDefault();
        const form = event.target;
        const errorsContainer = document.getElementById('composeErrors');
        const receiver = form.querySelector('[name="receiver_id"]').value;
        const subject = form.querySelector('[name="subject"]').value.trim();
        const message = form.querySelector('[name="message"]').value.trim();
        const fileEl = form.querySelector('[name="file"]');
        const errors = [];
        if (!receiver) errors.push('Selecciona un destinatario.');
        if (!subject) errors.push('Completa el asunto.');
        if (!message) errors.push('Escribe el mensaje.');
        if (fileEl && fileEl.files[0] && fileEl.files[0].size > 10 * 1024 * 1024) errors.push('El archivo debe ser menor a 10MB.');
        if (errors.length) {
            const msg = errors.join('<br>');
            if (errorsContainer) { errorsContainer.innerHTML = msg; errorsContainer.classList.remove('d-none'); }
            this.showAlert(msg, 'error'); return;
        }
        if (errorsContainer) { errorsContainer.innerHTML = ''; errorsContainer.classList.add('d-none'); }
        const formData = new FormData(form);
        $('#composeModalBody').html(`<div class="text-center py-4"><div class="spinner-border text-info" role="status"><span class="visually-hidden">Enviando mensaje...</span></div><p class="mt-2">Enviando mensaje...</p></div>`);
        $.ajax({
            url: '{{ route("messages.store") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: () => { this.showAlert('Mensaje enviado correctamente', 'success'); this.refreshInbox(); this.refreshSent(); setTimeout(() => $('#composeModal').modal('hide'), 800); },
            error: () => { this.showAlert('Error al enviar el mensaje', 'error'); setTimeout(() => this.loadComposeForm(), 800); }
        });
    },
    showAlert(message, type = 'info') {
        const cls = type === 'error' ? 'danger' : type;
        const icon = type === 'success' ? 'check' : (type === 'error' ? 'exclamation-triangle' : 'info');
        const alert = $(`<div class="alert alert-${cls} alert-dismissible fade show" role="alert"><i class="fas fa-${icon}-circle"></i> ${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`);
        $('.page-content').prepend(alert); setTimeout(() => alert.alert('close'), 4000);
    },
    switchToCompose() { this.openComposeModal(); },
    openMessageModal(id) {
        if (!id) return;
        this.loadMessage(id);
        $('#messageModal').modal({ backdrop: true, keyboard: true, focus: true, show: true });
    },
};

$(document)
    .on('click', '#inbox .message-row', function() { const id = $(this).data('message-id'); SigeSystem.openMessageModal(id); })
    .on('click', '#inbox .view-btn', function(e) { e.stopPropagation(); const id = $(this).data('message-id'); SigeSystem.openMessageModal(id); })
    .on('click', '#sent .btn-outline-primary[data-message-id]', function(e) { e.preventDefault(); const id = $(this).data('message-id'); SigeSystem.openMessageModal(id); });

$(document).ready(() => {
    SigeSystem.init();
    const bossBtn = $('#bossPendingBtn');
    if (bossBtn.length) {
        bossBtn.on('click', function() {
            const id = $(this).data('first-pending');
            if (id) {
                SigeSystem.openMessageModal(id);
                setTimeout(() => {
                    const modalVisible = $('#messageModal').hasClass('show');
                    if (!modalVisible) {
                        window.location.href = `/messages/${id}`;
                    }
                }, 600);
            }
        });
    }
});
window.switchToCompose = () => SigeSystem.switchToCompose();
window.sendMessage = (event) => SigeSystem.sendMessage(event);

$(document).on('submit', '#messageModalBody #forwardForm', function (e) {
    e.preventDefault(); const $f = $(this);
    $.post($f.attr('action'), $f.serialize())
        .done(function (res) {
            if (res && res.success) {
                $('#forwardModal').modal('hide');
                const id = $('#messageModalBody #message-vars').data('id');
                if (id) SigeSystem.openMessageModal(id);
            } else { alert((res && res.message) || 'Error al reenviar'); }
        })
        .fail(function () { alert('Error al reenviar'); });
});

$(document).on('submit', '#messageModalBody #replyForm', function (e) {
    e.preventDefault();
    const form = this; const action = form.getAttribute('action'); const fd = new FormData(form);
    fetch(action, { method: 'POST', headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }, body: fd })
        .then(r => r.json()).then(function (res) {
            if (res && res.success) {
                const id = $('#messageModalBody #message-vars').data('id');
                if (id) SigeSystem.openMessageModal(id); else location.reload();
            } else { alert((res && res.message) || 'Error al responder'); }
        }).catch(function () { alert('Error de red'); });
});

$(document).on('click', '#messageModalBody .seg-status', function (e) {
    e.preventDefault();
    const action = $(this).data('action');
    const statusUrl = $('#messageModalBody #message-vars').data('status');
    if (!statusUrl) return;
    $.post(statusUrl, { action })
        .done(function (res) {
            if (res && res.success) {
                const id = $('#messageModalBody #message-vars').data('id');
                if (id) SigeSystem.openMessageModal(id);
            } else { alert((res && res.message) || 'No se pudo actualizar'); }
        })
        .fail(function () { alert('Error de red'); });
});
</script>

<style>
    #sigeTabs .nav-link,
    #sigeTabs .nav-link i {
        color: #000000 !important;
    }

    #newMessageBtn,
    #newMessageBtn i {
        color: #000000 !important;
    }
</style>
@endsection





