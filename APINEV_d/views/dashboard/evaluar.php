<?php
// views/dashboard/evaluar.php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/DashboardController.php';

DashboardController::verificarSesion();
$usuario = DashboardController::usuarioSesion();

$pageTitle = 'Evaluar';
$subtitulo  = APP_SUBNAME;
require __DIR__ . '/../_partials/header.php';
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/public/css/evaluar.css">

<div class="app-body">
    <?php require __DIR__ . '/../_partials/sidebar.php'; ?>

    <section class="content-area ev-content">
        <h2>Evaluar</h2>

        <!-- ===== FILA SUPERIOR: DROPDOWNS ===== -->
        <div class="ev-dropdowns">

            <div class="ev-dropdown-panel">
                <span class="ev-group-label">Grupos</span>
                <div class="ev-select-wrap">
                    <select class="ev-select" id="sel-grupos">
                        <option value="">— Selecciona un grupo —</option>
                    </select>
                </div>
            </div>

            <div class="ev-dropdown-panel">
                <span class="ev-group-label">Instrumento Evaluación</span>
                <div class="ev-select-wrap">
                    <select class="ev-select" id="sel-instrumento">
                        <option value="">— Selecciona un instrumento —</option>
                    </select>
                </div>
            </div>

        </div>

        <!-- ===== FILA INFERIOR: TABLAS ===== -->
        <div class="ev-tables-row">

            <!-- ── Panel ALUMNOS ── -->
            <div class="ev-panel" id="ev-panel-alumnos">
                <div class="ev-panel-header">
                    <span class="ev-panel-title">Alumnos</span>
                </div>

                <div class="ev-nav-bar">
                    <button class="ev-nav-btn" id="ev-alumnos-prev">
                        <span class="ev-nav-arrow">&#9664;</span> Anterior
                    </button>
                    <button class="ev-nav-btn ev-nav-right" id="ev-alumnos-next">
                        Siguiente <span class="ev-nav-arrow">&#9654;</span>
                    </button>
                </div>

                <div class="ev-panel-body">
                    <table class="ev-tabla" id="tabla-alumnos">
                        <thead>
                            <tr>
                                <th class="ev-col-sort" data-col="0">ID <span class="ev-sort-icon">⇅</span></th>
                                <th class="ev-col-sort" data-col="1">Matrícula <span class="ev-sort-icon">⇅</span></th>
                                <th class="ev-col-sort" data-col="2">Nombre <span class="ev-sort-icon">⇅</span></th>
                                <th class="ev-col-sort" data-col="3">Puntos Obtenid <span class="ev-sort-icon">⇅</span></th>
                            </tr>
                        </thead>
                        <tbody id="tbody-alumnos">
                            <tr><td colspan="4" style="text-align:center;color:#aaa;">Selecciona un grupo e instrumento</td></tr>
                        </tbody>
                    </table>
                </div>

                <!-- Botón PDF -->
                <div style="padding:6px 10px; border-top:1px solid #d8e8f4; display:flex; justify-content:flex-end;">
                    <button id="btn-generar-pdf"
                            disabled
                            style="display:flex;align-items:center;gap:6px;
                                   padding:5px 14px;border-radius:4px;
                                   background:#1f3864;color:#fff;border:none;
                                   font-size:.82rem;font-weight:600;cursor:default;
                                   opacity:.4;transition:opacity .2s,cursor .2s;">
                        📄 Generar PDF
                    </button>
                </div>
            </div>

            <!-- ── Panel REACTIVOS ── -->
            <div class="ev-panel" id="ev-panel-reactivos">
                <div class="ev-panel-header">
                    <span class="ev-panel-title">Reactivos</span>
                </div>

                <div class="ev-nav-bar">
                    <button class="ev-nav-btn" id="ev-reactivos-prev">
                        <span class="ev-nav-arrow">&#9664;</span> Anterior
                    </button>
                    <button class="ev-nav-btn ev-nav-right" id="ev-reactivos-next">
                        Siguiente <span class="ev-nav-arrow">&#9654;</span>
                    </button>
                </div>

                <div class="ev-panel-body">
                    <table class="ev-tabla" id="tabla-reactivos">
                        <thead>
                            <tr>
                                <th class="ev-col-sort" data-col="0">II <span class="ev-sort-icon">⇅</span></th>
                                <th class="ev-col-sort" data-col="1">Text <span class="ev-sort-icon">⇅</span></th>
                                <th class="ev-col-sort" data-col="2">Indicador <span class="ev-sort-icon">⇅</span></th>
                                <th class="ev-col-sort" data-col="3">Puntos Máxim <span class="ev-sort-icon">⇅</span></th>
                                <th>Opera</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-reactivos">
                            <tr><td colspan="5" style="text-align:center;color:#aaa;">Selecciona un instrumento</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    </section>
</div>

<!-- ===== MODAL EVALUAR ===== -->
<div id="ev-modal-overlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:1000; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:8px; padding:1.5rem; min-width:340px; max-width:500px; width:90%; box-shadow:0 4px 24px rgba(0,0,0,.25);">
        <h3 style="margin:0 0 .5rem; color:#1a2b4a;" id="ev-modal-titulo">Evaluar reactivo</h3>
        <p style="margin:0 0 1rem; font-size:.85rem; color:#555;" id="ev-modal-alumno"></p>

        <div style="font-weight:600; margin-bottom:.5rem; font-size:.9rem;">Selecciona el criterio:</div>
        <div id="ev-modal-criterios" style="display:flex; flex-direction:column; gap:.4rem; max-height:240px; overflow-y:auto;"></div>

        <div style="display:flex; gap:.75rem; justify-content:flex-end; margin-top:1.2rem;">
            <button id="ev-modal-cancelar" style="padding:.45rem 1.1rem; border:1px solid #ccc; border-radius:5px; cursor:pointer; background:#fff;">Cancelar</button>
            <button id="ev-modal-guardar"  style="padding:.45rem 1.1rem; background:#27ae60; color:#fff; border:none; border-radius:5px; cursor:pointer; font-weight:600;">Guardar</button>
        </div>
    </div>
</div>

<script src="<?= BASE_URL ?>/public/js/dashboard.js"></script>
<script src="<?= BASE_URL ?>/public/js/evaluar.js"></script>
<?php require __DIR__ . '/../_partials/footer.php'; ?>