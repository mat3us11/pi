// assets/js/criar-roteiro.js

/* ========= Upload da capa ========= */
const input = document.getElementById("foto-capa");
const preview = document.getElementById("preview");
const placeholder = document.querySelector(".placeholder");

if (input) {
  input.addEventListener("change", () => {
    const file = input.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => {
      preview.src = e.target.result;
      preview.style.display = "block";
      if (placeholder) placeholder.style.display = "none";
    };
    reader.readAsDataURL(file);
  });
}

/* ========= Dropdown de categorias ========= */
function toggleDropdown() {
  const dropdown = document.getElementById("dropdown-content");
  dropdown.style.display = dropdown.style.display === "flex" ? "none" : "flex";
}
window.toggleDropdown = toggleDropdown;

const checkboxes = document.querySelectorAll(".dropdown-content input[type='checkbox']");
const dropdownText = document.getElementById("dropdown-text");
checkboxes.forEach(checkbox => {
  checkbox.addEventListener("change", () => {
    const selecionados = Array.from(checkboxes)
      .filter(cb => cb.checked)
      .map(cb => cb.parentElement.innerText.trim());
    dropdownText.textContent = selecionados.length
      ? selecionados.join(", ")
      : "Selecione uma ou mais categorias";
  });
});

/* ========= Autocomplete (Mapbox visual) ========= */
function debounce(fn, delay){ let t; return (...a)=>{ clearTimeout(t); t=setTimeout(()=>fn(...a), delay); }; }
const accessToken = "pk.eyJ1IjoiY2FtcHZpYSIsImEiOiJjbWRldzIwbnUwNnlqMmpvOHo5NnN6ZW42In0.-Dsl7B1W4suz30SbKnzJKg";
const bboxSP = "-53.0,-25.5,-44.0,-19.0";

async function fetchPlaces(query, inputId) {
  if (!query) return;
  const url = `https://api.mapbox.com/geocoding/v5/mapbox.places/${encodeURIComponent(query)}.json` +
              `?access_token=${accessToken}&autocomplete=true&limit=5&language=pt&bbox=${bboxSP}&country=BR`;
  const res = await fetch(url);
  const data = await res.json();
  showSuggestions(data.features || [], inputId);
}
const fetchPlacesDebounced = debounce(fetchPlaces, 300);

const startInput = document.getElementById("pontoPartida");
const destInput  = document.getElementById("destino");
if (startInput) startInput.addEventListener("input", e => fetchPlacesDebounced(e.target.value, "pontoPartida"));
if (destInput)  destInput .addEventListener("input", e => fetchPlacesDebounced(e.target.value, "destino"));

function showSuggestions(places, inputId) {
  const input = document.getElementById(inputId);
  hideAllSuggestions();

  const list = document.createElement("ul");
  list.classList.add("suggestions");

  places.forEach(place => {
    const item = document.createElement("li");
    item.textContent = place.place_name;
    item.onclick = () => {
      input.value = place.place_name;
      // se for campo de parada, grava no hidden-JSON irmão
      let hidden = document.getElementById(inputId + "_json");
      if (hidden) {
        hidden.value = JSON.stringify({
          nome: place.place_name,
          lat: place.center?.[1] ?? null,
          lon: place.center?.[0] ?? null
        });
      }
      hideAllSuggestions();
    };
    list.appendChild(item);
  });

  input.parentNode.appendChild(list);
}
function hideAllSuggestions(){
  document.querySelectorAll(".suggestions").forEach(l => l.remove());
}
document.addEventListener("click", (e) => {
  const isInput = e.target.tagName === "INPUT" && e.target.closest(".rota-item");
  const isSuggestion = e.target.closest(".suggestions");
  const isDropdown = e.target.closest(".dropdown");
  if (!isInput && !isSuggestion && !isDropdown) hideAllSuggestions();
});

/* ========= Paradas dinâmicas ========= */
const btnAddParada = document.getElementById("btn-add-parada");
const paradasDiv = document.getElementById("paradas");

if (btnAddParada && paradasDiv) {
  btnAddParada.addEventListener("click", () => {
    const index = paradasDiv.children.length + 1;
    const parada = document.createElement("div");
    parada.classList.add("rota-item");
    const inputId = `parada${index}`;
    parada.innerHTML = `
      <label><i class="ph ph-map-pin-line"></i> Parada ${index}</label>
      <input type="text" id="${inputId}" placeholder="Localização">
      <input type="hidden" id="${inputId}_json" name="paradas[]">
    `;
    paradasDiv.appendChild(parada);
    document.getElementById(inputId).addEventListener("input", e => fetchPlacesDebounced(e.target.value, inputId));
  });
}

