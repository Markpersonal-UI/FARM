<?php
session_start();
require 'config/database.php';
require 'config/session.php';

requireLogin();

$user_id = $_SESSION['user_id'];

$section = $_GET['section'] ?? 'dashboard';
$search_query = $_GET['search'] ?? '';
$filter_type = $_GET['filter'] ?? '';
$filter_status = $_GET['status'] ?? '';
$print_mode = isset($_GET['print']);

// Get user statistics
$total_livestock = fetchSingleResult('SELECT COUNT(*) as count FROM livestock WHERE user_id = ?', [$user_id], 'i')['count'];
$total_production = fetchSingleResult('SELECT COUNT(*) as count FROM production WHERE user_id = ?', [$user_id], 'i')['count'];
$total_income = fetchSingleResult('SELECT SUM(amount) as total FROM finance WHERE user_id = ? AND transaction_type = "income"', [$user_id], 'i')['total'] ?? 0;
$total_expense = fetchSingleResult('SELECT SUM(amount) as total FROM finance WHERE user_id = ? AND transaction_type = "expense"', [$user_id], 'i')['total'] ?? 0;
$total_health_issues = fetchSingleResult('SELECT COUNT(*) as count FROM health_records WHERE user_id = ?', [$user_id], 'i')['count'];

if ($section === 'livestock') {
    $query = 'SELECT * FROM livestock WHERE user_id = ?';
    $params = [$user_id];
    $types = 'i';
    
    if (!empty($search_query)) {
        $query .= ' AND (tag_number LIKE ? OR animal_type LIKE ?)';
        $params[] = "%$search_query%";
        $params[] = "%$search_query%";
        $types .= 'ss';
    }
    
    if (!empty($filter_type)) {
        $query .= ' AND animal_type = ?';
        $params[] = $filter_type;
        $types .= 's';
    }
    
    $query .= ' ORDER BY created_at DESC';
    $livestock = fetchAllResults($query, $params, $types);
} else {
    $livestock = fetchAllResults('SELECT * FROM livestock WHERE user_id = ? ORDER BY created_at DESC', [$user_id], 'i');
}

if ($section === 'production') {
    $query = 'SELECT p.*, l.tag_number FROM production p LEFT JOIN livestock l ON p.livestock_id = l.id WHERE p.user_id = ?';
    $params = [$user_id];
    $types = 'i';
    
    if (!empty($search_query)) {
        $query .= ' AND (p.production_type LIKE ? OR l.tag_number LIKE ?)';
        $params[] = "%$search_query%";
        $params[] = "%$search_query%";
        $types .= 'ss';
    }
    
    if (!empty($filter_type)) {
        $query .= ' AND p.production_type = ?';
        $params[] = $filter_type;
        $types .= 's';
    }
    
    $query .= ' ORDER BY p.production_date DESC';
    $production = fetchAllResults($query, $params, $types);
} else {
    $production = fetchAllResults('SELECT p.*, l.tag_number FROM production p LEFT JOIN livestock l ON p.livestock_id = l.id WHERE p.user_id = ? ORDER BY p.production_date DESC', [$user_id], 'i');
}

if ($section === 'health') {
    $query = 'SELECT h.*, l.tag_number FROM health_records h JOIN livestock l ON h.livestock_id = l.id WHERE h.user_id = ?';
    $params = [$user_id];
    $types = 'i';
    
    if (!empty($search_query)) {
        $query .= ' AND (h.issue_description LIKE ? OR l.tag_number LIKE ?)';
        $params[] = "%$search_query%";
        $params[] = "%$search_query%";
        $types .= 'ss';
    }
    
    if (!empty($filter_status)) {
        $query .= ' AND h.status = ?';
        $params[] = $filter_status;
        $types .= 's';
    }
    
    $query .= ' ORDER BY h.treatment_date DESC';
    $health = fetchAllResults($query, $params, $types);
} else {
    $health = fetchAllResults('SELECT h.*, l.tag_number FROM health_records h JOIN livestock l ON h.livestock_id = l.id WHERE h.user_id = ? ORDER BY h.treatment_date DESC', [$user_id], 'i');
}

