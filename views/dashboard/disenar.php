<?php
// views/dashboard/disenar.php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/DashboardController.php';

DashboardController::verificarSesion();
$usuario = DashboardController::usuarioSesion();

$pageTitle = 'Diseñar';
$subtitulo  = APP_SUBNAME;
require __DIR__ . '/../_partials/header.php';
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/public/css/disenar.css">
 
<div class="app-body">
    <?php require __DIR__ . '/../_partials/sidebar.php'; ?>
 
    <section class="content-area">
        <h2>Diseñar</h2>
 
        <!-- ===== SECCIÓN: CREADOS ===== -->
        <div class="panel" id="panel-creados">
            <div class="panel-header">
                <span class="panel-title">Creados</span>
                <button class="btn-add" id="btn-add-creado" title="Agregar instrumento">
                    <span>+</span>
                </button>
            </div>
            <div class="panel-body">
                <table class="tabla-sintesu" id="tabla-creados">
                    <thead>
                        <tr>
                            <th class="col-sort" data-col="id">ID <span class="sort-icon">⇅</span></th>
                            <th class="col-sort" data-col="reticula">Retícula <span class="sort-icon">⇅</span></th>
                            <th class="col-sort" data-col="clave">Clave <span class="sort-icon">⇅</span></th>
                            <th class="col-sort" data-col="nombre">Nombre <span class="sort-icon">⇅</span></th>
                            <th class="col-sort" data-col="docentes">Docentes <span class="sort-icon">⇅</span></th>
                            <th class="col-sort" data-col="reactivos">Reactivos <span class="sort-icon">⇅</span></th>
                            <th>Operaciones</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-creados">
                        <tr class="fila-seleccionada" data-id="276">
                            <td>276</td>
                            <td>ISIC-2010-224</td>
                            <td>EF1-01</td>
                            <td>ProgWeb: Línea del Tiempo</td>
                            <td>Héctor</td>
                            <td>3</td>
                            <td class="td-ops">
                                <button class="btn-op btn-modificar" data-id="276" data-tipo="creado">✏️ Modific</button>
                                <button class="btn-op btn-eliminar" data-id="276" data-tipo="creado">🗑️ Elimin</button>
                            </td>
                        </tr>
                        <tr data-id="314">
                            <td>314</td>
                            <td>ISIC-2010-224</td>
                            <td>EF3-03</td>
                            <td>FundProg: Prácticas Ciclos</td>
                            <td>Héctor-Mario</td>
                            <td>7</td>
                            <td class="td-ops">
                                <button class="btn-op btn-modificar" data-id="314" data-tipo="creado">✏️ Modific</button>
                                <button class="btn-op btn-eliminar" data-id="314" data-tipo="creado">🗑️ Elimin</button>
                            </td>
                        </tr>
                        <tr data-id="201">
                            <td>201</td>
                            <td>ISIC-2010-224</td>
                            <td>EF2-01</td>
                            <td>Leng&amp;Auto: Ejercicios ExpreRegu</td>
                            <td>Héctor-Eliud</td>
                            <td>6</td>
                            <td class="td-ops">
                                <button class="btn-op btn-modificar" data-id="201" data-tipo="creado">✏️ Modific</button>
                                <button class="btn-op btn-eliminar" data-id="201" data-tipo="creado">🗑️ Elimin</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
 
        <!-- ===== FILA INFERIOR: REACTIVOS + CRITERIOS ===== -->
        <div class="panel-row">
 
            <!-- REACTIVOS -->
            <div class="panel" id="panel-reactivos">
                <div class="panel-header">
                    <span class="panel-title">Reactivos</span>
                    <button class="btn-add" id="btn-add-reactivo" title="Agregar reactivo">
                        <span>+</span>
                    </button>
                </div>
                <div class="panel-body">
                    <table class="tabla-sintesu" id="tabla-reactivos">
                        <thead>
                            <tr>
                                <th class="col-sort" data-col="id">ID <span class="sort-icon">⇅</span></th>
                                <th class="col-sort" data-col="texto">Texto <span class="sort-icon">⇅</span></th>
                                <th class="col-sort" data-col="indicador">Indicador Alcance <span class="sort-icon">⇅</span></th>
                                <th class="col-sort" data-col="punto">Punto Máxim <span class="sort-icon">⇅</span></th>
                                <th>Opera</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-reactivos">
                            <tr class="fila-seleccionada" data-id="01">
                                <td>01</td>
                                <td class="td-texto">contiene las fuentes consul</td>
                                <td>E</td>
                                <td>5</td>
                                <td class="td-ops">
                                    <button class="btn-op btn-modificar" data-id="01" data-tipo="reactivo">Mc</button>
                                    <button class="btn-op btn-eliminar" data-id="01" data-tipo="reactivo">El</button>
                                </td>
                            </tr>
                            <tr data-id="02">
                                <td>02</td>
                                <td class="td-texto">entregó en tiempo y formato</td>
                                <td>F</td>
                                <td>10</td>
                                <td class="td-ops">
                                    <button class="btn-op btn-modificar" data-id="02" data-tipo="reactivo">Mc</button>
                                    <button class="btn-op btn-eliminar" data-id="02" data-tipo="reactivo">El</button>
                                </td>
                            </tr>
                            <tr data-id="03">
                                <td>03</td>
                                <td class="td-texto">contiene las estructuras ind</td>
                                <td></td>
                                <td>15</td>
                                <td class="td-ops">
                                    <button class="btn-op btn-modificar" data-id="03" data-tipo="reactivo">Mc</button>
                                    <button class="btn-op btn-eliminar" data-id="03" data-tipo="reactivo">El</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
 
            <!-- CRITERIOS -->
            <div class="panel" id="panel-criterios">
                <div class="panel-header">
                    <span class="panel-title">Criterios</span>
                    <button class="btn-add" id="btn-add-criterio" title="Agregar criterio">
                        <span>+</span>
                    </button>
                </div>
                <div class="panel-body">
                    <table class="tabla-sintesu" id="tabla-criterios">
                        <thead>
                            <tr>
                                <th class="col-sort" data-col="id">II <span class="sort-icon">⇅</span></th>
                                <th class="col-sort" data-col="puntos">Puntos Sugeri <span class="sort-icon">⇅</span></th>
                                <th class="col-sort" data-col="texto">Texto <span class="sort-icon">⇅</span></th>
                                <th>Opera</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-criterios">
                            <tr data-id="01">
                                <td>01</td>
                                <td>0</td>
                                <td class="td-texto">Falta indicar las fuentes consultada</td>
                                <td class="td-ops">
                                    <button class="btn-op btn-modificar" data-id="01" data-tipo="criterio">Mc</button>
                                    <button class="btn-op btn-eliminar" data-id="01" data-tipo="criterio">El</button>
                                </td>
                            </tr>
                            <tr class="fila-seleccionada" data-id="02">
                                <td>02</td>
                                <td>3</td>
                                <td class="td-texto">No se tiene el número de fuentes so</td>
                                <td class="td-ops">
                                    <button class="btn-op btn-modificar" data-id="02" data-tipo="criterio">Mc</button>
                                    <button class="btn-op btn-eliminar" data-id="02" data-tipo="criterio">El</button>
                                </td>
                            </tr>
                            <tr data-id="03">
                                <td>03</td>
                                <td>4</td>
                                <td class="td-texto">Las fuentes no están en formato AP</td>
                                <td class="td-ops">
                                    <button class="btn-op btn-modificar" data-id="03" data-tipo="criterio">Mc</button>
                                    <button class="btn-op btn-eliminar" data-id="03" data-tipo="criterio">El</button>
                                </td>
                            </tr>
                            <tr data-id="04">
                                <td>04</td>
                                <td>5</td>
                                <td class="td-texto">Las fuentes consultadas están com</td>
                                <td class="td-ops">
                                    <button class="btn-op btn-modificar" data-id="04" data-tipo="criterio">Mc</button>
                                    <button class="btn-op btn-eliminar" data-id="04" data-tipo="criterio">El</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
 
        </div><!-- /panel-row -->
 
    </section>
