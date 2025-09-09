# 🎬 Proyecto PHP: Lista de Películas por Ver

#### estructura de carpetas:

cineva/
│
├── scripts/
│ └── chat.js
|
├── chats/
│ └── index.php
│ └── chat.php
│ └── enviar_mensaje.php
│ └── cargar_mensajes.php
|
|
├── css/
│ └── main.css # Estilos generales: reset, body, headers, footer, layout
│ └── forms.css # Estilos de login, register, agregar/editar películas
│ └── navbar.css # Solo estilos del navbar
│ └── pelicula.css
│ └── chat.css
│ └── perfil.css
│ └── followers.css
│ └── dashboard.css # Estilos específicos de dashboard (tablas, resúmenes)
│ └── styles.css # importación de todos los anteriores
│
├── img/
│ ├── portadas/ # Portadas de películas/series
│ └── avatars/ # Fotos de perfil de usuarios
│
├── includes/
│ ├── config.php # Configuración general (paths, constantes, etc.)
│ ├── db.php # Conexión a la base de datos
│ ├── follows.php
│ ├── auth.php # Lógica de autenticación (login/logout, sesiones)
│ ├── funciones.php # Funciones auxiliares (limpieza, validaciones, filtros)
│ ├── mensajes.php
│ ├── mensajes_ajax.php
│ ├── peliculas.php # Funciones específicas para CRUD de películas
│ ├── usuarios.php # Funciones específicas para CRUD de usuarios
│ ├── amistad.php # Funciones para peticiones de amistad
│ └── scraper.php # (opcional) scraping de plataformas de streaming
│
├── templates/
│ ├── header.php
│ ├── footer.php
│ └── navbar.php # Menú superior con búsqueda, login/logout, etc.
│
├── peliculas/
│ ├── agregar.php # Formulario para añadir película/serie
│ ├── editar.php # Editar película/serie existente
│ ├── eliminar.php # Eliminar película/serie
│ └── ver.php # Detalle de película/serie
│
├── usuarios/
│ ├── accion_follow.php
│ ├── perfil.php # Página de usuario con sus películas, filtros, stats
│ ├── register.php # Registro de nuevos usuarios
│ ├── login.php # Login de usuario
│ └── logout.php # Logout de usuario
│ └── seguidores.php
│ └── seguidos.php
│
├── amigos/
│ └── index.php # Página para gestionar peticiones y amigos
│
├── buscar.php # Resultados de búsqueda
├── dashboard.php # Feed general con todas las películas/series añadidas
├── index.php # Página de inicio (landing o feed público si no logueado)
│
├── README.md # Documentación del proyecto
├── .htaccess # Opcional para seguridad
└── .gitignore

#### Paleta de colores:

navbar: #090d10

fondo: #121518

botones y títulos: #f4bf2c

Texto fondo de botones: #06080e

texto: #fcfceb

## ✅ Tareas básicas (principiantes / baja dificultad)

Estas tareas pueden realizarse por alguien que esté empezando con PHP, HTML y CSS.

- 🗂️ **Crear la estructura de carpetas** del proyecto (`css/`, `img/portadas/`, `includes/`, `templates/`...). ✅
- 🎨 **Diseñar `header.php` y `footer.php`** reutilizables.
- 📄 **Maquetar las vistas**:
  - `index.php`
  - `register.php`
  - `dashboard.php`
  - `agregar.php`, `editar.php`, `eliminar.php`, `ver.php`
- 🎯 **Crear los formularios HTML**:
  - Registro
  - Login
  - Agregar película
  - Editar
  - Filtrar
- 📷 **Crear el formulario de subida de imagen** (`<input type="file">`).
- 🎨 **Aplicar estilos** con CSS (o Bootstrap si se decide usar).
- 📊 **Maquetar el contador** de películas vistas/no vistas.
- 🧪 **Validar formularios en el cliente** con JavaScript (campos vacíos, etc.).

