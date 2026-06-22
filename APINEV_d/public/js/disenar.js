/**
 * public/js/disenar.js — Módulo Diseñar SINTESU/APINEV
 * CRUD completo con persistencia en BD. Sin frameworks.
 */
(function () {
    'use strict';
 
    const meta = document.querySelector('meta[name="base-url"]');
    const BASE  = meta ? meta.content : '';
    const API   = BASE + '/controllers/DisenarController.php';
 
    let idInstrumentoActivo = null;
    let idReactivoActivo    = null;
 
    /* ── AJAX helpers ─────────────────────────────────────── */
    function ajaxPost(datos) {
        return fetch(API, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: new URLSearchParams(datos)
        }).then(function (r) { return r.json(); });
    }
 
    function ajaxGet(params) {
        return fetch(API + '?' + new URLSearchParams(params).toString(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).then(function (r) { return r.json(); });
    }
 
    /* ── Toast ────────────────────────────────────────────── */
    function toast(msg, tipo) {
        var t = document.createElement('div');
        t.textContent = msg;
        t.style.cssText = [
            'position:fixed','bottom:1.5rem','right:1.5rem',
            'background:' + (tipo === 'error' ? '#c0392b' : '#27ae60'),
            'color:#fff','padding:.55rem 1.1rem','border-radius:6px',
            'font-size:.88rem','z-index:9999',
            'box-shadow:0 2px 8px rgba(0,0,0,.3)','transition:opacity .4s'
        ].join(';');
        document.body.appendChild(t);
        setTimeout(function () { t.style.opacity = '0'; }, 2300);
        setTimeout(function () { t.remove(); }, 2800);
    }
 
    /* ── 1. SELECCIÓN DE FILA ─────────────────────────────── */
    document.querySelectorAll('.tabla-sintesu tbody').forEach(function (tbody) {
        tbody.addEventListener('click', function (e) {
            var fila = e.target.closest('tr');
            if (!fila || e.target.closest('.btn-op')) return;
            tbody.querySelectorAll('.fila-seleccionada').forEach(function (tr) {
                tr.classList.remove('fila-seleccionada');
            });
            fila.classList.add('fila-seleccionada');
 
            if (tbody.id === 'tbody-creados') {
                idInstrumentoActivo = parseInt(fila.dataset.id, 10);
                idReactivoActivo    = null;
                cargarReactivos(idInstrumentoActivo);
                limpiarCriterios();
            }
            if (tbody.id === 'tbody-reactivos') {
                idReactivoActivo = parseInt(fila.dataset.id, 10);
                cargarCriterios(idReactivoActivo);
            }
        });
    });
 
    /* ── 2. ORDENAMIENTO ──────────────────────────────────── */
    document.querySelectorAll('.col-sort').forEach(function (th) {
        th.addEventListener('click', function () {
            var tabla = th.closest('table');
            var tbody = tabla.querySelector('tbody');
            var ths   = Array.from(th.parentNode.querySelectorAll('th'));
            var idx   = ths.indexOf(th);
            var asc   = th.dataset.sortDir !== 'asc';
            ths.forEach(function (h) {
                h.dataset.sortDir = '';
                var ico = h.querySelector('.sort-icon');
                if (ico) ico.textContent = '⇅';
            });
            th.dataset.sortDir = asc ? 'asc' : 'desc';
            var ico = th.querySelector('.sort-icon');
            if (ico) ico.textContent = asc ? '▲' : '▼';
            var filas = Array.from(tbody.querySelectorAll('tr'));
            filas.sort(function (a, b) {
                var ta = a.cells[idx] ? a.cells[idx].textContent.trim() : '';
                var tb = b.cells[idx] ? b.cells[idx].textContent.trim() : '';
                var na = parseFloat(ta), nb = parseFloat(tb);
                if (!isNaN(na) && !isNaN(nb)) return asc ? na - nb : nb - na;
                return asc ? ta.localeCompare(tb, 'es') : tb.localeCompare(ta, 'es');
            });
            filas.forEach(function (f) { tbody.appendChild(f); });
        });
    });
 
    /* ── 3. CARGAR INSTRUMENTOS ───────────────────────────── */
    function cargarInstrumentos() {
        ajaxGet({ accion: 'listar_instrumentos' }).then(function (res) {
            if (res.status !== 'ok') return;
            var tbody = document.getElementById('tbody-creados');
            tbody.innerHTML = '';
            res.datos.forEach(function (row) {
                tbody.appendChild(crearFilaInstrumento(row));
            });
        }).catch(function () { toast('Error al cargar instrumentos', 'error'); });
    }
 
    function crearFilaInstrumento(row) {
        var tr = document.createElement('tr');
        tr.dataset.id           = row.idInstrumentoEvaluacion;
        tr.dataset.indicaciones = row.indicaciones || '';
        tr.dataset.reticula     = row.reticula     || '';
 
        // Columnas: ID | Retícula | Clave | Nombre | Docentes | Reactivos
        [
            row.idInstrumentoEvaluacion,
            row.reticula      || '',
            row.clave         || '',
            row.nombre        || '',
            row.docentes      || '',
            row.totalReactivos || 0
        ].forEach(function (val) {
            var td = document.createElement('td');
            td.textContent = val;
            tr.appendChild(td);
        });
 
        var tdOps = document.createElement('td');
        tdOps.className = 'td-ops';
        tdOps.innerHTML =
            '<button class="btn-op btn-modificar" data-id="' + row.idInstrumentoEvaluacion + '" data-tipo="creado">✏️ Modific</button> ' +
            '<button class="btn-op btn-eliminar"  data-id="' + row.idInstrumentoEvaluacion + '" data-tipo="creado">🗑️ Elimin</button>';
        tr.appendChild(tdOps);
        return tr;
    }
 
    /* ── 4. CARGAR REACTIVOS ──────────────────────────────── */
    function cargarReactivos(idInstrumento) {
        ajaxGet({ accion: 'listar_reactivos', idInstrumento: idInstrumento }).then(function (res) {
            if (res.status !== 'ok') return;
            var tbody = document.getElementById('tbody-reactivos');
            tbody.innerHTML = '';
            res.datos.forEach(function (row) { tbody.appendChild(crearFilaReactivo(row)); });
            limpiarCriterios();
            // Actualizar conteo en la fila del instrumento activo
            actualizarConteoReactivos(idInstrumento, res.datos.length);
        }).catch(function () { toast('Error al cargar reactivos', 'error'); });
    }
 
    function actualizarConteoReactivos(idInstrumento, total) {
        var fila = document.querySelector('#tbody-creados tr[data-id="' + idInstrumento + '"]');
        if (fila && fila.cells[5]) fila.cells[5].textContent = total;
    }
 
    function crearFilaReactivo(row) {
        var tr = document.createElement('tr');
        tr.dataset.id = row.idReactivo;
        [row.idReactivo, row.enunciado, row.indicador, row.puntajeMaximo].forEach(function (val, i) {
            var td = document.createElement('td');
            td.textContent = val !== null && val !== undefined ? val : '';
            if (i === 1) td.className = 'td-texto';
            tr.appendChild(td);
        });
        var tdOps = document.createElement('td');
        tdOps.className = 'td-ops';
        tdOps.innerHTML =
            '<button class="btn-op btn-modificar" data-id="' + row.idReactivo + '" data-tipo="reactivo">Mc</button> ' +
            '<button class="btn-op btn-eliminar"  data-id="' + row.idReactivo + '" data-tipo="reactivo">El</button>';
        tr.appendChild(tdOps);
        return tr;
    }
 
    /* ── 5. CARGAR CRITERIOS ──────────────────────────────── */
    function cargarCriterios(idReactivo) {
        ajaxGet({ accion: 'listar_criterios', idReactivo: idReactivo }).then(function (res) {
            if (res.status !== 'ok') return;
            var tbody = document.getElementById('tbody-criterios');
            tbody.innerHTML = '';
            res.datos.forEach(function (row) { tbody.appendChild(crearFilaCriterio(row)); });
        }).catch(function () { toast('Error al cargar criterios', 'error'); });
    }
 
    function limpiarCriterios() {
        document.getElementById('tbody-criterios').innerHTML = '';
        idReactivoActivo = null;
    }
 
    function crearFilaCriterio(row) {
        var tr = document.createElement('tr');
        tr.dataset.id = row.idCriterio;
        [row.idCriterio, row.puntajeObtenido, row.retroalimentacion].forEach(function (val, i) {
            var td = document.createElement('td');
            td.textContent = val !== null && val !== undefined ? val : '';
            if (i === 2) td.className = 'td-texto';
            tr.appendChild(td);
        });
        var tdOps = document.createElement('td');
        tdOps.className = 'td-ops';
        tdOps.innerHTML =
            '<button class="btn-op btn-modificar" data-id="' + row.idCriterio + '" data-tipo="criterio">Mc</button> ' +
            '<button class="btn-op btn-eliminar"  data-id="' + row.idCriterio + '" data-tipo="criterio">El</button>';
        tr.appendChild(tdOps);
        return tr;
    }
 
    /* ── 6. MODAL INSTRUMENTO ─────────────────────────────── */
    var niOverlay   = document.getElementById('ni-overlay');
    var niBtnCancel = document.getElementById('ni-btn-cancel');
    var niBtnSave   = document.getElementById('ni-btn-save');
    var niClose     = document.getElementById('ni-close');
    var _niFilaEd   = null;
 
    // Crear el select de retícula dentro del modal
    function insertarSelectReticula() {
        // Verificar si ya existe
        if (document.getElementById('ni-reticula')) return;
        var rowClave = document.getElementById('ni-clave').closest('.ni-row');
        var divRow   = document.createElement('div');
        divRow.className = 'ni-row';
        divRow.innerHTML =
            '<label class="ni-label" for="ni-reticula">Retícula:</label>' +
            '<select class="ni-input" id="ni-reticula">' +
            '<option value="">-- Sin retícula --</option>' +
            '</select>';
        rowClave.parentNode.insertBefore(divRow, rowClave);
    }
 
    function cargarReticulasEnSelect(reticulaActual) {
        insertarSelectReticula();
        var sel = document.getElementById('ni-reticula');
        // Limpiar opciones previas excepto la primera
        while (sel.options.length > 1) sel.remove(1);
 
        ajaxGet({ accion: 'listar_reticulas' }).then(function (res) {
            if (res.status !== 'ok') return;
            res.datos.forEach(function (r) {
                var opt = document.createElement('option');
                opt.value       = r.idReticula;
                opt.textContent = r.idReticula + ' – ' + r.nombre;
                if (r.idReticula === reticulaActual) opt.selected = true;
                sel.appendChild(opt);
            });
            // Si no hay grupos aún, mostrar campo de texto libre
            if (res.datos.length === 0) {
                sel.outerHTML = '<input class="ni-input" id="ni-reticula" type="text" placeholder="Ej: ISIC-2010-224" value="' + (reticulaActual || '') + '">';
            }
        });
    }
 
    function abrirNuevoInstrumento() {
        document.getElementById('ni-titulo').textContent = 'Nuevo Instrumento';
        document.getElementById('ni-clave').value        = '';
        document.getElementById('ni-nombre').value       = '';
        document.getElementById('ni-indicaciones').value = '';
        _niFilaEd = null;
        niOverlay.style.display = '';
        cargarReticulasEnSelect('');
        // Calcular siguiente ID
        document.getElementById('ni-id').value = '...';
        ajaxGet({ accion: 'listar_instrumentos' }).then(function (res) {
            var maxId = 0;
            if (res.status === 'ok') res.datos.forEach(function (r) {
                var id = parseInt(r.idInstrumentoEvaluacion, 10);
                if (id > maxId) maxId = id;
            });
            document.getElementById('ni-id').value = maxId + 1;
        });
        setTimeout(function () { document.getElementById('ni-clave').focus(); }, 80);
    }
 
    function cerrarNuevoInstrumento() {
        niOverlay.style.display = 'none';
        _niFilaEd = null;
    }
 
    function abrirModificarInstrumento(fila) {
        var cs = Array.from(fila.cells);
        document.getElementById('ni-titulo').textContent    = 'Modificar Instrumento';
        document.getElementById('ni-id').value              = cs[0] ? cs[0].textContent.trim() : '';
        document.getElementById('ni-clave').value           = cs[2] ? cs[2].textContent.trim() : '';
        document.getElementById('ni-nombre').value          = cs[3] ? cs[3].textContent.trim() : '';
        document.getElementById('ni-indicaciones').value    = fila.dataset.indicaciones || '';
        _niFilaEd = fila;
        niOverlay.style.display = '';
        cargarReticulasEnSelect(fila.dataset.reticula || '');
        setTimeout(function () { document.getElementById('ni-clave').focus(); }, 80);
    }
 
    document.getElementById('btn-add-creado').addEventListener('click', abrirNuevoInstrumento);
    niClose.addEventListener('click', cerrarNuevoInstrumento);
    niBtnCancel.addEventListener('click', cerrarNuevoInstrumento);
    niOverlay.addEventListener('click', function (e) { if (e.target === niOverlay) cerrarNuevoInstrumento(); });
 
    niBtnSave.addEventListener('click', function () {
        var clave     = document.getElementById('ni-clave').value.trim();
        var nombre    = document.getElementById('ni-nombre').value.trim();
        var indic     = document.getElementById('ni-indicaciones').value.trim();
        var retEl     = document.getElementById('ni-reticula');
        var reticula  = retEl ? retEl.value.trim() : '';
 
        if (!clave)  { document.getElementById('ni-clave').style.borderColor='#cc2200'; document.getElementById('ni-clave').focus(); return; }
        if (!nombre) { document.getElementById('ni-nombre').style.borderColor='#cc2200'; document.getElementById('ni-nombre').focus(); return; }
 
        if (_niFilaEd) {
            var id = parseInt(_niFilaEd.dataset.id, 10);
            ajaxPost({ accion:'actualizar_instrumento', id:id, clave:clave, nombre:nombre, indicaciones:indic, reticula:reticula })
                .then(function (res) {
                    if (res.status !== 'ok') { toast(res.mensaje || 'Error al actualizar', 'error'); return; }
                    var cs = Array.from(_niFilaEd.cells);
                    if (cs[1]) cs[1].textContent = reticula;
                    if (cs[2]) cs[2].textContent = clave;
                    if (cs[3]) cs[3].textContent = nombre;
                    _niFilaEd.dataset.indicaciones = indic;
                    _niFilaEd.dataset.reticula     = reticula;
                    toast('Instrumento actualizado');
                    cerrarNuevoInstrumento();
                }).catch(function () { toast('Error de red', 'error'); });
        } else {
            ajaxPost({ accion:'crear_instrumento', clave:clave, nombre:nombre, indicaciones:indic, reticula:reticula })
                .then(function (res) {
                    if (res.status !== 'ok') { toast(res.mensaje || 'Error al guardar', 'error'); return; }
                    cargarInstrumentos();
                    toast('Instrumento guardado');
                    cerrarNuevoInstrumento();
                }).catch(function () { toast('Error de red', 'error'); });
        }
    });
 
    ['ni-clave','ni-nombre'].forEach(function (id) {
        var el = document.getElementById(id);
        if (el) el.addEventListener('input', function () { el.style.borderColor = ''; });
    });
 
    /* ── 7. MODAL REACTIVO ────────────────────────────────── */
    var nrOverlay   = document.getElementById('nr-overlay');
    var nrClose     = document.getElementById('nr-close');
    var nrBtnCancel = document.getElementById('nr-btn-cancel');
    var nrBtnSave   = document.getElementById('nr-btn-save');
    var _nrFilaEd   = null;
 
    function abrirNuevoReactivo() {
        if (!idInstrumentoActivo) { toast('Selecciona primero un instrumento', 'error'); return; }
        document.getElementById('nr-titulo').textContent = 'Nuevo Reactivo';
        document.getElementById('nr-texto').value        = '';
        document.getElementById('nr-puntaje').value      = '0';
        document.querySelectorAll('input[name="nr-indicador"]').forEach(function (r) { r.checked = r.value === 'E'; });
        _nrFilaEd = null;
        nrOverlay.style.display = '';
        // Siguiente ID
        document.getElementById('nr-id').value = '...';
        ajaxGet({ accion:'listar_reactivos', idInstrumento: idInstrumentoActivo }).then(function (res) {
            var maxId = 0;
            if (res.status === 'ok') res.datos.forEach(function (r) {
                var id = parseInt(r.idReactivo, 10);
                if (id > maxId) maxId = id;
            });
            document.getElementById('nr-id').value = maxId + 1;
        });
        setTimeout(function () { document.getElementById('nr-texto').focus(); }, 60);
    }
 
    function cerrarNuevoReactivo() { nrOverlay.style.display = 'none'; _nrFilaEd = null; }
 
    function abrirModificarReactivo(fila) {
        var cs = Array.from(fila.cells);
        document.getElementById('nr-titulo').textContent = 'Modificar Reactivo';
        document.getElementById('nr-id').value           = cs[0] ? cs[0].textContent.trim() : '';
        document.getElementById('nr-texto').value        = cs[1] ? cs[1].textContent.trim() : '';
        document.getElementById('nr-puntaje').value      = cs[3] ? cs[3].textContent.trim() : '0';
        var indAct = cs[2] ? cs[2].textContent.trim() : 'E';
        document.querySelectorAll('input[name="nr-indicador"]').forEach(function (r) { r.checked = r.value === indAct; });
        _nrFilaEd = fila;
        nrOverlay.style.display = '';
        setTimeout(function () { document.getElementById('nr-texto').focus(); }, 60);
    }
 
    document.getElementById('btn-add-reactivo').addEventListener('click', abrirNuevoReactivo);
    nrClose.addEventListener('click', cerrarNuevoReactivo);
    nrBtnCancel.addEventListener('click', cerrarNuevoReactivo);
    nrOverlay.addEventListener('click', function (e) { if (e.target === nrOverlay) cerrarNuevoReactivo(); });
 
    nrBtnSave.addEventListener('click', function () {
        var enunciado = document.getElementById('nr-texto').value.trim();
        var puntaje   = document.getElementById('nr-puntaje').value.trim();
        var radioSel  = document.querySelector('input[name="nr-indicador"]:checked');
        var indicador = radioSel ? radioSel.value : '';
 
        if (!enunciado) { document.getElementById('nr-texto').style.borderColor='#cc2200'; document.getElementById('nr-texto').focus(); return; }
 
        if (_nrFilaEd) {
            var id = parseInt(_nrFilaEd.dataset.id, 10);
            ajaxPost({ accion:'actualizar_reactivo', id:id, enunciado:enunciado, indicador:indicador, puntajeMaximo:puntaje })
                .then(function (res) {
                    if (res.status !== 'ok') { toast(res.mensaje || 'Error', 'error'); return; }
                    var cs = Array.from(_nrFilaEd.cells);
                    if (cs[1]) cs[1].textContent = enunciado;
                    if (cs[2]) cs[2].textContent = indicador;
                    if (cs[3]) cs[3].textContent = puntaje;
                    toast('Reactivo actualizado');
                    cerrarNuevoReactivo();
                }).catch(function () { toast('Error de red', 'error'); });
        } else {
            ajaxPost({ accion:'crear_reactivo', idInstrumento:idInstrumentoActivo, enunciado:enunciado, indicador:indicador, puntajeMaximo:puntaje })
                .then(function (res) {
                    if (res.status !== 'ok') { toast(res.mensaje || 'Error', 'error'); return; }
                    cargarReactivos(idInstrumentoActivo);
                    toast('Reactivo guardado');
                    cerrarNuevoReactivo();
                }).catch(function () { toast('Error de red', 'error'); });
        }
    });
 
    document.getElementById('nr-texto').addEventListener('input', function () { this.style.borderColor = ''; });
 
    /* ── 8. MODAL CONFIRMACIÓN ELIMINAR ───────────────────── */
    var ecOverlay = document.getElementById('ec-overlay');
    var ecBtnNo   = document.getElementById('ec-btn-no');
    var ecBtnSi   = document.getElementById('ec-btn-si');
    var _ecPend   = null;
 
    function abrirConfirmarEliminar(fila, tipo) {
        _ecPend = { fila:fila, tipo:tipo };
        var p1 = document.querySelector('#ec-overlay .ec-body p:first-child');
        var p2 = document.querySelector('#ec-overlay .ec-body p.ec-nota');
        if (tipo === 'reactivo') {
            p1.textContent = '¿Confirma eliminar el reactivo seleccionado?';
            if (p2) p2.innerHTML = '<strong>Nota:</strong> También se eliminarán sus criterios.';
        } else if (tipo === 'criterio') {
            p1.textContent = '¿Confirma eliminar el criterio seleccionado?';
            if (p2) p2.textContent = '';
        } else {
            p1.textContent = '¿Confirma eliminar el instrumento de evaluación seleccionado?';
            if (p2) p2.innerHTML = '<strong>Nota:</strong> Se eliminarán los reactivos y criterios exclusivos de este instrumento.';
        }
        ecOverlay.style.display = '';
        ecBtnNo.focus();
    }
 
    function cerrarConfirmarEliminar() { ecOverlay.style.display = 'none'; _ecPend = null; }
 
    ecBtnNo.addEventListener('click', cerrarConfirmarEliminar);
    ecOverlay.addEventListener('click', function (e) { if (e.target === ecOverlay) cerrarConfirmarEliminar(); });
 
    ecBtnSi.addEventListener('click', function () {
        if (!_ecPend) return;
        var fila = _ecPend.fila, tipo = _ecPend.tipo;
        var id   = parseInt(fila.dataset.id, 10);
        var mapa = { creado:'eliminar_instrumento', reactivo:'eliminar_reactivo', criterio:'eliminar_criterio' };
 
        ajaxPost({ accion:mapa[tipo], id:id }).then(function (res) {
            if (res.status !== 'ok') { toast(res.mensaje || 'No se pudo eliminar', 'error'); return; }
            fila.style.transition = 'opacity .25s';
            fila.style.opacity    = '0';
            setTimeout(function () {
                fila.remove();
                if (tipo === 'creado')   { document.getElementById('tbody-reactivos').innerHTML=''; limpiarCriterios(); idInstrumentoActivo=null; }
                if (tipo === 'reactivo') { limpiarCriterios(); idReactivoActivo=null; if(idInstrumentoActivo) actualizarConteoReactivos(idInstrumentoActivo, document.querySelectorAll('#tbody-reactivos tr').length); }
            }, 260);
            toast('Registro eliminado');
        }).catch(function () { toast('Error de red', 'error'); });
 
        cerrarConfirmarEliminar();
    });
 
    /* ── 9. MODAL CRITERIO ────────────────────────────────── */
    var ncOverlay   = document.getElementById('nc-overlay');
    var ncClose     = document.getElementById('nc-close');
    var ncBtnCancel = document.getElementById('nc-btn-cancel');
    var ncBtnSave   = document.getElementById('nc-btn-save');
    var _ncFilaEd   = null;
 
    function abrirNuevoCriterio() {
        if (!idReactivoActivo) { toast('Selecciona primero un reactivo', 'error'); return; }
        document.getElementById('nc-titulo').textContent = 'Nuevo Criterio';
        document.getElementById('nc-puntaje').value      = '0';
        document.getElementById('nc-retro').value        = '';
        _ncFilaEd = null;
        ncOverlay.style.display = '';
        // Siguiente ID
        document.getElementById('nc-id').value = '...';
        ajaxGet({ accion:'listar_criterios', idReactivo: idReactivoActivo }).then(function (res) {
            var maxId = 0;
            if (res.status === 'ok') res.datos.forEach(function (r) {
                var id = parseInt(r.idCriterio, 10);
                if (id > maxId) maxId = id;
            });
            document.getElementById('nc-id').value = maxId + 1;
        });
        setTimeout(function () { document.getElementById('nc-puntaje').focus(); }, 60);
    }
 
    function cerrarNuevoCriterio() { ncOverlay.style.display = 'none'; _ncFilaEd = null; }
 
    function abrirModificarCriterio(fila) {
        var cs = Array.from(fila.cells);
        document.getElementById('nc-titulo').textContent = 'Modificar Criterio';
        document.getElementById('nc-id').value           = cs[0] ? cs[0].textContent.trim() : '';
        document.getElementById('nc-puntaje').value      = cs[1] ? cs[1].textContent.trim() : '0';
        document.getElementById('nc-retro').value        = cs[2] ? cs[2].textContent.trim() : '';
        _ncFilaEd = fila;
        ncOverlay.style.display = '';
        setTimeout(function () { document.getElementById('nc-puntaje').focus(); }, 60);
    }
 
    document.getElementById('btn-add-criterio').addEventListener('click', abrirNuevoCriterio);
    ncClose.addEventListener('click', cerrarNuevoCriterio);
    ncBtnCancel.addEventListener('click', cerrarNuevoCriterio);
    ncOverlay.addEventListener('click', function (e) { if (e.target === ncOverlay) cerrarNuevoCriterio(); });
 
    ncBtnSave.addEventListener('click', function () {
        var puntaje = document.getElementById('nc-puntaje').value.trim();
        var retro   = document.getElementById('nc-retro').value.trim();
        if (!retro) { document.getElementById('nc-retro').style.borderColor='#cc2200'; document.getElementById('nc-retro').focus(); return; }
 
        if (_ncFilaEd) {
            var id = parseInt(_ncFilaEd.dataset.id, 10);
            ajaxPost({ accion:'actualizar_criterio', id:id, puntajeObtenido:puntaje, retroalimentacion:retro })
                .then(function (res) {
                    if (res.status !== 'ok') { toast(res.mensaje || 'Error', 'error'); return; }
                    var cs = Array.from(_ncFilaEd.cells);
                    if (cs[1]) cs[1].textContent = puntaje;
                    if (cs[2]) cs[2].textContent = retro;
                    toast('Criterio actualizado');
                    cerrarNuevoCriterio();
                }).catch(function () { toast('Error de red', 'error'); });
        } else {
            ajaxPost({ accion:'crear_criterio', idReactivo:idReactivoActivo, puntajeObtenido:puntaje, retroalimentacion:retro })
                .then(function (res) {
                    if (res.status !== 'ok') { toast(res.mensaje || 'Error', 'error'); return; }
                    cargarCriterios(idReactivoActivo);
                    toast('Criterio guardado');
                    cerrarNuevoCriterio();
                }).catch(function () { toast('Error de red', 'error'); });
        }
    });
 
    document.getElementById('nc-retro').addEventListener('input', function () { this.style.borderColor = ''; });
 
    /* ── 10. DELEGACIÓN MODIFICAR / ELIMINAR ─────────────── */
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.btn-op');
        if (!btn) return;
        var tipo = btn.dataset.tipo;
        var fila = btn.closest('tr');
        if (btn.classList.contains('btn-eliminar')) { abrirConfirmarEliminar(fila, tipo); return; }
        if (btn.classList.contains('btn-modificar')) {
            if (tipo === 'creado')   { abrirModificarInstrumento(fila); return; }
            if (tipo === 'reactivo') { abrirModificarReactivo(fila);    return; }
            if (tipo === 'criterio') { abrirModificarCriterio(fila);    return; }
        }
    });
 
    /* ── 11. ESCAPE GLOBAL ────────────────────────────────── */
    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') return;
        [niOverlay, nrOverlay, ncOverlay, ecOverlay].forEach(function (ov) {
            if (ov && ov.style.display !== 'none') ov.style.display = 'none';
        });
    });
 
    /* ── INIT ─────────────────────────────────────────────── */
    cargarInstrumentos();
 
})();