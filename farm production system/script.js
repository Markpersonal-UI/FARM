// Farm Production Management System - Full Application Logic with Advanced Features

class FarmSystem {
    constructor() {
        this.users = {
            admin: { id: 1, email: 'admin@farm.com', password: 'admin123', type: 'admin', name: 'Admin User' },
            staff: { id: 2, email: 'staff@farm.com', password: 'user123', type: 'user', name: 'Farm Staff' }
        };
        this.currentUser = null;
        this.livestock = [];
        this.feedInventory = [];
        this.productionRecords = [];
        this.financialRecords = [];
        this.healthRecords = [];
        this.inventoryRecords = [];
        this.taskAssignments = [];
        this.init();
    }

    init() {
        this.loadFromLocalStorage();
        this.setupEventListeners();
        this.checkSession();
    }

    loadFromLocalStorage() {
        const stored = localStorage.getItem('farmSystemData');
        if (stored) {
            const data = JSON.parse(stored);
            this.livestock = data.livestock || [];
            this.feedInventory = data.feedInventory || [];
            this.productionRecords = data.productionRecords || [];
            this.financialRecords = data.financialRecords || [];
            this.healthRecords = data.healthRecords || [];
            this.inventoryRecords = data.inventoryRecords || [];
            this.taskAssignments = data.taskAssignments || [];
        } else {
            this.initializeSampleData();
        }
    }

    initializeSampleData() {
        this.livestock = [
            { id: '#LV001', name: 'Bessie', type: 'Cattle', breed: 'Holstein', age: '5 years', status: 'Healthy', dateAdded: new Date().toISOString() },
            { id: '#LV002', name: 'Daisy', type: 'Cattle', breed: 'Jersey', age: '3 years', status: 'Healthy', dateAdded: new Date().toISOString() },
            { id: '#LV003', name: 'Max', type: 'Cattle', breed: 'Angus', age: '7 years', status: 'Monitoring', dateAdded: new Date().toISOString() }
        ];

        this.feedInventory = [
            { id: 'FD001', name: 'Corn Silage', amount: 5200, unit: 'kg', lastUpdated: new Date().toISOString(), minStock: 1000 },
            { id: 'FD002', name: 'Alfalfa Hay', amount: 3100, unit: 'kg', lastUpdated: new Date().toISOString(), minStock: 500 },
            { id: 'FD003', name: 'Grain Mix', amount: 120, unit: 'kg', lastUpdated: new Date().toISOString(), minStock: 100 }
        ];

        this.productionRecords = [
            { id: 'PR001', date: new Date().toISOString().split('T')[0], type: 'Milk', amount: 12850, unit: 'L', herd: 'Herd A', notes: 'Daily morning production' },
            { id: 'PR002', date: new Date().toISOString().split('T')[0], type: 'Eggs', amount: 8420, unit: 'units', herd: 'Herd B', notes: 'Daily collection' },
            { id: 'PR003', date: new Date().toISOString().split('T')[0], type: 'Meat', amount: 450, unit: 'kg', herd: 'Herd C', notes: 'Weekly processing' }
        ];

        this.financialRecords = [
            { id: 'FIN001', date: new Date().toISOString().split('T')[0], type: 'income', category: 'Milk Sales', amount: 28500, description: 'Monthly milk sales' },
            { id: 'FIN002', date: new Date().toISOString().split('T')[0], type: 'expense', category: 'Feed', amount: 8450, description: 'Feed supplies purchased' }
        ];

        this.healthRecords = [
            { id: 'H001', date: new Date().toISOString().split('T')[0], animalId: '#LV001', disease: 'Healthy', treatment: 'Routine checkup', veterinarian: 'Dr. Smith' },
            { id: 'H002', date: new Date().toISOString().split('T')[0], animalId: '#LV003', disease: 'Mild cough', treatment: 'Antibiotics', veterinarian: 'Dr. Jones' }
        ];

        this.inventoryRecords = [
            { id: 'INV001', name: 'Vaccine Stock A', quantity: 250, unit: 'doses', location: 'Cold Storage', status: 'In Stock' },
            { id: 'INV002', name: 'Medical Supplies', quantity: 120, unit: 'items', location: 'Clinic', status: 'Low Stock' },
            { id: 'INV003', name: 'Farm Equipment', quantity: 15, unit: 'units', location: 'Barn', status: 'In Stock' }
        ];

        this.saveToLocalStorage();
    }

