/**
 * public/js/evaluar.js — Módulo Evaluar SINTESU/APINEV
 * CRUD completo con persistencia en BD. Sin frameworks.
 */
(function () {
    'use strict';

    const meta = document.querySelector('meta[name="base-url"]');
    const BASE  = meta ? meta.content : '';
    const API   = BASE + '/controllers/EvaluarController.php';

    // Estado global
    let idGrupoActivo       = null;
    let idInstrumentoActivo = null;
    let idAlumnoActivo      = null;
    let idReactivoActivo    = null;

    /* ── AJAX helpers ───────────────────────────────────── */
    function ajaxGet(params) {
        return fetch(API + '?' + new URLSearchParams(params).toString(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).then(r => r.json());
    }

    function ajaxPost(datos) {
        return fetch(API, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: new URLSearchParams(datos)
        }).then(r => r.json());
    }

    /* ── Toast ──────────────────────────────────────────── */
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
        setTimeout(() => { t.style.opacity = '0'; }, 2300);
        setTimeout(() => { t.remove(); }, 2800);
    }

    /* ── 1. CARGAR DROPDOWNS AL INICIO ─────────────────── */
    function cargarGrupos() {
        ajaxGet({ accion: 'listar_grupos' }).then(resp => {
            if (resp.status !== 'ok') return;
            const sel = document.getElementById('sel-grupos');
            resp.datos.forEach(g => {
                const opt = document.createElement('option');
                opt.value = g.idGrupo;
                opt.textContent = g.nombre;
                sel.appendChild(opt);
            });
        });
    }

    function cargarInstrumentos() {
        ajaxGet({ accion: 'listar_instrumentos' }).then(resp => {
            if (resp.status !== 'ok') return;
            const sel = document.getElementById('sel-instrumento');
            resp.datos.forEach(i => {
                const opt = document.createElement('option');
                opt.value = i.idInstrumentoEvaluacion;
                opt.textContent = i.nombre;
                sel.appendChild(opt);
            });
        });
    }

    cargarGrupos();
    cargarInstrumentos();

    /* ── 2. CAMBIO EN SELECTS → recargar alumnos/reactivos ── */
    function onSelectChange() {
        idGrupoActivo       = parseInt(document.getElementById('sel-grupos').value)      || null;
        idInstrumentoActivo = parseInt(document.getElementById('sel-instrumento').value) || null;
        idAlumnoActivo      = null;
        idReactivoActivo    = null;

        if (idGrupoActivo && idInstrumentoActivo) {
            cargarAlumnos(idGrupoActivo, idInstrumentoActivo);
            cargarReactivos(idInstrumentoActivo);
        } else if (idInstrumentoActivo) {
            cargarReactivos(idInstrumentoActivo);
            limpiarTabla('tbody-alumnos', 4, 'Selecciona un grupo');
        } else {
            limpiarTabla('tbody-alumnos',   4, 'Selecciona un grupo e instrumento');
            limpiarTabla('tbody-reactivos', 5, 'Selecciona un instrumento');
        }
    }

    document.getElementById('sel-grupos').addEventListener('change', onSelectChange);
    document.getElementById('sel-instrumento').addEventListener('change', onSelectChange);

    /* ── 3. CARGAR ALUMNOS ─────────────────────────────── */
    function cargarAlumnos(idGrupo, idInstrumento) {
        const tbody = document.getElementById('tbody-alumnos');
        tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;color:#aaa;">Cargando…</td></tr>';

        ajaxGet({ accion: 'listar_alumnos', idGrupo, idInstrumento }).then(resp => {
            tbody.innerHTML = '';
            if (!resp.datos || resp.datos.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;color:#aaa;">Sin alumnos en este grupo</td></tr>';
                return;
            }
            resp.datos.forEach((a, idx) => {
                const tr = document.createElement('tr');
                tr.dataset.id = a.idUsuario;
                tr.innerHTML = `
                    <td>${idx + 1}</td>
                    <td>${a.matricula}</td>
                    <td>${a.nombre}</td>
                    <td class="ev-td-puntos">${a.puntosObtenidos}</td>`;
                tbody.appendChild(tr);
            });
            configurarNavegacion('tbody-alumnos', 'ev-alumnos-prev', 'ev-alumnos-next');
            activarSeleccionFila('tbody-alumnos', fila => {
                idAlumnoActivo = parseInt(fila.dataset.id, 10);
            });
        });
    }

    /* ── 4. CARGAR REACTIVOS ───────────────────────────── */
    function cargarReactivos(idInstrumento) {
        const tbody = document.getElementById('tbody-reactivos');
        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;color:#aaa;">Cargando…</td></tr>';

        ajaxGet({ accion: 'listar_reactivos', idInstrumento }).then(resp => {
            tbody.innerHTML = '';
            if (!resp.datos || resp.datos.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;color:#aaa;">Sin reactivos en este instrumento</td></tr>';
                return;
            }
            resp.datos.forEach((r, idx) => {
                const tr = document.createElement('tr');
                tr.dataset.id = r.idReactivo;
                tr.innerHTML = `
                    <td>${String(idx + 1).padStart(2,'0')}</td>
                    <td class="ev-td-texto">${r.enunciado}</td>
                    <td>${r.indicador}</td>
                    <td>${r.puntajeMaximo}</td>
                    <td class="ev-td-ops">
                        <button class="ev-btn-op ev-btn-ev" data-id="${r.idReactivo}" title="Evaluar">Ev</button>
                    </td>`;
                tbody.appendChild(tr);
            });
            configurarNavegacion('tbody-reactivos', 'ev-reactivos-prev', 'ev-reactivos-next');
            activarSeleccionFila('tbody-reactivos', fila => {
                idReactivoActivo = parseInt(fila.dataset.id, 10);
            });
        });
    }

    /* ── 5. BOTÓN "Ev" → abrir modal de criterios ──────── */
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.ev-btn-ev');
        if (!btn) return;

        if (!idAlumnoActivo) {
            toast('Selecciona primero un alumno.', 'error'); return;
        }
        if (!idGrupoActivo || !idInstrumentoActivo) {
            toast('Selecciona grupo e instrumento.', 'error'); return;
        }

        const fila = btn.closest('tr');
        idReactivoActivo = parseInt(fila ? fila.dataset.id : btn.dataset.id, 10);

        abrirModalEvaluar(idReactivoActivo);
    });

    /* ── 6. MODAL EVALUAR ───────────────────────────────── */
    let criterioSeleccionado = null;

    function abrirModalEvaluar(idReactivo) {
        criterioSeleccionado = null;

        // Nombre del alumno seleccionado
        const filaAlumno = document.querySelector('#tbody-alumnos .ev-fila-sel');
        const nombreAlumno = filaAlumno ? filaAlumno.cells[2].textContent : '(alumno)';

        // Enunciado del reactivo
        const filaReactivo = document.querySelector(`#tbody-reactivos tr[data-id="${idReactivo}"]`);
        const enunciado = filaReactivo ? filaReactivo.cells[1].textContent : 'Reactivo';

        document.getElementById('ev-modal-titulo').textContent = enunciado;
        document.getElementById('ev-modal-alumno').textContent = 'Alumno: ' + nombreAlumno;

        const contenedor = document.getElementById('ev-modal-criterios');
        contenedor.innerHTML = '<div style="color:#aaa;font-size:.85rem;">Cargando criterios…</div>';

        ajaxGet({ accion: 'listar_criterios', idReactivo }).then(resp => {
            contenedor.innerHTML = '';
            if (!resp.datos || resp.datos.length === 0) {
                contenedor.innerHTML = '<div style="color:#c0392b;font-size:.85rem;">Este reactivo no tiene criterios definidos.</div>';
                return;
            }
            resp.datos.forEach(c => {
                const div = document.createElement('div');
                div.style.cssText = 'display:flex;align-items:center;gap:.6rem;padding:.4rem .6rem;border:1px solid #dde;border-radius:5px;cursor:pointer;';
                div.dataset.criterioId = c.idCriterio;
                div.dataset.puntaje    = c.puntajeObtenido;
                div.innerHTML = `
                    <span style="font-weight:700;min-width:2rem;color:#1a2b4a;">${c.puntajeObtenido} pts</span>
                    <span style="font-size:.88rem;">${c.retroalimentacion}</span>`;

                div.addEventListener('click', () => {
                    contenedor.querySelectorAll('div[data-criterio-id]').forEach(d => {
                        d.style.background = '';
                        d.style.borderColor = '#dde';
                    });
                    div.style.background  = '#e8f5e9';
                    div.style.borderColor = '#27ae60';
                    criterioSeleccionado = { id: c.idCriterio, puntaje: c.puntajeObtenido };
                });

                contenedor.appendChild(div);
            });
        });

        const overlay = document.getElementById('ev-modal-overlay');
        overlay.style.display = 'flex';
    }

    document.getElementById('ev-modal-cancelar').addEventListener('click', () => {
        document.getElementById('ev-modal-overlay').style.display = 'none';
    });

    document.getElementById('ev-modal-overlay').addEventListener('click', e => {
        if (e.target === document.getElementById('ev-modal-overlay'))
            document.getElementById('ev-modal-overlay').style.display = 'none';
    });

    document.getElementById('ev-modal-guardar').addEventListener('click', () => {
        if (!criterioSeleccionado) {
            toast('Selecciona un criterio.', 'error'); return;
        }

        ajaxPost({
            accion:          'guardar_evaluacion',
            idEstudiante:    idAlumnoActivo,
            idGrupo:         idGrupoActivo,
            idInstrumento:   idInstrumentoActivo,
            idReactivo:      idReactivoActivo,
            idCriterio:      criterioSeleccionado.id,
            puntaje:         criterioSeleccionado.puntaje
        }).then(resp => {
            if (resp.status !== 'ok') {
                toast('Error: ' + (resp.mensaje || 'No se pudo guardar.'), 'error'); return;
            }
            // Actualizar puntos en la fila del alumno
            const filaAlumno = document.querySelector(`#tbody-alumnos tr[data-id="${idAlumnoActivo}"]`);
            if (filaAlumno) {
                filaAlumno.querySelector('.ev-td-puntos').textContent = resp.puntosObtenidos;
            }
            document.getElementById('ev-modal-overlay').style.display = 'none';
            toast('Evaluación guardada.', 'ok');
        }).catch(() => toast('Error de conexión.', 'error'));
    });

    /* ── 7. SELECCIÓN DE FILA (helper) ─────────────────── */
    function activarSeleccionFila(tbodyId, onSelect) {
        const tbody = document.getElementById(tbodyId);
        tbody.addEventListener('click', function (e) {
            const fila = e.target.closest('tr');
            if (!fila || e.target.closest('.ev-btn-op')) return;
            tbody.querySelectorAll('.ev-fila-sel').forEach(tr => tr.classList.remove('ev-fila-sel'));
            fila.classList.add('ev-fila-sel');
            if (onSelect) onSelect(fila);
        });
    }

    /* ── 8. ORDENAMIENTO POR COLUMNA ────────────────────── */
    document.addEventListener('click', function (e) {
        const th = e.target.closest('.ev-col-sort');
        if (!th) return;
        const tabla = th.closest('table');
        const tbody = tabla.querySelector('tbody');
        const ths   = Array.from(th.parentNode.querySelectorAll('th'));
        const idx   = parseInt(th.dataset.col, 10);
        const asc   = th.dataset.sortDir !== 'asc';

        ths.forEach(h => {
            h.dataset.sortDir = '';
            const ico = h.querySelector('.ev-sort-icon');
            if (ico) ico.textContent = '⇅';
        });
        th.dataset.sortDir = asc ? 'asc' : 'desc';
        const ico = th.querySelector('.ev-sort-icon');
        if (ico) ico.textContent = asc ? '▲' : '▼';

        const filas = Array.from(tbody.querySelectorAll('tr'));
        filas.sort((a, b) => {
            const ta = a.cells[idx] ? a.cells[idx].textContent.trim() : '';
            const tb = b.cells[idx] ? b.cells[idx].textContent.trim() : '';
            const na = parseFloat(ta), nb = parseFloat(tb);
            if (!isNaN(na) && !isNaN(nb)) return asc ? na - nb : nb - na;
            return asc ? ta.localeCompare(tb, 'es') : tb.localeCompare(ta, 'es');
        });
        filas.forEach(f => tbody.appendChild(f));
    });

    /* ── 9. NAVEGACIÓN ANTERIOR / SIGUIENTE ─────────────── */
    function configurarNavegacion(tbodyId, btnPrevId, btnNextId) {
        const tbody   = document.getElementById(tbodyId);
        const btnPrev = document.getElementById(btnPrevId);
        const btnNext = document.getElementById(btnNextId);

        // Limpiar listeners anteriores clonando los botones
        const prev2 = btnPrev.cloneNode(true);
        const next2 = btnNext.cloneNode(true);
        btnPrev.parentNode.replaceChild(prev2, btnPrev);
        btnNext.parentNode.replaceChild(next2, btnNext);

        function filas()          { return Array.from(tbody.querySelectorAll('tr')); }
        function filaSeleccionada() { return tbody.querySelector('.ev-fila-sel'); }

        function seleccionar(tr) {
            tbody.querySelectorAll('.ev-fila-sel').forEach(f => f.classList.remove('ev-fila-sel'));
            tr.classList.add('ev-fila-sel');
            tr.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
            actualizarBotones();
        }

        function actualizarBotones() {
            const lista = filas();
            const sel   = filaSeleccionada();
            const idx   = sel ? lista.indexOf(sel) : -1;
            prev2.disabled = idx <= 0;
            next2.disabled = idx < 0 || idx >= lista.length - 1;
        }

        prev2.addEventListener('click', () => {
            const lista = filas(), sel = filaSeleccionada();
            const idx = sel ? lista.indexOf(sel) : -1;
            if (idx > 0) seleccionar(lista[idx - 1]);
        });
        next2.addEventListener('click', () => {
            const lista = filas(), sel = filaSeleccionada();
            const idx = sel ? lista.indexOf(sel) : -1;
            if (idx < lista.length - 1) seleccionar(lista[idx + 1]);
        });

        actualizarBotones();
    }

    /* ── HELPER: tabla vacía ────────────────────────────── */
    function limpiarTabla(tbodyId, cols, msg) {
        document.getElementById(tbodyId).innerHTML =
            `<tr><td colspan="${cols}" style="text-align:center;color:#aaa;">${msg}</td></tr>`;
    }

})();