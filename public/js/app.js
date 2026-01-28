const { createApp } = Vue;

createApp({
    //Gestiona los datos del componente
    data() {
        return {
            licitaciones: [],
            actividades: [], 
            actividad_id: '',
            mostrarFormulario: false,
            form: {
                objeto: '',
                descripcion: '', 
                presupuesto: 0,
                moneda: 'COP',
                actividad_id: '',
                fecha_inicio: '2026-01-26',
                hora_inicio: '08:00',
                fecha_cierre: '2026-02-26',
                hora_cierre: '17:00'
            },
            descripcion: '',
            objeto: '',
            busqueda: '',
            detalle: 'detalle'
            
        }
    },
    //Inicia los datos al cargar el componente del DOM
    mounted() {
        this.getActividades(); // Obtengo las actividades
        this.getLicitaciones();

        // Esto inicializa manualmente todos los dropdowns después de que Vue carga
        var dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'))
        var dropdownList = dropdownElementList.map(function (dropdownToggleEl) {
            return new bootstrap.Dropdown(dropdownToggleEl)
        });
        
    },
    //Propiedades computalizadas
    computed: {
        //función para calcula los caracteres en tiempo real
        totalCaracteres() {
            return this.form.descripcion ? this.form.descripcion.length : 0;
        },
        //Cambia el color si supera el límite de caracteres
        excedeLimite() {
            return this.totalCaracteres > 400;
        },
        //función para calcular la cantidad de caracteres en tiempo real
        totalCaracteresObjeto(){
            return this.form.objeto ? this.form.objeto.trim().length : 0;
        },
        excedeLimiteObjeto(){
            return this.totalCaracteresObjeto === 0 || this.totalCaracteresObjeto > 150;
        },
        //Validación de fechas
        validacionFechas() {
        const f = this.form;
        
        //Verificar que todos los campos tengan valor
        if (!f.fecha_inicio || !f.hora_inicio || !f.fecha_cierre || !f.hora_cierre) {
            return { error: true, mensaje: 'Todos los campos de fecha y hora son obligatorios.' };
        }

        //Crear objetos Date para comparar (YYYY-MM-DDTHH:mm)
        const inicio = new Date(`${f.fecha_inicio}T${f.hora_inicio}`);
        const cierre = new Date(`${f.fecha_cierre}T${f.hora_cierre}`);

        //Validación: Fecha y Hora de cierre debe ser mayor a inicio
            if (cierre <= inicio) {
                return { 
                    error: true, 
                    mensaje: 'La fecha y hora de cierre deben ser posteriores a la de inicio.' 
                };
            }

            return { error: false, mensaje: '' };
        },
        //habilita si todo es válido
        formularioValido() {

                return (
                !this.excedeLimiteObjeto &&
                !this.excedeLimite &&
                this.form.actividad_id !== '' &&
                !this.validacionFechas.error
            );
        },
        
        
    },
    //Declaración de métodos 
    methods: {
        //Petición para obtener todas las actividades
        async getActividades() {
            try {
            const res = await axios.get('/api/actividades');
            this.actividades = res.data;
            } catch (e) {
            console.error(e);
            }
        },
        //Petición para realizar la busqueda en el listado
        async getLicitaciones() {
            try {
                const res = await axios.get('/api/licitaciones', {
                    params: { buscar: this.busqueda } 
                });
                this.licitaciones = res.data;
            } catch (e) {
                console.error("Error al filtrar:", e);
            }
        },

        //Petición para el almacenamiento de los datos de las ofertas
        guardarLicitacion() {
            axios.post('api/licitaciones', this.form)
            .then(response => {
                alert('¡Licitación publicada con éxito!');

                //Cierra el modal después de almacenar los datos
                const modalElement = document.getElementById('modalCrear');
                const modalBus = bootstrap.Modal.getInstance(modalElement);
                //Validamos si existe
                if (modalBus) {
                    modalBus.hide();
                }

                //limpia los campos del formulario
                this.resetForm(); 

                // Recarga los datos del listado
                this.getLicitaciones();
            })
            .catch(error => {
                console.error('Error:', error);
                alert('No se pudo guardar la licitación');
            });
        },
        //reinicia el estado y valores de los campos
        resetForm() {
            this.form = {
                objeto: '',
                descripcion: '',
                presupuesto: 0,
                moneda: 'COP',
                actividad_id: '',
                fecha_inicio: '',
                hora_inicio: '',
                fecha_cierre: '',
                hora_cierre: ''
            };
        },
    }
}).mount('#app');