// assets/app.js (responsive enhancements)
// Frontend-only SPA demo for Farm Production Management (Livestock).
// This file is the same demo as before but includes small responsive navigation helpers
// and accessibility tweaks for mobile screens (hamburger, mobile nav panel, closing behavior).

const LS_PREFIX = 'farm_livestock_demo_v1_';
const LS_USERS = LS_PREFIX + 'users';
const LS_LIVESTOCK = LS_PREFIX + 'livestock';
const LS_FEED = LS_PREFIX + 'feed';
const LS_PROD = LS_PREFIX + 'production';
const LS_HEALTH = LS_PREFIX + 'health';
const LS_INV = LS_PREFIX + 'inventory';
const LS_FIN = LS_PREFIX + 'financials';
const LS_CHAT = LS_PREFIX + 'chat';

const YEAR = new Date().getFullYear();
document.getElementById('year').textContent = YEAR;

let currentUser = null;
let chatOpen = false;
let chartInstance = null;
let mobileNavOpen = false;

// ----------------- Utilities -----------------
const $ = (s) => document.querySelector(s);
const $all = (s) => Array.from(document.querySelectorAll(s));
const escapeHtml = (s) => String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
function generateId(prefix='id') { return prefix + '_' + Math.random().toString(36).slice(2,9); }
function read(key, fallback=[]) { try { return JSON.parse(localStorage.getItem(key) || 'null') ?? fallback; } catch(e){ return fallback; } }
function write(key, value) { localStorage.setItem(key, JSON.stringify(value)); }

// ----------------- Seed data (only if empty) -----------------
(function seed() {
  if (!read(LS_USERS).length) {
    write(LS_USERS, [
      { id: 'u_admin', username: 'admin', password: 'adminpass', role: 'admin', name: 'Administrator' },
      { id: 'u_user', username: 'user', password: 'userpass', role: 'user', name: 'Farm Staff' }
    ]);
  }
  if (!read(LS_LIVESTOCK).length) {
    write(LS_LIVESTOCK, [
      { id: generateId('a'), tag_id: 'TAG-001', type: 'Cow', breed: 'Friesian', dob: '2021-02-10', status: 'Active', notes: 'Milking' },
      { id: generateId('a'), tag_id: 'TAG-002', type: 'Goat', breed: 'Boer', dob: '2022-06-05', status: 'Pregnant', notes: '' }
    ]);
  }
  if (!read(LS_FEED).length) {
    write(LS_FEED, [
      { id: generateId('f'), name: 'Hay Bale', quantity: 120, unit: 'bales', low_stock_threshold: 10 },
      { id: generateId('f'), name: 'Concentrate', quantity: 250, unit: 'kg', low_stock_threshold: 20 }
    ]);
  }
  if (!read(LS_PROD).length) write(LS_PROD, []);
  if (!read(LS_HEALTH).length) write(LS_HEALTH, []);
  if (!read(LS_INV).length) write(LS_INV, []);
  if (!read(LS_FIN).length) write(LS_FIN, []);
})();

// ----------------- Responsive nav helpers -----------------
function toggleMobileNav() {
  mobileNavOpen = !mobileNavOpen;
  if (mobileNavOpen) {
    openMobileNavPanel();
  } else {
    closeMobileNavPanel();
  }
}
function openMobileNavPanel() {
  // create panel if not exists
  let panel = document.getElementById('mobile-nav-panel');
  if (!panel) {
    panel = document.createElement('div');
    panel.id = 'mobile-nav-panel';
    panel.className = 'mobile-nav-panel';
    panel.innerHTML = `
      <div class="mobile-nav-inner">
        <button class="nav-btn" onclick="navigate('dashboard')">Dashboard</button>
        <button class="nav-btn" onclick="navigate('mylivestock')">My Livestock</button>
        <button class="nav-btn" onclick="navigate('feed')">Feed</button>
        <button class="nav-btn" onclick="navigate('production')">Production</button>
        <button class="nav-btn" onclick="navigate('health')">Health</button>
        <button class="nav-btn" onclick="navigate('inventory')">Inventory</button>
        <button class="nav-btn" onclick="navigate('reports')">Reports</button>
        <div id="mobile-admin-actions" style="margin-top:10px;display:none">
          <button class="nav-btn" onclick="navigate('users')">User Management</button>
          <button class="nav-btn" onclick="navigate('settings')">Settings</button>
        </div>
      </div>
    `;
    document.body.appendChild(panel);
  }
  // show/hide admin actions
  const adminPanel = document.getElementById('mobile-admin-actions');
  adminPanel.style.display = (currentUser && currentUser.role === 'admin') ? 'block' : 'none';
  panel.classList.add('open');
  // set aria and focus
  panel.setAttribute('aria-hidden', 'false');
}
function closeMobileNavPanel() {
  const panel = document.getElementById('mobile-nav-panel');
  if (panel) {
    panel.classList.remove('open');
    panel.setAttribute('aria-hidden', 'true');
  }
  mobileNavOpen = false;
}

// Close mobile nav when resizing to desktop
window.addEventListener('resize', () => {
  if (window.innerWidth > 900) {
    closeMobileNavPanel();
  }
});