</div>
 
<!-- ===== MODAL: Nuevo Instrumento (tabla Creados) ===== -->
<div class="ni-overlay" id="ni-overlay" style="display:none;" role="dialog" aria-modal="true" aria-labelledby="ni-titulo">
    <div class="ni-box" id="ni-box">
 
        <!-- Barra de título -->
        <div class="ni-header">
            <span id="ni-titulo">Nuevo Instrumento</span>
            <button class="ni-close" id="ni-close" title="Cerrar">✕</button>
        </div>
 
        <!-- Cuerpo del formulario -->
        <div class="ni-body">
 
            <div class="ni-row">
                <label class="ni-label" for="ni-id">ID:</label>
                <input class="ni-input" id="ni-id" type="text" placeholder="Autogenerado" readonly>
            </div>
 
            <div class="ni-row">
                <label class="ni-label" for="ni-clave">Clave:</label>
                <input class="ni-input" id="ni-clave" type="text" placeholder="EF1-01">
            </div>
 
            <div class="ni-row">
                <label class="ni-label" for="ni-nombre">Nombre:</label>
                <input class="ni-input" id="ni-nombre" type="text" placeholder="Nombre del instrumento">
            </div>
 
            <div class="ni-row ni-row-area">
                <label class="ni-label" for="ni-indicaciones">Indicaciones:</label>
                <textarea class="ni-textarea" id="ni-indicaciones" rows="4" placeholder="Indicaciones generales..."></textarea>
            </div>
 
        </div>
 
        <!-- Pie: botones X (cancelar) y 💾 (guardar) -->
        <div class="ni-footer">
            <button class="ni-btn ni-btn-cancel" id="ni-btn-cancel" title="Cancelar">
                ✕
            </button>
            <button class="ni-btn ni-btn-save" id="ni-btn-save" title="Guardar">
                💾
            </button>
        </div>
 
    </div>
