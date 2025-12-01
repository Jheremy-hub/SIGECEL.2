@extends('layouts.app')

@section('title', 'SIGE - Detalle del documento')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Detalle del documento</h4>
    <a href="{{ route('sige.index') }}" class="btn btn-outline-secondary btn-sm" title="Volver a SIGE">
        <i class="fas fa-arrow-left"></i> Volver a SIGE
    </a>
    </div>

    @include('messages.show')
@endsection

@section('scripts')
<script>
(function(){
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    function post(url, body){
        return fetch(url, { method:'POST', headers:{ 'X-CSRF-TOKEN': token, 'Accept':'application/json' }, body });
    }

    // Reply form (AJAX)
    const replyForm = document.getElementById('replyForm');
    if (replyForm) {
        replyForm.addEventListener('submit', function(e){
            e.preventDefault();
            const action = replyForm.getAttribute('action');
            const fd = new FormData(replyForm);
            post(action, fd).then(r=>r.json()).then(res=>{
                if (res && res.success) { location.reload(); }
                else { alert((res && res.message) || 'Error al responder'); }
            }).catch(()=>alert('Error de red'));
        });
    }

    // Forward form (AJAX in modal)
    const forwardForm = document.getElementById('forwardForm');
    if (forwardForm) {
        forwardForm.addEventListener('submit', function(e){
            e.preventDefault();
            const action = forwardForm.getAttribute('action');
            const data = new URLSearchParams(new FormData(forwardForm));
            post(action, data).then(r=>r.json()).then(res=>{
                if (res && res.success) {
                    const modalEl = document.getElementById('forwardModal');
                    if (modalEl) { const m = bootstrap.Modal.getOrCreateInstance(modalEl); m.hide(); }
                    location.reload();
                } else { alert((res && res.message) || 'Error al reenviar'); }
            }).catch(()=>alert('Error de red'));
        });
    }

    // Update status (AJAX)
    document.querySelectorAll('.seg-status').forEach(a => {
        a.addEventListener('click', function(e){
            e.preventDefault();
            const action = this.getAttribute('data-action');
            const statusUrl = document.getElementById('message-vars')?.dataset.status;
            if (!statusUrl || !action) return;
            const body = new URLSearchParams({ action });
            post(statusUrl, body).then(r=>r.json()).then(res=>{
                if (res && res.success) { location.reload(); }
                else { alert((res && res.message) || 'No se pudo actualizar'); }
            }).catch(()=>alert('Error de red'));
        });
    });

    // Ya no hay flujo de asignación independiente; solo se usa Reenviar.
})();
</script>
@endsection
