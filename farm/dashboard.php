<?php
require_once '../config/session.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farm Dashboard - Production Management</title>
    <link rel="stylesheet" href="css/design.css">
</head>
<body>
    <div class="dashboard-wrapper">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="logo">🚜 Farm</div>
                <button class="menu-toggle" id="menuToggle">☰</button>
            </div>
            
            <nav class="sidebar-nav">
                <a href="#" class="nav-item active" onclick="showSection('dashboard')">
                    <span class="icon">📊</span>
                    <span>Dashboard</span>
                </a>
                <a href="#" class="nav-item" onclick="showSection('livestock')">
                    <span class="icon">🐄</span>
                    <span>Livestock</span>
                </a>
                <a href="#" class="nav-item" onclick="showSection('production')">
                    <span class="icon">📦</span>
                    <span>Production</span>
                </a>
                <a href="#" class="nav-item" onclick="showSection('health')">
                    <span class="icon">⚕️</span>
                    <span>Health Records</span>
                </a>
                <a href="#" class="nav-item" onclick="showSection('finance')">
                    <span class="icon">💰</span>
                    <span>Finance</span>
                </a>
                <a href="#" class="nav-item" onclick="showSection('feeding')">
                    <span class="icon">🌾</span>
                    <span>Feeding Schedule</span>
                </a>
                <a href="#" class="nav-item" onclick="showSection('profile')">
                    <span class="icon">⚙️</span>
                    <span>Profile</span>
                </a>
            </nav>
            
            <div class="sidebar-footer">
                <div class="user-profile">
                    <div class="avatar" id="userAvatar">U</div>
                    <div class="user-info">
                        <p class="username" id="userName"><?php echo $_SESSION['full_name']; ?></p>
                        <p class="role">Farm Staff</p>
                    </div>
                </div>
                <button class="btn-logout" onclick="logout()">Logout</button>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <header class="top-header">
                <div class="header-left">
                    <h1 id="pageTitle">Dashboard</h1>
                    <p id="pageSubtitle">Welcome back to your farm</p>
                </div>
                <div class="header-right">
                    <button class="btn-secondary" onclick="showSection('profile')">Settings</button>
                </div>
            </header>

            <!-- Content Section -->
            <section class="content">
                <!-- Dashboard Section -->
                <div id="dashboard" class="section active">
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon">🐄</div>
                            <div class="stat-content">
                                <h3>Total Livestock</h3>
                                <p class="stat-number" id="totalLivestock">0</p>
                                <p class="stat-change">↑ On your farm</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">🐮</div>
                            <div class="stat-content">
                                <h3>Healthy Animals</h3>
                                <p class="stat-number" id="healthyCount">0</p>
                                <p class="stat-change">✓ All good</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">🚨</div>
                            <div class="stat-content">
                                <h3>Needs Attention</h3>
                                <p class="stat-number" id="warningCount">0</p>
                                <p class="stat-change">⚠️ Check status</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">💰</div>
                            <div class="stat-content">
                                <h3>Monthly Income</h3>
                                <p class="stat-number" id="monthlyIncome">$0</p>
                                <p class="stat-change">↑ This month</p>
                            </div>
                        </div>
                    </div>

                    <div class="dashboard-grid">
                        <div class="card">
                            <h2>Recent Livestock</h2>
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Tag</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody id="recentLivestock">
                                    <tr><td colspan="3">Loading...</td></tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="card">
                            <h2>Production This Month</h2>
                            <div id="productionSummary" style="padding: 20px;">Loading...</div>
                        </div>
                    </div>
                </div>

                <!-- Livestock Section -->
                <div id="livestock" class="section">
                    <div class="section-header">
                        <div>
                            <h2>Livestock Management</h2>
                            <p>Manage all your animals</p>
                        </div>
                        <button class="btn-primary" onclick="openModal('addLivestockModal')">Add Livestock</button>
                    </div>
                    
                    <div class="card">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Tag Number</th>
                                    <th>Type</th>
                                    <th>Breed</th>
                                    <th>Gender</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="livestockTable">
                                <tr><td colspan="6" style="text-align: center;">Loading...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Production Section -->
                <div id="production" class="section">
                    <div class="section-header">
                        <div>
                            <h2>Production Records</h2>
                            <p>Track your farm production</p>
                        </div>
                        <button class="btn-primary" onclick="openModal('addProductionModal')">Add Record</button>
                    </div>
                    
                    <div class="card">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Quantity</th>
                                    <th>Unit</th>
                                    <th>Value</th>
                                </tr>
                            </thead>
                            <tbody id="productionTable">
                                <tr><td colspan="5" style="text-align: center;">Loading...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Health Section -->
                <div id="health" class="section">
                    <div class="section-header">
                        <div>
                            <h2>Health Records</h2>
                            <p>Manage animal health</p>
                        </div>
                        <button class="btn-primary" onclick="openModal('addHealthModal')">Add Record</button>
                    </div>
                    
                    <div class="card">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Animal</th>
                                    <th>Issue</th>
                                    <th>Treatment</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="healthTable">
                                <tr><td colspan="5" style="text-align: center;">Loading...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Finance Section -->
                <div id="finance" class="section">
                    <div class="section-header">
                        <div>
                            <h2>Finance Management</h2>
                            <p>Track income and expenses</p>
                        </div>
                        <button class="btn-primary" onclick="openModal('addFinanceModal')">Add Transaction</button>
                    </div>
                    
                    <div class="finance-summary">
                        <div class="finance-card income">
                            <h4>Monthly Income</h4>
                            <p class="amount" id="incomeAmount">$0</p>
                            <p class="period">This month</p>
                        </div>
                        <div class="finance-card expense">
                            <h4>Monthly Expense</h4>
                            <p class="amount" id="expenseAmount">$0</p>
                            <p class="period">This month</p>
                        </div>
                        <div class="finance-card profit">
                            <h4>Net Profit</h4>
                            <p class="amount" id="profitAmount">$0</p>
                            <p class="period">This month</p>
                        </div>
                    </div>
                    
                    <div class="card">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Category</th>
                                    <th>Amount</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody id="financeTable">
                                <tr><td colspan="5" style="text-align: center;">Loading...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Feeding Section -->
                <div id="feeding" class="section">
                    <div class="section-header">
                        <div>
                            <h2>Feeding Schedule</h2>
                            <p>Manage feeding plans</p>
                        </div>
                        <button class="btn-primary" onclick="openModal('addFeedingModal')">Add Schedule</button>
                    </div>
                    
                    <div class="card">
                        <div id="feedingContent">Loading...</div>
                    </div>
                </div>

                <!-- Profile Section -->
                <div id="profile" class="section">
                    <div class="section-header">
                        <div>
                            <h2>My Profile</h2>
                            <p>Manage your account</p>
                        </div>
                    </div>
                    
                    <div class="card">
                        <h3>Profile Information</h3>
                        <div class="profile-form">
                            <div class="form-group">
                                <label>Full Name</label>
                                <input type="text" id="profileName" value="">
                            </div>
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" id="profileEmail" value="" disabled>
                            </div>
                            <div class="form-group">
                                <label>Phone</label>
                                <input type="tel" id="profilePhone" value="">
                            </div>
                            <button class="btn-primary" onclick="updateProfile()">Update Profile</button>
                        </div>
                    </div>

                    <div class="card">
                        <h3>Change Password</h3>
                        <div class="profile-form">
                            <div class="form-group">
                                <label>Old Password</label>
                                <input type="password" id="oldPassword" placeholder="Enter old password">
                            </div>
                            <div class="form-group">
                                <label>New Password</label>
                                <input type="password" id="newPassword" placeholder="Enter new password">
                            </div>
                            <div class="form-group">
                                <label>Confirm Password</label>
                                <input type="password" id="confirmPassword" placeholder="Confirm new password">
                            </div>
                            <button class="btn-primary" onclick="changePassword()">Change Password</button>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- Modals -->
    <div id="addLivestockModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add Livestock</h3>
                <button class="modal-close" onclick="closeModal('addLivestockModal')">×</button>
            </div>
            <form class="modal-form" id="addLivestockForm">
                <div class="form-group">
                    <label>Animal Type</label>
                    <select id="animalType" required>
                        <option value="">Select type</option>
                        <option value="Cow">Cow</option>
                        <option value="Chicken">Chicken</option>
                        <option value="Goat">Goat</option>
                        <option value="Pig">Pig</option>
                        <option value="Sheep">Sheep</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Tag Number</label>
                    <input type="text" id="tagNumber" placeholder="e.g., COW-001" required>
                </div>
                <div class="form-group">
                    <label>Breed</label>
                    <input type="text" id="breed" placeholder="e.g., Holstein">
                </div>
                <div class="form-group">
                    <label>Gender</label>
                    <select id="gender" required>
                        <option value="">Select gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Age (months)</label>
                    <input type="number" id="ageMonths" placeholder="0">
                </div>
                <div class="form-group">
                    <label>Weight (kg)</label>
                    <input type="number" id="weight" placeholder="0" step="0.01">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closeModal('addLivestockModal')">Cancel</button>
                    <button type="submit" class="btn-primary">Add Livestock</button>
                </div>
            </form>
        </div>
    </div>

    <div id="addProductionModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add Production Record</h3>
                <button class="modal-close" onclick="closeModal('addProductionModal')">×</button>
            </div>
            <form class="modal-form" id="addProductionForm">
                <div class="form-group">
                    <label>Production Type</label>
                    <select id="productionType" required>
                        <option value="">Select type</option>
                        <option value="Milk">Milk</option>
                        <option value="Eggs">Eggs</option>
                        <option value="Meat">Meat</option>
                        <option value="Wool">Wool</option>
                        <option value="Honey">Honey</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Quantity</label>
                    <input type="number" id="quantity" placeholder="0" step="0.01" required>
                </div>
                <div class="form-group">
                    <label>Unit</label>
                    <input type="text" id="unit" placeholder="e.g., Liters, kg, Dozen" required>
                </div>
                <div class="form-group">
                    <label>Production Date</label>
                    <input type="date" id="prodDate" required>
                </div>
                <div class="form-group">
                    <label>Price Per Unit</label>
                    <input type="number" id="pricePerUnit" placeholder="0" step="0.01">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closeModal('addProductionModal')">Cancel</button>
                    <button type="submit" class="btn-primary">Add Record</button>
                </div>
            </form>
        </div>
    </div>

    <div id="addHealthModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add Health Record</h3>
                <button class="modal-close" onclick="closeModal('addHealthModal')">×</button>
            </div>
            <form class="modal-form" id="addHealthForm">
                <div class="form-group">
                    <label>Select Livestock</label>
                    <select id="healthLivestock" required>
                        <option value="">Select animal</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Health Issue</label>
                    <input type="text" id="healthIssue" placeholder="Describe the issue" required>
                </div>
                <div class="form-group">
                    <label>Treatment</label>
                    <input type="text" id="treatment" placeholder="Treatment applied">
                </div>
                <div class="form-group">
                    <label>Medication</label>
                    <input type="text" id="medication" placeholder="Medication name">
                </div>
                <div class="form-group">
                    <label>Treatment Date</label>
                    <input type="date" id="treatmentDate" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closeModal('addHealthModal')">Cancel</button>
                    <button type="submit" class="btn-primary">Add Record</button>
                </div>
            </form>
        </div>
    </div>

    <div id="addFinanceModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add Finance Record</h3>
                <button class="modal-close" onclick="closeModal('addFinanceModal')">×</button>
            </div>
            <form class="modal-form" id="addFinanceForm">
                <div class="form-group">
                    <label>Transaction Type</label>
                    <select id="transType" required>
                        <option value="">Select type</option>
                        <option value="income">Income</option>
                        <option value="expense">Expense</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <input type="text" id="financeCategory" placeholder="e.g., Feed, Medicine, Sales">
                </div>
                <div class="form-group">
                    <label>Amount</label>
                    <input type="number" id="financeAmount" placeholder="0" step="0.01" required>
                </div>
                <div class="form-group">
                    <label>Date</label>
                    <input type="date" id="financeDate" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <input type="text" id="financeDescription" placeholder="Optional details">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closeModal('addFinanceModal')">Cancel</button>
                    <button type="submit" class="btn-primary">Add Record</button>
                </div>
            </form>
        </div>
    </div>

    <script src="js/dashboard.js"></script>
</body>
</html>
