# 📘 Taller: Documentación de un proyecto web con `README.md`

---

## 1. Introducción

Hoy vamos a aprender a documentar un proyecto de programación web usando **README.md**.  
Este archivo es fundamental en cualquier repositorio porque es lo primero que ven los usuarios y programadores que lo visitan.

👉 **Objetivo del taller:**

- Aprender a crear documentación clara y profesional.
- Usar como ejemplo la aplicación **Cineva**, desarrollada en **PHP, MySQL, XAMPP y Visual Studio Code**.

---

## 2. ¿Por qué un buen `README.md` es importante?

Un `README.md` no es un adorno: es la puerta de entrada a tu proyecto.

- 📖 **Facilita** que otros entiendan de qué trata el proyecto.
- 🛠️ **Ayuda** al propio equipo a recordar cómo configurarlo.
- ✨ Es la **carta de presentación** de tu repositorio.

---

## 3. Estructura básica de un `README.md`

Existen secciones habituales que no deberían faltar:

1. **Título y descripción del proyecto**
2. **Tecnologías utilizadas**
3. **Instalación y configuración**
4. **Estructura de carpetas**
5. **Uso de la aplicación**
6. **Funcionalidades principales**
7. **Contribución** (opcional)
8. **Licencia** (si aplica)

👉 En el taller iremos rellenando cada una de estas secciones con la información real de _Cineva_.

---

## 4. Aplicación al proyecto "Cineva"

### 🎬 Título y descripción

```markdown
# Cineva

Aplicación web en PHP y MySQL para gestionar y compartir películas y series con otros usuarios.
Incluye sistema de usuarios, perfiles, chat, notificaciones y CRUD de películas.
```

## Aquí siempre debemos poner el nombre del proyecto y una descripción breve pero clara.

## 5. Tecnologías utilizadas.

## Tecnologías

- PHP 8+
- MySQL / phpMyAdmin
- XAMPP
- HTML, CSS, JavaScript
- VS Code
- Git

## Es importante listar las herramientas y lenguajes que se usaron. Esto ayuda a que otros sepan qué necesitan instalar para trabajar con el proyecto.

## 6. Instalación y configuración.

## Instalación

1. Clona el repositorio:
   ```bash
   git clone https://github.com/Juanpedrogomezespinosa/cineva.git
   ```

- Mueve la carpeta al directorio XAMPP
- Crea una base de datos en PhpMyAdmin llamada cineva
- Importa el archivo cineva.sql
- Configura la conexión en includes/db.php
- Inicia XAMPP (Apache + MySQL) y abre el navegador: http://localhost/proyectos/cineva/

### Este apartado explica cómo instalar y ejecutar el proyecto desde cero. La idea es que

### Cualquier persona pueda levantar la app sin tener que preguntarnos nada extra.

---

## 7. Estructura de carpetas 📁

```markdown
cineva/
├── chats/ # Módulo de chat (enviar, cargar, listar mensajes)
├── css/ # Hojas de estilo organizadas por secciones
│ ├── agregar.css
│ ├── chat.css
│ ├── dashboard.css # Estilos de dashboard (tablas, resúmenes)
│ ├── editar-perfil.css
│ ├── followers.css
│ ├── forms.css # Estilos de login, register, agregar/editar películas
│ ├── lista-chats.css
│ ├── main.css # Estilos globales (reset, body, headers, layout)
│ ├── navbar.css # Solo estilos del navbar
│ ├── pelicula.css
│ ├── perfil.css
│ └── styles.css # Importa todos los estilos anteriores
│
├── img/
│ ├── avatars/ # Avatares de usuarios
│ ├── icons/ # Iconos usados en la app
│ └── portadas/ # Portadas de películas/series
│
├── includes/ # Archivos clave para lógica y conexión DB
│ ├── auth.php # Autenticación (login/logout, sesiones)
│ ├── config.php # Configuración general
│ ├── db.php # Conexión a la base de datos
│ ├── funciones.php # Funciones auxiliares
│ ├── peliculas.php # Funciones específicas para CRUD de películas
│ ├── usuarios.php # Funciones específicas para CRUD de usuarios
│ ├── scraper.php # (Opcional, futura implementación)
│ ├── mensajes*.php # Archivos relacionados con mensajes
│ ├── notificaciones*.php # Archivos para gestión de notificaciones
│ └── follows.php # Seguimiento entre usuarios
│
├── peliculas/ # CRUD de películas
│ ├── agregar.php
│ ├── editar.php
│ ├── eliminar.php
│ └── ver.php
│
├── scripts/ # Archivos JavaScript
│ ├── chat.js
│ └── notificaciones.js
│
├── templates/ # Componentes reutilizables
│ ├── header.php
│ ├── footer.php
│ └── navbar.php
│
├── usuarios/ # Gestión de usuarios
│ ├── login.php
│ ├── logout.php
│ ├── register.php
│ ├── perfil.php # Perfil con stats, películas, filtros
│ ├── editar-perfil.php
│ ├── procesar-editar-perfil.php
│ ├── accion_follow.php
│ ├── seguidores.php
│ ├── seguidos.php
│ └── seguidores_ajax.php
│
├── .gitignore
├── .htaccess
├── buscar.php # Resultados de búsqueda
├── dashboard.php # Feed general con películas/series añadidas
├── index.php # Página de inicio
└── README.md # Documentación del proyecto
```

```

```

### Documentar la estructura del proyecto con un árbol comentado ayuda a entender qué hace cada carpeta y archivo. Esto es oro para alguien que abre el repositorio por primera vez.

## 8. Uso de la aplicación.

### Uso

- Registro de usuario y login.
- Creación, edición y eliminación de películas.
- Seguimiento de otros usuarios.
- Chat privado en tiempo real.
- Notificaciones de actividad.

### Aquí contamos lo que el usuario puede hacer dentro de la aplicación.

## 9. Contribución

1. Haz un fork del proyecto.
2. Crea una rama nueva (`git checkout -b feature-nueva`).
3. Haz tus cambios y commitea (`git commit -m "Agrego nueva feature"`).
4. Haz push a tu rama (`git push origin feature-nueva`).
5. Abre un Pull Request.

### Si queremos que otros desarrolladores participen, les damos un proceso estándar de colaboración.

## 10. Licencia

Este proyecto se distribuye bajo la licencia MIT.
Puedes usarlo, modificarlo y compartirlo libremente.

## Indicar la licencia es importante para que quede claro qué puede y qué no puede hacer la gente con el proyecto.

## 11. Conclusión:

Un README.md bien hecho ahorra tiempo y hace que tu proyecto sea más profesional.

La clave es mantenerlo claro, ordenado y actualizado.

Ahora pueden aplicar esta misma estructura a sus propios proyectos.