// ----------------- Auth -----------------
function openLogin() { $('#modal').classList.remove('hidden'); $('#login-username').focus(); }
function closeLogin(e) { if (e && e.stopPropagation) e.stopPropagation(); $('#modal').classList.add('hidden'); }
function login(e) {
  e.preventDefault();
  const username = $('#login-username').value.trim();
  const password = $('#login-password').value;
  const users = read(LS_USERS);
  const u = users.find(x => x.username === username && x.password === password);
  if (!u) return alert('Invalid credentials (demo). Use admin/adminpass or user/userpass.');
  currentUser = { id: u.id, username: u.username, name: u.name, role: u.role };
  updateHeader();
  closeLogin();
  navigate('dashboard');
  closeMobileNavPanel();
}
function logout() {
  if (!confirm('Logout?')) return;
  currentUser = null;
  updateHeader();
  navigate('dashboard');
  closeMobileNavPanel();
}
function updateHeader() {
  $('#current-user').textContent = currentUser ? `${currentUser.name} (${currentUser.role})` : '';
  const btn = $('#btn-login');
  const adminActions = document.getElementById('admin-actions');
  if (currentUser) {
    btn.textContent = 'Logout';
    btn.onclick = logout;
    adminActions.style.display = currentUser.role === 'admin' ? 'inline-block' : 'none';
  } else {
    btn.textContent = 'Log In';
    btn.onclick = openLogin;
    adminActions.style.display = 'none';
  }
}

// ----------------- Navigation -----------------
function navigate(page) {
  // deselect all nav buttons, both desktop and mobile
  $all('.nav-btn').forEach(b => b.classList.remove('active'));
  const desktopBtn = document.getElementById('nav-' + page);
  if (desktopBtn) desktopBtn.classList.add('active');

  // close mobile nav after selection (if open)
  closeMobileNavPanel();

  switch(page) {
    case 'mylivestock': renderMyLivestock(); break;
    case 'feed': renderFeed(); break;
    case 'production': renderProduction(); break;
    case 'health': renderHealth(); break;
    case 'inventory': renderInventory(); break;
    case 'reports': renderReports(); break;
    case 'users': renderUsers(); break;
    case 'settings': renderSettings(); break;
    default: renderDashboard();
  }
}

// ----------------- Dashboard -----------------
function renderDashboard() {
  const livestock = read(LS_LIVESTOCK);
  const feed = read(LS_FEED);
  const prod = read(LS_PROD);
  const alerts = [
    ...feed.filter(f => f.quantity <= (f.low_stock_threshold || 5)).map(f => `Low feed: ${f.name} (${f.quantity} ${f.unit})`)
  ];
  document.getElementById('app').innerHTML = `
    <section class="panel">
      <h2>Dashboard</h2>
      <p class="small">Welcome${currentUser ? ', ' + currentUser.name : ''}. This demo stores data locally in your browser.</p>
      <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:12px">
        <div style="background:#f8fff6;padding:12px;border-radius:10px;min-width:160px">
          <div class="small">Livestock</div><div style="font-weight:700;font-size:20px">${livestock.length}</div>
        </div>
        <div style="background:#fff6f0;padding:12px;border-radius:10px;min-width:160px">
          <div class="small">Feed items</div><div style="font-weight:700;font-size:20px">${feed.length}</div>
        </div>
        <div style="background:#f8f9ff;padding:12px;border-radius:10px;min-width:160px">
          <div class="small">Production records</div><div style="font-weight:700;font-size:20px">${prod.length}</div>
        </div>
      </div>

      <div style="margin-top:18px;display:flex;gap:18px;flex-wrap:wrap;align-items:flex-start">
        <div style="flex:1;min-width:280px" class="panel">
          <h4>Production by Type</h4>
          <canvas id="dashboard-chart" style="height:200px;display:block"></canvas>
        </div>
        <div style="width:320px;min-width:220px" class="panel">
          <h4>Alerts</h4>
          ${alerts.length ? `<ul>${alerts.map(a=>`<li>${escapeHtml(a)}</li>`).join('')}</ul>` : '<div class="small muted">No active alerts.</div>'}
        </div>
      </div>
    </section>
  `;

  renderDashboardChart();
}

function renderDashboardChart() {
  const prods = read(LS_PROD).slice(0,50);
  const map = {};
  prods.forEach(p => { map[p.type] = (map[p.type] || 0) + Number(p.amount || 0); });
  const labels = Object.keys(map);
  const data = labels.map(l => map[l]);
  const ctx = document.getElementById('dashboard-chart');
  if (!ctx) return;
  if (chartInstance) chartInstance.destroy();
  chartInstance = new Chart(ctx, {
    type: 'doughnut',
    data: { labels, datasets: [{ data, backgroundColor: ['#6aa84f','#8bbf5b','#b7d7a8','#8b5e3c'] }] },
    options: { responsive:true, maintainAspectRatio:false }
  });
}

