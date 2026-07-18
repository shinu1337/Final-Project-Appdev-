localStorage.removeItem('ll:items');
document.addEventListener("DOMContentLoaded", () => {
  document.getElementById("sIcon").innerHTML = iconHtml("search");
  document.getElementById("fh").innerHTML = iconHtml("sliders") + " Filters";

  const params = new URLSearchParams(location.search);
  let cat = params.get("cat") || "All";
  let q = params.get("q") || "";
  let max = 100;
  let sort = "popular";
  let availOnly = false;

  const qEl = document.getElementById("q");
  const maxEl = document.getElementById("max");
  const maxLabel = document.getElementById("maxLabel");
  const sortEl = document.getElementById("sort");
  const availEl = document.getElementById("availOnly");
  qEl.value = q;

  document.getElementById("catRow").innerHTML = CATEGORIES.map(c =>
    `<button class="chip ${c===cat?'active':''}" data-cat="${c}">${c}</button>`
  ).join("");

  document.getElementById("catRow").addEventListener("click", (e) => {
    const b = e.target.closest("[data-cat]"); if (!b) return;
    cat = b.dataset.cat;
    document.querySelectorAll("#catRow .chip").forEach(c => c.classList.toggle("active", c.dataset.cat===cat));
    render();
  });
  qEl.addEventListener("input", (e) => { q = e.target.value; render(); });
  maxEl.addEventListener("input", (e) => { max = +e.target.value; maxLabel.textContent = `Up to $${max}`; render(); });
  sortEl.addEventListener("change", (e) => { sort = e.target.value; render(); });
  availEl.addEventListener("change", (e) => { availOnly = e.target.checked; render(); });

  // Quick add form
  document.getElementById("quickCat").innerHTML = CATEGORIES.slice(1).map(c => `<option>${c}</option>`).join("");
  document.getElementById("quickAdd").addEventListener("click", () => {
    const title = document.getElementById("quickTitle").value.trim();
    const price = Number(document.getElementById("quickPrice").value);
    const category = document.getElementById("quickCat").value;
    if (!title || price <= 0) { toast("Enter an item name and cost"); return; }
    const newItem = {
      id: "it-" + Date.now(),
      title,
      category,
      price,
      period: "day",
      location: getUser().location || "Local",
      rating: 0,
      reviews: 0,
      image: IMGS[Math.floor(Math.random() * IMGS.length)],
      owner: getUser().name || "You",
      ownerAvatar: getUser().avatar || "",
      description: "Newly listed item.",
      available: true,
    };
    const items = getItems(); items.unshift(newItem); LS.set("ll:items", items);
    const my = LS.get("ll:myListings", []); my.unshift(newItem.id); LS.set("ll:myListings", my);
    toast(`"${title}" added for $${price}/day`);
    document.getElementById("quickTitle").value = "";
    document.getElementById("quickPrice").value = "";
    render();
  });

  function render() {
    let arr = getItems().filter(i =>
      (cat === "All" || i.category === cat) &&
      i.price <= max &&
      i.title.toLowerCase().includes(q.toLowerCase()) &&
      (!availOnly || i.available)
    );
    if (sort === "price") arr = arr.slice().sort((a,b) => a.price - b.price);
    if (sort === "rating") arr = arr.slice().sort((a,b) => b.rating - a.rating);

    document.getElementById("resultCount").textContent = `${arr.length} results near you`;
    document.getElementById("grid").innerHTML = arr.map(itemCardHtml).join("");
    document.getElementById("empty").classList.toggle("hidden", arr.length !== 0);
  }
  render();
});
