// assets/js/editar-roteiros.js

/* ========= Prévia da capa ========= */
(function initCapaPreview() {
  const input = document.getElementById("foto-capa");
  const preview = document.getElementById("preview");
  const placeholder = document.querySelector(".placeholder");
  if (!input || !preview) return;

  input.addEventListener("change", () => {
    const file = input.files && input.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = (e) => {
      preview.src = e.target.result;
      preview.style.display = "block";
      if (placeholder) placeholder.style.display = "none";
    };
    reader.readAsDataURL(file);
  });
})();

/* ========= Dropdown de categorias ========= */
function toggleDropdown() {
  const dropdown = document.getElementById("dropdown-content");
  if (!dropdown) return;
  dropdown.style.display = dropdown.style.display === "flex" ? "none" : "flex";
}
(function initCategoriasDropdown() {
  const dropdown = document.getElementById("dropdown-content");
  const dropdownText = document.getElementById("dropdown-text");
  const header = document.querySelector(".dropdown-header");
  if (!dropdown || !dropdownText || !header) return;

  const setOpen = (open) => dropdown.style.display = open ? "flex" : "none";

  function atualizarTexto() {
    const selecionados = Array.from(dropdown.querySelectorAll("input[type='checkbox']:checked"))
      .map(cb => cb.parentElement.innerText.trim());
    dropdownText.textContent = selecionados.length
      ? selecionados.join(", ")
      : "Selecione uma ou mais categorias";
  }

  header.addEventListener("click", () => setOpen(dropdown.style.display !== "flex"));
  dropdown.addEventListener("change", atualizarTexto);
  document.addEventListener("click", (e) => { if (!e.target.closest(".dropdown")) setOpen(false); });
  document.addEventListener("keydown", (e) => { if (e.key === "Escape") setOpen(false); });

  // estado inicial
  atualizarTexto();
})();

/* ========= Util: debounce ========= */
function debounce(fn, delay) { let t; return (...args)=>{ clearTimeout(t); t=setTimeout(()=>fn(...args), delay); }; }

/* ========= Autocomplete Mapbox ========= */
(function initAutocomplete() {
  const accessToken = "pk.eyJ1IjoiY2FtcHZpYSIsImEiOiJjbWRldzIwbnUwNnlqMmpvOHo5NnN6ZW42In0.-Dsl7B1W4suz30SbKnzJKg";
  const bboxSP = "-53.0,-25.5,-44.0,-19.0";
  const controllers = new Map();

  function cancelPrev(inputId) {
    const prev = controllers.get(inputId);
    if (prev) prev.abort();
    const c = new AbortController();
    controllers.set(inputId, c);
    return c.signal;
  }

  async function fetchPlaces(query, inputId) {
    if (!query) return;
    const signal = cancelPrev(inputId);
    const url = `https://api.mapbox.com/geocoding/v5/mapbox.places/${encodeURIComponent(query)}.json?access_token=${accessToken}&autocomplete=true&limit=5&language=pt&bbox=${bboxSP}&country=BR`;
    try {
      const res = await fetch(url, { signal });
      const data = await res.json();
      showSuggestions(data.features || [], inputId);
    } catch (_) {}
  }
  const fetchPlacesDebounced = debounce(fetchPlaces, 250);

  function installKeyboardNav(input) {
    input.addEventListener("keydown", (e) => {
      const list = (input.closest(".rota-item") || input.parentElement || input).querySelector(".suggestions");
      if (!list) return;
      const items = Array.from(list.querySelectorAll("li"));
      if (!items.length) return;

      const current = list.querySelector("li.active");
      let idx = current ? items.indexOf(current) : -1;

      if (e.key === "ArrowDown") {
        e.preventDefault(); idx = (idx + 1) % items.length; markActive(list, idx);
      } else if (e.key === "ArrowUp") {
        e.preventDefault(); idx = (idx - 1 + items.length) % items.length; markActive(list, idx);
      } else if (e.key === "Enter") {
        if (idx >= 0) { e.preventDefault(); items[idx].click(); }
      } else if (e.key === "Escape") {
        list.remove();
      }
    });
  }
  function markActive(list, index) {
    const items = Array.from(list.querySelectorAll("li"));
    items.forEach(li => li.classList.remove("active"));
    const target = items[index];
    if (target) { target.classList.add("active"); target.scrollIntoView({ block: "nearest" }); }
  }

  function showSuggestions(places, inputId) {
    const input = document.getElementById(inputId);
    if (!input) return;
    const container = input.closest(".rota-item") || input.parentElement || input;
    container.style.position = container.style.position || "relative";

    const old = container.querySelector(".suggestions");
    if (old) old.remove();

    const list = document.createElement("ul");
    list.className = "suggestions";
    list.setAttribute("data-for", inputId);

    places.forEach((place, idx) => {
      const li = document.createElement("li");
      li.textContent = place.place_name;
      li.tabIndex = 0;
      li.addEventListener("click", () => {
        input.value = place.place_name;
        list.remove();
        input.dispatchEvent(new Event("change"));
      });
      li.addEventListener("mouseenter", () => markActive(list, idx));
      list.appendChild(li);
    });

    container.appendChild(list);
  }

  function closeAllSuggestions() {
    document.querySelectorAll(".suggestions").forEach(ul => ul.remove());
  }
  document.addEventListener("click", (e) => {
    const isInput = e.target.tagName === "INPUT" && e.target.closest(".rota-item");
    const isSuggestion = e.target.closest(".suggestions");
    const isDropdown = e.target.closest(".dropdown");
    if (!isInput && !isSuggestion && !isDropdown) closeAllSuggestions();
  });
  document.addEventListener("keydown", (e) => { if (e.key === "Escape") closeAllSuggestions(); });

  const partida = document.getElementById("pontoPartida");
  const destino  = document.getElementById("destino");
  if (partida) { partida.addEventListener("input", e => fetchPlacesDebounced(e.target.value, "pontoPartida")); installKeyboardNav(partida); }
  if (destino) { destino.addEventListener("input", e => fetchPlacesDebounced(e.target.value, "destino")); installKeyboardNav(destino); }

  const btnAddParada = document.getElementById("btn-add-parada");
  const paradasDiv = document.getElementById("paradas");

  function bindParadaInput(input) {
    if (!input) return;
    input.addEventListener("input", e => fetchPlacesDebounced(e.target.value, input.id));
    installKeyboardNav(input);
  }

  if (paradasDiv) {
    paradasDiv.querySelectorAll("input[type='text']").forEach(bindParadaInput);
  }

  if (btnAddParada && paradasDiv) {
    btnAddParada.addEventListener("click", () => {
      const index = paradasDiv.children.length + 1;
      const wrap = document.createElement("div");
      wrap.className = "rota-item";
      const id = `parada${index}`;
      wrap.innerHTML = `<input type="text" id="${id}" name="paradas[]" placeholder="Localização">`;
      paradasDiv.appendChild(wrap);
      bindParadaInput(wrap.querySelector("input"));
    });
  }
})();