---

## ⚙️ Tareas intermedias (con conocimientos de PHP / SQL básicos)

Ideales para quien ya tenga nociones sólidas de PHP procedural y MySQL.

- 🛠️ **Crear la base de datos** en phpMyAdmin con las tablas `usuarios` y `peliculas`.
- 🔌 **Programar la conexión a la base de datos** con `db.php` usando PDO o MySQLi.
- 🔑 **Programar el sistema de registro de usuario** (`register.php`):
  - Validar email único
  - `password_hash`
- 🔐 **Programar el sistema de login** (`index.php`):
  - Verificar credenciales con `password_verify`
  - Iniciar sesión (`$_SESSION`)
- 🧭 **Implementar sistema de logout** (`logout.php`).
- 🔒 **Proteger las rutas internas** (`dashboard`, `agregar`, `editar`, `eliminar`...) con comprobación de sesión (`auth.php`).
- 📝 **Guardar y mostrar las portadas subidas** (guardadas en `/img/portadas/`).
- 🧹 **Crear funciones auxiliares** en `funciones.php` para:
  - Validación de datos
  - Limpieza (`trim`, `htmlspecialchars`, etc.)
  - Mostrar alertas (`$_SESSION['mensaje']`)

---

## 🧠 Tareas avanzadas (requieren manejo de lógica compleja o seguridad)

Para los más experimentados del grupo o quien quiera asumir un reto.

- 📦 **Implementar CRUD completo de películas**:
  - `agregar.php`: Insertar en base de datos
  - `editar.php`: Obtener datos y actualizar
  - `eliminar.php`: Confirmación y borrado
  - `ver.php`: Mostrar detalles de película
- 🧮 **Programar el contador** de películas vistas / no vistas por usuario.
- ⭐ **Implementar sistema de favoritos** (toggle favorito ON/OFF).
- 📊 **Crear filtros en `dashboard.php`** por:
  - Género
  - Plataforma
  - Prioridad
  - Favoritos
- 🗃️ **Implementar paginación o scroll** si hay muchas películas.
- ✍️ **Añadir campo de reseña** (texto largo) y sistema de valoración (1 a 5 estrellas).
- 🔒 **Asegurar la aplicación contra**:
  - Inyección SQL (usar consultas preparadas)
  - XSS (escapar datos)
  - Subidas inseguras (validar extensión/tamaño de imagen)
- 🧪 **Probar todo el flujo de usuario**:
  - Registro > login > añadir > editar > marcar como vista > borrar
  - Crear página 404 not found.

---

## 📊 Resumen de dificultad y tareas

| Dificultad         | N.º tareas | Descripción general                                   |
| ------------------ | ---------- | ----------------------------------------------------- |
| 🟢 **Básicas**     | 8          | Maquetación, formularios, frontend simple             |
| 🟡 **Intermedias** | 8          | Backend básico: sesiones, conexión BD, login/registro |
| 🔴 **Avanzadas**   | 8          | Lógica compleja, seguridad, filtros, valoración       |

🚨Próximas implementaciones:🚨

- Nueva sección con filtros: género, plataforma, visto (si o no) favoritos, etc.
- Barra de búsqueda. añadir una búsqueda

- Hacer un diseño más profesional, en vez de ser un listado, quiero que sea una lista de cartas.

- Hacer que todas las películas añadidas por los usuarios aparezcan en el dashboard,
- En el perfil de cada usuario deben salir sus películas y series

- La página de usuario tendrá sección de filtros también para filtrar tus propias películas.

- Cuando en la página de película, aparezca, "agregada por" y un nombre, que ese nombre sea un enlace hacia el perfil de usuario.

- añadir sistema de seguir usuarios

- en el perfil de usuario debe aparecer el número de publicaciones, las personas que sigue y las que lo siguen.

## Próximas implementaciones 🚀

