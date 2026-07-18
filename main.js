const savedTheme = localStorage.getItem('theme') || 'dark';
document.documentElement.setAttribute('data-theme', savedTheme);

const ICONS = {
  package: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16.5 9.4 7.55 4.24"/><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><path d="M3.29 7 12 12l8.71-5"/><path d="M12 22V12"/></svg>',
  search: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>',
  star: '<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>',
  mapPin: '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>',
  arrow: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>',
  bell: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>',
  menu: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="4" y1="7" x2="20" y2="7"/><line x1="4" y1="12" x2="20" y2="12"/><line x1="4" y1="17" x2="20" y2="17"/></svg>',
  eye: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>',
  edit: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>',
  trash: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>',
  check: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>',
  x: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>',
  message: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2Z"/></svg>',
  plus: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>',
  trending: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/></svg>',
  dollar: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>',
  clock: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>',
  shield: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/><polyline points="9 12 11 14 15 10"/></svg>',
  wallet: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12V7H5a2 2 0 0 1 0-4h14v4"/><path d="M3 5v14a2 2 0 0 0 2 2h16v-5"/><path d="M18 12a2 2 0 0 0 0 4h4v-4Z"/></svg>',
  sparkles: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3-1.9 5.6L4.5 10.5l5.6 1.9L12 18l1.9-5.6 5.6-1.9-5.6-1.9Z"/></svg>',
  sliders: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="4" y1="21" x2="4" y2="14"/><line x1="4" y1="10" x2="4" y2="3"/><line x1="12" y1="21" x2="12" y2="12"/><line x1="12" y1="8" x2="12" y2="3"/><line x1="20" y1="21" x2="20" y2="16"/><line x1="20" y1="12" x2="20" y2="3"/><line x1="1" y1="14" x2="7" y2="14"/><line x1="9" y1="8" x2="15" y2="8"/><line x1="17" y1="16" x2="23" y2="16"/></svg>',
  sun: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>',
  moon: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>'
};

function iconHtml(name) { return ICONS[name] || ""; }

function updateThemeButton() {
  const btn = document.getElementById('theme-toggle');
  if (!btn) return;
  const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
  btn.innerHTML = isDark ? iconHtml('sun') : iconHtml('moon');
}

const CATEGORIES = ["All", "Electronics", "Tools", "Sports", "Outdoors", "Music", "Vehicles", "Party", "Books"];
const IMGS = [
  "https://images.unsplash.com/photo-1519183071298-a2962be96f83?w=800",
  "https://images.unsplash.com/photo-1526178613552-2b45c6c302f0?w=800",
  "https://images.unsplash.com/photo-1544441893-675973e31985?w=800",
  "https://images.unsplash.com/photo-1449426468159-d96dbf08f19f?w=800",
];

const ITEMS = [
  { id: "it-1",  title: "Sony Alpha a6400 Camera",  category: "Electronics", price: 20, period: "day", location: "Your City", rating: 4.8, reviews: 12, image: IMGS[0], owner: "Owner", ownerAvatar: "", description: "Edit this description with your item's details.", available: true },
  { id: "it-2",  title: "Item Name 2",  category: "Tools",       price: 15, period: "day", location: "Your City", rating: 4.6, reviews: 9,  image: IMGS[1], owner: "Owner", ownerAvatar: "", description: "Edit this description with your item's details.", available: true },
  { id: "it-3",  title: "Item Name 3",  category: "Sports",      price: 25, period: "day", location: "Your City", rating: 4.7, reviews: 18, image: IMGS[2], owner: "Owner", ownerAvatar: "", description: "Edit this description with your item's details.", available: true },
  { id: "it-4",  title: "Item Name 4",  category: "Outdoors",    price: 18, period: "day", location: "Your City", rating: 4.5, reviews: 7,  image: IMGS[3], owner: "Owner", ownerAvatar: "", description: "Edit this description with your item's details.", available: true },
  { id: "it-5",  title: "Item Name 5",  category: "Music",       price: 30, period: "day", location: "Your City", rating: 4.9, reviews: 22, image: IMGS[0], owner: "Owner", ownerAvatar: "", description: "Edit this description with your item's details.", available: true },
  { id: "it-6",  title: "Item Name 6",  category: "Electronics", price: 40, period: "day", location: "Your City", rating: 4.7, reviews: 14, image: IMGS[1], owner: "Owner", ownerAvatar: "", description: "Edit this description with your item's details.", available: true },
  { id: "it-7",  title: "Item Name 7",  category: "Party",       price: 40, period: "day", location: "Your City", rating: 4.6, reviews: 10, image: IMGS[2], owner: "Owner", ownerAvatar: "", description: "Edit this description with your item's details.", available: true },
  { id: "it-8",  title: "Item Name 8",  category: "Books",       price: 5,  period: "day", location: "Your City", rating: 4.8, reviews: 6,  image: IMGS[3], owner: "Owner", ownerAvatar: "", description: "Edit this description with your item's details.", available: true },
];
const RENTAL_REQUESTS = [];

const LS = {
  get(key, fallback) {
    try { const v = localStorage.getItem(key); return v ? JSON.parse(v) : fallback; }
    catch { return fallback; }
  },
  set(key, value) { try { localStorage.setItem(key, JSON.stringify(value)); } catch {} },
  push(key, value) { const arr = LS.get(key, []); arr.push(value); LS.set(key, arr); },
};