// ----------------- The modules (Livestock, Feed, Production, Health, Inventory, Reports, Users, Settings) -----------------
// For brevity the module code is unchanged apart from ensuring table wrappers are responsive
// Livestock module
function renderMyLivestock() {
  const livestock = read(LS_LIVESTOCK);
  const visible = livestock;
  document.getElementById('app').innerHTML = `
    <section class="panel">
      <h2>My Livestock</h2>
      <p class="small">View and update animal details. Users can report health issues and record daily activities.</p>
      <div class="panel">
        <button class="primary" onclick="openAddAnimal()">Add Animal</button>
        <button class="ghost" onclick="importSampleAnimals()">Load Sample Animals</button>
      </div>
      <div class="panel table-responsive">
        <table class="table">
          <thead><tr><th>Tag</th><th>Type</th><th>Breed</th><th>DOB</th><th>Status</th><th>Actions</th></tr></thead>
          <tbody>
            ${visible.map(a => `<tr>
              <td>${escapeHtml(a.tag_id)}</td>
              <td>${escapeHtml(a.type)}</td>
              <td>${escapeHtml(a.breed)}</td>
              <td>${escapeHtml(a.dob || '')}</td>
              <td>${escapeHtml(a.status)}</td>
              <td>
                <button onclick="viewAnimal('${a.id}')">View</button>
                ${currentUser && currentUser.role === 'admin' ? `<button onclick="editAnimal('${a.id}')">Edit</button><button onclick="deleteAnimal('${a.id}')">Delete</button>` : `<button onclick="reportIssue('${a.id}')">Report</button>`}
              </td></tr>`).join('')}
          </tbody>
        </table>
      </div>
    </section>
  `;
}
function openAddAnimal() {
  const modalId = 'inline-animal';
  closeInline(modalId);
  const container = document.createElement('div');
  container.id = modalId;
  container.className = 'panel';
  container.innerHTML = `
    <h3>Add Animal</h3>
    <form id="add-animal-form">
      <div class="form-grid">
        <label>Tag ID <input id="a-tag" required /></label>
        <label>Type <input id="a-type" required /></label>
        <label>Breed <input id="a-breed" /></label>
        <label>Date of Birth <input id="a-dob" type="date" /></label>
        <label>Status <input id="a-status" value="Active" /></label>
        <label>Notes <input id="a-notes" /></label>
      </div>
      <div class="actions"><button class="primary" type="submit">Save</button><button type="button" class="ghost" onclick="closeInline('${modalId}')">Cancel</button></div>
    </form>
  `;
  document.getElementById('app').prepend(container);
  $('#add-animal-form').addEventListener('submit', (e) => {
    e.preventDefault();
    const newA = {
      id: generateId('a'),
      tag_id: $('#a-tag').value.trim(),
      type: $('#a-type').value.trim(),
      breed: $('#a-breed').value.trim(),
      dob: $('#a-dob').value,
      status: $('#a-status').value.trim(),
      notes: $('#a-notes').value.trim()
    };
    const list = read(LS_LIVESTOCK);
    list.unshift(newA);
    write(LS_LIVESTOCK, list);
    closeInline(modalId);
    renderMyLivestock();
  });
}
function editAnimal(id) {
  const a = read(LS_LIVESTOCK).find(x => x.id === id);
  if (!a) return alert('Not found');
  const modalId = 'inline-edit-animal';
  closeInline(modalId);
  const container = document.createElement('div');
  container.id = modalId;
  container.className = 'panel';
  container.innerHTML = `
    <h3>Edit Animal</h3>
    <form id="edit-animal-form">
      <div class="form-grid">
        <label>Tag ID <input id="e-tag" value="${escapeHtml(a.tag_id)}" required /></label>
        <label>Type <input id="e-type" value="${escapeHtml(a.type)}" required /></label>
        <label>Breed <input id="e-breed" value="${escapeHtml(a.breed)}" /></label>
        <label>DOB <input id="e-dob" type="date" value="${escapeHtml(a.dob||'')}" /></label>
        <label>Status <input id="e-status" value="${escapeHtml(a.status)}" /></label>
        <label>Notes <input id="e-notes" value="${escapeHtml(a.notes)}" /></label>
      </div>
      <div class="actions"><button class="primary" type="submit">Save</button><button type="button" class="ghost" onclick="closeInline('${modalId}')">Cancel</button></div>
    </form>
  `;
  document.getElementById('app').prepend(container);
  $('#edit-animal-form').addEventListener('submit', (e) => {
    e.preventDefault();
    const list = read(LS_LIVESTOCK).map(x => x.id === id ? {
      ...x,
      tag_id: $('#e-tag').value.trim(),
      type: $('#e-type').value.trim(),
      breed: $('#e-breed').value.trim(),
      dob: $('#e-dob').value,
      status: $('#e-status').value.trim(),
      notes: $('#e-notes').value.trim()
    } : x);
    write(LS_LIVESTOCK, list);
    closeInline(modalId);
    renderMyLivestock();
  });
}
function viewAnimal(id) {
  const a = read(LS_LIVESTOCK).find(x => x.id === id);
  if (!a) return alert('Not found');
  const health = read(LS_HEALTH).filter(h => h.animal_id === id);
  const production = read(LS_PROD).filter(p => p.animal_id === id);
  const modalId = 'inline-view-animal';
  closeInline(modalId);
  const container = document.createElement('div');
  container.id = modalId;
  container.className = 'panel';
  container.innerHTML = `
    <h3>Animal: ${escapeHtml(a.tag_id)}</h3>
    <div><strong>Type:</strong> ${escapeHtml(a.type)} &nbsp; <strong>Breed:</strong> ${escapeHtml(a.breed)}</div>
    <div style="margin-top:8px"><strong>DOB:</strong> ${escapeHtml(a.dob || '')} &nbsp; <strong>Status:</strong> ${escapeHtml(a.status)}</div>
    <div style="margin-top:10px"><strong>Notes:</strong> ${escapeHtml(a.notes)}</div>
    <hr/>
    <h4>Health / Vaccination History</h4>
    ${health.length ? `<ul>${health.map(h=>`<li>${escapeHtml(h.date)} - ${escapeHtml(h.note)}</li>`).join('')}</ul>` : '<div class="small muted">No health records.</div>'}
    <h4>Production</h4>
    ${production.length ? `<div class="table-responsive"><table class="table"><thead><tr><th>Date</th><th>Type</th><th>Amount</th></tr></thead><tbody>${production.map(p=>`<tr><td>${escapeHtml(p.date)}</td><td>${escapeHtml(p.type)}</td><td>${escapeHtml(p.amount)} ${escapeHtml(p.unit)}</td></tr>`).join('')}</tbody></table></div>` : '<div class="small muted">No production entries.</div>'}
    <div class="actions" style="margin-top:12px">
      <button class="primary" onclick="closeInline('${modalId}')">Close</button>
    </div>
  `;
  document.getElementById('app').prepend(container);
}
function deleteAnimal(id) {
  if (!confirm('Delete this animal?')) return;
  const list = read(LS_LIVESTOCK).filter(x => x.id !== id);
  write(LS_LIVESTOCK, list);
  renderMyLivestock();
}
function reportIssue(id) {
  const note = prompt('Describe the issue or health concern:');
  if (!note) return;
  const rec = {
    id: generateId('h'),
    animal_id: id,
    date: new Date().toISOString().slice(0,10),
    note: note,
    reported_by: currentUser ? currentUser.username : 'anonymous'
  };
  const health = read(LS_HEALTH);
  health.unshift(rec);
  write(LS_HEALTH, health);
  alert('Issue reported. Admin will be notified (demo).');
  renderMyLivestock();
}

