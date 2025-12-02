<?php
require_once '../config/session.php';
requireAdmin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Farm Management</title>
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
            
            <nav class="sidebar-nav" id="sidebarNav">
                <a href="#" class="nav-item active" onclick="showSection('dashboard')">
                    <span class="icon">📊</span>
                    <span>Dashboard</span>
                </a>
                <a href="#" class="nav-item" onclick="showSection('users')">
                    <span class="icon">👥</span>
                    <span>Users Management</span>
                </a>
                <a href="#" class="nav-item" onclick="showSection('livestock')">
                    <span class="icon">🐄</span>
                    <span>All Livestock</span>
                </a>
                <a href="#" class="nav-item" onclick="showSection('production')">
                    <span class="icon">📦</span>
                    <span>Production</span>
                </a>
                <a href="#" class="nav-item" onclick="showSection('reports')">
                    <span class="icon">📈</span>
                    <span>Reports</span>
                </a>
            </nav>
            
            <div class="sidebar-footer">
                <div class="user-profile">
                    <div class="avatar" id="userAvatar">A</div>
                    <div class="user-info">
                        <p class="username" id="userName"><?php echo $_SESSION['full_name']; ?></p>
                        <p class="role">Administrator</p>
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
                    <p id="pageSubtitle">System Overview & Statistics</p>
                </div>
                <div class="header-right">
                    <button class="btn-secondary" onclick="showSection('settings')">Settings</button>
                </div>
            </header>

            <!-- Content Section -->
            <section class="content">
                <!-- Dashboard Section -->
                <div id="dashboard" class="section active">
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon">👥</div>
                            <div class="stat-content">
                                <h3>Total Users</h3>
                                <p class="stat-number" id="totalUsers">0</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">🐄</div>
                            <div class="stat-content">
                                <h3>Total Livestock</h3>
                                <p class="stat-number" id="totalLivestock">0</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">📊</div>
                            <div class="stat-content">
                                <h3>Total Production</h3>
                                <p class="stat-number" id="totalProduction">0</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">💰</div>
                            <div class="stat-content">
                                <h3>Total Revenue</h3>
                                <p class="stat-number" id="totalRevenue">$0</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Users Management -->
                <div id="users" class="section">
                    <div class="section-header">
                        <div>
                            <h2>Users Management</h2>
                            <p>Manage all farm users</p>
                        </div>
                    </div>
                    
                    <div class="card">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Full Name</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="usersTable">
                                <tr><td colspan="6" style="text-align: center;">Loading...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Livestock Section -->
                <div id="livestock" class="section">
                    <div class="section-header">
                        <div>
                            <h2>All Livestock</h2>
                            <p>System-wide livestock overview</p>
                        </div>
                    </div>
                    
                    <div class="card">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Tag Number</th>
                                    <th>Type</th>
                                    <th>Owner</th>
                                    <th>Status</th>
                                    <th>Age</th>
                                </tr>
                            </thead>
                            <tbody id="livestockTable">
                                <tr><td colspan="5" style="text-align: center;">Loading...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Production Section -->
                <div id="production" class="section">
                    <div class="section-header">
                        <div>
                            <h2>Production Records</h2>
                            <p>System-wide production overview</p>
                        </div>
                    </div>
                    
                    <div class="card">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Quantity</th>
                                    <th>Value</th>
                                    <th>User</th>
                                </tr>
                            </thead>
                            <tbody id="productionTable">
                                <tr><td colspan="5" style="text-align: center;">Loading...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Reports Section -->
                <div id="reports" class="section">
                    <div class="section-header">
                        <div>
                            <h2>System Reports</h2>
                            <p>Analytics and statistics</p>
                        </div>
                    </div>
                    
                    <div class="card">
                        <h3>Monthly Summary</h3>
                        <div style="padding: 20px; text-align: center;">
                            <p id="reportSummary">Loading report data...</p>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <script src="js/admin-dashboard.js"></script>
</body>
</html>
