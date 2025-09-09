document.addEventListener("DOMContentLoaded", () => {
  const icono = document.getElementById("icono-notificaciones");
  const lista = document.getElementById("lista-notificaciones");
  const contador = document.getElementById("contador-notificaciones");

  function cargarNotificaciones() {
    fetch("/includes/notificaciones_ajax.php")
      .then((res) => res.json())
      .then((data) => {
        lista.innerHTML = "";
        let noLeidas = 0;

        data.forEach((n) => {
          if (n.leido == 0) noLeidas++;
          const item = document.createElement("div");
          item.classList.add("notificacion-item");
          item.textContent =
            n.tipo === "seguimiento"
              ? `${n.origen_nombre} te ha seguido`
              : `${n.origen_nombre} comentó en tu película`;
          lista.appendChild(item);
        });

        contador.textContent = noLeidas > 0 ? noLeidas : "";
      });
  }

  // Toggle dropdown
  icono.addEventListener("click", () => {
    lista.classList.toggle("show");
    // marcar como leídas al abrir
    fetch("/includes/marcar_notificaciones.php", { method: "POST" }).then(
      () => {
        contador.textContent = "";
      }
    );
  });

  // Actualizar cada 30 segundos
  setInterval(cargarNotificaciones, 30000);
  cargarNotificaciones();
});
