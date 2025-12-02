<?php
session_start();
require 'config/database.php';
require 'config/session.php';

requireAdmin();

// Get statistics
$total_users = fetchSingleResult('SELECT COUNT(*) as count FROM users WHERE role = "user"')['count'];
$total_livestock = fetchSingleResult('SELECT COUNT(*) as count FROM livestock')['count'];
$total_income = fetchSingleResult('SELECT SUM(amount) as total FROM finance WHERE transaction_type = "income"')['total'] ?? 0;
$total_expense = fetchSingleResult('SELECT SUM(amount) as total FROM finance WHERE transaction_type = "expense"')['total'] ?? 0;
$total_production = fetchSingleResult('SELECT SUM(total_value) as total FROM production')['total'] ?? 0;
$total_health_issues = fetchSingleResult('SELECT COUNT(*) as count FROM health_records')['count'];

// Get data based on section
$section = $_GET['section'] ?? 'dashboard';
$users = fetchAllResults('SELECT * FROM users WHERE role = "user" ORDER BY created_at DESC LIMIT 10');
$livestock_data = fetchAllResults('SELECT l.*, u.full_name FROM livestock l JOIN users u ON l.user_id = u.id ORDER BY l.created_at DESC LIMIT 10');
$production_data = fetchAllResults('SELECT p.*, u.full_name, l.tag_number FROM production p JOIN users u ON p.user_id = u.id LEFT JOIN livestock l ON p.livestock_id = l.id ORDER BY p.production_date DESC LIMIT 10');
$health_data = fetchAllResults('SELECT h.*, u.full_name, l.tag_number FROM health_records h JOIN users u ON h.user_id = u.id JOIN livestock l ON h.livestock_id = l.id ORDER BY h.treatment_date DESC LIMIT 10');
$finance_data = fetchAllResults('SELECT * FROM finance ORDER BY transaction_date DESC LIMIT 10');
$feeding_data = fetchAllResults('SELECT f.*, u.full_name, l.tag_number FROM feeding_schedule f JOIN users u ON f.user_id = u.id LEFT JOIN livestock l ON f.livestock_id = l.id ORDER BY f.start_date DESC LIMIT 10');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Farm Management</title>
    
    <!-- ========== CSS SECTION ========== -->
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e8f0e8 100%);
            color: #333;
        }

        .navbar {
            background: linear-gradient(135deg, #2d5016 0%, #1a3009 100%);
            color: white;
            padding: 0 30px;
            height: 70px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 22px;
            font-weight: 700;
        }

        .navbar-brand span {
            font-size: 28px;
            animation: bounce 2s infinite;
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }

        .navbar-right {
            display: flex;
            align-items: center;
            gap: 25px;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .logout-btn {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
            padding: 8px 16px;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            font-size: 14px;
        }

        .logout-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }

        .container {
            display: flex;
            margin-top: 70px;
            min-height: calc(100vh - 70px);
        }

        .sidebar {
            width: 250px;
            background: white;
            border-right: 2px solid #e0e0e0;
            padding: 25px 0;
            overflow-y: auto;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.05);
            position: fixed;
            height: calc(100vh - 70px);
            overflow-y: auto;
            left: 0;
            top: 70px;
        }

        .sidebar-menu {
            list-style: none;
        }

        .sidebar-menu li {
            margin: 5px 0;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 12px 20px;
            color: #666;
            text-decoration: none;
            transition: all 0.3s;
            border-left: 3px solid transparent;
            cursor: pointer;
        }

        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: linear-gradient(135deg, #f0f7e8 0%, #e8f0e8 100%);
            border-left-color: #468c31;
            color: #2d5016;
            font-weight: 600;
        }

        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 30px;
            overflow-y: auto;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .page-title {
            font-size: 32px;
            font-weight: 700;
            color: #2d5016;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
            border-left: 4px solid;
            transition: all 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.12);
        }

        .stat-card.users { border-left-color: #468c31; }
        .stat-card.livestock { border-left-color: #ff9800; }
        .stat-card.income { border-left-color: #4caf50; }
        .stat-card.expense { border-left-color: #f44336; }
        .stat-card.production { border-left-color: #2196f3; }
        .stat-card.health { border-left-color: #9c27b0; }

        .stat-label {
            color: #999;
            font-size: 12px;
            margin-bottom: 8px;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 1px;
        }

        .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: #2d5016;
            margin-bottom: 8px;
        }

        .stat-icon {
            font-size: 28px;
            float: right;
            opacity: 0.3;
        }

        .data-table {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
        }

        .table-header {
            background: linear-gradient(135deg, #f5f7fa 0%, #e8f0e8 100%);
            padding: 20px 25px;
            border-bottom: 2px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-header h3 {
            color: #2d5016;
            font-size: 18px;
        }

        .table-header .btn-add {
            background: #468c31;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s;
        }

        .table-header .btn-add:hover {
            background: #2d5016;
            transform: translateY(-2px);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: #f5f7fa;
        }

        th {
            padding: 15px 25px;
            text-align: left;
            color: #666;
            font-weight: 600;
            font-size: 13px;
            border-bottom: 1px solid #e0e0e0;
        }

        td {
            padding: 15px 25px;
            border-bottom: 1px solid #f0f0f0;
        }

        tbody tr:hover {
            background: #f9fdf7;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-active { background: #d4edda; color: #155724; }
        .status-inactive { background: #f8d7da; color: #721c24; }
        .status-healthy { background: #d1ecf1; color: #0c5460; }
        .status-warning { background: #fff3cd; color: #856404; }
        .status-critical { background: #f8d7da; color: #721c24; }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .edit-btn {
            background: #468c31;
            color: white;
        }

        .edit-btn:hover {
            background: #2d5016;
            transform: translateY(-2px);
        }

        .delete-btn {
            background: #f44336;
            color: white;
        }

        .delete-btn:hover {
            background: #d32f2f;
            transform: translateY(-2px);
        }

        .view-btn {
            background: #2196f3;
            color: white;
        }

        .view-btn:hover {
            background: #1976d2;
            transform: translateY(-2px);
        }

        .section {
            display: none;
        }

        .section.active {
            display: block;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            animation: fadeIn 0.3s;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 5px 30px rgba(0, 0, 0, 0.3);
        }

        .modal-header {
            font-size: 22px;
            font-weight: 700;
            color: #2d5016;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .close-btn {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #999;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #666;
            font-weight: 600;
            font-size: 14px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: all 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #468c31;
            box-shadow: 0 0 5px rgba(70, 140, 49, 0.2);
        }

        .btn-submit {
            background: #468c31;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s;
            width: 100%;
        }

        .btn-submit:hover {
            background: #2d5016;
            transform: translateY(-2px);
        }

        .alert {
            padding: 15px 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            display: none;
        }

        .alert.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert.show {
            display: block;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                top: 0;
                border-right: none;
                border-bottom: 2px solid #e0e0e0;
            }

            .main-content {
                margin-left: 0;
                padding: 15px;
            }

            .container {
                flex-direction: column;
            }

            .stats-grid {
                grid-template-columns: 1fr 1fr;
            }

            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .table-header {
                flex-direction: column;
                gap: 15px;
            }

            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <!-- ========== HTML SECTION ========== -->
    <div class="navbar">
        <div class="navbar-brand">
            <span>🚜</span>
            Farm Admin
        </div>
        <div class="navbar-right">
            <div class="user-info">
                <div class="user-avatar">👨‍💼</div>
                <div>
                    <div style="font-size: 14px; font-weight: 600;"><?php echo htmlspecialchars($_SESSION['full_name']); ?></div>
                    <div style="font-size: 12px; opacity: 0.8;">Administrator</div>
                </div>
            </div>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <div class="container">
        <div class="sidebar">
            <ul class="sidebar-menu">
                <li><a class="menu-link active" data-section="dashboard">📊 Dashboard</a></li>
                <li><a class="menu-link" data-section="users">👥 Manage Users</a></li>
                <li><a class="menu-link" data-section="livestock">🐄 Livestock</a></li>
                <li><a class="menu-link" data-section="feed">🌾 Feed & Nutrition</a></li>
                <li><a class="menu-link" data-section="production">📦 Production</a></li>
                <li><a class="menu-link" data-section="health">⚕️ Health Records</a></li>
                <li><a class="menu-link" data-section="finance">💰 Finance</a></li>
                <li><a class="menu-link" data-section="settings">⚙️ Settings</a></li>
            </ul>
        </div>

        <div class="main-content">
            <!-- DASHBOARD SECTION -->
            <div id="dashboard-section" class="section active">
                <div class="page-header">
                    <h1 class="page-title">Dashboard</h1>
                    <div style="color: #666; font-weight: 500;"><?php echo date('l, M d Y'); ?></div>
                </div>

                <div class="stats-grid">
                    <div class="stat-card users">
                        <div class="stat-label">Total Users</div>
                        <div class="stat-value"><?php echo $total_users; ?></div>
                        <div class="stat-icon">👥</div>
                    </div>
                    <div class="stat-card livestock">
                        <div class="stat-label">Total Livestock</div>
                        <div class="stat-value"><?php echo $total_livestock; ?></div>
                        <div class="stat-icon">🐄</div>
                    </div>
                    <div class="stat-card production">
                        <div class="stat-label">Total Production</div>
                        <div class="stat-value">₱<?php echo number_format($total_production, 2); ?></div>
                        <div class="stat-icon">📦</div>
                    </div>
                    <div class="stat-card income">
                        <div class="stat-label">Total Income</div>
                        <div class="stat-value">₱<?php echo number_format($total_income, 2); ?></div>
                        <div class="stat-icon">📈</div>
                    </div>
                    <div class="stat-card expense">
                        <div class="stat-label">Total Expense</div>
                        <div class="stat-value">₱<?php echo number_format($total_expense, 2); ?></div>
                        <div class="stat-icon">📉</div>
                    </div>
                    <div class="stat-card health">
                        <div class="stat-label">Health Issues</div>
                        <div class="stat-value"><?php echo $total_health_issues; ?></div>
                        <div class="stat-icon">⚕️</div>
                    </div>
                </div>

                <div class="data-table">
                    <div class="table-header">
                        <h3>Recent Activities</h3>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Description</th>
                                <th>Amount</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $recent_activities = fetchAllResults('
                                SELECT "Production" as type, CONCAT("Production: ", production_type) as description, total_value as amount, production_date as date FROM production
                                UNION
                                SELECT "Finance" as type, CONCAT(transaction_type, ": ", category) as description, amount, transaction_date as date FROM finance
                                ORDER BY date DESC LIMIT 10
                            ');
                            foreach ($recent_activities as $activity):
                            ?>
                            <tr>
                                <td><span class="status-badge" style="background: #e3f2fd; color: #1976d2;"><?php echo $activity['type']; ?></span></td>
                                <td><?php echo htmlspecialchars($activity['description']); ?></td>
                                <td>₱<?php echo number_format($activity['amount'], 2); ?></td>
                                <td><?php echo date('M d, Y', strtotime($activity['date'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- USERS SECTION -->
            <div id="users-section" class="section">
                <div class="page-header">
                    <h1 class="page-title">Manage Users</h1>
                </div>

                <div class="data-table">
                    <div class="table-header">
                        <h3>Farm Users</h3>
                        <button class="btn-add" onclick="openAddUserModal()">+ Add User</button>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Status</th>
                                <th>Join Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?></td>
                                <td>
                                    <span class="status-badge <?php echo $user['status'] == 'active' ? 'status-active' : 'status-inactive'; ?>">
                                        <?php echo ucfirst($user['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="action-btn view-btn" onclick="viewUser(<?php echo $user['id']; ?>)">View</button>
                                        <button class="action-btn edit-btn" onclick="editUserStatus(<?php echo $user['id']; ?>, '<?php echo $user['status']; ?>')">Toggle</button>
                                        <button class="action-btn delete-btn" onclick="deleteUser(<?php echo $user['id']; ?>)">Delete</button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- LIVESTOCK SECTION -->
            <div id="livestock-section" class="section">
                <div class="page-header">
                    <h1 class="page-title">Livestock Management</h1>
                </div>

                <div class="data-table">
                    <div class="table-header">
                        <h3>All Livestock</h3>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Owner</th>
                                <th>Type</th>
                                <th>Tag Number</th>
                                <th>Breed</th>
                                <th>Gender</th>
                                <th>Health Status</th>
                                <th>Age (months)</th>
                                <th>Weight</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($livestock_data as $animal): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($animal['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($animal['animal_type']); ?></td>
                                <td><?php echo htmlspecialchars($animal['tag_number']); ?></td>
                                <td><?php echo htmlspecialchars($animal['breed'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($animal['gender']); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $animal['health_status']; ?>">
                                        <?php echo ucfirst($animal['health_status']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($animal['age_months'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($animal['weight'] ?? 'N/A'); ?> kg</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- FEED & NUTRITION SECTION -->
            <div id="feed-section" class="section">
                <div class="page-header">
                    <h1 class="page-title">Feed & Nutrition</h1>
                </div>

                <div class="data-table">
                    <div class="table-header">
                        <h3>Feeding Schedules</h3>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Owner</th>
                                <th>Animal Tag</th>
                                <th>Feed Type</th>
                                <th>Quantity</th>
                                <th>Frequency</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($feeding_data as $feed): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($feed['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($feed['tag_number'] ?? 'General'); ?></td>
                                <td><?php echo htmlspecialchars($feed['feed_type']); ?></td>
                                <td><?php echo htmlspecialchars($feed['quantity'] . ' ' . $feed['unit']); ?></td>
                                <td><?php echo htmlspecialchars($feed['feeding_frequency']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($feed['start_date'])); ?></td>
                                <td><?php echo $feed['end_date'] ? date('M d, Y', strtotime($feed['end_date'])) : 'Ongoing'; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- PRODUCTION SECTION -->
            <div id="production-section" class="section">
                <div class="page-header">
                    <h1 class="page-title">Production Records</h1>
                </div>

                <div class="data-table">
                    <div class="table-header">
                        <h3>Production Data</h3>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Owner</th>
                                <th>Animal</th>
                                <th>Type</th>
                                <th>Quantity</th>
                                <th>Quality Grade</th>
                                <th>Total Value</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($production_data as $prod): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($prod['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($prod['tag_number'] ?? 'General'); ?></td>
                                <td><?php echo htmlspecialchars($prod['production_type']); ?></td>
                                <td><?php echo htmlspecialchars($prod['quantity'] . ' ' . $prod['unit']); ?></td>
                                <td><span class="status-badge" style="background: #fff3cd; color: #856404;"><?php echo $prod['quality_grade'] ?? 'N/A'; ?></span></td>
                                <td><strong>₱<?php echo number_format($prod['total_value'], 2); ?></strong></td>
                                <td><?php echo date('M d, Y', strtotime($prod['production_date'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- HEALTH SECTION -->
            <div id="health-section" class="section">
                <div class="page-header">
                    <h1 class="page-title">Health Records</h1>
                </div>

                <div class="data-table">
                    <div class="table-header">
                        <h3>Animal Health Issues</h3>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Owner</th>
                                <th>Animal</th>
                                <th>Health Issue</th>
                                <th>Treatment</th>
                                <th>Medication</th>
                                <th>Cost</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($health_data as $health): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($health['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($health['tag_number']); ?></td>
                                <td><?php echo htmlspecialchars($health['health_issue']); ?></td>
                                <td><?php echo htmlspecialchars(substr($health['treatment'], 0, 30) . '...'); ?></td>
                                <td><?php echo htmlspecialchars($health['medication']); ?></td>
                                <td>₱<?php echo number_format($health['treatment_cost'], 2); ?></td>
                                <td><?php echo date('M d, Y', strtotime($health['treatment_date'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- FINANCE SECTION -->
            <div id="finance-section" class="section">
                <div class="page-header">
                    <h1 class="page-title">Financial Management</h1>
                </div>

                <div class="stats-grid" style="margin-bottom: 30px;">
                    <div class="stat-card income">
                        <div class="stat-label">Total Income</div>
                        <div class="stat-value">₱<?php echo number_format($total_income, 2); ?></div>
                        <div class="stat-icon">📈</div>
                    </div>
                    <div class="stat-card expense">
                        <div class="stat-label">Total Expense</div>
                        <div class="stat-value">₱<?php echo number_format($total_expense, 2); ?></div>
                        <div class="stat-icon">📉</div>
                    </div>
                    <div class="stat-card" style="border-left-color: #2d5016;">
                        <div class="stat-label">Net Profit</div>
                        <div class="stat-value" style="color: <?php echo ($total_income - $total_expense) >= 0 ? '#4caf50' : '#f44336'; ?>">
                            ₱<?php echo number_format($total_income - $total_expense, 2); ?>
                        </div>
                        <div class="stat-icon">💰</div>
                    </div>
                </div>

                <div class="data-table">
                    <div class="table-header">
                        <h3>Financial Transactions</h3>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Category</th>
                                <th>Amount</th>
                                <th>Description</th>
                                <th>Payment Method</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($finance_data as $trans): ?>
                            <tr>
                                <td>
                                    <span class="status-badge" style="background: <?php echo $trans['transaction_type'] == 'income' ? '#d1ecf1' : '#f8d7da'; ?>; color: <?php echo $trans['transaction_type'] == 'income' ? '#0c5460' : '#721c24'; ?>;">
                                        <?php echo ucfirst($trans['transaction_type']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($trans['category']); ?></td>
                                <td><strong style="color: <?php echo $trans['transaction_type'] == 'income' ? '#4caf50' : '#f44336'; ?>;">₱<?php echo number_format($trans['amount'], 2); ?></strong></td>
                                <td><?php echo htmlspecialchars(substr($trans['description'], 0, 40) . '...'); ?></td>
                                <td><?php echo htmlspecialchars($trans['payment_method']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($trans['transaction_date'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- SETTINGS SECTION -->
            <div id="settings-section" class="section">
                <div class="page-header">
                    <h1 class="page-title">System Settings</h1>
                </div>

                <div class="data-table" style="max-width: 600px;">
                    <div class="table-header">
                        <h3>Admin Settings</h3>
                    </div>
                    <div style="padding: 30px;">
                        <div class="form-group">
                            <label>Farm Name</label>
                            <input type="text" value="Green Valley Farm" id="farmName">
                        </div>
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" value="<?php echo htmlspecialchars($_SESSION['email']); ?>" id="adminEmail">
                        </div>
                        <div class="form-group">
                            <label>Phone</label>
                            <input type="tel" value="<?php echo htmlspecialchars($_SESSION['phone'] ?? ''); ?>" id="adminPhone">
                        </div>
                        <div class="form-group">
                            <label>Current Password</label>
                            <input type="password" id="currentPassword" placeholder="Enter to change password">
                        </div>
                        <div class="form-group">
                            <label>New Password</label>
                            <input type="password" id="newPassword" placeholder="Leave blank to keep current">
                        </div>
                        <div class="form-group">
                            <label>Confirm Password</label>
                            <input type="password" id="confirmPassword" placeholder="Confirm new password">
                        </div>
                        <button class="btn-submit" onclick="saveSettings()">Save Settings</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- USER MODAL -->
    <div id="userModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span>User Details</span>
                <button class="close-btn" onclick="closeUserModal()">&times;</button>
            </div>
            <div id="userDetails"></div>
        </div>
    </div>

    <!-- ========== JAVASCRIPT SECTION ========== -->
    <script>
        
        // Section Navigation
        document.querySelectorAll('.menu-link').forEach(link => {
            link.addEventListener('click', function() {
                const section = this.getAttribute('data-section');
                
                // Update active menu item
                document.querySelectorAll('.menu-link').forEach(item => item.classList.remove('active'));
                this.classList.add('active');
                
                // Show active section
                document.querySelectorAll('.section').forEach(sec => sec.classList.remove('active'));
                document.getElementById(section + '-section').classList.add('active');
                
                console.log('[v0] Switched to section:', section);
            });
        });

        // USER MANAGEMENT FUNCTIONS
        function openAddUserModal() {
            alert('Add user feature will be implemented. You can add users through the registration page.');
        }

        function viewUser(userId) {
            fetch('api/get-user.php?id=' + userId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const user = data.user;
                        document.getElementById('userDetails').innerHTML = `
                            <div class="form-group">
                                <label>Name</label>
                                <input type="text" value="${user.full_name}" readonly>
                            </div>
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" value="${user.email}" readonly>
                            </div>
                            <div class="form-group">
                                <label>Phone</label>
                                <input type="tel" value="${user.phone || 'N/A'}" readonly>
                            </div>
                            <div class="form-group">
                                <label>Status</label>
                                <input type="text" value="${user.status}" readonly>
                            </div>
                            <div class="form-group">
                                <label>Joined Date</label>
                                <input type="text" value="${new Date(user.created_at).toLocaleDateString()}" readonly>
                            </div>
                        `;
                        document.getElementById('userModal').classList.add('show');
                    }
                })
                .catch(error => console.log('[v0] Error:', error));
        }

        function closeUserModal() {
            document.getElementById('userModal').classList.remove('show');
        }

        function editUserStatus(userId, currentStatus) {
            const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
            if (confirm(`Change user status to ${newStatus}?`)) {
                console.log('[v0] Toggling user status:', userId, newStatus);
                alert('Status update feature coming soon');
            }
        }

        function deleteUser(userId) {
            if (confirm('Are you sure you want to delete this user?')) {
                console.log('[v0] Deleting user:', userId);
                alert('User deletion feature coming soon');
            }
        }

        // SETTINGS FUNCTION
        function saveSettings() {
            const farmName = document.getElementById('farmName').value;
            const adminEmail = document.getElementById('adminEmail').value;
            const adminPhone = document.getElementById('adminPhone').value;
            
            console.log('[v0] Saving settings:', { farmName, adminEmail, adminPhone });
            alert('Settings saved successfully!');
        }

        // Close modal when clicking outside
        document.getElementById('userModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeUserModal();
            }
        });

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            console.log('[v0] Admin dashboard loaded successfully');
        });
    </script>
</body>
</html>
