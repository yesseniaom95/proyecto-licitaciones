<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle de Licitación</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div id="app" class="container mt-5">
        <div v-if="loading" class="text-center py-5">
            <div class="spinner-border text-warning" role="status"></div>
            <p>Cargando información...</p>
        </div>

        <div v-else-if="oferta">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Licitación: {{ oferta.consecutivo }}</h2>
                <div class="d-flex gap-3">
                    <button class="btn btn-success" @click="abrirModalEditar">
                        Editar
                    </button>
                </div>
            </div>
            <ul class="nav nav-tabs">
                <li class="nav-item">
                    <button class="nav-link" :class="{active: tabActiva === 'info'}" @click="tabActiva = 'info'">Información Básica</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" :class="{active: tabActiva === 'cronograma'}" @click="tabActiva = 'cronograma'">Cronograma</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" :class="{active: tabActiva === 'docs'}" @click="tabActiva = 'docs'">Documentos</button>
                </li>
            </ul>

            <div class="tab-content border border-top-0 p-4">
                <div v-show="tabActiva === 'info'" class="tab-pane fade show active">
                    <div class="p-2"><strong>Objeto:</strong> {{ oferta.objeto }}</div>
                    <div class="p-2"><strong>Descripción:</strong> {{ oferta.descripcion }}</div>
                    <div class="p-2"><strong>Moneda:</strong> {{ oferta.moneda }}</div>
                    <div class="p-2"><strong>Presupuesto:</strong> {{ oferta.presupuesto }}</div>
                </div>
                <div v-show="tabActiva === 'cronograma'" class="tab-pane fade show active">
                    <div class="d-flex flex-row mb-3">
                        <div class="p-2"><strong>Fecha Inicio:</strong> {{ oferta.fecha_inicio }}</div>
                        <div class="p-2"><strong>Hora Inicio:</strong> {{ oferta.hora_inicio }}</div>
                    </div>
                    <div class="d-flex flex-row mb-3">
                        <div class="p-2"><strong>Fecha cierre:</strong> {{ oferta.fecha_cierre }}</div>
                        <div class="p-2"><strong>Hora cierre:</strong> {{ oferta.hora_cierre}}</div>
                    </div>
                    <p> </p>
                    <p></p>
                </div>
                <div v-show="tabActiva === 'docs'" class="tab-pane fade show active">
                    <div v-show="tabActiva === 'docs'" class="tab-pane fade show active">
                        <div v-if="oferta.documentos && oferta.documentos.length > 0">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Título</th>
                                        <th>Descripción</th>
                                        <th>Archivo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="doc in oferta.documentos" :key="doc.id">
                                        <td>{{ doc.titulo }}</td>
                                        <td>{{ doc.descripcion }}</td>
                                        <!-- Actualmente no es funcional el botón  -->
                                        <td>
                                            <a :href="'/uploads/' + doc.archivo" target="_blank" class="btn btn-sm btn-outline-danger">
                                                Ver PDF
                                            </a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div v-else class="text-center p-4">
                            <p class="text-muted">No hay documentos cargados para esta licitación.</p>
                        </div>
                    </div>
                </div>
            </div> 
        </div>
    
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg shadow-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">
                    <i class="bi bi-pencil-square"></i> Editar Licitación: {{ ofertaEdit.consecutivo }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-4">
                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <h6 class="text-primary border-bottom pb-2">1. Información Básica</h6>
                    </div>
                    
                    <div class="col-md-8">
                        <label class="form-label fw-bold">Objeto de la Licitación</label>
                        <input type="text" class="form-control" v-model="ofertaEdit.objeto" maxlength="150">
                        <div class="text-end small text-muted">{{ ofertaEdit.objeto?.length || 0 }} / 150</div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Presupuesto</label>
                        <div class="input-group">
                            <select v-model="ofertaEdit.moneda" class="form-select" style="max-width: 90px;">
                                <option value="COP">COP</option>
                                <option value="USD">USD</option>
                                <option value="EUR">EUR</option>
                            </select>
                            <input type="number" v-model="ofertaEdit.presupuesto" class="form-control" step="0.01">
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-bold">Actividad Relacionada</label>
                        <select v-model="ofertaEdit.actividad_id" class="form-select" required>
                            <option value="">-- Seleccione una actividad --</option>
                            <option v-for="act in actividades" :key="act.id" :value="act.id">
                                {{ act.codigo_producto }} - {{ act.producto }}
                            </option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-bold">Descripción Detallada</label>
                        <textarea v-model="ofertaEdit.descripcion" class="form-control" rows="3" maxlength="400"></textarea>
                        <div class="text-end small text-muted">{{ ofertaEdit.descripcion?.length || 0 }} / 400</div>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <h6 class="text-primary border-bottom pb-2">2. Cronograma (Tabla de Tiempos)</h6>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Fecha Inicio</label>
                        <input type="date" class="form-control" v-model="ofertaEdit.fecha_inicio">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Hora Inicio</label>
                        <input type="time" class="form-control" v-model="ofertaEdit.hora_inicio">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Fecha Cierre</label>
                        <input type="date" class="form-control" v-model="ofertaEdit.fecha_cierre">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Hora Cierre</label>
                        <input type="time" class="form-control" v-model="ofertaEdit.hora_cierre">
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-12">
                        <h6 class="text-primary border-bottom pb-2">3. Gestión de Documentos</h6>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <small class="text-muted">Se requiere al menos 1 documento (PDF/ZIP)</small>
                            <button
                                type="button"
                                class="btn btn-outline-primary btn-sm"
                                @click="abrirSubModalDoc"
                            >
                                <i class="bi bi-plus-circle"></i> Agregar Documento
                            </button>
                        </div>
                        <table class="table table-sm table-hover border">
                            <thead class="table-light">
                                <tr>
                                    <th>Título</th>
                                    <th>Descripción</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(doc, index) in ofertaEdit.documentos" :key="index">
                                    <td>{{ doc.titulo }}</td>
                                    <td>{{ doc.descripcion }}</td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-link text-danger p-0" @click="eliminarDoc(index)">
                                            Eliminar
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="!ofertaEdit.documentos || ofertaEdit.documentos.length === 0">
                                    <td colspan="3" class="text-center text-danger small">Debe cargar al menos un documento</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" @click="actualizarLicitacion" :disabled="enviando">
                    <span v-if="enviando" class="spinner-border spinner-border-sm" role="status"></span>
                    <span v-else>Actualizar</span>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalNuevoDoc" tabindex="-1" aria-labelledby="subModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-primary shadow">
            <div class="modal-header">
                <h6 class="modal-title" id="subModalLabel">Detalles del Nuevo Documento</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Título del Documento:</label>
                    <input type="text" v-model="tempDoc.titulo" class="form-control form-control-sm" placeholder="Ej: Nit actualizado">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Descripción corta:</label>
                    <textarea v-model="tempDoc.descripcion" class="form-control form-control-sm" rows="2" maxlength="200"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Seleccionar Archivo (PDF o ZIP):</label>
                    <input type="file" ref="fileInput" class="form-control form-control-sm" 
                        accept=".pdf,.zip" @change="capturarArchivo">
                    <div v-if="errorDoc" class="text-danger small mt-1">{{ errorDoc }}</div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary btn-sm" 
                        @click="vincularDocAListado" 
                        :disabled="!docEsValido">
                    Agregar al Listado
                </button>
            </div>
        </div>
    </div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="/js/detalle_oferta.js"></script>
</body>
</html>