// ----------------- Feed (responsive table wrapper used) -----------------
function renderFeed() {
  const list = read(LS_FEED);
  document.getElementById('app').innerHTML = `
    <section class="panel">
      <h2>Feed & Nutrition</h2>
      <p class="small">Manage feed inventory, record consumption, and allocate feeds to animals/groups.</p>
      <div class="panel">
        <button class="primary" onclick="openAddFeed()">Add Feed Item</button>
        <button class="ghost" onclick="exportJSON('${LS_FEED}','feed-items.json')">Export Feed</button>
      </div>
      <div class="panel table-responsive">
        <table class="table">
          <thead><tr><th>Name</th><th>Quantity</th><th>Unit</th><th>Low Threshold</th><th>Actions</th></tr></thead>
          <tbody>
            ${list.map(f => `<tr><td>${escapeHtml(f.name)}</td><td>${f.quantity}</td><td>${escapeHtml(f.unit)}</td><td>${f.low_stock_threshold||''}</td>
              <td><button onclick="openEditFeed('${f.id}')">Edit</button><button onclick="consumeFeed('${f.id}')">Consume</button><button onclick="deleteFeed('${f.id}')">Delete</button></td></tr>`).join('')}
          </tbody>
        </table>
      </div>
    </section>
  `;
}
function openAddFeed() {
  const modalId = 'inline-add-feed';
  closeInline(modalId);
  const container = document.createElement('div');
  container.id = modalId;
  container.className = 'panel';
  container.innerHTML = `
    <h3>Add Feed Item</h3>
    <form id="add-feed-form">
      <div class="form-grid">
        <label>Name <input id="f-name" required /></label>
        <label>Quantity <input id="f-qty" type="number" value="0" /></label>
        <label>Unit <input id="f-unit" value="kg" /></label>
        <label>Low stock threshold <input id="f-threshold" type="number" value="10" /></label>
      </div>
      <div class="actions"><button class="primary" type="submit">Save</button><button type="button" class="ghost" onclick="closeInline('${modalId}')">Cancel</button></div>
    </form>
  `;
  document.getElementById('app').prepend(container);
  $('#add-feed-form').addEventListener('submit', (e) => {
    e.preventDefault();
    const item = {
      id: generateId('f'),
      name: $('#f-name').value.trim(),
      quantity: Number($('#f-qty').value) || 0,
      unit: $('#f-unit').value.trim(),
      low_stock_threshold: Number($('#f-threshold').value) || 0
    };
    const list = [item, ...read(LS_FEED)];
    write(LS_FEED, list);
    closeInline(modalId);
    renderFeed();
  });
}
function openEditFeed(id) {
  const f = read(LS_FEED).find(x => x.id === id);
  if (!f) return;
  const modalId = 'inline-edit-feed';
  closeInline(modalId);
  const container = document.createElement('div');
  container.id = modalId;
  container.className = 'panel';
  container.innerHTML = `
    <h3>Edit Feed</h3>
    <form id="edit-feed-form">
      <div class="form-grid">
        <label>Name <input id="ef-name" value="${escapeHtml(f.name)}" required /></label>
        <label>Quantity <input id="ef-qty" type="number" value="${escapeHtml(f.quantity)}" /></label>
        <label>Unit <input id="ef-unit" value="${escapeHtml(f.unit)}" /></label>
        <label>Low stock threshold <input id="ef-threshold" type="number" value="${escapeHtml(f.low_stock_threshold||0)}" /></label>
      </div>
      <div class="actions"><button class="primary" type="submit">Save</button><button type="button" class="ghost" onclick="closeInline('${modalId}')">Cancel</button></div>
    </form>
  `;
  document.getElementById('app').prepend(container);
  $('#edit-feed-form').addEventListener('submit', (e) => {
    e.preventDefault();
    const updated = read(LS_FEED).map(x => x.id === id ? {
      ...x,
      name: $('#ef-name').value.trim(),
      quantity: Number($('#ef-qty').value)||0,
      unit: $('#ef-unit').value.trim(),
      low_stock_threshold: Number($('#ef-threshold').value)||0
    } : x);
    write(LS_FEED, updated);
    closeInline(modalId);
    renderFeed();
  });
}
function consumeFeed(id) {
  const qty = Number(prompt('Amount consumed (in item unit):', '0'));
  if (!qty && qty !== 0) return;
  const list = read(LS_FEED).map(x => x.id === id ? { ...x, quantity: (Number(x.quantity)||0) - qty } : x);
  write(LS_FEED, list);
  alert('Feed consumption recorded (demo).');
  renderFeed();
}
function deleteFeed(id) {
  if (!confirm('Delete feed item?')) return;
  const list = read(LS_FEED).filter(x => x.id !== id);
  write(LS_FEED, list);
  renderFeed();
}

