
const input = document.getElementById("foto-capa");
const preview = document.getElementById("preview");
const placeholder = document.querySelector(".placeholder");

input.addEventListener("change", () => {
    const file = input.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = e => {
            preview.src = e.target.result;
            preview.style.display = "block";
            placeholder.style.display = "none";
        };
        reader.readAsDataURL(file);
    }
});

function toggleDropdown() {
  const dropdown = document.getElementById("dropdown-content");
  dropdown.style.display = dropdown.style.display === "flex" ? "none" : "flex";
}

const checkboxes = document.querySelectorAll(".dropdown-content input[type='checkbox']");
const dropdownText = document.getElementById("dropdown-text");

checkboxes.forEach(checkbox => {
  checkbox.addEventListener("change", () => {
    const selecionados = Array.from(checkboxes)
      .filter(cb => cb.checked)
      .map(cb => cb.parentElement.innerText);

    dropdownText.textContent = selecionados.length > 0
      ? selecionados.join(", ")
      : "Selecione uma ou mais categorias";
  });
});

const accessToken = "pk.eyJ1IjoiY2FtcHZpYSIsImEiOiJjbWRldzIwbnUwNnlqMmpvOHo5NnN6ZW42In0.-Dsl7B1W4suz30SbKnzJKg";
const bboxSP = "-53.0,-25.5,-44.0,-19.0"; 

async function fetchPlaces(query, inputId) {
  if (!query) return;

  const url = `https://api.mapbox.com/geocoding/v5/mapbox.places/${encodeURIComponent(query)}.json?` +
              `access_token=${accessToken}&autocomplete=true&limit=5&language=pt&bbox=${bboxSP}&country=BR`;

  const response = await fetch(url);
  const data = await response.json();

  showSuggestions(data.features, inputId);
}

function showSuggestions(places, inputId) {
  const input = document.getElementById(inputId);
  hideAllSuggestions();
  let list = document.createElement("ul");
  list.classList.add("suggestions");

  places.forEach(place => {
    let item = document.createElement("li");
    item.textContent = place.place_name;
    item.onclick = () => {
      input.value = place.place_name;
      hideAllSuggestions();
    };
    list.appendChild(item);
  });

  input.parentNode.appendChild(list);
}

function hideAllSuggestions() {
  document.querySelectorAll(".suggestions").forEach(list => list.remove());
}

document.getElementById("pontoPartida").addEventListener("input", e => fetchPlaces(e.target.value, "pontoPartida"));
document.getElementById("destino").addEventListener("input", e => fetchPlaces(e.target.value, "destino"));

const btnAddParada = document.getElementById("btn-add-parada");
const paradasDiv = document.getElementById("paradas");

btnAddParada.addEventListener("click", () => {
  const index = paradasDiv.children.length + 1;
  const parada = document.createElement("div");
  parada.classList.add("rota-item");

  parada.innerHTML = `
    <label><i class="ph ph-map-pin-line"></i> Parada ${index}</label>
    <input type="text" id="parada${index}" name="paradas[]" placeholder="Localização">
  `;

  paradasDiv.appendChild(parada);

  const inputParada = parada.querySelector("input");
  inputParada.addEventListener("input", e => fetchPlaces(e.target.value, inputParada.id));
});
document.addEventListener("click", (e) => {
  const isInput = e.target.tagName === "INPUT" && e.target.closest(".rota-item");
  const isSuggestion = e.target.closest(".suggestions");
  const isDropdown = e.target.closest(".dropdown");
  if (!isInput && !isSuggestion && !isDropdown) {
    hideAllSuggestions();
  }
});
