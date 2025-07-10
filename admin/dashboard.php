<?php
session_start();
require '../db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - User Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #004d99;
            --secondary-color: #4a5568;
            --accent-color: #16a085;
            --border-radius: 8px;
            --vintage-shadow: 0 8px 30px rgba(0, 77, 153, 0.12);
            --gradient-primary: linear-gradient(135deg, #004d99, #0066cc);
            --gradient-secondary: linear-gradient(to right, #f8fafc, #fff);
            --gradient-accent: linear-gradient(135deg, #16a085, #2c9678);
        }

        body {
            background: var(--gradient-secondary);
            font-family: 'Roboto', Arial, sans-serif;
            color: var(--secondary-color);
        }

        .container {
            max-width: 1200px;
            background: #fff;
            border-radius: var(--border-radius);
            box-shadow: var(--vintage-shadow);
            padding: 2.5rem;
            margin-top: 2rem;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
        }

        .container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 8px;
            background: var(--gradient-primary);
        }

        h1 {
            color: var(--primary-color);
            margin-bottom: 2rem;
            font-weight: 700;
        }

        .btn {
            border-radius: var(--border-radius);
            padding: 0.8rem 1.5rem;
            font-weight: 600;
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: var(--gradient-primary);
            border: none;
        }

        .btn-success {
            background: var(--gradient-accent);
            border: none;
        }

        .table-responsive {
            margin-top: 2rem;
            border-radius: var(--border-radius);
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .table {
            margin-bottom: 0;
            background: white;
        }

        .table thead th {
            background: var(--gradient-primary);
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 0.5px;
            padding: 1rem;
            border-bottom: none;
            text-align: center;
            white-space: nowrap;
        }

        .table tbody tr:hover {
            background-color: rgba(0, 77, 153, 0.05);
        }

        .table td {
            padding: 1rem;
            vertical-align: middle;
            border-color: rgba(0, 77, 153, 0.1);
        }

        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(0, 77, 153, 0.02);
        }

        .btn-sm {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            transition: all 0.3s ease;
        }

        .btn-sm:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 77, 153, 0.2);
        }

        .nav-pills {
            margin-bottom: 2rem;
        }

        .nav-pills .nav-link {
            color: var(--primary-color);
            border-radius: var(--border-radius);
            padding: 0.8rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .nav-pills .nav-link.active {
            background: var(--gradient-primary);
        }

        .modal-content {
            border-radius: var(--border-radius);
            box-shadow: var(--vintage-shadow);
        }

        .modal-header {
            background: var(--gradient-primary);
            color: white;
            border-radius: var(--border-radius) var(--border-radius) 0 0;
        }

        .form-control {
            border-radius: var(--border-radius);
            padding: 0.8rem;
            border: 1px solid rgba(0, 77, 153, 0.2);
        }
    </style>
</head>
<body>
    <?php require '../includes/header.php'; ?>

    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="animate__animated animate__fadeInDown">User Management</h1>
            <div>
                <a href="../index.php" class="btn btn-outline-primary me-2">
                    <i class="fas fa-home"></i> Home
                </a>
                <a href="../logout.php" class="btn btn-outline-danger">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>

        <button class="btn btn-success mb-4" data-bs-toggle="modal" data-bs-target="#addUserModal">
            <i class="fas fa-user-plus"></i> Add New User
        </button>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
                    while ($user = $stmt->fetch()) {
                        echo '<tr>';
                        echo '<td>' . htmlspecialchars($user['username']) . '</td>';
                        echo '<td>' . htmlspecialchars($user['full_name']) . '</td>';
                        echo '<td>' . htmlspecialchars($user['email']) . '</td>';
                        echo '<td>' . htmlspecialchars($user['role']) . '</td>';
                        echo '<td>' . htmlspecialchars($user['created_at']) . '</td>';
                        echo '<td>';
                        echo '<button class="btn btn-sm btn-primary me-2" onclick="editUser(' . $user['id'] . ')">';
                        echo '<i class="fas fa-edit"></i> Edit</button>';
                        if ($user['role'] !== 'admin') {
                            echo '<button class="btn btn-sm btn-danger" onclick="deleteUser(' . $user['id'] . ')">';
                            echo '<i class="fas fa-trash-alt"></i> Delete</button>';
                        }
                        echo '</td>';
                        echo '</tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addUserForm" action="add_user.php" method="post">
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="full_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select name="role" class="form-control" required>
                                <option value="user">User</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" form="addUserForm" class="btn btn-primary">Add User</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editUser(userId) {
            // Implement edit user functionality
            window.location.href = `edit_user.php?id=${userId}`;
        }

        function deleteUser(userId) {
            if (confirm('Are you sure you want to delete this user?')) {
                window.location.href = `delete_user.php?id=${userId}`;
            }
        }
    </script>
</body>
</html>