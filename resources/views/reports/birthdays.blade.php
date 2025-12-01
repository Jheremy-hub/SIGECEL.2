@extends('layouts.app')

@section('title', 'Reporte de Cumpleaños')
@section('page-title', 'Reporte de Cumpleaños')

@section('content')
<div class="container-fluid" id="reporteCumpleanosWrapper"
    data-month="{{ (int) $month }}"
    data-bg-url="{{ asset($currentBackground ?? 'Backend/Style/Ima.Cumple.jpg') }}"
    data-greeting-title="{{ $greetingTitle }}"
    data-greeting-message="{{ $greetingMessage }}">
    <!-- Encabezado y estadísticas -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header text-white py-3" style="background-color: #1e88e5;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">
                        <i class="fas fa-birthday-cake me-2"></i>
                        Reporte de Cumpleaños
                    </h5>
                    <small>
                        Cumpleaños de colegiados - {{ $monthName }} {{ now()->year }}
                    </small>
                </div>
            </div>
        </div>

        <div class="card-body p-4">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body d-flex align-items-center p-3">
                            <div class="me-3 bg-primary bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-birthday-cake text-primary fs-3"></i>
                            </div>
                            <div>
                                <h3 class="mb-0 fw-bold text-primary">{{ $birthdaysToday }}</h3>
                                <small class="text-muted">Cumpleaños hoy</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body d-flex align-items-center p-3">
                            <div class="me-3 bg-warning bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-calendar-week text-warning fs-3"></i>
                            </div>
                            <div>
                                <h3 class="mb-0 fw-bold text-warning">{{ $birthdaysNextWeek }}</h3>
                                <small class="text-muted">Próxima semana</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body d-flex align-items-center p-3">
                            <div class="me-3 bg-info bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-calendar-alt text-info fs-3"></i>
                            </div>
                            <div>
                                <h3 class="mb-0 fw-bold text-info">{{ $totalBirthdays }}</h3>
                                <small class="text-muted">Total del mes</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista y filtros -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center p-3">
            <h5 class="mb-0">
                <i class="fas fa-list me-2 text-primary"></i>
                Lista de cumpleaños - {{ $monthName }}
            </h5>
            <div class="badge bg-primary text-white rounded-pill px-3 py-2" id="totalRegistros">
                <i class="fas fa-user-friends me-1"></i> {{ $totalBirthdays }} registros
            </div>
        </div>

        <div class="card-body p-0">
            <!-- Filtros -->
            <div class="p-3 border-bottom bg-light">
                <div class="row g-2 align-items-end">
                    <div class="col-md-2">
                        <label for="monthFilter" class="form-label mb-1">Mes</label>
                        <select id="monthFilter" class="form-select form-select-sm">
                            @php
                            $monthNames = [
                            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
                            ];
                            @endphp
                            @foreach($monthNames as $num => $name)
                            <option value="{{ $num }}" @selected($month===$num)>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="filtroDia" class="form-label mb-1">Día</label>
                        <select id="filtroDia" class="form-select form-select-sm">
                            <option value="">Todos</option>
                            @for ($i = 1; $i <= 31; $i++)
                                <option value="{{ $i }}" {{ isset($todayDay) && $todayDay === $i ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="filtroCorreo" class="form-label mb-1">Correo</label>
                        <select id="filtroCorreo" class="form-select form-select-sm">
                            <option value="">Todos</option>
                            <option value="si" selected>Con correo</option>
                            <option value="no">Sin correo</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="buscarNombre" class="form-label mb-1">Buscar</label>
                        <input type="text" id="buscarNombre" class="form-control form-control-sm" placeholder="Nombre o N° colegiado">
                    </div>
                    <div class="col-md-2 d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-sm btn-primary" id="btnAplicarFiltros">
                            <i class="fas fa-filter me-1"></i>Aplicar
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnLimpiarFiltros">
                            Limpiar
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-success" id="btnExportarCSV">
                            <i class="fas fa-file-csv me-1"></i>CSV
                        </button>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-12 d-flex justify-content-end gap-2 flex-wrap">
                        <input type="file" id="inputImagenSaludo" accept="image/*" class="d-none" title="Seleccionar imagen de saludo">
                        <button type="button" class="btn btn-sm btn-secondary" id="btnSubirImagen">
                            <i class="fas fa-upload me-1"></i> Actualizar imagen
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnVistaPreviaImagen">
                            <i class="fas fa-eye me-1"></i> Vista previa
                        </button>

                        <button type="button" class="btn btn-sm btn-success" id="btnSaludarTodos" style="display:none;">
                            <i class="fas fa-users me-1"></i> Saludar a todos
                        </button>
                        <button type="button" class="btn btn-sm btn-info" id="btnVerReporteSaludos">
                            <i class="fas fa-history me-1"></i> Ver reporte de saludos
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tabla -->
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tablaCumpleanos">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 140px">N° Colegiado</th>
                            <th>Nombre completo</th>
                            <th style="width: 260px">Correo</th>
                            <th style="width: 90px">Edad</th>
                            <th style="width: 140px">Estado</th>
                            <th style="width: 130px">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyCumpleanos">
                        @forelse($rows as $index => $r)
                        <tr class="{{ $r['es_hoy'] ? 'table-warning' : '' }} {{ $r['dias_restantes'] <= 7 && !$r['es_hoy'] ? 'bg-light' : '' }}" data-dia="{{ $r['dia'] }}">
                            <td class="fw-semibold colegiado">
                                @if(!empty($r['nro_colegiado']))
                                {{ str_pad($r['nro_colegiado'], 5, '0', STR_PAD_LEFT) }}
                                @else
                                <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex align-items-center user-info">
                                    <div class="me-3 p-2 bg-light rounded-circle avatar">
                                        <i class="fas fa-user text-primary"></i>
                                    </div>
                                    <div class="user-details">
                                        <strong>{{ $r['nombre'] }}</strong>
                                        @if(!empty($r['direccion']))
                                        <small class="text-muted d-block">
                                            <i class="fas fa-map-marker-alt me-1"></i>{{ $r['direccion'] }}
                                        </small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if(!empty($r['correo']))
                                <a href="mailto:{{ $r['correo'] }}" class="text-decoration-none correo-link">
                                    <i class="fas fa-envelope text-primary me-1"></i>{{ $r['correo'] }}
                                </a>
                                @else
                                <span class="text-muted">
                                    <i class="fas fa-envelope me-1"></i>Sin correo
                                </span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary rounded-pill px-2 py-1 edad-badge">
                                    {{ $r['edad'] }} años
                                </span>
                            </td>
                            <td>
                                @if($r['es_hoy'])
                                <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3 py-1 w-100">
                                    <i class="fas fa-birthday-cake me-1"></i> ¡HOY!
                                </span>
                                @elseif($r['dias_restantes'] <= 7)
                                    <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill px-3 py-1 w-100">
                                    <i class="fas fa-clock me-1"></i> En {{ $r['dias_restantes'] }} días
                                    </span>
                                    @else
                                    <span class="badge bg-light text-muted rounded-pill px-3 py-1 w-100">
                                        <i class="fas fa-calendar-check me-1"></i> {{ $r['dias_restantes'] }} días
                                    </span>
                                    @endif
                            </td>
                            <td>
                                @if(!empty($r['correo']))
                                <button type="button"
                                    class="btn btn-sm btn-primary btn-saludar"
                                    data-email="{{ $r['correo'] }}"
                                    data-nombre="{{ $r['nombre'] }}">
                                    <i class="fas fa-gift me-1"></i> Saludar
                                </button>
                                @else
                                <span class="text-muted small">Sin correo</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-birthday-cake fa-3x mb-3"></i>
                                    <h5>No hay cumpleaños registrados para {{ $monthName }}</h5>
                                    <p class="mt-2">No se encontraron registros de cumpleaños en este mes</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($totalBirthdays > 0)
        <div class="card-footer bg-light py-3">
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    Los registros están ordenados por día del mes y nombre
                </small>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-warning text-dark">
                        <i class="fas fa-clock me-1"></i> En 7 días
                    </span>
                    <span class="badge bg-danger">
                        <i class="fas fa-birthday-cake me-1"></i> Hoy
                    </span>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

{{-- Cargar fuente Poppins de Google Fonts --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700;900&display=swap" rel="stylesheet">

@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Cambio de mes (recarga la página con ?m=)
        var monthSelect = document.getElementById('monthFilter');
        if (monthSelect) {
            monthSelect.addEventListener('change', function() {
                var m = this.value || '';
                var url = new URL(window.location.href);
                if (m) {
                    url.searchParams.set('m', m);
                } else {
                    url.searchParams.delete('m');
                }
                window.location.href = url.toString();
            });
        }

        var tbody = document.getElementById('tbodyCumpleanos');
        if (!tbody) {
            return;
        }

        var filtroDia = document.getElementById('filtroDia');
        var filtroCorreo = document.getElementById('filtroCorreo');
        var buscarNombre = document.getElementById('buscarNombre');
        var btnAplicarFiltros = document.getElementById('btnAplicarFiltros');
        var btnLimpiarFiltros = document.getElementById('btnLimpiarFiltros');
        var btnExportarCSV = document.getElementById('btnExportarCSV');
        var btnSubirImagen = document.getElementById('btnSubirImagen');
        var btnVistaPreviaImagen = document.getElementById('btnVistaPreviaImagen');
        var btnSaludarTodos = document.getElementById('btnSaludarTodos');
        var btnVerReporteSaludos = document.getElementById('btnVerReporteSaludos');
        var inputImagenSaludo = document.getElementById('inputImagenSaludo');
        var totalRegistrosEl = document.getElementById('totalRegistros');
        var greetUrl = "{{ route('reports.birthdays.greet') }}";
        var csrfToken = document.querySelector('meta[name=\"csrf-token\"]').getAttribute('content');
        var wrapper = document.getElementById('reporteCumpleanosWrapper');
        var currentMonth = wrapper ? parseInt(wrapper.getAttribute('data-month'), 10) || 0 : 0;
        var currentBgUrl = wrapper ? wrapper.getAttribute('data-bg-url') || '' : '';

        var filas = Array.prototype.slice.call(tbody.querySelectorAll('tr'));
        var registroSaludos = [];
        try {
            registroSaludos = JSON.parse(localStorage.getItem('registroSaludos_CEL') || '[]');
        } catch (e) {
            registroSaludos = [];
        }

        function guardarRegistroSaludos() {
            try {
                localStorage.setItem('registroSaludos_CEL', JSON.stringify(registroSaludos));
            } catch (e) {
                // ignorar errores de almacenamiento
            }
        }

        var datosOriginales = filas.map(function(fila) {
            var nombreEl = fila.querySelector('.user-details strong');
            var nombre = nombreEl ? nombreEl.textContent.trim() : '';
            var colegiadoEl = fila.querySelector('.colegiado');
            var colegiado = colegiadoEl ? colegiadoEl.textContent.trim() : '';
            var correoEl = fila.querySelector('.correo-link');
            var correo = correoEl ? correoEl.textContent.trim() : '';
            var tieneCorreo = !!correoEl;
            var diaAttr = fila.getAttribute('data-dia');
            var dia = diaAttr ? parseInt(diaAttr, 10) : null;

            return {
                elemento: fila,
                nombre: nombre,
                colegiado: colegiado,
                correo: correo,
                tieneCorreo: tieneCorreo,
                dia: dia
            };
        });

        function aplicarFiltros() {
            var diaSel = (filtroDia && filtroDia.value) ? parseInt(filtroDia.value, 10) : null;
            var correoFiltro = filtroCorreo ? filtroCorreo.value : '';
            var busqueda = buscarNombre ? buscarNombre.value.trim().toLowerCase() : '';
            var visibles = 0;

            datosOriginales.forEach(function(d) {
                var mostrar = true;

                if (diaSel !== null && d.dia !== null && d.dia !== diaSel) {
                    mostrar = false;
                }

                if (mostrar && correoFiltro === 'si' && !d.tieneCorreo) {
                    mostrar = false;
                }
                if (mostrar && correoFiltro === 'no' && d.tieneCorreo) {
                    mostrar = false;
                }

                if (mostrar && busqueda) {
                    var texto = (d.nombre + ' ' + d.colegiado).toLowerCase();
                    if (texto.indexOf(busqueda) === -1) {
                        mostrar = false;
                    }
                }

                d.elemento.style.display = mostrar ? '' : 'none';
                if (mostrar) {
                    visibles++;
                }
            });

            if (totalRegistrosEl) {
                totalRegistrosEl.innerHTML = '<i class=\"fas fa-user-friends me-1\"></i> ' + visibles + ' registros';
            }

            if (btnSaludarTodos) {
                var filasConCorreo = datosOriginales.filter(function(d) {
                    return d.tieneCorreo && d.elemento.style.display !== 'none';
                });
                btnSaludarTodos.style.display = filasConCorreo.length > 0 ? 'inline-block' : 'none';
            }
        }

        if (btnAplicarFiltros) {
            btnAplicarFiltros.addEventListener('click', aplicarFiltros);
        }

        if (btnLimpiarFiltros) {
            btnLimpiarFiltros.addEventListener('click', function() {
                if (filtroDia) filtroDia.value = '';
                if (filtroCorreo) filtroCorreo.value = '';
                if (buscarNombre) buscarNombre.value = '';
                aplicarFiltros();
            });
        }

        if (buscarNombre) {
            buscarNombre.addEventListener('input', aplicarFiltros);
        }

        if (filtroDia) {
            filtroDia.addEventListener('change', aplicarFiltros);
        }

        if (filtroCorreo) {
            filtroCorreo.addEventListener('change', aplicarFiltros);
        }

        if (btnExportarCSV) {
            btnExportarCSV.addEventListener('click', function() {
                var filasVisibles = datosOriginales.filter(function(d) {
                    return d.elemento.style.display !== 'none';
                });
                if (filasVisibles.length === 0) {
                    return;
                }

                var csv = 'N° Colegiado,Nombre,Correo,Fecha,Edad\\r\\n';
                filasVisibles.forEach(function(d) {
                    var dia = d.dia;
                    var fecha = '';
                    if (dia) {
                        var dd = String(dia).padStart(2, '0');
                        var mm = String(currentMonth).padStart(2, '0');
                        fecha = dd + '/' + mm;
                    }
                    var edad = '';
                    var edadEl = d.elemento.querySelector('.edad-badge');
                    if (edadEl) {
                        var edadTexto = edadEl.textContent || '';
                        edad = edadTexto.replace(/\\D+/g, '');
                    }

                    csv += "\"" + d.colegiado + "\",\"" + d.nombre + "\",\"" + d.correo + "\",\"" + fecha + "\",\"" + edad + "\"\r\n";
                });

                var blob = new Blob([csv], {
                    type: 'text/csv;charset=utf-8;'
                });
                var link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = 'Cumpleanos_' + new Date().toISOString().slice(0, 10) + '.csv';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            });
        }

        // Aplicar filtros iniciales al cargar (día actual + con correo)
        aplicarFiltros();
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var tbody = document.getElementById('tbodyCumpleanos');
        if (!tbody) return;

        var btnSubirImagen = document.getElementById('btnSubirImagen');
        var btnSaludarTodos = document.getElementById('btnSaludarTodos');
        var btnVerReporteSaludos = document.getElementById('btnVerReporteSaludos');
        var inputImagenSaludo = document.getElementById('inputImagenSaludo');
        var btnAplicarFiltros = document.getElementById('btnAplicarFiltros');
        var btnLimpiarFiltros = document.getElementById('btnLimpiarFiltros');
        var filtroDia = document.getElementById('filtroDia');
        var filtroCorreo = document.getElementById('filtroCorreo');
        var buscarNombre = document.getElementById('buscarNombre');

        var wrapper = document.getElementById('reporteCumpleanosWrapper');
        var greetUrl = "{{ route('reports.birthdays.greet') }}";
        var csrfToken = document.querySelector('meta[name=\"csrf-token\"]').getAttribute('content');

        var registroSaludos = [];
        try {
            registroSaludos = JSON.parse(localStorage.getItem('registroSaludos_CEL') || '[]');
        } catch (e) {
            registroSaludos = [];
        }

        function guardarRegistroSaludos() {
            try {
                localStorage.setItem('registroSaludos_CEL', JSON.stringify(registroSaludos));
            } catch (e) {}
        }

        function actualizarBotonSaludarTodos() {
            if (!btnSaludarTodos) return;
            var filas = Array.prototype.slice.call(tbody.querySelectorAll('tr'));
            var hay = filas.some(function(fila) {
                if (fila.style.display === 'none') return false;
                return !!fila.querySelector('.correo-link');
            });
            btnSaludarTodos.style.display = hay ? 'inline-block' : 'none';
        }

        // Subir / actualizar imagen de fondo (POST al backend)
        var updateImageUrl = "{{ route('reports.birthdays.image') }}";
        var wrapper = document.getElementById('reporteCumpleanosWrapper');
        var currentBgUrl = wrapper ? wrapper.getAttribute('data-bg-url') || '' : '';
        var assetBase = "{{ asset('') }}".replace(/\/$/, '');

        if (btnSubirImagen && inputImagenSaludo) {
            btnSubirImagen.addEventListener('click', function() {
                inputImagenSaludo.click();
            });

            inputImagenSaludo.addEventListener('change', function() {
                var file = inputImagenSaludo.files && inputImagenSaludo.files[0];
                if (!file) return;

                var formData = new FormData();
                formData.append('imagen', file);

                btnSubirImagen.disabled = true;
                btnSubirImagen.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Actualizando...';

                fetch(updateImageUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: formData
                }).then(function(response) {
                    if (!response.ok) throw new Error('Error al subir la imagen');
                    return response.json().catch(function() {
                        return {};
                    });
                }).then(function(data) {
                    alert('Imagen de fondo actualizada correctamente.');
                    if (data.ruta_imagen && wrapper) {
                        // Construir la URL absoluta correcta del fondo
                        currentBgUrl = assetBase + '/' + data.ruta_imagen.replace(/^\/+/, '');
                        wrapper.setAttribute('data-bg-url', currentBgUrl);
                    }
                }).catch(function() {
                    alert('No se pudo actualizar la imagen. Intente nuevamente.');
                }).finally(function() {
                    btnSubirImagen.disabled = false;
                    btnSubirImagen.innerHTML = '<i class="fas fa-upload me-1"></i> Actualizar imagen';
                });
            });
        }

        // Vista previa de la imagen de fondo
        var btnVistaPreviaImagen = document.getElementById('btnVistaPreviaImagen');
        if (btnVistaPreviaImagen) {
            btnVistaPreviaImagen.addEventListener('click', function() {
                var url = currentBgUrl;
                if (!url) {
                    alert('No hay imagen configurada para vista previa.');
                    return;
                }

                var overlay = document.createElement('div');
                overlay.id = 'modalVistaPreviaImagen';
                overlay.style.position = 'fixed';
                overlay.style.top = '0';
                overlay.style.left = '0';
                overlay.style.right = '0';
                overlay.style.bottom = '0';
                overlay.style.backgroundColor = 'rgba(0,0,0,0.5)';
                overlay.style.zIndex = '1050';
                overlay.style.display = 'flex';
                overlay.style.alignItems = 'center';
                overlay.style.justifyContent = 'center';

                var html = '';
                html += '<div class="bg-white rounded shadow" style="max-width: 800px; width: 95%; max-height: 90vh; overflow:auto;">';
                html += '  <div class="p-3 bg-secondary text-white d-flex justify-content-between align-items-center">';
                html += '    <h5 class="mb-0"><i class="fas fa-eye me-2"></i>Vista previa de la imagen de saludo</h5>';
                html += '    <button type="button" class="btn btn-sm btn-light" id="btnCerrarVistaPrevia">&times;</button>';
                html += '  </div>';
                html += '  <div class="p-3 text-center">';
                html += '    <div style="position:relative; display:inline-block; max-width:100%;">';
                html += '      <img src="' + url + '" alt="Imagen de saludo" style="max-width:100%; height:auto; border-radius:4px; box-shadow:0 2px 6px rgba(0,0,0,.2);">';
                html += '      <div style="position:absolute; top:60%; left:50%; transform:translate(-50%, -50%); width:70%; text-align:center; color:#ffffff; text-shadow:2px 2px 4px rgba(0,0,0,.7); font-family:Poppins, sans-serif; text-transform:capitalize; font-weight:700;">';
                html += '        <div style="font-size:42px; font-weight:bold; letter-spacing:2px; line-height:1;">Nombres</div>';
                html += '        <div style="font-size:42px; font-weight:bold; letter-spacing:2px;">Apellidos</div>';
                html += '      </div>';
                html += '    </div>';
                html += '  </div>';
                html += '</div>';

                overlay.innerHTML = html;
                document.body.appendChild(overlay);

                function cerrarVista() {
                    var m = document.getElementById('modalVistaPreviaImagen');
                    if (m && m.parentNode) {
                        m.parentNode.removeChild(m);
                    }
                }

                overlay.addEventListener('click', function(e) {
                    if (e.target.id === 'modalVistaPreviaImagen') {
                        cerrarVista();
                    }
                });

                document.getElementById('btnCerrarVistaPrevia').addEventListener('click', cerrarVista);
            });
        }

        // Función para generar imagen de saludo (shared entre individual y masivo)
        var wrapper = document.getElementById('reporteCumpleanosWrapper');
        var bgUrl = wrapper ? wrapper.getAttribute('data-bg-url') || '' : '';


        function generarImagenSaludo(nombre, callback) {
            // Función auxiliar para capitalizar
            function capitalize(str) {
                return str.toLowerCase().replace(/\b\w/g, function(c) {
                    return c.toUpperCase();
                });
            }

            // Crear canvas offscreen - RESOLUCIÓN MEJORADA 1000x750
            var canvas = document.createElement('canvas');
            canvas.width = 1000;
            canvas.height = 750;

            var ctx = canvas.getContext('2d', {
                alpha: false, // Sin transparencia = más rápido
                willReadFrequently: false
            });

            // Pre-cargar imagen de fondo
            var img = new Image();
            img.crossOrigin = 'anonymous';

            img.onload = function() {
                // Dibujar fondo en alta resolución (colores originales)
                ctx.drawImage(img, 0, 0, 1000, 750);

                // Configurar texto
                ctx.fillStyle = '#ffffff';
                ctx.shadowColor = 'rgba(0,0,0,0.7)';
                ctx.shadowBlur = 5;
                ctx.shadowOffsetX = 2;
                ctx.shadowOffsetY = 2;
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';

                // Dividir nombre en nombres y apellidos
                var partes = nombre.trim().split(' ');
                var nombres = '';
                var apellidos = '';

                if (partes.length <= 2) {
                    nombres = nombre;
                } else {
                    nombres = partes.slice(0, 2).join(' ');
                    apellidos = partes.slice(2).join(' ');
                }

                // Posición vertical (52% desde arriba - mejor centrado)
                var centerY = 750 * 0.57;

                if (apellidos) {
                    // Dos líneas: nombres arriba, apellidos abajo
                    // Fuente reducida a 42px
                    ctx.font = '900 42px Poppins, Arial, sans-serif';
                    ctx.fillText('Econ. ' + capitalize(nombres), 500, centerY - 26);
                    ctx.fillText(capitalize(apellidos), 500, centerY + 26);
                } else {
                    // Una sola línea
                    ctx.font = '900 42px Poppins, Arial, sans-serif';
                    ctx.fillText('Econ. ' + capitalize(nombres), 500, centerY);
                }

                // Convertir a dataURL JPEG con 95% de calidad (mejorado)
                var dataUrl = canvas.toDataURL('image/jpeg', 0.95);
                callback(dataUrl);
            };

            img.onerror = function() {
                console.error('Error al cargar imagen de fondo');
                callback(null);
            };

            // Cargar imagen de fondo
            img.src = bgUrl;
        }

        // Saludar a todos (procesando uno por uno con progreso)
        if (btnSaludarTodos) {
            btnSaludarTodos.addEventListener('click', function() {
                var filas = Array.prototype.slice.call(tbody.querySelectorAll('tr'));
                var filasConCorreo = filas.filter(function(fila) {
                    if (fila.style.display === 'none') return false;
                    return !!fila.querySelector('.correo-link');
                });

                if (filasConCorreo.length === 0) {
                    alert('No hay colegiados con correo en el filtro actual.');
                    return;
                }

                var total = filasConCorreo.length;
                var enviadas = 0;
                var originalHtml = btnSaludarTodos.innerHTML;
                btnSaludarTodos.disabled = true;

                function procesarSiguiente(index) {
                    if (index >= total) {
                        btnSaludarTodos.disabled = false;
                        btnSaludarTodos.innerHTML = originalHtml;
                        alert('Se han lanzado saludos a ' + enviadas + ' colegiados (revisar correo para confirmar entregas).');
                        return;
                    }

                    var fila = filasConCorreo[index];
                    var correoEl = fila.querySelector('.correo-link');
                    var nombreEl = fila.querySelector('.user-details strong');
                    var colegiadoEl = fila.querySelector('.colegiado');
                    var email = correoEl ? correoEl.textContent.trim() : '';
                    var nombre = nombreEl ? nombreEl.textContent.trim() : '';
                    var colegiado = colegiadoEl ? colegiadoEl.textContent.trim() : '';

                    if (!email) {
                        procesarSiguiente(index + 1);
                        return;
                    }

                    btnSaludarTodos.innerHTML = 'Enviando ' + (index + 1) + '/' + total;

                    // Generar imagen personalizada para cada colegiado
                    generarImagenSaludo(nombre, function(dataUrl) {
                        var payload = {
                            email: email,
                            name: nombre
                        };
                        if (dataUrl) {
                            payload.image = dataUrl;
                        }

                        fetch(greetUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(payload)
                        }).then(function() {
                            enviadas++;
                            registroSaludos.push({
                                nombre: nombre,
                                colegiado: colegiado,
                                email: email,
                                fechaHora: new Date().toISOString()
                            });
                            guardarRegistroSaludos();
                        }).catch(function() {
                            // Ignorar errores individuales para continuar con el resto
                        }).finally(function() {
                            // Pequeña pausa para que la interfaz siga respondiendo
                            setTimeout(function() {
                                procesarSiguiente(index + 1);
                            }, 100); // Aumentado a 100ms para dar tiempo a la generación de imagen
                        });
                    });
                }

                procesarSiguiente(0);

            });
        }

        // Ver reporte de saludos del día
        if (btnVerReporteSaludos) {
            btnVerReporteSaludos.addEventListener('click', function() {
                var hoy = new Date().toISOString().slice(0, 10);
                var saludosHoy = registroSaludos.filter(function(s) {
                    if (!s.fechaHora) return false;
                    var f = new Date(s.fechaHora);
                    if (isNaN(f.getTime())) return false;
                    return f.toISOString().slice(0, 10) === hoy;
                });

                var overlay = document.createElement('div');
                overlay.id = 'modalReporteSaludos';
                overlay.style.position = 'fixed';
                overlay.style.top = '0';
                overlay.style.left = '0';
                overlay.style.right = '0';
                overlay.style.bottom = '0';
                overlay.style.backgroundColor = 'rgba(0,0,0,0.5)';
                overlay.style.zIndex = '1050';
                overlay.style.display = 'flex';
                overlay.style.alignItems = 'center';
                overlay.style.justifyContent = 'center';

                var html = '';
                html += '<div class=\"bg-white rounded shadow\" style=\"max-width: 700px; width: 95%; max-height: 80vh; overflow:auto;\">';
                html += '  <div class=\"p-3 bg-primary text-white d-flex justify-content-between align-items-center\">';
                html += '    <h5 class=\"mb-0\"><i class=\"fas fa-history me-2\"></i>Reporte de saludos - Hoy</h5>';
                html += '    <button type=\"button\" class=\"btn btn-sm btn-light\" id=\"btnCerrarReporteSaludos\">&times;</button>';
                html += '  </div>';
                html += '  <div class=\"p-3\">';

                if (saludosHoy.length === 0) {
                    html += '    <div class=\"text-center text-muted py-4\">';
                    html += '      <i class=\"fas fa-bell-slash fa-2x mb-2\"></i>';
                    html += '      <p class=\"mb-0\">No se han registrado saludos hoy.</p>';
                    html += '    </div>';
                } else {
                    html += '    <table class=\"table table-sm align-middle mb-0\">';
                    html += '      <thead><tr><th>Colegiado</th><th>Nombre</th><th>Correo</th><th style=\"width:110px;\">Hora</th></tr></thead>';
                    html += '      <tbody>';
                    saludosHoy.forEach(function(s) {
                        var f = new Date(s.fechaHora);
                        var hora = isNaN(f.getTime()) ? '' : f.toLocaleTimeString('es-PE', {
                            hour: '2-digit',
                            minute: '2-digit'
                        });
                        html += '<tr>';
                        html += '<td>' + (s.colegiado || '') + '</td>';
                        html += '<td>' + (s.nombre || '') + '</td>';
                        html += '<td>' + (s.email || '') + '</td>';
                        html += '<td>' + hora + '</td>';
                        html += '</tr>';
                    });
                    html += '      </tbody>';
                    html += '    </table>';
                }

                html += '  </div>';
                html += '  <div class=\"p-3 border-top text-end\">';
                html += '    <button type=\"button\" class=\"btn btn-sm btn-secondary\" id=\"btnCerrarReporteSaludosFooter\">Cerrar</button>';
                html += '  </div>';
                html += '</div>';

                overlay.innerHTML = html;
                document.body.appendChild(overlay);

                function cerrarReporte() {
                    var m = document.getElementById('modalReporteSaludos');
                    if (m && m.parentNode) {
                        m.parentNode.removeChild(m);
                    }
                }

                overlay.addEventListener('click', function(e) {
                    if (e.target.id === 'modalReporteSaludos') {
                        cerrarReporte();
                    }
                });

                document.getElementById('btnCerrarReporteSaludos').addEventListener('click', cerrarReporte);
                document.getElementById('btnCerrarReporteSaludosFooter').addEventListener('click', cerrarReporte);
            });
        }

        // Registrar saludos individuales para el reporte (siempre que se pulse Saludar)
        tbody.addEventListener('click', function(e) {
            var btn = e.target.closest('.btn-saludar');
            if (!btn) return;
            var email = btn.getAttribute('data-email');
            var nombre = btn.getAttribute('data-nombre') || '';
            var colegiadoCell = btn.closest('tr').querySelector('.colegiado');
            var colegiado = colegiadoCell ? colegiadoCell.textContent.trim() : '';
            if (!email) return;

            registroSaludos.push({
                nombre: nombre,
                colegiado: colegiado,
                email: email,
                fechaHora: new Date().toISOString()
            });
            guardarRegistroSaludos();
        });

        // Actualizar botón Saludar a todos con los cambios de filtro
        actualizarBotonSaludarTodos();
        if (btnAplicarFiltros) btnAplicarFiltros.addEventListener('click', function() {
            setTimeout(actualizarBotonSaludarTodos, 100);
        });
        if (btnLimpiarFiltros) btnLimpiarFiltros.addEventListener('click', function() {
            setTimeout(actualizarBotonSaludarTodos, 100);
        });
        if (filtroDia) filtroDia.addEventListener('change', function() {
            setTimeout(actualizarBotonSaludarTodos, 100);
        });
        if (filtroCorreo) filtroCorreo.addEventListener('change', function() {
            setTimeout(actualizarBotonSaludarTodos, 100);
        });
        if (buscarNombre) buscarNombre.addEventListener('input', function() {
            setTimeout(actualizarBotonSaludarTodos, 150);
        });
    });
