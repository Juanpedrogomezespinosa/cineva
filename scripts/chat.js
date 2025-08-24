document.addEventListener("DOMContentLoaded", () => {
  const chatForm = document.getElementById("chat-form");
  const chatBox = document.getElementById("chat-box");

  const currentUserId = parseInt(
    chatForm.querySelector('input[name="receptor_id"]').dataset.currentUserId,
    10
  );

  // Guardar último mensaje para evitar duplicados
  let ultimoMensajeId = 0;

  function agregarMensaje(msgObj) {
    const { id, mensaje, nombre, creado_en, emisor_id } = msgObj;

    // Ignorar si ya está agregado
    if (id <= ultimoMensajeId) return;

    const div = document.createElement("div");
    div.classList.add("mensaje");
    if (emisor_id == currentUserId) div.classList.add("usuario");
    div.innerHTML = `<strong>${nombre}:</strong> ${mensaje} <small>${creado_en}</small>`;

    chatBox.appendChild(div);
    // Scroll suave hasta el último mensaje
    div.scrollIntoView({ behavior: "smooth", block: "end" });

    ultimoMensajeId = id;
  }

  // Enviar mensaje
  chatForm.addEventListener("submit", (e) => {
    e.preventDefault();
    const formData = new FormData(chatForm);

    fetch("../includes/mensajes_ajax.php", {
      method: "POST",
      body: formData,
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.success) {
          agregarMensaje({
            id: Date.now(), // ID temporal para el nuevo mensaje
            mensaje: data.mensaje,
            nombre: "Tú",
            creado_en: data.creado_en,
            emisor_id: currentUserId,
          });
          chatForm.mensaje.value = "";
        } else {
          alert(data.error || "Error al enviar mensaje.");
        }
      })
      .catch((err) => {
        console.error("Error en envío:", err);
        alert("Error de conexión.");
      });
  });

  // Cargar mensajes del servidor
  async function cargarMensajes() {
    try {
      const receptor_id = chatForm.receptor_id.value;
      const res = await fetch(
        `../includes/mensajes_ajax.php?receptor_id=${receptor_id}`
      );
      const data = await res.json();
      data.forEach(agregarMensaje);
    } catch (err) {
      console.error("Error al cargar mensajes:", err);
    }
  }

  // Carga inicial
  cargarMensajes();
  // Recarga cada 2 segundos
  setInterval(cargarMensajes, 2000);
});