</div>
 
<!-- ===== MODAL: Nuevo Reactivo (tabla Reactivos) ===== -->
<div class="ni-overlay" id="nr-overlay" style="display:none;" role="dialog" aria-modal="true" aria-labelledby="nr-titulo">
    <div class="ni-box" id="nr-box">
 
        <div class="ni-header">
            <span id="nr-titulo">Nuevo Reactivo</span>
            <button class="ni-close" id="nr-close" title="Cerrar">✕</button>
        </div>
 
        <div class="ni-body">
 
            <div class="ni-row">
                <label class="ni-label" for="nr-id">ID:</label>
                <input class="ni-input" id="nr-id" type="text" placeholder="Autogenerado" readonly>
            </div>
 
            <div class="ni-row">
                <label class="ni-label" for="nr-texto">Texto:</label>
                <input class="ni-input" id="nr-texto" type="text" placeholder="Descripción del reactivo">
            </div>
 
            <div class="ni-row">
                <label class="ni-label">Indicador de Al:</label>
                <div class="nr-radio-group">
                    <label class="nr-radio"><input type="radio" name="nr-indicador" value="A"> A</label>
                    <label class="nr-radio"><input type="radio" name="nr-indicador" value="B"> B</label>
                    <label class="nr-radio"><input type="radio" name="nr-indicador" value="C"> C</label>
                    <label class="nr-radio"><input type="radio" name="nr-indicador" value="D"> D</label>
                    <label class="nr-radio"><input type="radio" name="nr-indicador" value="E" checked> E</label>
                </div>
            </div>
 
            <div class="ni-row">
                <label class="ni-label" for="nr-puntaje">Puntaje Máximo:</label>
                <input class="ni-input" id="nr-puntaje" type="number" min="0" value="0">
            </div>
 
        </div>
 
        <div class="ni-footer">
            <button class="ni-btn ni-btn-cancel" id="nr-btn-cancel" title="Cancelar">✕</button>
            <button class="ni-btn ni-btn-save"   id="nr-btn-save"   title="Guardar">💾</button>
        </div>
 
    </div>