</script>

{{-- Script extra para forzar que la vista previa use el texto actualizado --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var btn = document.getElementById('btnVistaPreviaImagen');
        var tbody = document.getElementById('tbodyCumpleanos');
        if (!btn || !tbody) return;

        // Clonar el botón para eliminar cualquier listener anterior
        var nuevoBtn = btn.cloneNode(true);
        btn.parentNode.replaceChild(nuevoBtn, btn);

        var urlBg = document.getElementById('reporteCumpleanosWrapper') ?
            document.getElementById('reporteCumpleanosWrapper').getAttribute('data-bg-url') || '' :
            '';

        var wrapper = document.getElementById('reporteCumpleanosWrapper');
        var tituloVista = wrapper ? wrapper.getAttribute('data-greeting-title') || '' : '';
        var mensajeVista = wrapper ? wrapper.getAttribute('data-greeting-message') || '' : '';

        nuevoBtn.addEventListener('click', function() {
            if (!urlBg) {
                alert('No hay imagen configurada para vista previa.');
                return;
            }

            var nombreDemo = 'NOMBRE DEL COLEGIADO';
            var filaEjemplo = tbody.querySelector('tr');
            if (filaEjemplo) {
                var nombreElDemo = filaEjemplo.querySelector('.user-details strong');
                if (nombreElDemo && nombreElDemo.textContent.trim()) {
                    nombreDemo = nombreElDemo.textContent.trim();
                }
            }

            var overlay = document.createElement('div');
            overlay.id = 'modalVistaPreviaImagen';
            overlay.style.position = 'fixed';
            overlay.style.top = '0';
            overlay.style.left = '0';
            overlay.style.right = '0';
            overlay.style.bottom = '0';
            overlay.style.backgroundColor = 'rgba(0,0,0,0.5)';
            overlay.style.zIndex = '1050';
            overlay.style.display = 'flex';
            overlay.style.alignItems = 'center';
            overlay.style.justifyContent = 'center';

            var html = '';
            html += '<div class="bg-white rounded shadow" style="max-width: 800px; width: 95%; max-height: 90vh; overflow:auto;">';
            html += '  <div class="p-3 bg-secondary text-white d-flex justify-content-between align-items-center">';
            html += '    <h5 class="mb-0"><i class="fas fa-eye me-2"></i>Vista previa de la imagen de saludo</h5>';
            html += '    <button type="button" class="btn btn-sm btn-light" id="btnCerrarVistaPrevia">&times;</button>';
            html += '  </div>';
            html += '  <div class="p-3 text-center">';
            html += '    <div style="position:relative; display:inline-block; max-width:100%;">';
            html += '      <img src="' + urlBg + '" alt="Imagen de saludo" style="max-width:100%; height:auto; border-radius:4px; box-shadow:0 2px 6px rgba(0,0,0,.2);">';
            html += '      <div style="position:absolute; top:60%; left:50%; transform:translate(-50%, -50%); width:70%; text-align:center; color:#ffffff; text-shadow:2px 2px 4px rgba(0,0,0,.7); font-family:Poppins, sans-serif; text-transform:capitalize; font-weight:700;">';

            // Dividir nombreDemo en nombres y apellidos
            var partesDemo = nombreDemo.trim().split(' ');
            var nombresDemo = '';
            var apellidosDemo = '';
            if (partesDemo.length <= 2) {
                nombresDemo = nombreDemo.toLowerCase();
            } else {
                nombresDemo = partesDemo.slice(0, 2).join(' ').toLowerCase();
                apellidosDemo = partesDemo.slice(2).join(' ').toLowerCase();
            }

            html += '        <div style="font-size:34px; font-weight:bold; letter-spacing:2px; line-height:1;">' + nombresDemo + '</div>';
            if (apellidosDemo) {
                html += '        <div style="font-size:34px; font-weight:bold; letter-spacing:2px;">' + apellidosDemo + '</div>';
            }
            html += '      </div>';
            html += '    </div>';
            html += '  </div>';
            html += '</div>';

            overlay.innerHTML = html;
            document.body.appendChild(overlay);

            function cerrarVista() {
                var m = document.getElementById('modalVistaPreviaImagen');
                if (m && m.parentNode) {
                    m.parentNode.removeChild(m);
                }
            }

            overlay.addEventListener('click', function(e) {
                if (e.target.id === 'modalVistaPreviaImagen') {
                    cerrarVista();
                }
            });

            document.getElementById('btnCerrarVistaPrevia').addEventListener('click', cerrarVista);
        });
    });
