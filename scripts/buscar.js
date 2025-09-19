document.addEventListener("DOMContentLoaded", () => {
  const searchIcon = document.getElementById("search-icon");
  const searchInput = document.getElementById("search-input");
  const searchResults = document.getElementById("search-results");

  if (!searchIcon || !searchInput || !searchResults) return;

  // =========================
  // Mostrar/ocultar input con animación
  // =========================
  searchIcon.addEventListener("click", () => {
    searchInput.classList.toggle("active");
    if (searchInput.classList.contains("active")) {
      searchInput.focus();
    } else {
      searchResults.style.display = "none";
      searchResults.innerHTML = "";
    }
  });

  // =========================
  // Buscar en tiempo real con AJAX
  // =========================
  searchInput.addEventListener("keyup", () => {
    const query = searchInput.value.trim();

    if (query.length < 2) {
      searchResults.style.display = "none";
      searchResults.innerHTML = "";
      return;
    }

    fetch(
      `${APP_URL}includes/buscar_ajax.php?query=${encodeURIComponent(query)}`
    )
      .then((res) => res.json())
      .then((data) => {
        if (data.success && data.results.length > 0) {
          // Generar enlaces de resultados
          searchResults.innerHTML = data.results
            .map(
              (item) =>
                `<a href="${item.url}" class="search-item">${item.texto}</a>`
            )
            .join("");
          searchResults.style.display = "flex";
        } else {
          searchResults.innerHTML = `<p style="padding:10px;color:#666;">Sin resultados</p>`;
          searchResults.style.display = "flex";
          console.log("Depuración búsqueda:", data.debug);
        }
      })
      .catch((err) => {
        console.error("Error en la búsqueda AJAX:", err);
        searchResults.innerHTML = `<p style="padding:10px;color:#666;">Error al buscar</p>`;
        searchResults.style.display = "flex";
      });
  });

  // =========================
  // Cerrar resultados al hacer click fuera
  // =========================
  document.addEventListener("click", (e) => {
    if (
      !searchResults.contains(e.target) &&
      e.target !== searchInput &&
      e.target !== searchIcon
    ) {
      searchResults.style.display = "none";
    }
  });
});