### 1️⃣ Dashboard (dashboard.php)

- Mostrar **todas las películas de todos los usuarios**.
- Añadir **sección de filtros** por:
  - Género
  - Plataforma
  - Visto (Sí/No)
  - Favorito (Sí/No)
- Implementar **barra de búsqueda** por título de película.
- Cambiar diseño de listado a **cartas (cards)**.
- Cada card debe mostrar:
  - Título
  - Género
  - Plataforma
  - Visto
  - Favorito
  - “Agregada por” con enlace al perfil del usuario.

### 2️⃣ Perfil de usuario (usuarios/perfil.php)

- Mostrar **todas las películas del usuario** en formato cards.
- Añadir **filtros** para sus propias películas.
- Mostrar **estadísticas del usuario**:
  - Número de publicaciones
  - Personas que sigue
  - Personas que lo siguen
- Implementar **botón seguir / dejar de seguir** usuarios.
- Mantener enlace “Agregada por” en cada card.

### 3️⃣ Películas (peliculas/)

- Modificar `ver.php` para mostrar:
  - “Agregada por” con enlace al perfil del usuario.
- Mantener funcionalidad CRUD (agregar, editar, eliminar) asociada al `usuario_id`.

### 4️⃣ Sistema de seguidores (includes/usuarios.php o nuevo includes/seguidores.php)

- Funciones para:
  - Seguir a un usuario
  - Dejar de seguir a un usuario
  - Consultar lista de seguidores
  - Consultar lista de seguidos
- Integrar con perfil y dashboard.

### 5️⃣ Filtros y búsqueda (includes/funciones.php o includes/peliculas.php)

- Crear funciones reutilizables para:
  - Aplicar filtros dinámicos (género, plataforma, visto, favorito)
  - Aplicar búsqueda por título
  - Combinar filtros y búsqueda
- Integrar en:
  - Dashboard
  - Perfil de usuario
  - Página de búsqueda general (`buscar.php`)

### 6️⃣ Frontend / CSS

- Crear **cards CSS** para películas en `dashboard.css`.
- Estilos de **barra de filtros** (inputs, selects, checkboxes).
- Estilos para **botón seguir / dejar de seguir**.
- Ajustes **responsive** para desktop y móvil.

### 7️⃣ Integración general

- Añadir enlaces de “perfil” en dashboard y página de película.
- Revisar seguridad:
  - Evitar mostrar botones de editar/eliminar a otros usuarios.
  - Sanitizar variables de URL.
- Probar todas las consultas con filtros combinados.

nueva idea: que te aparezca una notificación cada vez que alguien comente tu publicación y cada vez que te siga alguien.

cuando entres en el perfil de alguien que te sigue, debe poner "te sigue"

|---------------------------------------------------|
| Nombre usuario |
|---------------------------------------------------|
| |mensaje entrante| |
| |
| |mensaje saliente| |
| |
| |
| |
| |
| |
| |
|---------------------------------------------------|
| |boton enviar| |
|---------------------------------------------------|

estructura del mensaje:

|-------------------------|
|texto mensaje |
| |
| hora |
|-------------------------|
La hora debe ser en formato 24 horas, por ejemplo 21:09

próximas implementaciones:

- Sistema de notificaciones para nuevos seguidores, nuevos comentarios en la publicación. (añadir icono campana)
- Hacer que en el icono de chats aparezca un número cada vez que te hablen.
- hacer que los comentarios dentro de la publicación puedan editarse y eliminarse.
- añadir posibilidad de enviar publicación a otro usuario, es decir, añadir opción de compartir.
- Añadir sección para ti y sección seguidores (dividir el contenido entre usuarios que sigues (seguidores) y lo que te recomienda la app (para ti)) en el front se dará la opción de elegir pestaña.
- hacer todas las pantallas responsive tamaño tablet y móvil.

mandar proyecto a: IFCD0210mainjobs@gmail.com
