document.addEventListener("DOMContentLoaded", () => {
  const chatForm = document.getElementById("chat-form");
  const chatBox = document.getElementById("chat-box");

  const currentUserId = parseInt(
    chatForm.querySelector('input[name="receptor_id"]').dataset.currentUserId,
    10
  );
  const receptorId = parseInt(chatForm.receptor_id.value, 10);

  let ultimoMensajeId = 0;

  // Formatear hora en 24h, sin fecha
  function formatearHora24(creadoEn) {
    // creadoEn = "YYYY-MM-DD HH:MM:SS"
    const partes = creadoEn.split(" "); // ["YYYY-MM-DD", "HH:MM:SS"]
    if (partes.length < 2) return "";
    const tiempo = partes[1].split(":"); // ["HH", "MM", "SS"]
    const hh = tiempo[0].padStart(2, "0");
    const mm = tiempo[1].padStart(2, "0");
    return `${hh}:${mm}`;
  }

  function escapeHtml(texto) {
    const mapa = {
      "&": "&amp;",
      "<": "&lt;",
      ">": "&gt;",
      '"': "&quot;",
      "'": "&#039;",
    };
    return String(texto).replace(/[&<>"']/g, (m) => mapa[m]);
  }

  function agregarMensaje(msgObj) {
    const {
      id,
      mensaje,
      nombre,
      creado_en,
      emisor_id,
      avatar,
      avatar_usuario_actual,
    } = msgObj;

    if (id <= ultimoMensajeId) return;

    const div = document.createElement("div");
    div.classList.add("mensaje");

    if (emisor_id === currentUserId) div.classList.add("usuario");

    const avatarUrl =
      emisor_id === currentUserId
        ? avatar_usuario_actual || "default.png"
        : avatar || "default.png";

    const hora24 = formatearHora24(creado_en);

    div.innerHTML = `
      <img src="../img/avatars/${avatarUrl}" class="avatar" alt="Avatar de ${nombre}">
      <div class="contenido">
        <strong>${nombre}</strong>
        <div class="texto-mensaje">${escapeHtml(mensaje)}</div>
        <small class="hora">${hora24}</small>
      </div>
    `;

    chatBox.appendChild(div);
    div.scrollIntoView({ behavior: "smooth", block: "end" });

    ultimoMensajeId = id;
  }

  async function cargarMensajes() {
    try {
      const res = await fetch(
        `${window.MENSAJES_ENDPOINT}?receptor_id=${receptorId}&ultimo_id=${ultimoMensajeId}`
      );
      if (!res.ok) {
        console.error("Chat: error HTTP", res.status);
        return;
      }

      const data = await res.json();

      if (Array.isArray(data)) {
        data.forEach(agregarMensaje);
      } else if (data.success === false) {
        console.error("Error AJAX:", data.error, data.detalle ?? "");
      } else {
        console.warn("Datos recibidos no son un array:", data);
      }
    } catch (err) {
      console.error("Error al cargar mensajes:", err);
    }
  }

  chatForm.addEventListener("submit", async (e) => {
    e.preventDefault();
    const formData = new FormData(chatForm);

    try {
      const res = await fetch(window.MENSAJES_ENDPOINT, {
        method: "POST",
        body: formData,
      });

      if (!res.ok) {
        console.error("Chat: error HTTP en envío", res.status);
        return;
      }

      const data = await res.json();

      if (data.success) {
        agregarMensaje({
          id: data.id,
          mensaje: data.mensaje,
          nombre: "Tú",
          creado_en: data.creado_en,
          emisor_id: currentUserId,
          avatar_usuario_actual: data.avatar_usuario_actual,
        });
        chatForm.mensaje.value = "";
      } else {
        alert(data.error || "Error al enviar mensaje");
      }
    } catch (err) {
      console.error("Error en envío:", err);
      alert("Error de conexión");
    }
  });

  // Carga inicial y polling cada 2 segundos
  cargarMensajes();
  setInterval(cargarMensajes, 2000);
});