if ($section === 'finance') {
    $query = 'SELECT * FROM finance WHERE user_id = ?';
    $params = [$user_id];
    $types = 'i';
    
    if (!empty($search_query)) {
        $query .= ' AND (category LIKE ? OR description LIKE ?)';
        $params[] = "%$search_query%";
        $params[] = "%$search_query%";
        $types .= 'ss';
    }
    
    if (!empty($filter_type)) {
        $query .= ' AND transaction_type = ?';
        $params[] = $filter_type;
        $types .= 's';
    }
    
    $query .= ' ORDER BY transaction_date DESC';
    $finance = fetchAllResults($query, $params, $types);
} else {
    $finance = fetchAllResults('SELECT * FROM finance WHERE user_id = ? ORDER BY transaction_date DESC', [$user_id], 'i');
}

if ($section === 'feeding') {
    $query = 'SELECT f.*, l.tag_number FROM feeding_schedule f LEFT JOIN livestock l ON f.livestock_id = l.id WHERE f.user_id = ?';
    $params = [$user_id];
    $types = 'i';
    
    if (!empty($search_query)) {
        $query .= ' AND (f.feed_type LIKE ? OR l.tag_number LIKE ?)';
        $params[] = "%$search_query%";
        $params[] = "%$search_query%";
        $types .= 'ss';
    }
    
    $query .= ' ORDER BY f.start_date DESC';
    $feeding = fetchAllResults($query, $params, $types);
} else {
    $feeding = fetchAllResults('SELECT f.*, l.tag_number FROM feeding_schedule f LEFT JOIN livestock l ON f.livestock_id = l.id WHERE f.user_id = ? ORDER BY f.start_date DESC', [$user_id], 'i');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farm Dashboard - Farm Management System</title>
    
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

        @media print {
            .navbar, .sidebar, .page-header, .search-bar, .no-print {
                display: none;
            }
            body {
                background: white;
            }
            .main-content {
                margin-left: 0;
                padding: 20px;
            }
            table {
                width: 100%;
                border-collapse: collapse;
            }
            table, th, td {
                border: 1px solid #ddd;
            }
        }

        .navbar {
            background: linear-gradient(135deg, #468c31 0%, #2d5016 100%);
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

        .search-bar {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            flex-wrap: wrap;
        }

        .search-bar input,
        .search-bar select {
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        .search-bar input:focus,
        .search-bar select:focus {
            outline: none;
            border-color: #468c31;
            box-shadow: 0 0 5px rgba(70, 140, 49, 0.2);
        }

        .btn-group {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 14px;
            font-weight: 600;
        }

        .btn-primary {
            background: linear-gradient(135deg, #468c31 0%, #2d5016 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(70, 140, 49, 0.3);
        }

        .btn-print {
            background: #8B8B8B;
            color: white;
        }

        .btn-print:hover {
            background: #696969;
            transform: translateY(-2px);
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
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            border-left: 4px solid #468c31;
            transition: all 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        .stat-label {
            font-size: 14px;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: #2d5016;
        }

        .data-table {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-top: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: linear-gradient(135deg, #2d5016 0%, #1a3009 100%);
            color: white;
        }

        th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
        }

        tbody tr:hover {
            background: #f9f9f9;
        }

        .badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }

        .badge-success {
            background: #d4edda;
            color: #155724;
        }

        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }

        .badge-danger {
            background: #f8d7da;
            color: #721c24;
        }

        .badge-info {
            background: #d1ecf1;
            color: #0c5460;
        }

        .empty-message {
            text-align: center;
            padding: 40px;
            color: #999;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
            }

            .main-content {
                margin-left: 200px;
            }

            .page-header {
                flex-direction: column;
                gap: 15px;
            }

            .search-bar {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <!-- ========== HTML SECTION ========== -->
    
    <!-- Navbar -->
    <div class="navbar">
        <div class="navbar-brand">
            <span>🚜</span> Farm Management System
        </div>
        <div class="navbar-right">
            <div class="user-info">
                <div class="user-avatar">👨</div>
                <div>
                    <div style="font-size: 14px;"><strong><?php echo $_SESSION['full_name']; ?></strong></div>
                    <div style="font-size: 12px; opacity: 0.8;">Farmer</div>
                </div>
            </div>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <ul class="sidebar-menu">
                <li><a href="user.php?section=dashboard" class="<?php echo $section === 'dashboard' ? 'active' : ''; ?>">📊 Dashboard</a></li>
                <li><a href="user.php?section=livestock" class="<?php echo $section === 'livestock' ? 'active' : ''; ?>">🐄 Livestock</a></li>
                <li><a href="user.php?section=feeding" class="<?php echo $section === 'feeding' ? 'active' : ''; ?>">🌾 Feed & Nutrition</a></li>
                <li><a href="user.php?section=production" class="<?php echo $section === 'production' ? 'active' : ''; ?>">📦 Production</a></li>
                <li><a href="user.php?section=health" class="<?php echo $section === 'health' ? 'active' : ''; ?>">💊 Health</a></li>
                <li><a href="user.php?section=finance" class="<?php echo $section === 'finance' ? 'active' : ''; ?>">💰 Finance</a></li>
                <li><a href="user.php?section=profile" class="<?php echo $section === 'profile' ? 'active' : ''; ?>">👤 Profile</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Dashboard Section -->
            <?php if ($section === 'dashboard'): ?>
                <div class="page-header no-print">
                    <h1 class="page-title">📊 My Farm Dashboard</h1>
                </div>

                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-label">Total Livestock</div>
                        <div class="stat-value"><?php echo $total_livestock; ?></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Production Records</div>
                        <div class="stat-value"><?php echo $total_production; ?></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Total Income</div>
                        <div class="stat-value">₱<?php echo number_format($total_income, 2); ?></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Total Expense</div>
                        <div class="stat-value">₱<?php echo number_format($total_expense, 2); ?></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Net Profit</div>
                        <div class="stat-value" style="color: <?php echo ($total_income - $total_expense) >= 0 ? 'green' : 'red'; ?>;">₱<?php echo number_format($total_income - $total_expense, 2); ?></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Health Issues</div>
                        <div class="stat-value"><?php echo $total_health_issues; ?></div>
                    </div>
                </div>

            <!-- Livestock Section -->
            <?php elseif ($section === 'livestock'): ?>
                <div class="page-header no-print">
                    <h1 class="page-title">🐄 My Livestock</h1>
                    <div class="btn-group">
                        <button class="btn btn-print" onclick="window.print()">🖨️ Print</button>
                    </div>
                </div>

                <div class="search-bar no-print">
                    <form style="display: flex; gap: 10px; width: 100%; flex-wrap: wrap;" method="GET">
                        <input type="hidden" name="section" value="livestock">
                        <input type="text" name="search" placeholder="Search by tag or type..." value="<?php echo htmlspecialchars($search_query); ?>" style="flex: 1; min-width: 200px;">
                        <select name="filter" style="min-width: 150px;">
                            <option value="">All Types</option>
                            <option value="Cattle" <?php echo $filter_type === 'Cattle' ? 'selected' : ''; ?>>Cattle</option>
                            <option value="Poultry" <?php echo $filter_type === 'Poultry' ? 'selected' : ''; ?>>Poultry</option>
                            <option value="Goat" <?php echo $filter_type === 'Goat' ? 'selected' : ''; ?>>Goat</option>
                            <option value="Pig" <?php echo $filter_type === 'Pig' ? 'selected' : ''; ?>>Pig</option>
                        </select>
                        <button type="submit" class="btn btn-primary">🔍 Search</button>
                        <a href="user.php?section=livestock" class="btn" style="background: #ddd; text-decoration: none;">Clear</a>
                    </form>
                </div>

                <?php if (!empty($livestock)): ?>
                    <div class="data-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Tag Number</th>
                                    <th>Animal Type</th>
                                    <th>Breed</th>
                                    <th>Gender</th>
                                    <th>Age (months)</th>
                                    <th>Weight (kg)</th>
                                    <th>Location</th>
                                    <th>Added Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($livestock as $animal): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($animal['tag_number']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($animal['animal_type']); ?></td>
                                        <td><?php echo htmlspecialchars($animal['breed']); ?></td>
                                        <td><?php echo htmlspecialchars($animal['gender']); ?></td>
                                        <td><?php echo $animal['age_months']; ?></td>
                                        <td><?php echo number_format($animal['weight'], 2); ?></td>
                                        <td><?php echo htmlspecialchars($animal['location']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($animal['created_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-message">
                        <p>No livestock found. Start by adding your first animal!</p>
                    </div>
                <?php endif; ?>

            <!-- Feed & Nutrition Section -->
            <?php elseif ($section === 'feeding'): ?>
                <div class="page-header no-print">
                    <h1 class="page-title">🌾 Feed & Nutrition</h1>
                    <div class="btn-group">
                        <button class="btn btn-print" onclick="window.print()">🖨️ Print</button>
                    </div>
                </div>

                <div class="search-bar no-print">
                    <form style="display: flex; gap: 10px; width: 100%;" method="GET">
                        <input type="hidden" name="section" value="feeding">
                        <input type="text" name="search" placeholder="Search by feed type..." value="<?php echo htmlspecialchars($search_query); ?>">
                        <button type="submit" class="btn btn-primary">🔍 Search</button>
                        <a href="user.php?section=feeding" class="btn" style="background: #ddd; text-decoration: none;">Clear</a>
                    </form>
                </div>

                <?php if (!empty($feeding)): ?>
                    <div class="data-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Feed Type</th>
                                    <th>Quantity (kg)</th>
                                    <th>Cost (₱)</th>
                                    <th>Livestock Tag</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($feeding as $feed): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($feed['feed_type']); ?></strong></td>
                                        <td><?php echo number_format($feed['quantity_kg'], 2); ?></td>
                                        <td>₱<?php echo number_format($feed['cost'], 2); ?></td>
                                        <td><?php echo htmlspecialchars($feed['tag_number'] ?? 'N/A'); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($feed['start_date'])); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($feed['end_date'])); ?></td>
                                        <td><?php echo htmlspecialchars($feed['notes']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-message">
                        <p>No feeding schedules found.</p>
                    </div>
                <?php endif; ?>

            <!-- Production Section -->
            <?php elseif ($section === 'production'): ?>
                <div class="page-header no-print">
                    <h1 class="page-title">📦 Production Records</h1>
                    <div class="btn-group">
                        <button class="btn btn-print" onclick="window.print()">🖨️ Print</button>
                    </div>
                </div>

                <div class="search-bar no-print">
                    <form style="display: flex; gap: 10px; width: 100%; flex-wrap: wrap;" method="GET">
                        <input type="hidden" name="section" value="production">
                        <input type="text" name="search" placeholder="Search production..." value="<?php echo htmlspecialchars($search_query); ?>" style="flex: 1; min-width: 200px;">
                        <select name="filter" style="min-width: 150px;">
                            <option value="">All Types</option>
                            <option value="Milk" <?php echo $filter_type === 'Milk' ? 'selected' : ''; ?>>Milk</option>
                            <option value="Eggs" <?php echo $filter_type === 'Eggs' ? 'selected' : ''; ?>>Eggs</option>
                            <option value="Meat" <?php echo $filter_type === 'Meat' ? 'selected' : ''; ?>>Meat</option>
                            <option value="Wool" <?php echo $filter_type === 'Wool' ? 'selected' : ''; ?>>Wool</option>
                        </select>
                        <button type="submit" class="btn btn-primary">🔍 Search</button>
                        <a href="user.php?section=production" class="btn" style="background: #ddd; text-decoration: none;">Clear</a>
                    </form>
                </div>

                <?php if (!empty($production)): ?>
                    <div class="data-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Production Type</th>
                                    <th>Quantity</th>
                                    <th>Unit</th>
                                    <th>Quality Grade</th>
                                    <th>Unit Price</th>
                                    <th>Total Value</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($production as $prod): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($prod['production_type']); ?></strong></td>
                                        <td><?php echo number_format($prod['quantity'], 2); ?></td>
                                        <td><?php echo htmlspecialchars($prod['unit']); ?></td>
                                        <td><span class="badge badge-info"><?php echo htmlspecialchars($prod['quality_grade']); ?></span></td>
                                        <td>₱<?php echo number_format($prod['unit_price'], 2); ?></td>
                                        <td><strong>₱<?php echo number_format($prod['total_value'], 2); ?></strong></td>
                                        <td><?php echo date('M d, Y', strtotime($prod['production_date'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-message">
                        <p>No production records found.</p>
                    </div>
                <?php endif; ?>

            <!-- Health Section -->
            <?php elseif ($section === 'health'): ?>
                <div class="page-header no-print">
                    <h1 class="page-title">💊 Health Records</h1>
                    <div class="btn-group">
                        <button class="btn btn-print" onclick="window.print()">🖨️ Print</button>
                    </div>
                </div>

                <div class="search-bar no-print">
                    <form style="display: flex; gap: 10px; width: 100%; flex-wrap: wrap;" method="GET">
                        <input type="hidden" name="section" value="health">
                        <input type="text" name="search" placeholder="Search health issues..." value="<?php echo htmlspecialchars($search_query); ?>" style="flex: 1; min-width: 200px;">
                        <select name="status" style="min-width: 150px;">
                            <option value="">All Status</option>
                            <option value="Under Treatment" <?php echo $filter_status === 'Under Treatment' ? 'selected' : ''; ?>>Under Treatment</option>
                            <option value="Recovered" <?php echo $filter_status === 'Recovered' ? 'selected' : ''; ?>>Recovered</option>
                            <option value="Critical" <?php echo $filter_status === 'Critical' ? 'selected' : ''; ?>>Critical</option>
                        </select>
                        <button type="submit" class="btn btn-primary">🔍 Search</button>
                        <a href="user.php?section=health" class="btn" style="background: #ddd; text-decoration: none;">Clear</a>
                    </form>
                </div>

                <?php if (!empty($health)): ?>
                    <div class="data-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Animal Tag</th>
                                    <th>Issue Description</th>
                                    <th>Treatment</th>
                                    <th>Medication</th>
                                    <th>Status</th>
                                    <th>Treatment Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($health as $h): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($h['tag_number']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($h['issue_description']); ?></td>
                                        <td><?php echo htmlspecialchars($h['treatment']); ?></td>
                                        <td><?php echo htmlspecialchars($h['medication']); ?></td>
                                        <td>
                                            <?php 
                                            $status_badge = $h['status'] === 'Recovered' ? 'badge-success' : 
                                                            ($h['status'] === 'Critical' ? 'badge-danger' : 'badge-warning');
                                            ?>
                                            <span class="badge <?php echo $status_badge; ?>"><?php echo htmlspecialchars($h['status']); ?></span>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($h['treatment_date'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-message">
                        <p>No health records found.</p>
                    </div>
                <?php endif; ?>

            <!-- Finance Section -->
            <?php elseif ($section === 'finance'): ?>
                <div class="page-header no-print">
                    <h1 class="page-title">💰 Finance Management</h1>
                    <div class="btn-group">
                        <button class="btn btn-print" onclick="window.print()">🖨️ Print</button>
                    </div>
                </div>

                <div class="search-bar no-print">
                    <form style="display: flex; gap: 10px; width: 100%; flex-wrap: wrap;" method="GET">
                        <input type="hidden" name="section" value="finance">
                        <input type="text" name="search" placeholder="Search transactions..." value="<?php echo htmlspecialchars($search_query); ?>" style="flex: 1; min-width: 200px;">
                        <select name="filter" style="min-width: 150px;">
                            <option value="">All Transactions</option>
                            <option value="income" <?php echo $filter_type === 'income' ? 'selected' : ''; ?>>Income</option>
                            <option value="expense" <?php echo $filter_type === 'expense' ? 'selected' : ''; ?>>Expense</option>
                        </select>
                        <button type="submit" class="btn btn-primary">🔍 Search</button>
                        <a href="user.php?section=finance" class="btn" style="background: #ddd; text-decoration: none;">Clear</a>
                    </form>
                </div>

                <?php if (!empty($finance)): ?>
                    <div class="data-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Description</th>
                                    <th>Type</th>
                                    <th>Amount (₱)</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $total_income_calc = 0;
                                $total_expense_calc = 0;
                                foreach ($finance as $fin): 
                                    if ($fin['transaction_type'] === 'income') {
                                        $total_income_calc += $fin['amount'];
                                    } else {
                                        $total_expense_calc += $fin['amount'];
                                    }
                                ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($fin['category']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($fin['description']); ?></td>
                                        <td>
                                            <?php 
                                            $badge_class = $fin['transaction_type'] === 'income' ? 'badge-success' : 'badge-danger';
                                            ?>
                                            <span class="badge <?php echo $badge_class; ?>"><?php echo ucfirst($fin['transaction_type']); ?></span>
                                        </td>
                                        <td><strong>₱<?php echo number_format($fin['amount'], 2); ?></strong></td>
                                        <td><?php echo date('M d, Y', strtotime($fin['transaction_date'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="stats-grid" style="margin-top: 20px;">
                        <div class="stat-card">
                            <div class="stat-label">Total Income</div>
                            <div class="stat-value" style="color: green;">₱<?php echo number_format($total_income_calc, 2); ?></div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-label">Total Expense</div>
                            <div class="stat-value" style="color: red;">₱<?php echo number_format($total_expense_calc, 2); ?></div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-label">Net Profit</div>
                            <div class="stat-value" style="color: <?php echo ($total_income_calc - $total_expense_calc) >= 0 ? 'green' : 'red'; ?>;">₱<?php echo number_format($total_income_calc - $total_expense_calc, 2); ?></div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="empty-message">
                        <p>No finance records found.</p>
                    </div>
                <?php endif; ?>

            <!-- Profile Section -->
            <?php elseif ($section === 'profile'): ?>
                <div class="page-header no-print">
                    <h1 class="page-title">👤 My Profile</h1>
                </div>

                <div style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);">
                    <h2 style="color: #2d5016; margin-bottom: 20px;">My Information</h2>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div>
                            <label style="display: block; margin-bottom: 8px; color: #666; font-weight: 600;">Full Name</label>
                            <input type="text" value="<?php echo htmlspecialchars($_SESSION['full_name']); ?>" readonly style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; background: #f5f5f5;">
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 8px; color: #666; font-weight: 600;">Email</label>
                            <input type="email" value="<?php echo htmlspecialchars($_SESSION['email']); ?>" readonly style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; background: #f5f5f5;">
                        </div>
                    </div>

                    <div style="margin-top: 30px;">
                        <h3 style="color: #2d5016; margin-bottom: 15px;">Farm Statistics</h3>
                        <table style="width: 100%;">
                            <tr style="border-bottom: 1px solid #f0f0f0;">
                                <td style="padding: 10px; font-weight: 600;">Total Livestock</td>
                                <td style="padding: 10px;"><?php echo $total_livestock; ?></td>
                            </tr>
                            <tr style="border-bottom: 1px solid #f0f0f0;">
                                <td style="padding: 10px; font-weight: 600;">Production Records</td>
                                <td style="padding: 10px;"><?php echo $total_production; ?></td>
                            </tr>
                            <tr style="border-bottom: 1px solid #f0f0f0;">
                                <td style="padding: 10px; font-weight: 600;">Account Status</td>
                                <td style="padding: 10px;"><span class="badge badge-success">Active</span></td>
                            </tr>
                            <tr>
                                <td style="padding: 10px; font-weight: 600;">Member Since</td>
                                <td style="padding: 10px;"><?php echo date('M d, Y'); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ========== JAVASCRIPT SECTION ========== -->
    <script>
        // Set active menu item
        document.querySelectorAll('.sidebar-menu a').forEach(link => {
            if (link.href.includes('section=' + '<?php echo $section; ?>')) {
                link.classList.add('active');
            }
        });

        function enhancedPrint() {
            const printWindow = window.open('', '_blank');
            const tableHTML = document.querySelector('.data-table').outerHTML;
            
            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Farm Management Report</title>
                    <style>
                        body { font-family: Arial, sans-serif; margin: 20px; }
                        table { width: 100%; border-collapse: collapse; }
                        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
                        th { background: #2d5016; color: white; }
                        tr:nth-child(even) { background: #f9f9f9; }
                        h1 { color: #2d5016; }
                    </style>
                </head>
                <body>
                    <h1>Farm Management Report - <?php echo date('M d, Y'); ?></h1>
                    ${tableHTML}
                </body>
                </html>
            `);
            printWindow.document.close();
            printWindow.print();
        }

        // Search functionality
        const searchForm = document.querySelector('.search-bar form');
        if (searchForm) {
            searchForm.addEventListener('submit', function(e) {
                const searchInput = this.querySelector('input[name="search"]');
                if (searchInput && searchInput.value.trim() === '') {
                    e.preventDefault();
                    alert('Please enter a search term');
                }
            });
        }
    </script>
</body>
</html>
