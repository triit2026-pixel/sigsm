# Sistema Hospital de Clínicas

Proyecto de egreso 2026.

## Tecnologías
- PHP (PDO)
- MySQL
- Bootstrap 5
- HTML, CSS y JavaScript
- XAMPP (entorno local)

## Instalación

1. Clonar el repositorio dentro de `C:\xampp\htdocs\`:
   ```bash
   git clone https://github.com/triit2026-pixel/sigsm.git
   ```
2. Iniciar Apache y MySQL desde el panel de XAMPP.
3. Crear la base de datos `sigsm` en phpMyAdmin e importar, en este orden:
   - `sql/schema.sql`
   - `sql/datos_iniciales.sql`
4. Copiar `conexion.example.php` como `conexion.php` y completar los datos de conexión a la base.
5. Configurar `base_url` con la IP de la PC (por ejemplo `http://192.168.1.4/sigsm`), para que los QR abran desde el celular dentro de la misma red.
6. Abrir el sistema en el navegador: `http://localhost/sigsm`.

## Funcionalidades

**Documentación**
- Alta, edición y baja lógica de documentos en PDF.
- Organización por categorías, que corresponden a los servicios del hospital.
- Código QR por documento para acceder desde el celular.
- Listado con buscador por título y filtros por categoría y estado.
- Contador de escaneos por documento.

**Encuestas anónimas**
- Creación de encuestas con preguntas de texto libre, escala del 1 al 5 u opción múltiple.
- Estados: borrador, activa y cerrada (el QR solo funciona con la encuesta activa).
- Visualización de las respuestas de los pacientes.

**Usuarios y roles**
- Médico: usa únicamente la pantalla de Documentos.
- Administrativo: gestiona documentos, categorías, encuestas y usuarios, y puede ver la actividad reciente.
- Registro de actividad: correo, rol, hora de ingreso y salida, e IP, con filtro por fecha y hora.

## Integrantes

- Facundo Rosa
- Felipe Garcia
- Camilo Masseras
- Thiago Nogues