// ----------------- Production -----------------
function renderProduction() {
  const list = read(LS_PROD);
  document.getElementById('app').innerHTML = `
    <section class="panel">
      <h2>Production Records</h2>
      <p class="small">Record daily outputs like milk, eggs, or meat.</p>
      <div class="panel">
        <button class="primary" onclick="openAddProduction()">Add Production</button>
        <button class="ghost" onclick="exportJSON('${LS_PROD}','production.json')">Export Production</button>
      </div>
      <div class="panel table-responsive">
        <table class="table">
          <thead><tr><th>Date</th><th>Animal/Group</th><th>Type</th><th>Amount</th><th>Unit</th><th>Actions</th></tr></thead>
          <tbody>
            ${list.map(p=>`<tr><td>${escapeHtml(p.date)}</td><td>${escapeHtml(p.subject || '—')}</td><td>${escapeHtml(p.type)}</td><td>${escapeHtml(p.amount)}</td><td>${escapeHtml(p.unit)}</td><td><button onclick="deleteProduction('${p.id}')">Delete</button></td></tr>`).join('')}
          </tbody>
        </table>
      </div>
    </section>
  `;
}
function openAddProduction() {
  const modalId = 'inline-add-prod';
  closeInline(modalId);
  const container = document.createElement('div');
  container.id = modalId;
  container.className = 'panel';
  container.innerHTML = `
    <h3>Add Production</h3>
    <form id="add-prod-form">
      <div class="form-grid">
        <label>Date <input id="p-date" type="date" value="${new Date().toISOString().slice(0,10)}" required /></label>
        <label>Animal/Group <input id="p-subject" placeholder="TAG-001 or Herd A" /></label>
        <label>Type <select id="p-type"><option>Milk</option><option>Eggs</option><option>Meat</option></select></label>
        <label>Amount <input id="p-amount" type="number" value="0" /></label>
        <label>Unit <input id="p-unit" value="kg" /></label>
      </div>
      <div class="actions"><button class="primary" type="submit">Save</button><button type="button" class="ghost" onclick="closeInline('${modalId}')">Cancel</button></div>
    </form>
  `;
  document.getElementById('app').prepend(container);
  $('#add-prod-form').addEventListener('submit', (e)=> {
    e.preventDefault();
    const rec = {
      id: generateId('p'),
      date: $('#p-date').value,
      subject: $('#p-subject').value.trim(),
      type: $('#p-type').value,
      amount: Number($('#p-amount').value)||0,
      unit: $('#p-unit').value
    };
    const list = [rec, ...read(LS_PROD)];
    write(LS_PROD, list);
    closeInline(modalId);
    renderProduction();
  });
}
function deleteProduction(id) {
  if (!confirm('Delete production record?')) return;
  const list = read(LS_PROD).filter(x => x.id !== id);
  write(LS_PROD, list);
  renderProduction();
}

// ----------------- Health -----------------
function renderHealth() {
  const list = read(LS_HEALTH);
  document.getElementById('app').innerHTML = `
    <section class="panel">
      <h2>Health & Veterinary</h2>
      <p class="small">Manage vaccinations, treatments, and visit logs.</p>
      <div class="panel">
        <button class="primary" onclick="openAddHealth()">Add Health Record</button>
        <button class="ghost" onclick="exportJSON('${LS_HEALTH}','health.json')">Export Health Records</button>
      </div>
      <div class="panel table-responsive">
        <table class="table">
          <thead><tr><th>Date</th><th>Animal</th><th>Note</th><th>Reported By</th><th>Actions</th></tr></thead>
          <tbody>
            ${list.map(h=>`<tr><td>${escapeHtml(h.date)}</td><td>${escapeHtml(h.animal_tag || '—')}</td><td>${escapeHtml(h.note)}</td><td>${escapeHtml(h.reported_by || '')}</td><td><button onclick="deleteHealth('${h.id}')">Delete</button></td></tr>`).join('')}
          </tbody>
        </table>
      </div>
    </section>
  `;
}
function openAddHealth() {
  const modalId = 'inline-add-health';
  closeInline(modalId);
  const container = document.createElement('div');
  container.id = modalId;
  container.className = 'panel';
  container.innerHTML = `
    <h3>Add Health Record</h3>
    <form id="add-health-form">
      <div class="form-grid">
        <label>Date <input id="h-date" type="date" value="${new Date().toISOString().slice(0,10)}" /></label>
        <label>Animal Tag <input id="h-tag" placeholder="TAG-001" /></label>
        <label>Note <input id="h-note" /></label>
        <label>Reported by <input id="h-by" value="${currentUser ? currentUser.username : ''}" /></label>
      </div>
      <div class="actions"><button class="primary" type="submit">Save</button><button type="button" class="ghost" onclick="closeInline('${modalId}')">Cancel</button></div>
    </form>
  `;
  document.getElementById('app').prepend(container);
  $('#add-health-form').addEventListener('submit', e => {
    e.preventDefault();
    const rec = {
      id: generateId('h'),
      date: $('#h-date').value,
      animal_tag: $('#h-tag').value.trim(),
      note: $('#h-note').value.trim(),
      reported_by: $('#h-by').value.trim()
    };
    const list = [rec, ...read(LS_HEALTH)];
    write(LS_HEALTH, list);
    closeInline(modalId);
    renderHealth();
  });
}
function deleteHealth(id) {
  if (!confirm('Delete health record?')) return;
  const list = read(LS_HEALTH).filter(x => x.id !== id);
  write(LS_HEALTH, list);
  renderHealth();
}

