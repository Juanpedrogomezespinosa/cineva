# 🎬 Proyecto PHP: Lista de Películas por Ver

#### Arquitectura:

cineva/
│
├── css/
│ └── styles.css
│
├── img/
│ └── portadas/ # Aquí se guardarán las portadas subidas por los usuarios
│
├── includes/
│ ├── db.php # Conexión a la base de datos
│ ├── auth.php # Lógica de autenticación (login/logout, sesiones)
│ └── funciones.php # Funciones auxiliares (limpieza, validaciones, filtros)
│
├── templates/
│ ├── header.php
│ └── footer.php
│
├── index.php # Página de inicio con opción de login o registro
├── register.php # Registro de nuevos usuarios
├── logout.php # Cierre de sesión
├── dashboard.php # Panel principal del usuario
├── agregar.php # Formulario para añadir película
├── editar.php # Editar película existente
├── eliminar.php # Confirmación y acción de eliminar película
├── ver.php # Detalles individuales de una película
└── .htaccess # Opcional para seguridad básica (evitar acceso a includes)
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

- Nueva sección con filtros.
- Barra de búsqueda.
- Hacer scrapping para saber en qué plataforma está disponible cada película o serie
- Hacer un diseño más realista.

- Hacer que todas las películas añadidas por los usuarios aparezcan en el dashboard, añadir una página de cada usuario en la que salgan tus películas o series. ❓❓❓❓❓

- La página de usuario tendrá sección de filtros también.

- Cuando en la página de película, aparezca, "agregada por" y un nombre, que ese nombre sea un enlace hacia el perfil de usuario.

- agregar peticiones de amistad.

- en el perfil de usuario debe aparecer el número de publicaciones, las personas que sigue y las que lo siguen.