</div>
 
<!-- ===== MODAL: Confirmación Eliminar Instrumento ===== -->
<div class="ni-overlay" id="ec-overlay" style="display:none;" role="dialog" aria-modal="true" aria-labelledby="ec-titulo">
    <div class="ni-box ec-box">
 
        <div class="ni-header">
            <span id="ec-titulo">Pregunta</span>
        </div>
 
        <div class="ni-body ec-body">
            <p>
                ¿Confirma eliminar el instrumento de evaluación seleccionado?
            </p>
            <p class="ec-nota">
                <strong>Nota:</strong> Al eliminar también se eliminarán los reactivos y criterios que no estén incorporados a otro instrumento de evaluación.
            </p>
        </div>
 
        <div class="ec-footer">
            <button class="ec-btn ec-btn-no" id="ec-btn-no">No</button>
            <button class="ec-btn ec-btn-si" id="ec-btn-si">Si</button>
        </div>
 
    </div>
</div>
 
<!-- ===== MODAL: Nuevo Criterio (tabla Criterios) ===== -->
<div class="ni-overlay" id="nc-overlay" style="display:none;" role="dialog" aria-modal="true" aria-labelledby="nc-titulo">
    <div class="ni-box" id="nc-box">
 
        <div class="ni-header">
            <span id="nc-titulo">Nuevo Criterio</span>
            <button class="ni-close" id="nc-close" title="Cerrar">✕</button>
        </div>
 
        <div class="ni-body">
 
            <div class="ni-row">
                <label class="ni-label" for="nc-id">ID:</label>
                <input class="ni-input" id="nc-id" type="text" placeholder="Autogenerado" readonly>
            </div>
 
            <div class="ni-row">
                <label class="ni-label" for="nc-puntaje">Puntaje Obtenido:</label>
                <input class="ni-input" id="nc-puntaje" type="number" min="0" value="0">
            </div>
 
            <div class="ni-row ni-row-area">
                <label class="ni-label" for="nc-retro">Retroalimentación:</label>
                <textarea class="ni-textarea" id="nc-retro" rows="4" placeholder="Descripción del criterio..."></textarea>
            </div>
 
        </div>
 
        <div class="ni-footer">
            <button class="ni-btn ni-btn-cancel" id="nc-btn-cancel" title="Cancelar">✕</button>
            <button class="ni-btn ni-btn-save"   id="nc-btn-save"   title="Guardar">💾</button>
        </div>
 
    </div>
</div>
 
<!-- Modal genérico para editar (Reactivos / Criterios) -->
<div class="modal-overlay" id="modal-overlay" style="display:none;">
    <div class="modal-box" id="modal-box">
        <div class="modal-header">
            <span id="modal-titulo">Formulario</span>
            <button class="modal-close" id="modal-close">✕</button>
        </div>
        <div class="modal-body" id="modal-body">
            <!-- Contenido dinámico vía JS -->
        </div>
        <div class="modal-footer">
            <button class="btn-modal-cancel" id="btn-modal-cancel">Cancelar</button>
            <button class="btn-modal-ok" id="btn-modal-ok">Guardar</button>
        </div>
    </div>
</div>

<script src="<?= BASE_URL ?>/public/js/dashboard.js"></script>
<script src="<?= BASE_URL ?>/public/js/disenar.js?v=2"></script>
<?php require __DIR__ . '/../_partials/footer.php'; ?>