</script>

{{-- Librería para capturar la tarjeta como imagen en el navegador --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>

{{-- Script para que el correo reciba una sola imagen (fondo + texto) generada en el navegador --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var tbody = document.getElementById('tbodyCumpleanos');
        if (!tbody) return;

        var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        var greetUrl = "{{ route('reports.birthdays.greet') }}";
        var wrapper = document.getElementById('reporteCumpleanosWrapper');
        var bgUrl = wrapper ? wrapper.getAttribute('data-bg-url') || '' : '';

        function generarImagenSaludo(nombre, callback) {
            // Función auxiliar para capitalizar
            function capitalize(str) {
                return str.toLowerCase().replace(/\b\w/g, function(c) {
                    return c.toUpperCase();
                });
            }

            // Crear canvas offscreen - RESOLUCIÓN MEJORADA 1000x750
            var canvas = document.createElement('canvas');
            canvas.width = 1000;
            canvas.height = 750;

            var ctx = canvas.getContext('2d', {
                alpha: false, // Sin transparencia = más rápido
                willReadFrequently: false
            });

            // Pre-cargar imagen de fondo
            var img = new Image();
            img.crossOrigin = 'anonymous';

            img.onload = function() {
                // Dibujar fondo en alta resolución (colores originales)
                ctx.drawImage(img, 0, 0, 1000, 750);

                // Configurar texto
                ctx.fillStyle = '#ffffff';
                ctx.shadowColor = 'rgba(0,0,0,0.7)';
                ctx.shadowBlur = 5;
                ctx.shadowOffsetX = 2;
                ctx.shadowOffsetY = 2;
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';

                // Dividir nombre en nombres y apellidos
                var partes = nombre.trim().split(' ');
                var nombres = '';
                var apellidos = '';

                if (partes.length <= 2) {
                    nombres = nombre;
                } else {
                    nombres = partes.slice(0, 2).join(' ');
                    apellidos = partes.slice(2).join(' ');
                }

                // Posición vertical (55% desde arriba)
                var centerY = 750 * 0.57;

                if (apellidos) {
                    // Dos líneas: nombres arriba, apellidos abajo
                    // Fuente reducida a 42px
                    ctx.font = '900 42px Poppins, Arial, sans-serif';
                    ctx.fillText('Econ. ' + capitalize(nombres), 500, centerY - 26);
                    ctx.fillText(capitalize(apellidos), 500, centerY + 26);
                } else {
                    // Una sola línea
                    ctx.font = '900 42px Poppins, Arial, sans-serif';
                    ctx.fillText('Econ. ' + capitalize(nombres), 500, centerY);
                }

                // Convertir a dataURL JPEG con 95% de calidad (mejorado)
                var dataUrl = canvas.toDataURL('image/jpeg', 0.95);
                callback(dataUrl);
            };




            img.onerror = function() {
                console.error('Error al cargar imagen de fondo');
                callback(null);
            };

            // Cargar imagen de fondo
            img.src = bgUrl;
        }




        tbody.addEventListener('click', function(e) {
            var btn = e.target.closest('.btn-saludar');
            if (!btn) return;

            var email = btn.getAttribute('data-email');
            var nombre = btn.getAttribute('data-nombre') || '';

            if (!email) {
                alert('Este colegiado no tiene correo configurado.');
                return;
            }

            var originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Enviando...';

            // Dar tiempo al navegador para actualizar la UI antes de generar la imagen
            setTimeout(function() {
                generarImagenSaludo(nombre, function(dataUrl) {
                    var payload = {
                        email: email,
                        name: nombre
                    };
                    if (dataUrl) {
                        payload.image = dataUrl;
                    }

                    fetch(greetUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(payload)
                    }).then(function(response) {
                        if (!response.ok) {
                            throw new Error('Error en la respuesta');
                        }
                        return response.json().catch(function() {
                            return {};
                        });
                    }).then(function() {
                        alert('Saludo enviado a ' + nombre + ' (' + email + ').');
                    }).catch(function() {
                        alert('No se pudo enviar el saludo. Intente nuevamente más tarde.');
                    }).finally(function() {
                        btn.disabled = false;
                        btn.innerHTML = originalHtml;
                    });
                });
            }, 50); // Timeout reducido para mejor rendimiento
        });
    });
</script>
@endsection