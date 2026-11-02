# Sistema Hospital de Clínicas (S.I.G.S.M.)

Proyecto de egreso 2026.

El personal del hospital carga documentos y crea encuestas desde un panel; el paciente accede a todo desde su celular, escaneando un QR genérico que lo lleva a su propio home, sin registrarse.

## Tecnologías

- PHP (PDO)
- MySQL / MariaDB
- Bootstrap 5
- HTML, CSS y JavaScript
- Google reCAPTCHA (inicio de sesión)
- qrcodejs (generación del código QR)
- XAMPP (entorno local)

## Instalación

1. Clonar el repositorio dentro de `C:\xampp\htdocs\`:

git clone https://github.com/triit2026-pixel/sigsm.git


2. Iniciar Apache y MySQL desde el panel de XAMPP.
3. Crear la base de datos `sigsm` en phpMyAdmin (cotejamiento `utf8mb4_unicode_ci`) e importar, en este orden:
   - `sql/schema.sql`
   - `sql/datos_iniciales.sql`
4. Si `datos_iniciales.sql` no incluye un usuario administrativo, crear uno en la tabla `usuario` con rol `administrativo`, la contraseña se guarda con `password_hash` de PHP.
5. Copiar `config.example.php` como `config.php` y completar:
   - los datos de conexión a la base,
   - las claves de reCAPTCHA (se generan en la consola de Google, registrando el dominio donde se use),
   - `base_url`, la dirección donde corre el sistema (por ejemplo `http://localhost/sigsm`).
6. Comprobar que la carpeta `storage/documentos` tenga permiso de escritura, ya que ahí se guardan los PDF.
7. Abrir el sistema en el navegador: `http://localhost/sigsm`.

`config.php` contiene claves y contraseñas, por eso no se incluye en el repositorio.

## Estructura

- `paciente/`: portal público del paciente, este no requiere inicio de sesión.
- `includes/`: funciones compartidas, autenticación y encabezado.
- `sql/`: estructura de la base y datos iniciales.
- `storage/documentos/`: PDF subidos.

## Funcionalidades

### Portal del paciente

- Un único código QR general lleva a todos los pacientes al mismo portal, pensado para celular.
- Dos secciones: **Documentación** y **Encuestas**.
- Documentación: documentos activos con buscador por título y filtro por categoría.
- Encuestas: encuestas activas con buscador por nombre y filtro por área, y respuesta anónima.

### Documentación

- Alta, edición y baja lógica de documentos en PDF.
- Organización por categorías, que corresponden a los servicios del Hospital de Clínicas.
- Listado con buscador por título y filtros por categoría y estado.
- Contador de visitas por documento.

### Encuestas anónimas

- Preguntas de texto libre, escala del 1 al 5 u opción múltiple.
- Cada encuesta tiene un área o es general.
- Estados: borrador, activa y cerrada (solo las activas aparecen en el portal).
- Visualización de los resultados.

### Usuarios y roles

- **Médico:** usa únicamente la pantalla de Documentos.
- **Administrativo:** gestiona documentos, categorías, encuestas y usuarios, genera el QR del portal y consulta la actividad reciente.
- Registro de actividad: correo, rol, hora de ingreso y salida, e IP, con filtro por fecha y hora.
- Inicio de sesión protegido con reCAPTCHA.

## Código QR del portal y demostración desde celulares

El administrativo genera, descarga e imprime el QR desde la opción **QR del portal** del menú. El QR usa la dirección desde la que se abrió el sistema, o la de `base_url` si se abre por `localhost`.

Para una demostración fuera de la red local se puede usar Cloudflare Tunnel:

cloudflared tunnel --url http://localhost:80


Se genera una dirección pública temporal. El QR debe generarse después de levantar el túnel, porque la dirección cambia cada vez.

## Integrantes

- Facundo Rosa
- Felipe Garcia
- Camilo Masseras
- Thiago Nogues
