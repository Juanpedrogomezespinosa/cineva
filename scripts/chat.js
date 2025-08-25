document.addEventListener("DOMContentLoaded", () => {
  const chatForm = document.getElementById("chat-form");
  const chatBox = document.getElementById("chat-box");

  const currentUserId = parseInt(
    chatForm.querySelector('input[name="receptor_id"]').dataset.currentUserId,
    10
  );
  const receptorId = parseInt(chatForm.receptor_id.value, 10);

  let ultimoMensajeId = 0;

  function agregarMensaje(msgObj) {
    const { id, mensaje, nombre, creado_en, emisor_id } = msgObj;

    if (id <= ultimoMensajeId) return;

    const div = document.createElement("div");
    div.classList.add("mensaje");
    if (emisor_id === currentUserId) div.classList.add("usuario");
    div.innerHTML = `<strong>${nombre}:</strong> ${mensaje} <small>${creado_en}</small>`;

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