// ----------------- Inventory -----------------
function renderInventory() {
  const list = read(LS_INV);
  document.getElementById('app').innerHTML = `
    <section class="panel">
      <h2>Inventory</h2>
      <p class="small">Track equipment, medicines, and supplies.</p>
      <div class="panel">
        <button class="primary" onclick="openAddInventory()">Add Item</button>
        <button class="ghost" onclick="exportJSON('${LS_INV}','inventory.json')">Export Inventory</button>
      </div>
      <div class="panel table-responsive">
        <table class="table">
          <thead><tr><th>Item</th><th>Qty</th><th>Location</th><th>Condition</th><th>Actions</th></tr></thead>
          <tbody>
            ${list.map(i=>`<tr><td>${escapeHtml(i.name)}</td><td>${i.qty}</td><td>${escapeHtml(i.location)}</td><td>${escapeHtml(i.condition)}</td><td><button onclick="reportInventory('${i.id}')">Report</button><button onclick="deleteInventory('${i.id}')">Delete</button></td></tr>`).join('')}
          </tbody>
        </table>
      </div>
    </section>
  `;
}
function openAddInventory() {
  const modalId = 'inline-add-inv';
  closeInline(modalId);
  const container = document.createElement('div');
  container.id = modalId;
  container.className = 'panel';
  container.innerHTML = `
    <h3>Add Inventory Item</h3>
    <form id="add-inv-form">
      <div class="form-grid">
        <label>Name <input id="i-name" required /></label>
        <label>Quantity <input id="i-qty" type="number" value="1" /></label>
        <label>Location <input id="i-loc" /></label>
        <label>Condition <input id="i-cond" value="Good" /></label>
      </div>
      <div class="actions"><button class="primary" type="submit">Save</button><button type="button" class="ghost" onclick="closeInline('${modalId}')">Cancel</button></div>
    </form>
  `;
  document.getElementById('app').prepend(container);
  $('#add-inv-form').addEventListener('submit', e => {
    e.preventDefault();
    const item = {
      id: generateId('inv'),
      name: $('#i-name').value.trim(),
      qty: Number($('#i-qty').value)||0,
      location: $('#i-loc').value.trim(),
      condition: $('#i-cond').value.trim()
    };
    const list = [item, ...read(LS_INV)];
    write(LS_INV, list);
    closeInline(modalId);
    renderInventory();
  });
}
function reportInventory(id) {
  const note = prompt('Describe issue (damaged/missing):');
  if (!note) return;
  alert('Report submitted (demo).');
  const list = read(LS_INV).map(x => x.id === id ? { ...x, notes: (x.notes || '') + `\n[${new Date().toISOString().slice(0,10)}] ${note}` } : x);
  write(LS_INV, list);
  renderInventory();
}
function deleteInventory(id) {
  if (!confirm('Delete item?')) return;
  const list = read(LS_INV).filter(x => x.id !== id);
  write(LS_INV, list);
  renderInventory();
}

// ----------------- Reports & Financials -----------------
function renderReports() {
  const prods = read(LS_PROD);
  const fin = read(LS_FIN);
  const totalIncome = fin.filter(f => f.type === 'income').reduce((s,i) => s + (Number(i.amount)||0),0);
  const totalExpense = fin.filter(f => f.type === 'expense').reduce((s,i) => s + (Number(i.amount)||0),0);
  document.getElementById('app').innerHTML = `
    <section class="panel">
      <h2>Reports & Analytics</h2>
      <div style="display:flex;gap:12px;flex-wrap:wrap">
        <div style="background:#f8fff6;padding:12px;border-radius:10px"><div class="small">Production Records</div><div style="font-weight:700">${prods.length}</div></div>
        <div style="background:#fff6f0;padding:12px;border-radius:10px"><div class="small">Total Income</div><div style="font-weight:700">${totalIncome}</div></div>
        <div style="background:#f8f9ff;padding:12px;border-radius:10px"><div class="small">Total Expense</div><div style="font-weight:700">${totalExpense}</div></div>
      </div>
      <div style="margin-top:12px">
        <h4>Recent Production</h4>
        ${prods.length ? `<div class="table-responsive"><table class="table"><thead><tr><th>Date</th><th>Type</th><th>Amount</th><th>Subject</th></tr></thead><tbody>${prods.slice(0,20).map(p=>`<tr><td>${escapeHtml(p.date)}</td><td>${escapeHtml(p.type)}</td><td>${escapeHtml(p.amount)}</td><td>${escapeHtml(p.subject||'')}</td></tr>`).join('')}</tbody></table></div>` : '<div class="small muted">No production records.</div>'}
      </div>
    </section>
  `;
}

