const { createApp } = Vue;

createApp({
    //Gestiona los datos del componente
    data() {
        return {
            oferta: null,
            loading: true,
            tabActiva: 'info',
            ofertaEdit: {},
            idEditando: null,
            actividades: [],
            subModal: null,
            ofertaEdit: {
                documentos: [] 
            },
            cargando: false,
            tempDoc: { titulo: '', descripcion: '', archivo: null },
            errorDoc: ''
        };
    },
    //Inicia los datos al cargar el componente del DOM
    mounted() {
        this.getActividades(); // Lista las actividades

        //Obtención del id por medio del query
        const params = new URLSearchParams(window.location.search);
        const id = params.get('id');

        //Validación del id 
        if (!id) {
        alert('ID no encontrado');
        return;
        }
        //Petición asincrona para recuperar los datos iniciales
        axios
        .get(`api/detalle-licitacion?id=${id}`)
            .then(response => {
                console.log('Respuesta cruda del API:', response.data);

                this.oferta = response.data;
                this.loading = false;
            })
        .catch(error => {
            console.error('Error cargando detalle:', error);
            alert('No se pudo cargar la información');
        });
    },
    //Propiedades computalizadas
    computed: {
        docEsValido() {
            return this.tempDoc.titulo && this.tempDoc.archivo && !this.errorDoc;
        }
    },
    //Declaración de métodos 
    methods: {
        //Recuperación de las actividades
        async getActividades() {
            try {
            const res = await axios.get('/api/actividades');
            this.actividades = res.data;
            } catch (e) {
            console.error(e);
            }
        },
        //Método para la edición del formulario.
        abrirModalEditar() {
            this.ofertaEdit = JSON.parse(JSON.stringify(this.oferta));

            // Asegurar estructura
            if (!this.ofertaEdit.documentos) {
            this.ofertaEdit.documentos = [];
            }

            // Abrir modal principal
            const modal = new bootstrap.Modal(
            document.getElementById('exampleModal')
            );
            modal.show();
        },
        // Carga temporal del modal para subir el documento
        abrirSubModalDoc() {
            // Limpiar modal temporal
            this.tempDoc = {
                titulo: '',
                descripcion: '',
                archivo: null
            };
            this.errorDoc = null;

            const subModal = new bootstrap.Modal(
                document.getElementById('modalNuevoDoc')
            );
            subModal.show();
        },

        //Filtra de acuerdo a la extensión y verifica el estado del objeto
        capturarArchivo(e) {
            const file = e.target.files[0];
            if (!file) return;

            const ext = file.name.split('.').pop().toLowerCase();
            if (!['pdf', 'zip'].includes(ext)) {
                this.errorDoc = 'Solo se permiten PDF o ZIP';
                this.tempDoc.archivo = null;
                return;
            }

            this.errorDoc = null;
            this.tempDoc.archivo = file;
        },
        //Vincula el documento al listado de edición
        vincularDocAListado() {
            if (!this.tempDoc.archivo || !this.tempDoc.titulo) {
                this.errorDoc = 'Debe completar título y archivo';
                return;
            }

            this.ofertaEdit.documentos.push({
                titulo: this.tempDoc.titulo,
                descripcion: this.tempDoc.descripcion,
                archivo: this.tempDoc.archivo
            });

            // Cerrar submodal
            bootstrap.Modal.getInstance(
                document.getElementById('modalNuevoDoc')
            ).hide();
        },
        //Elimina documentos
        eliminarDoc(index) {
            if(confirm("¿Seguro que desea quitar este documento?")) {
                this.ofertaEdit.documentos.splice(index, 1);
            }
        },
        //Construcción de la petición de actualización
        async actualizarLicitacion() {
            if (!this.validarFormulario()) return;
            this.enviando = true; 

            try {
                const dataEnvio = new FormData();
                
                // Agrega el id
                dataEnvio.append('id', this.ofertaEdit.id);

                // Agregar campos básicos
                const campos = ['objeto', 'presupuesto', 'moneda', 'actividad_id', 'descripcion', 'fecha_inicio', 'hora_inicio', 'fecha_cierre', 'hora_cierre'];
                campos.forEach(campo => {
                    dataEnvio.append(campo, this.ofertaEdit[campo] || '');
                });

                // Carga de documentos
                let contadorCarga = 0;
                this.ofertaEdit.documentos.forEach((doc) => {
                    // Solo enviamos si es un archivo nuevo
                    if (doc.archivo instanceof File) {
                        dataEnvio.append(`docs[${contadorCarga}]`, doc.archivo);
                        dataEnvio.append(`docs_meta[${contadorCarga}][titulo]`, doc.titulo);
                        dataEnvio.append(`docs_meta[${contadorCarga}][descripcion]`, doc.descripcion);
                        contadorCarga++;
                    }
                });

                const response = await axios.post('api/actualizar-licitacion', dataEnvio, {
                    headers: { 'Content-Type': 'multipart/form-data' }
                });

                if (response.data.status === 'success') {
                    alert('¡Licitación actualizada!');
                    location.reload(); // Recargar para ver los cambios y nuevos docs
                }
            } catch (error) {
                console.error(error);
                alert("Error al actualizar: " + (error.response?.data?.message || "Error de conexión"));
            } finally {
                this.enviando = false;
            }
        },
        //Validación antes de realizar el envio 
        validarFormulario() {
            if (this.ofertaEdit.documentos.length === 0) {
                alert("La prueba exige al menos 1 documento cargado.");
                return false;
            }
            
            return true;
        }
    }
}).mount('#app');