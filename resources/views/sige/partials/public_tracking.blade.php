@php
    $code = $code ?? '';
    $error = $error ?? null;
@endphp

<div class="sige-public-tracking px-3 py-3">
    <div class="d-flex align-items-center mb-3">
        <div class="me-3 d-flex align-items-center justify-content-center track-hero-icon">
            <i class="fas fa-search fa-lg text-primary"></i>
        </div>
        <div>
            <h4 class="mb-0">SIGECEL</h4>
            <small class="text-muted">Consulta el estado de tu documento</small>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form method="GET" action="{{ route('tracking.public') }}" class="row g-3 align-items-end">
                <div class="col-lg-9 col-md-8">
                    <label for="code" class="form-label">Código del documento</label>
                    <input type="text"
                           class="form-control"
                           id="code"
                           name="code"
                           value="{{ old('code', $code) }}"
                           placeholder="Ejemplo: 00025-25 o 25">
                    <small class="text-muted">Ingresa el código que aparece en tu documento para ver en qué área se encuentra.</small>
                </div>
                <div class="col-lg-3 col-md-4 d-grid">
                    <button type="submit" class="btn btn-primary mt-lg-4 mt-md-4 mt-2">
                        <i class="fas fa-search me-1"></i> Buscar
                    </button>
                </div>
            </form>
            @if($error)
                <div class="alert alert-danger mt-3 mb-0">{{ $error }}</div>
            @endif
        </div>
    </div>
</div>

<style>
.sige-public-tracking {
    max-width: 1100px;
    margin: 0 auto;
}
.track-hero-icon {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    background: #e7f1ff;
}
</style>