// ----------------- Users (Admin) -----------------
function renderUsers() {
  if (!currentUser || currentUser.role !== 'admin') return alert('Admin only');
  const users = read(LS_USERS);
  document.getElementById('app').innerHTML = `
    <section class="panel">
      <h2>User Management</h2>
      <p class="small">Add or remove users, assign roles, and manage access.</p>
      <div class="panel"><button class="primary" onclick="openAddUser()">Add User</button><button class="ghost" onclick="exportJSON('${LS_USERS}','users.json')">Export Users</button></div>
      <div class="panel table-responsive">
        <table class="table">
          <thead><tr><th>Username</th><th>Name</th><th>Role</th><th>Actions</th></tr></thead>
          <tbody>${users.map(u=>`<tr><td>${escapeHtml(u.username)}</td><td>${escapeHtml(u.name||'')}</td><td>${escapeHtml(u.role)}</td><td><button onclick="editUser('${u.id}')">Edit</button>${u.username !== 'admin' ? `<button onclick="deleteUser('${u.id}')">Delete</button>` : ''}</td></tr>`).join('')}</tbody>
        </table>
      </div>
    </section>
  `;
}
function openAddUser() {
  const modalId = 'inline-add-user';
  closeInline(modalId);
  const container = document.createElement('div');
  container.id = modalId;
  container.className = 'panel';
  container.innerHTML = `
    <h3>Add User</h3>
    <form id="add-user-form">
      <div class="form-grid">
        <label>Username <input id="u-username" required /></label>
        <label>Password <input id="u-password" type="password" required /></label>
        <label>Name <input id="u-name" /></label>
        <label>Role <select id="u-role"><option>user</option><option>admin</option></select></label>
      </div>
      <div class="actions"><button class="primary" type="submit">Save</button><button type="button" class="ghost" onclick="closeInline('${modalId}')">Cancel</button></div>
    </form>
  `;
  document.getElementById('app').prepend(container);
  $('#add-user-form').addEventListener('submit', e => {
    e.preventDefault();
    const users = read(LS_USERS);
    const newUser = {
      id: generateId('u'),
      username: $('#u-username').value.trim(),
      password: $('#u-password').value,
      name: $('#u-name').value.trim(),
      role: $('#u-role').value
    };
    users.unshift(newUser);
    write(LS_USERS, users);
    closeInline(modalId);
    renderUsers();
  });
}
function editUser(id) {
  const u = read(LS_USERS).find(x => x.id === id);
  if (!u) return;
  const modalId = 'inline-edit-user';
  closeInline(modalId);
  const container = document.createElement('div');
  container.id = modalId;
  container.className = 'panel';
  container.innerHTML = `
    <h3>Edit User</h3>
    <form id="edit-user-form">
      <div class="form-grid">
        <label>Username <input id="eu-username" value="${escapeHtml(u.username)}" required /></label>
        <label>Password <input id="eu-password" type="password" placeholder="leave to keep" /></label>
        <label>Name <input id="eu-name" value="${escapeHtml(u.name||'')}" /></label>
        <label>Role <select id="eu-role"><option${u.role==='user'?' selected':''}>user</option><option${u.role==='admin'?' selected':''}>admin</option></select></label>
      </div>
      <div class="actions"><button class="primary" type="submit">Save</button><button type="button" class="ghost" onclick="closeInline('${modalId}')">Cancel</button></div>
    </form>
  `;
  document.getElementById('app').prepend(container);
  $('#edit-user-form').addEventListener('submit', e => {
    e.preventDefault();
    const users = read(LS_USERS).map(x => x.id === id ? {
      ...x,
      username: $('#eu-username').value.trim(),
      password: $('#eu-password').value ? $('#eu-password').value : x.password,
      name: $('#eu-name').value.trim(),
      role: $('#eu-role').value
    } : x);
    write(LS_USERS, users);
    closeInline(modalId);
    renderUsers();
  });
}
function deleteUser(id) {
  if (!confirm('Delete user?')) return;
  const users = read(LS_USERS).filter(x => x.id !== id);
  write(LS_USERS, users);
  renderUsers();
}

// ----------------- Settings -----------------
function renderSettings() {
  if (!currentUser || currentUser.role !== 'admin') return alert('Admin only');
  const settings = { farmName: 'Demo Farm', timezone: 'UTC' };
  document.getElementById('app').innerHTML = `
    <section class="panel">
      <h2>Settings</h2>
      <p class="small">Customize farm profile, data backup, and system configurations.</p>
      <div class="panel">
        <h4>Farm Profile</h4>
        <div class="form-grid">
          <label>Farm name <input id="s-farm" value="${escapeHtml(settings.farmName)}" /></label>
          <label>Timezone <input id="s-tz" value="${escapeHtml(settings.timezone)}" /></label>
        </div>
        <div class="actions" style="margin-top:12px"><button class="primary" onclick="alert('Saved (demo)')">Save</button><button class="ghost" onclick="exportAll()">Backup (Export All JSON)</button></div>
      </div>
    </section>
  `;
}
function exportAll() {
  const data = {
    users: read(LS_USERS),
    livestock: read(LS_LIVESTOCK),
    feed: read(LS_FEED),
    production: read(LS_PROD),
    health: read(LS_HEALTH),
    inventory: read(LS_INV),
    financials: read(LS_FIN)
  };
  const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
  const a = document.createElement('a');
  a.href = URL.createObjectURL(blob);
  a.download = `farm-backup-${new Date().toISOString().slice(0,10)}.json`;
  a.click();
  URL.revokeObjectURL(a.href);
}

