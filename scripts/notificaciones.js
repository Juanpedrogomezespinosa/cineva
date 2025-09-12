document.addEventListener("DOMContentLoaded", () => {
  const icono = document.getElementById("icono-notificaciones");
  const lista = document.getElementById("lista-notificaciones");
  const contador = document.getElementById("contador-notificaciones");

  function generarDestino(notificacion) {
    if (notificacion.tipo === "seguimiento") {
      return `usuarios/perfil.php?id=${notificacion.origen_id}`;
    } else if (notificacion.tipo === "comentario") {
      return `peliculas/ver.php?id=${notificacion.comentario_pelicula_id}#comentario_${notificacion.relacion_id}`;
    }
    return "#";
  }

  function marcarYRedirigir(notificacion) {
    const destino = generarDestino(notificacion);
    window.location.href = `includes/marcar_notificacion_individual.php?id=${
      notificacion.id
    }&url=${encodeURIComponent(destino)}`;
  }

  function cargarNotificaciones() {
    fetch("includes/notificaciones_ajax.php")
      .then((respuesta) => respuesta.json())
      .then((datos) => {
        lista.innerHTML = "";
        let noLeidas = 0;

        datos.forEach((notificacion) => {
          if (notificacion.leido == 0) noLeidas++;

          const elemento = document.createElement("div");
          elemento.classList.add("notificacion-item");
          elemento.textContent =
            notificacion.tipo === "seguimiento"
              ? `${notificacion.origen_nombre} te ha seguido`
              : `${notificacion.origen_nombre} comentó en tu película`;

          elemento.addEventListener("click", () => {
            marcarYRedirigir(notificacion);
          });

          lista.appendChild(elemento);
        });

        contador.textContent =
          noLeidas > 0 ? (noLeidas > 9 ? "9+" : noLeidas) : "";
        contador.style.display = noLeidas > 0 ? "inline-block" : "none";
      });
  }

  icono.addEventListener("click", () => {
    lista.classList.toggle("show");
  });

  setInterval(cargarNotificaciones, 30000);
  cargarNotificaciones();
});