/* ========= Modal Sugerir Locais (simples) ========= */
const btnSugerir = document.getElementById("btn-sugerir-pois");
const modal = document.getElementById("modal-sugestoes");
const closeModalBtn = document.getElementById("close-modal");
const addSelecionadosBtn = document.getElementById("add-selecionados");
const sugestoesList = document.getElementById("sugestoes-list");

function openModal(){ modal.style.display = 'flex'; }
function closeModal(){ modal.style.display = 'none'; }

if (btnSugerir && modal) {
  btnSugerir.addEventListener("click", async () => {
    const city = (document.getElementById("destino")?.value || '').trim();
    if (!city) { alert("Preencha o Destino primeiro."); return; }

    // categorias marcadas
    const cats = Array.from(document.querySelectorAll("input[name='categorias[]']:checked"))
      .map(e => e.value).join(',');

    sugestoesList.innerHTML = "<p>Carregando...</p>";
    openModal();

    try {
      const url = `../processos/sugerir-pois.php?city=${encodeURIComponent(city)}&cats=${encodeURIComponent(cats)}&limit=20`;
      const res = await fetch(url);
      const data = await res.json();

      if (!data.pois || !data.pois.length) {
        sugestoesList.innerHTML = "<p>Nenhum local encontrado.</p>";
        return;
      }

      // grid simples de cards
      const grid = document.createElement('div');
      grid.className = 'sugestoes-grid';

      data.pois.forEach((p, i) => {
        const card = document.createElement('label');
        card.className = 'sug-card';
        card.innerHTML = `
          <input class="sug-check" type="checkbox" data-json='${JSON.stringify(p)}'>
          <img class="sug-thumb" src="${p.image_url || ''}" alt="">
          <div class="sug-content">
            <div class="sug-titulo">${p.nome || 'Local'}</div>
            <div class="sug-cat">${(p.categoria || '').replace(':',' • ')}</div>
          </div>
        `;
        grid.appendChild(card);
      });

      sugestoesList.innerHTML = '';
      sugestoesList.appendChild(grid);
    } catch (err) {
      sugestoesList.innerHTML = "<p>Erro ao buscar locais.</p>";
    }
  });
}
if (closeModalBtn) closeModalBtn.addEventListener("click", closeModal);
window.addEventListener("click", (e) => { if (e.target === modal) closeModal(); });

// realce simples no card quando marcar
if (sugestoesList) {
  sugestoesList.addEventListener('change', (e) => {
    const chk = e.target.closest('.sug-check');
    if (!chk) return;
    const card = chk.closest('.sug-card');
    if (!card) return;
    card.classList.toggle('is-selected', chk.checked);
  });
}

/* ========= Adicionar selecionados às paradas ========= */
if (addSelecionadosBtn && paradasDiv) {
  addSelecionadosBtn.addEventListener("click", () => {
    const checks = sugestoesList.querySelectorAll(".sug-check:checked");
    if (!checks.length) { alert("Selecione pelo menos um local."); return; }

    checks.forEach(chk => {
      const p = JSON.parse(chk.getAttribute('data-json') || '{}');
      if (!p || !p.nome) return;

      const index = paradasDiv.children.length + 1;
      const parada = document.createElement("div");
      parada.classList.add("rota-item");
      const inputId = `parada${index}`;
      parada.innerHTML = `
        <label><i class="ph ph-map-pin-line"></i> Parada ${index}</label>
        <input type="text" id="${inputId}" value="${p.nome}">
        <input type="hidden" id="${inputId}_json" name="paradas[]" value='${JSON.stringify({nome:p.nome, lat:p.lat, lon:p.lon})}'>
      `;
      paradasDiv.appendChild(parada);
    });

    closeModal();
  });
}

/* ========= Evitar duplo clique em "Publicar" ========= */
const form = document.querySelector('form[action=""][method="POST"]');
if (form) {
  form.addEventListener('submit', (e) => {
    const btn = form.querySelector('.btn-principal');
    if (btn && btn.disabled) { e.preventDefault(); return false; }
    if (btn) {
      btn.disabled = true;
      btn.dataset.originalText = btn.textContent;
      btn.textContent = 'Publicando...';
    }
  });
}
