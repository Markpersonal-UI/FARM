<?php
session_start();
require 'config/database.php';
require 'config/session.php';

requireLogin();

$user_id = $_SESSION['user_id'];

$total_livestock = fetchSingleResult('SELECT COUNT(*) as count FROM livestock WHERE user_id = ?', [$user_id], 'i')['count'];
$total_production = fetchSingleResult('SELECT COUNT(*) as count FROM production WHERE user_id = ?', [$user_id], 'i')['count'];
$total_income = fetchSingleResult('SELECT SUM(amount) as total FROM finance WHERE user_id = ? AND transaction_type = "income"', [$user_id], 'i')['total'] ?? 0;
$total_expense = fetchSingleResult('SELECT SUM(amount) as total FROM finance WHERE user_id = ? AND transaction_type = "expense"', [$user_id], 'i')['total'] ?? 0;

$livestock = fetchAllResults('SELECT * FROM livestock WHERE user_id = ? ORDER BY created_at DESC LIMIT 5', [$user_id], 'i');

$livestock_error = '';
$livestock_success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_livestock'])) {
    $animal_type = $_POST['animal_type'] ?? '';
    $tag_number = $_POST['tag_number'] ?? '';
    $breed = $_POST['breed'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $age_months = intval($_POST['age_months'] ?? 0);
    $weight = floatval($_POST['weight'] ?? 0);
    $location = $_POST['location'] ?? '';
    
    if (empty($animal_type) || empty($tag_number)) {
        $livestock_error = 'Animal type and tag number are required';
    } else {
        $result = executeQuery(
            'INSERT INTO livestock (user_id, animal_type, tag_number, breed, gender, age_months, weight, location) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
            [$user_id, $animal_type, $tag_number, $breed, $gender, $age_months, $weight, $location],
            'issssiis'
        );
        
        if ($result['success']) {
            $livestock_success = 'Livestock added successfully!';
            header('Refresh: 1; url=dashboard.php');
        } else {
            $livestock_error = 'Failed to add livestock';
        }
    }
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
            background: #f5f7fa;
            color: #333;
        }

        .navbar {
            background: linear-gradient(135deg, #468c31 0%, #2d5016 100%);
            color: white;
            padding: 0 30px;
            height: 70px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
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
            height: calc(100vh - 70px);
        }

        .sidebar {
            width: 280px;
            background: white;
            border-right: 1px solid #e0e0e0;
            padding: 30px 0;
            overflow-y: auto;
        }

        .sidebar-menu {
            list-style: none;
        }

        .sidebar-menu li {
            margin: 0;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px 25px;
            color: #666;
            text-decoration: none;
            transition: all 0.3s;
            border-left: 3px solid transparent;
            cursor: pointer;
        }

        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: #f0f7e8;
            border-left-color: #468c31;
            color: #2d5016;
        }

        .main-content {
            flex: 1;
            overflow-y: auto;
            padding: 30px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .page-title {
            font-size: 32px;
            font-weight: 700;
            color: #2d5016;
        }

        .btn-primary {
            background: linear-gradient(135deg, #468c31 0%, #2d5016 100%);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(70, 140, 49, 0.3);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            border-left: 4px solid #468c31;
        }

        .stat-label {
            color: #999;
            font-size: 13px;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: #2d5016;
        }

        .stat-icon {
            font-size: 28px;
            float: right;
            opacity: 0.2;
        }

        .section {
            display: none;
        }

        .section.active {
            display: block;
        }

        .data-table {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-top: 20px;
        }

        .table-header {
            background: #f5f7fa;
            padding: 20px 25px;
            border-bottom: 1px solid #e0e0e0;
        }

        .table-header h3 {
            color: #2d5016;
            font-size: 18px;
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
            background: #fafbfc;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            background: #e8f5e9;
            color: #1b5e20;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 15px;
        }

        .modal-header h2 {
            color: #2d5016;
            font-size: 22px;
        }

        .close-btn {
            background: none;
            border: none;
            font-size: 28px;
            cursor: pointer;
            color: #999;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            color: #2d5016;
            font-weight: 600;
            font-size: 14px;
        }

        input[type="text"],
        input[type="number"],
        input[type="date"],
        select {
            width: 100%;
            padding: 10px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 14px;
        }

        input:focus,
        select:focus {
            outline: none;
            border-color: #468c31;
            box-shadow: 0 0 0 3px rgba(70, 140, 49, 0.1);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .modal-footer {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 25px;
        }

        .btn-cancel {
            background: #e0e0e0;
            color: #333;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
        }

        .btn-cancel:hover {
            background: #d0d0d0;
        }

        .success-box {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .error-box {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                border-right: none;
                border-bottom: 1px solid #e0e0e0;
                padding: 0;
            }

            .sidebar-menu a {
                padding: 12px 20px;
            }

            .stats-grid {
                grid-template-columns: 1fr 1fr;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
        }
    </style>
</head>
<body>
    <!-- ========== HTML SECTION ========== -->
    <div class="navbar">
        <div class="navbar-brand">
            <span>🌾</span>
            My Farm
        </div>
        <div class="navbar-right">
            <div class="user-info">
                <div class="user-avatar">👨‍🌾</div>
                <div>
                    <div style="font-size: 14px; font-weight: 600;"><?php echo htmlspecialchars($_SESSION['full_name']); ?></div>
                    <div style="font-size: 12px; opacity: 0.8;">Farmer</div>
                </div>
            </div>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <div class="container">
        <div class="sidebar">
            <ul class="sidebar-menu">
                <li><a onclick="showSection('dashboard')" class="active">📊 Dashboard</a></li>
                <li><a onclick="showSection('livestock')">🐄 My Livestock</a></li>
                <li><a onclick="showSection('production')">🥛 Production</a></li>
                <li><a onclick="showSection('health')">⚕️ Health Records</a></li>
                <li><a onclick="showSection('finance')">💰 Finance</a></li>
                <li><a onclick="showSection('feeding')">🌾 Feeding Schedule</a></li>
            </ul>
        </div>

        <div class="main-content">
            <!-- Dashboard Section -->
            <div id="dashboard" class="section active">
                <div class="page-header">
                    <h1 class="page-title">Welcome Back!</h1>
                    <button class="btn-primary" onclick="openModal('addLivestockModal')">+ Add Livestock</button>
                </div>

                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-label">My Livestock</div>
                        <div class="stat-value"><?php echo $total_livestock; ?></div>
                        <div class="stat-icon">🐄</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Production Records</div>
                        <div class="stat-value"><?php echo $total_production; ?></div>
                        <div class="stat-icon">🥛</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Total Income</div>
                        <div class="stat-value">₱<?php echo number_format($total_income, 2); ?></div>
                        <div class="stat-icon">📈</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Total Expense</div>
                        <div class="stat-value">₱<?php echo number_format($total_expense, 2); ?></div>
                        <div class="stat-icon">📉</div>
                    </div>
                </div>

                <div class="data-table">
                    <div class="table-header">
                        <h3>My Livestock</h3>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Animal Type</th>
                                <th>Tag Number</th>
                                <th>Breed</th>
                                <th>Gender</th>
                                <th>Age (months)</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($livestock as $animal): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($animal['animal_type']); ?></td>
                                <td><?php echo htmlspecialchars($animal['tag_number']); ?></td>
                                <td><?php echo htmlspecialchars($animal['breed'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($animal['gender']); ?></td>
                                <td><?php echo $animal['age_months']; ?></td>
                                <td><span class="status-badge"><?php echo ucfirst($animal['health_status'] ?? 'healthy'); ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Livestock Section -->
            <div id="livestock" class="section">
                <div class="page-header">
                    <h1 class="page-title">My Livestock</h1>
                    <button class="btn-primary" onclick="openModal('addLivestockModal')">+ Add Livestock</button>
                </div>

                <?php if ($livestock_success): ?>
                    <div class="success-box"><?php echo $livestock_success; ?></div>
                <?php endif; ?>

                <?php if ($livestock_error): ?>
                    <div class="error-box"><?php echo $livestock_error; ?></div>
                <?php endif; ?>

                <div class="data-table">
                    <div class="table-header">
                        <h3>All Livestock Records</h3>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Animal Type</th>
                                <th>Tag Number</th>
                                <th>Breed</th>
                                <th>Weight (kg)</th>
                                <th>Location</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($livestock as $animal): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($animal['animal_type']); ?></td>
                                <td><?php echo htmlspecialchars($animal['tag_number']); ?></td>
                                <td><?php echo htmlspecialchars($animal['breed'] ?? 'N/A'); ?></td>
                                <td><?php echo $animal['weight'] ?? 'N/A'; ?></td>
                                <td><?php echo htmlspecialchars($animal['location'] ?? 'N/A'); ?></td>
                                <td><span class="status-badge"><?php echo ucfirst($animal['health_status'] ?? 'healthy'); ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Production Section -->
            <div id="production" class="section">
                <div class="page-header">
                    <h1 class="page-title">Production Records</h1>
                    <button class="btn-primary" onclick="openModal('addProductionModal')">+ Add Production</button>
                </div>
                <p style="color: #999; margin-top: 20px;">Production tracking coming soon...</p>
            </div>

            <!-- Health Section -->
            <div id="health" class="section">
                <div class="page-header">
                    <h1 class="page-title">Health Records</h1>
                    <button class="btn-primary" onclick="openModal('addHealthModal')">+ Add Health Record</button>
                </div>
                <p style="color: #999; margin-top: 20px;">Health tracking coming soon...</p>
            </div>

            <!-- Finance Section -->
            <div id="finance" class="section">
                <div class="page-header">
                    <h1 class="page-title">Financial Management</h1>
                    <button class="btn-primary" onclick="openModal('addFinanceModal')">+ Add Transaction</button>
                </div>
                <p style="color: #999; margin-top: 20px;">Financial tracking coming soon...</p>
            </div>

            <!-- Feeding Schedule Section -->
            <div id="feeding" class="section">
                <div class="page-header">
                    <h1 class="page-title">Feeding Schedule</h1>
                    <button class="btn-primary" onclick="openModal('addFeedingModal')">+ Add Feed Schedule</button>
                </div>
                <p style="color: #999; margin-top: 20px;">Feeding schedule management coming soon...</p>
            </div>
        </div>
    </div>

    <!-- Add Livestock Modal -->
    <div id="addLivestockModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add New Livestock</h2>
                <button class="close-btn" onclick="closeModal('addLivestockModal')">×</button>
            </div>
            <form method="POST" action="dashboard.php" id="livestockForm">
                <input type="hidden" name="add_livestock" value="1">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="animal_type">Animal Type</label>
                        <select id="animal_type" name="animal_type" required>
                            <option value="">Select Animal</option>
                            <option value="Cow">Cow</option>
                            <option value="Pig">Pig</option>
                            <option value="Chicken">Chicken</option>
                            <option value="Goat">Goat</option>
                            <option value="Sheep">Sheep</option>
                            <option value="Horse">Horse</option>
                            <option value="Duck">Duck</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="gender">Gender</label>
                        <select id="gender" name="gender" required>
                            <option value="">Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="tag_number">Tag Number</label>
                    <input type="text" id="tag_number" name="tag_number" required placeholder="e.g., TAG-001">
                </div>

                <div class="form-group">
                    <label for="breed">Breed</label>
                    <input type="text" id="breed" name="breed" placeholder="e.g., Holstein">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="age_months">Age (months)</label>
                        <input type="number" id="age_months" name="age_months" min="0" placeholder="0">
                    </div>
                    <div class="form-group">
                        <label for="weight">Weight (kg)</label>
                        <input type="number" id="weight" name="weight" step="0.01" placeholder="0.00">
                    </div>
                </div>

                <div class="form-group">
                    <label for="location">Location</label>
                    <input type="text" id="location" name="location" placeholder="e.g., Barn A">
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="closeModal('addLivestockModal')">Cancel</button>
                    <button type="submit" class="btn-primary">Add Livestock</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ========== JAVASCRIPT SECTION ========== -->
    <script>
        function showSection(sectionId) {
            const sections = document.querySelectorAll('.section');
            sections.forEach(section => section.classList.remove('active'));
            
            const sidebar = document.querySelectorAll('.sidebar-menu a');
            sidebar.forEach(link => link.classList.remove('active'));
            
            document.getElementById(sectionId).classList.add('active');
            event.target.classList.add('active');
            
            console.log('[v0] Switched to section:', sectionId);
        }

        function openModal(modalId) {
            document.getElementById(modalId).classList.add('active');
            console.log('[v0] Modal opened:', modalId);
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
            console.log('[v0] Modal closed:', modalId);
        }

        window.addEventListener('click', function(e) {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                if (e.target == modal) {
                    modal.classList.remove('active');
                }
            });
        });

        document.getElementById('livestockForm').addEventListener('submit', function(e) {
            const animalType = document.getElementById('animal_type').value;
            const tagNumber = document.getElementById('tag_number').value.trim();
            const gender = document.getElementById('gender').value;

            if (!animalType || !tagNumber || !gender) {
                e.preventDefault();
                alert('Please fill in all required fields');
                return false;
            }

            if (tagNumber.length < 3) {
                e.preventDefault();
                alert('Tag number must be at least 3 characters');
                return false;
            }

            console.log('[v0] Livestock form submitted:', {animalType, tagNumber, gender});
        });

        document.addEventListener('DOMContentLoaded', function() {
            console.log('[v0] Dashboard page loaded successfully');
        });
    </script>
</body>
</html>
