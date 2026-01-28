<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Licitaciones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .badge-estado { font-size: 0.9em; padding: 0.5em 1em; }
        .form-label { font-weight: bold; }

        .table-responsive, .table {
            overflow: visible !important;
        }

        td {
            overflow: visible !important;
            position: relative; /* Ayuda al posicionamiento del menú */
        }
    </style>
</head>
<body>
    <div id="app" class="container mt-5 pb-5">
    <header class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
        <h1 class="text-primary">Portal de Licitaciones</h1>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCrear">
            + Nueva Licitación
        </button>
    </header>

    <div class="card mb-3 p-3 shadow-sm">
        <div class="row align-items-center">
            <div class="col-md-1">
                <label class="fw-bold">Filtrar:</label>
            </div>
            <div class="col-md-11">
                <input type="text" v-model="busqueda" @keyup.enter="getLicitaciones" class="form-control" placeholder="Presione Enter para buscar...">
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Consecutivo</th>
                        <th>Objeto</th>
                        <th>Descripción</th>
                        <th>Apertura</th>
                        <th>Cierre</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in licitaciones" :key="item.id">
                        <td class="fw-bold text-secondary">{{ item.consecutivo }}</td>
                        <td>{{ item.objeto }}</td>
                        <td>{{ item.descripcion }}</td>
                        <td>{{ item.fecha_inicio }}</td>
                        <td>{{ item.fecha_cierre }}</td>
                        <td><span class="badge bg-success badge-estado">{{ item.estado }}</span></td>
                        <td class="text-center">
                            <a :href="'?url=detalle_oferta&id=' + item.id" class="btn btn-sm btn-info text-white">
                                Ver detalle
                            </a>
                        </td>
                    </tr>
                    <tr v-if="licitaciones.length === 0">
                        <td colspan="7" class="text-center py-5 text-muted">No hay licitaciones registradas.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="modalCrear" tabindex="-1" aria-labelledby="modalCrearLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCrearLabel">Crear Nueva Licitación</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form @submit.prevent="guardarLicitacion">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label">Objeto de la Licitación:</label>
                                <input type="text" v-model="form.objeto" class="form-control" :class="{'is-invalid': excedeLimiteObjeto && form.objeto.length > 0}">
                                <div class="d-flex justify-content-between mt-1">
                                    <small v-if="totalCaracteresObjeto === 0 && form.objeto.length > 0" class="text-danger">Ingresa al menos una letra.</small>
                                    <small :class="excedeLimiteObjeto ? 'text-danger fw-bold' : 'text-muted'" class="ms-auto">
                                        {{ totalCaracteresObjeto }} / 150
                                    </small>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label">Presupuesto:</label>
                                <div class="input-group">
                                    <select v-model="form.moneda" class="form-select" style="max-width: 90px;">
                                        <option value="COP">COP</option>
                                        <option value="USD">USD</option>
                                        <option value="EUR">EUR</option>
                                    </select>
                                    <input type="number" v-model="form.presupuesto" class="form-control" required>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Actividad Relacionada:</label>
                                <select v-model="form.actividad_id" class="form-select" required>
                                    <option value="">-- Seleccione una actividad --</option>
                                    <option v-for="act in actividades" :key="act.id" :value="act.id">
                                        {{ act.codigo_producto }} - {{ act.producto }}
                                    </option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Descripción Detallada:</label>
                                <textarea v-model="form.descripcion" class="form-control" :class="{'is-invalid': excedeLimite}" rows="3" maxlength="405"></textarea>
                                <div class="d-flex justify-content-end mt-1">
                                    <small :class="excedeLimite ? 'text-danger fw-bold' : 'text-muted'">{{ totalCaracteres }} / 400 caracteres</small>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Fecha Inicio:</label>
                                <input type="date" v-model="form.fecha_inicio" class="form-control" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Hora Inicio:</label>
                                <input type="time" v-model="form.hora_inicio" class="form-control" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Fecha Cierre:</label>
                                <input type="date" v-model="form.fecha_cierre" :min="form.fecha_inicio" class="form-control" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Hora Cierre:</label>
                                <input type="time" v-model="form.hora_cierre" class="form-control" required>
                            </div>
                            <div v-if="validacionFechas.error" class="col-12 alert alert-warning py-1 small">
                                {{ validacionFechas.mensaje }}
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success px-4" :disabled="!formularioValido">Publicar Licitación</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="/js/app.js"></script>
</body>
</html>