// ----------------- Inline helpers & export -----------------
function closeInline(id) { const el = document.getElementById(id); if (el) el.remove(); }
function exportJSON(lsKey, filename='export.json') {
  const blob = new Blob([JSON.stringify(read(lsKey), null, 2)], { type: 'application/json' });
  const a = document.createElement('a');
  a.href = URL.createObjectURL(blob);
  a.download = filename;
  a.click();
  URL.revokeObjectURL(a.href);
}

// ----------------- Chatbot (UI-only) -----------------
function toggleChat(e) {
  if (e && e.stopPropagation) e.stopPropagation();
  chatOpen = !chatOpen;
  const el = document.getElementById('chatbot');
  if (chatOpen) { el.classList.remove('closed'); el.setAttribute('aria-hidden','false'); } else { el.classList.add('closed'); el.setAttribute('aria-hidden','true'); }
  renderChatHistory();
}
function sendChat(e) {
  e.preventDefault();
  const input = $('#chat-input');
  const text = input.value.trim();
  if (!text) return;
  appendChat('user', text);
  input.value = '';
  setTimeout(() => {
    const reply = farmBotReply(text);
    appendChat('bot', reply);
  }, 300);
}
function appendChat(who, text) {
  const body = document.getElementById('chat-body');
  const row = document.createElement('div');
  row.style.marginBottom = '8px';
  if (who === 'user') {
    row.innerHTML = `<div style="text-align:right"><div style="display:inline-block;background:#e9f7ee;padding:8px;border-radius:8px">${escapeHtml(text)}</div></div>`;
  } else {
    row.innerHTML = `<div style="text-align:left"><div style="display:inline-block;background:#fff;padding:8px;border-radius:8px">${escapeHtml(text)}</div></div>`;
  }
  body.appendChild(row);
  body.scrollTop = body.scrollHeight;
  saveChatHistory();
}
function farmBotReply(text) {
  const t = text.toLowerCase();
  if (t.includes('help') || t.includes('how')) return "You can add records under each module. For capstone recommendations ask 'capstone'.";
  if (t.includes('capstone') || t.includes('thesis')) return "For a strong capstone: add backend + DB, RBAC, offline sync, mapping, IoT sensors, analytics/forecasting, and evaluate with experiments. See README for details.";
  if (t.includes('export') || t.includes('backup')) return "Use Settings → Backup (Export All JSON) to download data.";
  if (t.includes('sample')) { importSampleAnimals(); return "Sample animals loaded."; }
  return "Sorry, I don't fully understand. Try: 'How to add an animal?', 'capstone recommendations', or 'export'.";
}
function renderChatHistory() {
  const body = document.getElementById('chat-body');
  body.innerHTML = '';
  const history = read(LS_CHAT);
  history.forEach(h => appendChat(h.who, h.text));
}
function saveChatHistory() {
  const body = document.getElementById('chat-body');
  const nodes = Array.from(body.children);
  const history = nodes.map(n => {
    const text = n.innerText.trim();
    const isUser = n.innerHTML.includes('text-align:right');
    return { who: isUser ? 'user' : 'bot', text };
  }).slice(-50);
  write(LS_CHAT, history);
}

// ----------------- Small helpers & init -----------------
function importSampleAnimals() {
  const sample = [
    { id: generateId('a'), tag_id: 'TAG-101', type: 'Cow', breed: 'Jersey', dob: '2020-01-12', status: 'Active', notes: 'High-yield' },
    { id: generateId('a'), tag_id: 'TAG-102', type: 'Chicken', breed: 'Rhode Island', dob: '2023-05-01', status: 'Active', notes: 'Egg layers' }
  ];
  const list = [...sample, ...read(LS_LIVESTOCK)];
  write(LS_LIVESTOCK, list);
  alert('Sample animals imported.');
  renderMyLivestock();
}
function closeAllModalsOnEsc(e) {
  if (e.key === 'Escape') {
    closeLogin();
    closeMobileNavPanel();
    const inline = document.querySelectorAll('[id^="inline-"]');
    inline.forEach(n => n.remove());
    if (chatOpen) toggleChat();
  }
}
document.addEventListener('keydown', closeAllModalsOnEsc);

// Initialize UI
(function init() {
  updateHeader();
  navigate('dashboard');

  // restore chat history
  const chatHistory = read(LS_CHAT);
  if (chatHistory.length) {
    const body = document.getElementById('chat-body');
    chatHistory.forEach(h => appendChat(h.who, h.text));
  }

  // close mobile nav when clicking outside
  document.addEventListener('click', (e) => {
    const panel = document.getElementById('mobile-nav-panel');
    const hamburger = document.getElementById('hamburger');
    if (!panel) return;
    if (mobileNavOpen && !panel.contains(e.target) && !hamburger.contains(e.target)) {
      closeMobileNavPanel();
    }
  });
})();