    saveToLocalStorage() {
        const data = {
            livestock: this.livestock,
            feedInventory: this.feedInventory,
            productionRecords: this.productionRecords,
            financialRecords: this.financialRecords,
            healthRecords: this.healthRecords,
            inventoryRecords: this.inventoryRecords,
            taskAssignments: this.taskAssignments
        };
        localStorage.setItem('farmSystemData', JSON.stringify(data));
    }

    setupEventListeners() {
        const loginForm = document.getElementById('loginForm');
        if (loginForm) {
            loginForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleLogin();
            });
        }

        const logoutBtn = document.getElementById('logoutBtn');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', () => {
                this.handleLogout();
            });
        }

        const menuToggle = document.getElementById('menuToggle');
        if (menuToggle) {
            menuToggle.addEventListener('click', () => {
                document.querySelector('.sidebar').classList.toggle('active');
            });
        }

        document.addEventListener('click', (e) => {
            if (e.target.closest('.nav-item')) {
                const section = e.target.closest('.nav-item').dataset.section;
                if (section) this.switchSection(section);
            }
        });

        const addButtons = {
            'addAnimalBtn': 'addLivestockModal',
            'addFeedBtn': 'addFeedModal',
            'addProductionBtn': 'addProductionModal',
            'addHealthBtn': 'addHealthModal',
            'addInventoryBtn': 'addInventoryModal'
        };

        Object.keys(addButtons).forEach(btnId => {
            const btn = document.getElementById(btnId);
            if (btn) {
                btn.addEventListener('click', () => this.openModal(addButtons[btnId]));
            }
        });

        const forms = {
            'addLivestockForm': 'handleAddLivestock',
            'addFeedForm': 'handleAddFeed',
            'addProductionForm': 'handleAddProduction',
            'addHealthForm': 'handleAddHealth',
            'addInventoryForm': 'handleAddInventory'
        };

        Object.keys(forms).forEach(formId => {
            const form = document.getElementById(formId);
            if (form) {
                form.addEventListener('submit', (e) => {
                    e.preventDefault();
                    this[forms[formId]](e);
                });
            }
        });

        window.addEventListener('click', (event) => {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                if (event.target === modal) {
                    modal.classList.remove('active');
                }
            });
        });
    }

    printTable(tableId, title) {
        const table = document.getElementById(tableId);
        if (!table) {
            alert('Table not found');
            return;
        }

        const printWindow = window.open('', '', 'height=600,width=800');
        const html = `
            <html>
            <head>
                <title>${title}</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 20px; }
                    h1 { color: #27ae60; text-align: center; }
                    .print-date { text-align: center; color: #7f8c8d; margin-bottom: 20px; }
                    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                    th { background: #27ae60; color: white; padding: 12px; text-align: left; }
                    td { padding: 10px; border-bottom: 1px solid #ddd; }
                    tr:nth-child(even) { background: #f5f7fa; }
                    .badge { padding: 5px 10px; border-radius: 3px; font-size: 12px; }
                    .badge.healthy { background: #d4edda; color: #155724; }
                    .badge.warning { background: #fff3cd; color: #856404; }
                </style>
            </head>
            <body>
                <h1>${title}</h1>
                <p class="print-date">Generated on: ${new Date().toLocaleString()}</p>
                ${table.parentElement.innerHTML}
            </body>
            </html>
        `;
        printWindow.document.write(html);
        printWindow.document.close();
        setTimeout(() => {
            printWindow.print();
            printWindow.close();
        }, 250);
    }

    handleLogin() {
        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value;
        const userType = document.getElementById('userType').value;

        if (userType === 'admin' && this.users.admin.email === email && this.users.admin.password === password) {
            this.currentUser = { ...this.users.admin, avatar: 'AU' };
            this.showDashboard();
        } else if (userType === 'user' && this.users.staff.email === email && this.users.staff.password === password) {
            this.currentUser = { ...this.users.staff, avatar: 'FS' };
            this.showDashboard();
        } else {
            alert('Invalid credentials. Use demo credentials provided.');
            return;
        }

        localStorage.setItem('currentUser', JSON.stringify(this.currentUser));
    }

    showDashboard() {
        document.getElementById('loginPage').style.display = 'none';
        document.getElementById('dashboardPage').style.display = 'flex';
        this.updateUserInfo();
        this.renderNavigation();
        this.loadDashboardData();
        this.switchSection('dashboard');
    }

    updateUserInfo() {
        const avatar = document.getElementById('userAvatar');
        const name = document.getElementById('userName');
        const role = document.getElementById('userRole');

        if (avatar) avatar.textContent = this.currentUser.avatar;
        if (name) name.textContent = this.currentUser.name;
        if (role) role.textContent = this.currentUser.type === 'admin' ? 'Administrator' : 'Farm Staff';
    }

    renderNavigation() {
        const sidebarNav = document.getElementById('sidebarNav');
        if (!sidebarNav) return;
        
        sidebarNav.innerHTML = '';

        const navItems = this.currentUser.type === 'admin' ? [
            { section: 'dashboard', label: 'Dashboard', icon: '📊' },
            { section: 'livestock', label: 'Livestock', icon: '🐄' },
            { section: 'feed', label: 'Feed & Nutrition', icon: '🌽' },
            { section: 'production', label: 'Production', icon: '📈' },
            { section: 'health', label: 'Health', icon: '🏥' },
            { section: 'inventory', label: 'Inventory', icon: '📦' },
            { section: 'finance', label: 'Finance', icon: '💰' },
            { section: 'reports', label: 'Reports', icon: '📋' },
            { section: 'users', label: 'Users', icon: '👥' },
            { section: 'settings', label: 'Settings', icon: '⚙️' }
        ] : [
            { section: 'dashboard', label: 'Dashboard', icon: '📊' },
            { section: 'livestock', label: 'My Animals', icon: '🐄' },
            { section: 'feed', label: 'Feed Records', icon: '🌽' },
            { section: 'production', label: 'Daily Tasks', icon: '📈' },
            { section: 'health', label: 'Health Reports', icon: '🏥' },
            { section: 'inventory', label: 'Inventory', icon: '📦' },
            { section: 'profile', label: 'My Profile', icon: '👤' }
        ];

        navItems.forEach((item, index) => {
            const navItem = document.createElement('a');
            navItem.className = 'nav-item' + (index === 0 ? ' active' : '');
            navItem.dataset.section = item.section;
            navItem.innerHTML = `<span class="icon">${item.icon}</span><span>${item.label}</span>`;
            navItem.addEventListener('click', (e) => {
                e.preventDefault();
                this.switchSection(item.section);
            });
            sidebarNav.appendChild(navItem);
        });
    }

    switchSection(section) {
        document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
        document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));

        const selectedSection = document.getElementById(section);
        if (selectedSection) selectedSection.classList.add('active');

        const selectedNav = document.querySelector(`[data-section="${section}"]`);
        if (selectedNav) selectedNav.classList.add('active');

        const navText = document.querySelector(`[data-section="${section}"] span:last-child`)?.textContent || 'Dashboard';
        const pageTitle = document.getElementById('pageTitle');
        if (pageTitle) pageTitle.textContent = navText;

        document.querySelector('.sidebar').classList.remove('active');
    }

    loadDashboardData() {
        this.populateActivityList();
        this.populateLivestock();
        this.populateFeedData();
        this.populateProductionData();
        this.populateHealthData();
        this.populateInventoryData();
        this.populateFinanceData();
        this.populateReports();
        this.populateProfile();
        this.populateUsersManagement();
        
        this.updateAddButtonsVisibility();
    }

    updateAddButtonsVisibility() {
        const isAdmin = this.currentUser.type === 'admin';
        
        const addBtns = [
            'addAnimalBtn',
            'addFeedBtn',
            'addProductionBtn',
            'addHealthBtn',
            'addInventoryBtn'
        ];
        
        addBtns.forEach(btnId => {
            const btn = document.getElementById(btnId);
            if (btn) {
                btn.style.display = isAdmin ? 'block' : 'none';
            }
        });
    }

    populateActivityList() {
        const activities = this.currentUser.type === 'admin' ? [
            { icon: '📋', title: 'New animals registered', desc: '45 cattle added to inventory', time: '2 hours ago' },
            { icon: '🏥', title: 'Health check completed', desc: 'Routine vaccination for herd B', time: '5 hours ago' },
            { icon: '🌽', title: 'Feed order received', desc: '5 tons of premium feed delivered', time: '1 day ago' }
        ] : [
            { icon: '🐄', title: 'Task assigned', desc: 'Feed herd A - morning shift', time: '2 hours ago' },
            { icon: '📝', title: 'Production recorded', desc: 'Milk production: 250L', time: '4 hours ago' },
            { icon: '✓', title: 'Daily task completed', desc: 'Morning animal inspection done', time: '1 day ago' }
        ];

        const activityList = document.getElementById('activityList');
        if (!activityList) return;

        activityList.innerHTML = activities.map(a => `
            <div class="activity-item">
                <div class="activity-icon">${a.icon}</div>
                <div class="activity-content">
                    <p><strong>${a.title}</strong></p>
                    <p class="text-muted">${a.desc}</p>
                    <p class="text-muted time">${a.time}</p>
                </div>
            </div>
        `).join('');
    }

    populateLivestock() {
        const livestockTable = document.getElementById('livestockTable');
        if (!livestockTable) return;

        livestockTable.innerHTML = this.livestock.map(animal => `
            <tr>
                <td>${animal.id}</td>
                <td>${animal.name}</td>
                <td>${animal.type}</td>
                <td>${animal.breed}</td>
                <td>${animal.age}</td>
                <td><span class="badge ${animal.status === 'Healthy' ? 'healthy' : 'warning'}">${animal.status}</span></td>
                <td><button class="btn-text" onclick="farmSystem.viewAnimalDetails('${animal.id}')">View</button></td>
            </tr>
        `).join('');

        const addBtn = document.getElementById('addAnimalBtn');
        if (addBtn) {
            addBtn.style.display = this.currentUser.type === 'admin' ? 'block' : 'none';
            addBtn.onclick = () => this.openModal('addLivestockModal');
        }
    }

    viewAnimalDetails(animalId) {
        const animal = this.livestock.find(a => a.id === animalId);
        if (animal) {
            alert(`Animal Details:\nID: ${animal.id}\nName: ${animal.name}\nType: ${animal.type}\nStatus: ${animal.status}\n\n(Expand for full details)`);
        }
    }

    openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('active');
            
            const today = new Date().toISOString().split('T')[0];
            const dateInputs = modal.querySelectorAll('input[type="date"]');
            dateInputs.forEach(input => {
                // Set minimum date to prevent selecting past dates
                input.min = today;
                // Auto-fill with today's date if empty
                if (!input.value) {
                    input.value = today;
                }
            });
            console.log("[v0] Modal opened: " + modalId);
        }
    }

    closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('active');
            console.log("[v0] Modal closed: " + modalId);
        }
    }

    handleAddLivestock(event) {
        event.preventDefault();
        console.log("[v0] Adding new livestock record");
        
        const name = document.getElementById('livestockName').value?.trim();
        const type = document.getElementById('livestockType').value?.trim();
        const breed = document.getElementById('livestockBreed').value?.trim();
        const age = document.getElementById('livestockAge').value?.trim();
        const status = document.getElementById('livestockStatus').value?.trim();

        if (!name || !type || !breed || !age || !status) {
            alert('Please fill in all required fields');
            return;
        }

        const newAnimal = {
            id: '#LV' + String(this.livestock.length + 1).padStart(3, '0'),
            name: name,
            type: type,
            breed: breed,
            age: age,
            status: status,
            dateAdded: new Date().toISOString()
        };

        this.livestock.unshift(newAnimal);
        this.saveToLocalStorage();
        this.populateLivestock();
        this.closeModal('addLivestockModal');
        document.getElementById('addLivestockForm').reset();
        console.log("[v0] Livestock added successfully:", newAnimal);
        alert('Animal added successfully!');
    }

    handleAddFeed(event) {
        event.preventDefault();
        console.log("[v0] Adding new feed record");
        
        const name = document.getElementById('feedName').value?.trim();
        const amount = parseFloat(document.getElementById('feedAmount').value);
        const unit = document.getElementById('feedUnit').value?.trim();
        const minStock = parseFloat(document.getElementById('feedMinStock').value);

        if (!name || !amount || !unit || !minStock || isNaN(amount) || isNaN(minStock)) {
            alert('Please fill in all required fields with valid numbers');
            return;
        }

        const newFeed = {
            id: 'FD' + String(this.feedInventory.length + 1).padStart(3, '0'),
            name: name,
            amount: amount,
            unit: unit,
            lastUpdated: new Date().toISOString(),
            minStock: minStock
        };

        this.feedInventory.unshift(newFeed);
        this.saveToLocalStorage();
        this.populateFeedData();
        this.closeModal('addFeedModal');
        document.getElementById('addFeedForm').reset();
        console.log("[v0] Feed added successfully:", newFeed);
        alert('Feed inventory added successfully!');
    }

    handleAddProduction(event) {
        event.preventDefault();
        console.log("[v0] Adding new production record");
        
        const date = document.getElementById('productionDate').value?.trim();
        const type = document.getElementById('productionType').value?.trim();
        const amount = parseFloat(document.getElementById('productionAmount').value);
        const unit = document.getElementById('productionUnit').value?.trim();
        const herd = document.getElementById('productionHerd').value?.trim();
        const notes = document.getElementById('productionNotes').value?.trim() || '';

        if (!date || !type || !amount || !unit || !herd || isNaN(amount)) {
            alert('Please fill in all required fields with valid numbers');
            return;
        }

        const newRecord = {
            id: 'PR' + String(this.productionRecords.length + 1).padStart(3, '0'),
            date: date,
            type: type,
            amount: amount,
            unit: unit,
            herd: herd,
            notes: notes
        };

        this.productionRecords.unshift(newRecord);
        this.saveToLocalStorage();
        this.populateProductionData();
        this.populateReports();
        this.closeModal('addProductionModal');
        document.getElementById('addProductionForm').reset();
        console.log("[v0] Production record added successfully:", newRecord);
        alert('Production record added successfully!');
    }

    handleAddHealth(event) {
        event.preventDefault();
        console.log("[v0] Adding new health record");
        
        const date = document.getElementById('healthDate').value?.trim();
        const animalId = document.getElementById('healthAnimalId').value?.trim();
        const disease = document.getElementById('healthDisease').value?.trim();
        const treatment = document.getElementById('healthTreatment').value?.trim();
        const veterinarian = document.getElementById('healthVeterinarian').value?.trim();

        if (!date || !animalId || !disease || !treatment || !veterinarian) {
            alert('Please fill in all required fields');
            return;
        }

        const newRecord = {
            id: 'H' + String(this.healthRecords.length + 1).padStart(3, '0'),
            date: date,
            animalId: animalId,
            disease: disease,
            treatment: treatment,
            veterinarian: veterinarian
        };

        this.healthRecords.unshift(newRecord);
        this.saveToLocalStorage();
        this.populateHealthData();
        this.closeModal('addHealthModal');
        document.getElementById('addHealthForm').reset();
        console.log("[v0] Health record added successfully:", newRecord);
        alert('Health record added successfully!');
    }

    handleAddInventory(event) {
        event.preventDefault();
        console.log("[v0] Adding new inventory item");
        
        const name = document.getElementById('inventoryName').value?.trim();
        const quantity = parseFloat(document.getElementById('inventoryQuantity').value);
        const unit = document.getElementById('inventoryUnit').value?.trim();
        const location = document.getElementById('inventoryLocation').value?.trim();
        const status = document.getElementById('inventoryStatus').value?.trim();

        if (!name || !quantity || !unit || !location || !status || isNaN(quantity)) {
            alert('Please fill in all required fields with valid numbers');
            return;
        }

        const newItem = {
            id: 'INV' + String(this.inventoryRecords.length + 1).padStart(3, '0'),
            name: name,
            quantity: quantity,
            unit: unit,
            location: location,
            status: status
        };

        this.inventoryRecords.unshift(newItem);
        this.saveToLocalStorage();
        this.populateInventoryData();
        this.closeModal('addInventoryModal');
        document.getElementById('addInventoryForm').reset();
        console.log("[v0] Inventory item added successfully:", newItem);
        alert('Inventory item added successfully!');
    }

    populateFeedData() {
        const feedList = document.getElementById('feedList');
        if (!feedList) return;

        feedList.innerHTML = this.feedInventory.map(f => {
            const percentage = Math.min(100, (f.amount / (f.minStock * 5)) * 100);
            const color = percentage < 30 ? '#e74c3c' : '#27ae60';
            return `
                <div class="feed-item">
                    <div class="feed-header">
                        <h4>${f.name}</h4>
                        <span class="badge">${f.amount} ${f.unit}</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress" style="width: ${percentage}%; background: ${color};"></div>
                    </div>
                </div>
            `;
        }).join('');

        const feedTable = document.createElement('div');
        feedTable.className = 'card';
        feedTable.innerHTML = `
            <div style="overflow-x: auto;">
                <table class="data-table" id="feedInventoryTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Feed Name</th>
                            <th>Amount</th>
                            <th>Unit</th>
                            <th>Minimum Stock</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${this.feedInventory.map(f => `
                            <tr>
                                <td>${f.id}</td>
                                <td>${f.name}</td>
                                <td>${f.amount}</td>
                                <td>${f.unit}</td>
                                <td>${f.minStock}</td>
                                <td><span class="badge ${f.amount > f.minStock ? 'healthy' : 'warning'}">${f.amount > f.minStock ? 'Adequate' : 'Low Stock'}</span></td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `;
        
        const feedSection = document.getElementById('feed');
        const existingTable = feedSection.querySelector('.card:has(#feedInventoryTable)');
        if (existingTable) {
            existingTable.remove();
        }
        feedSection.appendChild(feedTable);
    }

    populateProductionData() {
        const stats = [
            { icon: '🥛', title: 'Milk Production', value: '12,850 L', change: '↑ 5.2% today' },
            { icon: '🥚', title: 'Egg Production', value: '8,420', change: '↑ 3.1% today' },
            { icon: '🥩', title: 'Meat Processed', value: '450 kg', change: '→ On target' },
            { icon: '🧀', title: 'By-Products', value: '85 kg', change: '↑ 12% today' }
        ];

        const productionStats = document.getElementById('productionStats');
        if (!productionStats) return;

        productionStats.innerHTML = stats.map(s => `
            <div class="prod-stat">
                <h4>${s.icon} ${s.title}</h4>
                <p class="stat-large">${s.value}</p>
                <p class="text-success">${s.change}</p>
            </div>
        `).join('');

        const productionTitle = document.getElementById('productionTitle');
        const productionSubtitle = document.getElementById('productionSubtitle');
        if (productionTitle) {
            productionTitle.textContent = this.currentUser.type === 'admin' ? 'Production Records' : 'Daily Tasks';
        }
        if (productionSubtitle) {
            productionSubtitle.textContent = this.currentUser.type === 'admin' ? 'Track all farm production' : 'Your assigned daily tasks';
        }

        const prodTable = document.createElement('div');
        prodTable.className = 'card';
        prodTable.innerHTML = `
            <div style="overflow-x: auto;">
                <table class="data-table" id="productionRecordsTable">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Unit</th>
                            <th>Herd</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${this.productionRecords.map(p => `
                            <tr>
                                <td>${p.date}</td>
                                <td>${p.type}</td>
                                <td>${p.amount}</td>
                                <td>${p.unit}</td>
                                <td>${p.herd}</td>
                                <td>${p.notes || '-'}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `;
        
        const productionSection = document.getElementById('production');
        const existingProdTable = productionSection.querySelector('.card:has(#productionRecordsTable)');
        if (existingProdTable) {
            existingProdTable.remove();
        }
        if (productionSection) {
            productionSection.appendChild(prodTable);
        }
    }

    populateHealthData() {
        const healthSection = document.getElementById('health');
        if (!healthSection) return;

        const scheduleList = document.getElementById('scheduleList');
        if (scheduleList) {
            scheduleList.innerHTML = `
                <div class="schedule-item">
                    <div class="schedule-dot">📋</div>
                    <div class="schedule-content">
                        <p><strong>Anthrax Vaccination</strong></p>
                        <p class="text-muted">Herd B - 234 animals</p>
                        <p class="text-muted time">Nov 18, 2025</p>
                    </div>
                </div>
                <div class="schedule-item">
                    <div class="schedule-dot">📋</div>
                    <div class="schedule-content">
                        <p><strong>Foot & Mouth Check</strong></p>
                        <p class="text-muted">All herds - Routine</p>
                        <p class="text-muted time">Nov 22, 2025</p>
                    </div>
                </div>
            `;
        }

        const alertList = document.getElementById('alertList');
        if (alertList) {
            alertList.innerHTML = `
                <div class="alert-item warning">
                    <span class="alert-icon">⚠️</span>
                    <div>
                        <p><strong>3 animals</strong> showing mild cough</p>
                        <p class="text-muted">Herd C - Monitor closely</p>
                    </div>
                </div>
                <div class="alert-item info">
                    <span class="alert-icon">ℹ️</span>
                    <div>
                        <p><strong>Vet visit</strong> scheduled tomorrow</p>
                        <p class="text-muted">10:00 AM - Routine checkup</p>
                    </div>
                </div>
            `;
        }

        const healthTableSection = document.createElement('div');
        healthTableSection.className = 'card';
        healthTableSection.innerHTML = `
            <div style="overflow-x: auto;">
                <table class="data-table" id="healthTable">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Animal ID</th>
                            <th>Disease/Condition</th>
                            <th>Treatment</th>
                            <th>Veterinarian</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${this.healthRecords.map(h => `
                            <tr>
                                <td>${h.date}</td>
                                <td>${h.animalId}</td>
                                <td>${h.disease}</td>
                                <td>${h.treatment}</td>
                                <td>${h.veterinarian}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `;
        
        const existingHealthTable = healthSection.querySelector('.card:has(#healthTable)');
        if (existingHealthTable) {
            existingHealthTable.remove();
        }
        healthSection.appendChild(healthTableSection);
    }

    populateInventoryData() {

        const inventorySection = document.getElementById('inventory');
        if (!inventorySection) return;

        const html = `
            <div class="section-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <div>
                    <h2>Inventory Management</h2>
                    <p>Manage farm equipment, medicines, and supplies</p>
                </div>
                <div>
                    ${this.currentUser.type === 'admin' ? '<button class="btn-primary" onclick="farmSystem.openModal(\'addInventoryModal\')" style="margin-right: 10px;">+ Add Item</button>' : ''}
                    <button class="btn-primary" onclick="farmSystem.printTable(\'inventoryTable\', \'Inventory Report\')">🖨️ Print</button>
                </div>
            </div>
            <div class="card">
                <div style="overflow-x: auto;">
                    <table class="data-table" id="inventoryTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Item Name</th>
                                <th>Quantity</th>
                                <th>Unit</th>
                                <th>Location</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${this.inventoryRecords.map(inv => `
                                <tr>
                                    <td>${inv.id}</td>
                                    <td>${inv.name}</td>
                                    <td>${inv.quantity}</td>
                                    <td>${inv.unit}</td>
                                    <td>${inv.location}</td>
                                    <td><span class="badge ${inv.status === 'In Stock' ? 'healthy' : 'warning'}">${inv.status}</span></td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            </div>
        `;
        inventorySection.innerHTML = html;
    }

    populateFinanceData() {
        const totalIncome = this.financialRecords.filter(r => r.type === 'income').reduce((sum, r) => sum + r.amount, 0);
        const totalExpense = this.financialRecords.filter(r => r.type === 'expense').reduce((sum, r) => sum + r.amount, 0);
        const profit = totalIncome - totalExpense;

        const financeSummary = document.getElementById('financeSummary');
        if (financeSummary) {
            financeSummary.innerHTML = `
                <div class="finance-card income">
                    <h4>Total Income</h4>
                    <p class="amount">$${totalIncome.toLocaleString()}</p>
                    <p class="period">This month</p>
                </div>
                <div class="finance-card expense">
                    <h4>Total Expenses</h4>
                    <p class="amount">$${totalExpense.toLocaleString()}</p>
                    <p class="period">This month</p>
                </div>
                <div class="finance-card profit">
                    <h4>Net Profit</h4>
                    <p class="amount">$${profit.toLocaleString()}</p>
                    <p class="period">This month</p>
                </div>
            `;
        }

        const revenueItems = document.getElementById('revenueItems');
        if (revenueItems) {
            revenueItems.innerHTML = `
                <div style="overflow-x: auto;">
                    <table class="data-table" id="financeTable">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Category</th>
                                <th>Amount</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${this.financialRecords.map(r => `
                                <tr>
                                    <td>${r.date}</td>
                                    <td><span class="badge ${r.type === 'income' ? 'healthy' : 'warning'}">${r.type.charAt(0).toUpperCase() + r.type.slice(1)}</span></td>
                                    <td>${r.category}</td>
                                    <td>$${r.amount.toLocaleString()}</td>
                                    <td>${r.description}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
                <div style="margin-top: 20px; text-align: right;">
                    <button class="btn-primary" onclick="farmSystem.printTable('financeTable', 'Financial Report')">🖨️ Print Finance Report</button>
                </div>
            `;
        }
    }

    populateReports() {
        const reportsSection = document.getElementById('reports');
        if (!reportsSection) return;

        const productionTable = `
            <div class="section-header">
                <div>
                    <h2>Reports & Analytics</h2>
                    <p>View detailed farm production and performance reports</p>
                </div>
                <button class="btn-primary" onclick="farmSystem.printTable('productionTable', 'Production Report')">🖨️ Print</button>
            </div>
            <div class="card">
                <h3>Production Records</h3>
                <div style="overflow-x: auto;">
                    <table class="data-table" id="productionTable">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Unit</th>
                                <th>Herd</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${this.productionRecords.map(p => `
                                <tr>
                                    <td>${p.date}</td>
                                    <td>${p.type}</td>
                                    <td>${p.amount}</td>
                                    <td>${p.unit}</td>
                                    <td>${p.herd}</td>
                                    <td>${p.notes || '-'}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            </div>
        `;
        reportsSection.innerHTML = productionTable;
    }

    populateProfile() {
        const profileForm = document.getElementById('profileForm');
        if (!profileForm) return;

        profileForm.innerHTML = `
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" value="${this.currentUser.name}" readonly>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" value="${this.currentUser.email}" readonly>
            </div>
            <div class="form-group">
                <label>Role</label>
                <input type="text" value="${this.currentUser.type === 'admin' ? 'Administrator' : 'Farm Staff'}" readonly>
            </div>
            <div class="form-group">
                <label>New Password</label>
                <input type="password" placeholder="Enter new password (optional)">
            </div>
            <button class="btn-primary" onclick="alert('Profile updated (demo)')">Update Profile</button>
        `;
    }

    populateUsersManagement() {
        const section = document.getElementById('users');
        if (!section || this.currentUser.type !== 'admin') return;

        const usersTable = `
            <div class="section-header">
                <div>
                    <h2>User Management</h2>
                    <p>Manage system users and permissions</p>
                </div>
                <div>
                    <button class="btn-primary" onclick="farmSystem.openAddUserForm()" style="margin-right: 10px;">+ Add User</button>
                    <button class="btn-primary" onclick="farmSystem.printTable('usersTable', 'Users Report')">🖨️ Print</button>
                </div>
            </div>
            <div class="card">
                <div style="overflow-x: auto;">
                    <table class="data-table" id="usersTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1</td>
                                <td>Admin User</td>
                                <td>admin@farm.com</td>
                                <td><span class="badge">Administrator</span></td>
                                <td><button class="btn-text" onclick="alert('Edit user (demo)')">Edit</button></td>
                            </tr>
                            <tr>
                                <td>2</td>
                                <td>Farm Staff</td>
                                <td>staff@farm.com</td>
                                <td><span class="badge healthy">Staff</span></td>
                                <td><button class="btn-text" onclick="alert('Edit user (demo)')">Edit</button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        `;
        section.innerHTML = usersTable;
    }

    openAddUserForm() {
        const name = prompt('User full name:');
        if (!name) return;
        const email = prompt('User email:');
        if (!email) return;
        alert('User added successfully (demo)!');
    }

    handleLogout() {
        localStorage.removeItem('currentUser');
        this.currentUser = null;
        document.getElementById('loginPage').style.display = 'flex';
        document.getElementById('dashboardPage').style.display = 'none';
        const loginForm = document.getElementById('loginForm');
        if (loginForm) loginForm.reset();
    }

    checkSession() {
        const savedUser = localStorage.getItem('currentUser');
        if (savedUser) {
            this.currentUser = JSON.parse(savedUser);
            this.showDashboard();
        }
    }
}

let farmSystem;

// Initialize the application when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    farmSystem = new FarmSystem();
});
