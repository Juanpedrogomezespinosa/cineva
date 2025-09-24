document.addEventListener("DOMContentLoaded", () => {
  const icono = document.getElementById("icono-notificaciones");
  const lista = document.getElementById("lista-notificaciones");
  const contador = document.getElementById("contador-notificaciones");

  // Genera la URL de destino según tipo de notificación
  function generarDestino(notificacion) {
    if (notificacion.tipo === "seguimiento") {
      return `usuarios/perfil.php?id=${encodeURIComponent(
        notificacion.origen_id
      )}`;
    } else if (notificacion.tipo === "comentario") {
      return `peliculas/ver.php?id=${encodeURIComponent(
        notificacion.comentario_pelicula_id
      )}#comentario_${encodeURIComponent(notificacion.relacion_id)}`;
    }
    return "#";
  }

  // Marca la notificación como leída y redirige
  function marcarYRedirigir(notificacion) {
    fetch(
      `includes/marcar_notificacion_individual.php?id=${encodeURIComponent(
        notificacion.id
      )}`,
      {
        method: "POST",
        credentials: "same-origin",
      }
    )
      .then(() => {
        window.location.href = generarDestino(notificacion);
      })
      .catch(() => {
        window.location.href = generarDestino(notificacion);
      });
  }

  // Marca una notificación como leída sin redirigir
  function marcarLeida(notificacionId) {
    return fetch(
      `includes/marcar_notificacion_individual.php?id=${encodeURIComponent(
        notificacionId
      )}`,
      {
        method: "POST",
        credentials: "same-origin",
      }
    ).catch(() => {
      console.error("Error al marcar notificación como leída");
    });
  }

  // Marca todas las notificaciones como leídas
  function marcarTodas() {
    return fetch("includes/marcar_todas_notificaciones.php", {
      method: "POST",
      credentials: "same-origin",
      headers: { Accept: "application/json" },
    })
      .then((res) => res.json())
      .catch((e) => {
        console.error("Error al marcar todas las notificaciones:", e);
        return { success: false };
      });
  }

  // Seguir usuario desde notificación
  function seguirUsuario(usuarioId, boton, notificacionId) {
    const data = new FormData();
    data.append("usuario_id", usuarioId);
    data.append("accion", "seguir");

    fetch("usuarios/accion_follow.php", {
      method: "POST",
      body: data,
      credentials: "same-origin",
    })
      .then((res) => res.json())
      .then((res) => {
        if (res.success) {
          boton.textContent = "Siguiendo";
          boton.disabled = true;

          // Marcar la notificación como leída al seguir de vuelta
          if (notificacionId) {
            marcarLeida(notificacionId);
          }
        } else {
          alert(res.message || "Error al seguir al usuario");
        }
      })
      .catch(() => {
        alert("Error en la conexión");
      });
  }

  // Cargar notificaciones y actualizar botones
  function cargarNotificaciones() {
    fetch("includes/notificaciones_ajax.php", { credentials: "same-origin" })
      .then((res) => res.json())
      .then((datos) => {
        lista.innerHTML = "";
        let noLeidas = 0;

        datos.forEach((notificacion) => {
          if (notificacion.leido == 0) noLeidas++;

          const elemento = document.createElement("div");
          elemento.classList.add("notificacion-item");
          if (notificacion.leido == 0) elemento.classList.add("no-leida");

          if (notificacion.tipo === "seguimiento") {
            const yaSigues = Number(notificacion.ya_sigues) === 1;
            elemento.innerHTML = `
              <span class="notificacion-text">${
                notificacion.origen_nombre
              } te ha seguido</span>
              <button class="btn-seguir" data-id="${notificacion.origen_id}" ${
              yaSigues ? "disabled" : ""
            }>${yaSigues ? "Siguiendo" : "Seguir"}</button>
            `;

            const boton = elemento.querySelector(".btn-seguir");
            boton.addEventListener("click", (e) => {
              e.stopPropagation();
              seguirUsuario(notificacion.origen_id, boton, notificacion.id);
            });

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

  // Toggle panel y marcar todas al abrir
  if (icono) {
    icono.addEventListener("click", () => {
      const opening = !lista.classList.contains("show");
      lista.classList.toggle("show");

      if (opening) {
        marcarTodas().then((res) => {
          if (res && res.success) {
            contador.textContent = "";
            contador.style.display = "none";
            cargarNotificaciones();
          }
        });
      }
    });
  }

  // Cargar al inicio y cada 30 segundos
  cargarNotificaciones();
  setInterval(cargarNotificaciones, 30000);
});
