document.addEventListener("DOMContentLoaded", () => {
  const icono = document.getElementById("icono-notificaciones");
  const lista = document.getElementById("lista-notificaciones");
  const contador = document.getElementById("contador-notificaciones");

  // Genera la URL de destino según tipo de notificación
  function generarDestino(notificacion) {
    if (notificacion.tipo === "seguimiento") {
      return `usuarios/perfil.php?id=${notificacion.origen_id}`;
    } else if (notificacion.tipo === "comentario") {
      return `peliculas/ver.php?id=${notificacion.comentario_pelicula_id}#comentario_${notificacion.relacion_id}`;
    }
    return "#";
  }

  // Marca la notificación como leída y redirige
  function marcarYRedirigir(notificacion) {
    fetch(`includes/marcar_notificacion_individual.php?id=${notificacion.id}`, {
      method: "POST",
    }).then(() => {
      const destino = generarDestino(notificacion);
      window.location.href = destino;
    });
  }

  // Función para seguir a un usuario desde la notificación
  function seguirUsuario(usuarioId, boton) {
    const data = new FormData();
    data.append("usuario_id", usuarioId);
    data.append("accion", "seguir");

    fetch("usuarios/accion_follow.php", {
      method: "POST",
      body: data,
    })
      .then((res) => res.json())
      .then((res) => {
        if (res.success) {
          boton.textContent = "Ya le sigues";
          boton.disabled = true;
        } else {
          alert(res.message || "Error al seguir al usuario");
        }
      })
      .catch(() => {
        alert("Error en la conexión");
      });
  }

  // Carga las notificaciones y las muestra en el dropdown
  function cargarNotificaciones() {
    fetch("includes/notificaciones_ajax.php")
      .then((res) => res.json())
      .then((datos) => {
        lista.innerHTML = "";
        let noLeidas = 0;

        datos.forEach((notificacion) => {
          if (notificacion.leido == 0) noLeidas++;

          const elemento = document.createElement("div");
          elemento.classList.add("notificacion-item");

          if (notificacion.tipo === "seguimiento") {
            elemento.innerHTML = `
                            <span class="notificacion-text">${notificacion.origen_nombre} te ha seguido</span>
                            <button class="btn-seguir" data-id="${notificacion.origen_id}">Seguir de vuelta</button>
                        `;

            // Click en el botón "Seguir de vuelta"
            const boton = elemento.querySelector(".btn-seguir");
            boton.addEventListener("click", (e) => {
              e.stopPropagation();
              seguirUsuario(notificacion.origen_id, boton);
            });

            // Click en el texto de la notificación → redirige al perfil
            elemento
              .querySelector(".notificacion-text")
              .addEventListener("click", () => {
                marcarYRedirigir(notificacion);
              });
          } else if (notificacion.tipo === "comentario") {
            elemento.textContent = `${notificacion.origen_nombre} comentó en tu película`;
            elemento.addEventListener("click", () => {
              marcarYRedirigir(notificacion);
            });
          }

          lista.appendChild(elemento);
        });

        // Actualizar contador
        contador.textContent =
          noLeidas > 0 ? (noLeidas > 9 ? "9+" : noLeidas) : "";
        contador.style.display = noLeidas > 0 ? "inline-block" : "none";
      })
      .catch((e) => console.error("Error al cargar notificaciones:", e));
  }

  // Mostrar/ocultar lista al hacer click en el icono
  if (icono) {
    icono.addEventListener("click", () => {
      lista.classList.toggle("show");
    });
  }

  // Cargar notificaciones al inicio y cada 30 segundos
  cargarNotificaciones();
  setInterval(cargarNotificaciones, 30000);
});