function initStore() {
  const SCHEMA = "v4-preset";
  if (LS.get("ll:schema") !== SCHEMA) {
    ["ll:items", "ll:requests", "ll:myListings", "ll:favorites", "ll:user"].forEach(k => localStorage.removeItem(k));
    LS.set("ll:schema", SCHEMA);
  }
  if (!LS.get("ll:items")) LS.set("ll:items", ITEMS);
  if (!LS.get("ll:requests")) LS.set("ll:requests", RENTAL_REQUESTS);
  if (!LS.get("ll:myListings")) LS.set("ll:myListings", []);
  if (!LS.get("ll:favorites")) LS.set("ll:favorites", []);
  if (!LS.get("ll:user")) LS.set("ll:user", { name: "", email: "", avatar: "", location: "", bio: "" });
}

function getItems() { return LS.get("ll:items", ITEMS); }
function getRequests() { return LS.get("ll:requests", RENTAL_REQUESTS); }
function getMyListings() {
  const ids = LS.get("ll:myListings", []);
  return getItems().filter(i => ids.includes(i.id));
}
function getUser() { return LS.get("ll:user") || { name: "", email: "", avatar: "", location: "", bio: "" }; }

function renderChrome() {
  const path = location.pathname.split("/").pop() || "index.html";
  const navPath = (p) => path === p ? "active" : "";
  const navHtml = `
    <nav class="navbar">
      <div class="nav-inner">
        <a href="index.html" class="brand">
          <span class="brand-mark">${iconHtml('package')}</span>
          <span class="brand-name">Share<em>Nest</em></span>
        </a>
        <div class="nav-links" id="navLinks">
          <a href="browse.html" class="${navPath('browse.html')}">Browse</a>
          <a href="dashboard.html" class="${navPath('dashboard.html')}">Dashboard</a>
          <a href="listings.html" class="${navPath('listings.html')}">My Listings</a>
          <a href="requests.html" class="${navPath('requests.html')}">Requests</a>
          <a href="post-item.html" class="${navPath('post-item.html')}">Post Item</a>
        </div>
        <div class="nav-actions">
          <div class="search-box">
            ${iconHtml('search')}
            <input type="text" placeholder="Search items…" id="navSearch" />
          </div>
          <button id="theme-toggle" class="icon-btn" title="Toggle Theme"></button>
          <a href="profile.html" class="icon-btn" title="Profile" aria-label="Profile">
            ${iconHtml('package')}
          </a>
          <button class="icon-btn menu-toggle" id="menuToggle" aria-label="Menu">${iconHtml('menu')}</button>
        </div>
      </div>
    </nav>
  `;
  const footerHtml = `
    <footer class="footer">
      <div class="footer-grid">
        <div>
          <div class="brand" style="margin-bottom:.75rem">
            <span class="brand-mark">${iconHtml('package')}</span>
            <span class="brand-name">Share<em>Nest</em></span>
          </div>
          <p class="muted text-sm" style="max-width:280px">The neighborhood marketplace for renting tools, tech and toys — trusted by thousands of neighbors.</p>
        </div>
        <div><h4>Marketplace</h4><ul>
          <li><a href="browse.html">Browse</a></li>
          <li><a href="post-item.html">List an item</a></li>
          <li><a href="#">How it works</a></li>
        </ul></div>
        <div><h4>Company</h4><ul>
          <li><a href="#">About</a></li>
          <li><a href="#">Careers</a></li>
          <li><a href="#">Contact</a></li>
        </ul></div>
        <div><h4>Legal</h4><ul>
          <li><a href="#">Terms</a></li>
          <li><a href="#">Privacy</a></li>
          <li><a href="#">Trust & Safety</a></li>
        </ul></div>
      </div>
      <div class="footer-bottom">© 2026 ShareNest. All rights reserved.</div>
    </footer>
  `;
  const navSlot = document.getElementById("nav");
  if (navSlot) navSlot.outerHTML = navHtml;
  const footSlot = document.getElementById("footer");
  if (footSlot) footSlot.outerHTML = footerHtml;

  updateThemeButton();

  document.getElementById("menuToggle")?.addEventListener("click", () => {
    document.getElementById("navLinks")?.classList.toggle("open");
  });
 
  document.getElementById("navSearch")?.addEventListener("keydown", (e) => {
    if (e.key === "Enter") {
      const q = e.target.value.trim();
      location.href = "browse.html" + (q ? `?q=${encodeURIComponent(q)}` : "");
    }
  });
}

function itemCardHtml(item) {
  return `
    <a class="item-card" href="item-details.html?id=${item.id}">
      <div class="item-media">
        <img src="${item.image}" alt="${item.title}" loading="lazy" />
        <span class="tag">${item.category}</span>
        ${!item.available ? '<span class="tag tag-booked">Booked</span>' : ''}
      </div>
      <div class="item-body">
        <div class="item-title-row">
          <h3 class="item-title">${item.title}</h3>
          <span class="rating"><span class="star">${iconHtml('star')}</span>${item.rating.toFixed(1)}</span>
        </div>
        <div class="item-loc">${iconHtml('mapPin')} ${item.location}</div>
        <div class="item-price-row">
          <span class="item-price">$${item.price}<small>/${item.period}</small></span>
          <span class="item-reviews">${item.reviews} reviews</span>
        </div>
      </div>
    </a>`;
}

function toast(msg) {
  let el = document.querySelector(".toast");
  if (!el) { el = document.createElement("div"); el.className = "toast"; document.body.appendChild(el); }
  el.textContent = msg;
  el.classList.add("show");
  setTimeout(() => el.classList.remove("show"), 2200);
}

document.addEventListener('click', (e) => {
  if (e.target && (e.target.id === 'theme-toggle' || e.target.closest('#theme-toggle'))) {
    let currentTheme = document.documentElement.getAttribute('data-theme');
    let newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    document.documentElement.setAttribute('data-theme', newTheme);
    localStorage.setItem('theme', newTheme);
    updateThemeButton();
  }
});

initStore();
document.addEventListener("DOMContentLoaded", renderChrome);