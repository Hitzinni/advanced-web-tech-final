<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Order;

class ManagerController
{
    private $orderModel;
    private $token;
    private $db;
    
    public function __construct()
    {
        $this->orderModel = new Order();
        $this->token = $_ENV['MANAGER_TOKEN'] ?? 'default_token_change_me';
        require_once BASE_PATH . '/src/Helpers/Database.php';
        $this->db = \App\Helpers\Database::getInstance();
    }
    
    public function show(): void
    {
        header('Content-Type: application/json');
        
        // Bearer token auth
        $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (!preg_match('/^Bearer\s+(.+)$/', $auth, $matches) || !hash_equals($this->token, $matches[1])) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid order ID']);
            exit;
        }
        
        $order = $this->orderModel->getDetailedById($id);
        
        if ($order === null) {
            http_response_code(404);
            echo json_encode(['error' => 'Order not found']);
            exit;
        }
        
        // Assemble response
        $response = [
            'order_id'   => (int)$order['order_id'],
            'ordered_at' => $order['ordered_at'],
            'product'    => [
                'id'       => (int)$order['product_id'],
                'name'     => $order['product_name'],
                'category' => $order['category'],
                'price'    => number_format((float)$order['product_price'], 2),
            ],
            'customer'   => [
                'id'    => (int)$order['user_id'],
                'name'  => $order['user_name'],
                'email' => $order['email'],
                'phone' => $order['phone'],
            ],
        ];
        
        echo json_encode($response);
    }

    /**
     * Show user management interface
     */
    public function users(): void
    {
        // Check if user is a manager
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'manager') {
            http_response_code(403);
            echo 'Access denied. Only managers can access this page.';
            exit;
        }
        
        // Get all users
        $sql = "SELECT id, name, email, role FROM user ORDER BY name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $users = $stmt->fetchAll();
        
        require_once BASE_PATH . '/src/Helpers/View.php';
        \App\Helpers\View::output('manager/users', [
            'pageTitle' => 'User Management',
            'metaDescription' => 'Manage users and roles',
            'users' => $users
        ]);
    }
    
    /**
     * Update user role
     */
    public function updateUserRole(): void
    {
        // Check if user is a manager
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'manager') {
            http_response_code(403);
            echo 'Access denied. Only managers can access this page.';
            exit;
        }
        
        // Get user ID and new role from query string
        $userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $newRole = isset($_GET['role']) ? $_GET['role'] : '';
        
        if (!$userId || !in_array($newRole, ['customer', 'manager'])) {
            $_SESSION['flash_message'] = [
                'type' => 'danger',
                'text' => 'Invalid user ID or role.'
            ];
            header('Location: manager-users');
            exit;
        }
        
        // Prevent managers from removing their own manager role
        if ($userId === (int)$_SESSION['user_id'] && $newRole !== 'manager') {
            $_SESSION['flash_message'] = [
                'type' => 'danger',
                'text' => 'You cannot remove your own manager role.'
            ];
            header('Location: manager-users');
            exit;
        }
        
        try {
            // Update user role
            $sql = "UPDATE user SET role = ? WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$newRole, $userId]);
            
            if ($result) {
                $_SESSION['flash_message'] = [
                    'type' => 'success',
                    'text' => 'User role updated successfully.'
                ];
            } else {
                $_SESSION['flash_message'] = [
                    'type' => 'danger',
                    'text' => 'Failed to update user role.'
                ];
            }
        } catch (\PDOException $e) {
            error_log("Error updating user role: " . $e->getMessage());
            $_SESSION['flash_message'] = [
                'type' => 'danger',
                'text' => 'An error occurred while updating user role.'
            ];
        }
        
        header('Location: manager-users');
        exit;
    }
    
    /**
     * Delete a user
     */
    public function deleteUser(): void
    {
        // Check if user is a manager
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'manager') {
            http_response_code(403);
            echo 'Access denied. Only managers can delete users.';
            exit;
        }
        
        // Get user ID from query string
        $userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if (!$userId) {
            $_SESSION['flash_message'] = [
                'type' => 'danger',
                'text' => 'Invalid user ID.'
            ];
            header('Location: manager-users');
            exit;
        }
        
        // Prevent managers from deleting their own account
        if ($userId === (int)$_SESSION['user_id']) {
            $_SESSION['flash_message'] = [
                'type' => 'danger',
                'text' => 'You cannot delete your own account.'
            ];
            header('Location: manager-users');
            exit;
        }
        
        try {
            // Delete user
            $sql = "DELETE FROM user WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$userId]);
            
            if ($result) {
                $_SESSION['flash_message'] = [
                    'type' => 'success',
                    'text' => 'User account deleted successfully.'
                ];
            } else {
                $_SESSION['flash_message'] = [
                    'type' => 'danger',
                    'text' => 'Failed to delete user account.'
                ];
            }
        } catch (\PDOException $e) {
            error_log("Error deleting user: " . $e->getMessage());
            $_SESSION['flash_message'] = [
                'type' => 'danger',
                'text' => 'An error occurred while deleting the user account.'
            ];
        }
        
        header('Location: manager-users');
        exit;
    }

    /**
     * Reset a user's password
     */
    public function resetUserPassword(): void
    {
        // Check if user is a manager
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'manager') {
            http_response_code(403);
            echo 'Access denied. Only managers can reset user passwords.';
            exit;
        }
        
        // Get user ID from query string
        $userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if (!$userId) {
            $_SESSION['flash_message'] = [
                'type' => 'danger',
                'text' => 'Invalid user ID.'
            ];
            header('Location: manager-users');
            exit;
        }
        
        try {
            // Generate a random temporary password
            $tempPassword = $this->generateTempPassword();
            
            // Hash the password
            $passwordHash = password_hash($tempPassword, PASSWORD_BCRYPT, ['cost' => 12]);
            
            // Update user's password
            $sql = "UPDATE user SET password_hash = ? WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$passwordHash, $userId]);
            
            // Get user email for displaying in the message
            $userSql = "SELECT email FROM user WHERE id = ?";
            $userStmt = $this->db->prepare($userSql);
            $userStmt->execute([$userId]);
            $userEmail = $userStmt->fetchColumn();
            
            if ($result) {
                $_SESSION['flash_message'] = [
                    'type' => 'success',
                    'text' => "Password for {$userEmail} has been reset to: <strong>{$tempPassword}</strong><br>Please provide this temporary password to the user."
                ];
            } else {
                $_SESSION['flash_message'] = [
                    'type' => 'danger',
                    'text' => 'Failed to reset user password.'
                ];
            }
        } catch (\PDOException $e) {
            error_log("Error resetting user password: " . $e->getMessage());
            $_SESSION['flash_message'] = [
                'type' => 'danger',
                'text' => 'An error occurred while resetting the user password.'
            ];
        }
        
        header('Location: manager-users');
        exit;
    }
    
    /**
     * Generate a random temporary password
     */
    private function generateTempPassword($length = 12): string
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_';
        $password = '';
        
        // Ensure the password has at least one letter and one number
        $password .= 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'[random_int(0, 51)];
        $password .= '0123456789'[random_int(0, 9)];
        
        // Fill the rest randomly
        for ($i = 0; $i < $length - 2; $i++) {
            $password .= $chars[random_int(0, strlen($chars) - 1)];
        }
        
        // Shuffle the password to avoid predictable patterns
        return str_shuffle($password);
    }

    /**
     * Show all orders for management
     */
    public function orders(): void
    {
        // Check if user is a manager
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'manager') {
            http_response_code(403);
            echo 'Access denied. Only managers can access this page.';
            exit;
        }
        
        // Process filter parameters
        $statusFilter = isset($_GET['status']) ? $_GET['status'] : '';
        $dateRange = isset($_GET['date_range']) ? $_GET['date_range'] : '';
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        
        // Build WHERE clauses based on filters
        $whereClause = '';
        $params = [];
        
        if (!empty($statusFilter)) {
            $whereClause .= " AND o.status = ?";
            $params[] = $statusFilter;
        }
        
        if (!empty($dateRange)) {
            $today = date('Y-m-d');
            if ($dateRange === 'today') {
                $whereClause .= " AND DATE(o.ordered_at) = ?";
                $params[] = $today;
            } elseif ($dateRange === 'week') {
                $lastWeek = date('Y-m-d', strtotime('-7 days'));
                $whereClause .= " AND DATE(o.ordered_at) >= ?";
                $params[] = $lastWeek;
            } elseif ($dateRange === 'month') {
                $lastMonth = date('Y-m-d', strtotime('-30 days'));
                $whereClause .= " AND DATE(o.ordered_at) >= ?";
                $params[] = $lastMonth;
            }
        }
        
        if (!empty($search)) {
            // Check if search is numeric (likely an order ID)
            if (is_numeric($search)) {
                $whereClause .= " AND o.id = ?";
                $params[] = (int)$search;
            } else {
                // Otherwise search by email (partial match)
                $whereClause .= " AND u.email LIKE ?";
                $params[] = "%$search%";
            }
        }
        
        try {
            // Get all orders with user details
            $sql = "SELECT o.*, u.name as user_name, u.email, u.phone,
                   (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count
                   FROM orders o
                   JOIN user u ON o.user_id = u.id
                   WHERE 1=1 $whereClause
                   ORDER BY o.ordered_at DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $newOrders = $stmt->fetchAll();
            
            // Mark these as new format orders
            foreach ($newOrders as &$order) {
                $order['is_new_format'] = true;
            }
            
            // Also fetch legacy orders if they exist
            $legacyOrders = [];
            try {
                // Check if legacy order table exists
                $tableCheck = $this->db->query("SHOW TABLES LIKE 'order'");
                $legacyTableExists = $tableCheck->rowCount() > 0;
                
                if ($legacyTableExists) {
                    // Build WHERE clause for legacy orders
                    $legacyWhereClause = '';
                    $legacyParams = [];
                    
                    if (!empty($search) && is_numeric($search)) {
                        $legacyWhereClause .= " AND o.id = ?";
                        $legacyParams[] = (int)$search;
                    } elseif (!empty($search)) {
                        $legacyWhereClause .= " AND u.email LIKE ?";
                        $legacyParams[] = "%$search%";
                    }
                    
                    if (!empty($dateRange)) {
                        $today = date('Y-m-d');
                        if ($dateRange === 'today') {
                            $legacyWhereClause .= " AND DATE(o.ordered_at) = ?";
                            $legacyParams[] = $today;
                        } elseif ($dateRange === 'week') {
                            $lastWeek = date('Y-m-d', strtotime('-7 days'));
                            $legacyWhereClause .= " AND DATE(o.ordered_at) >= ?";
                            $legacyParams[] = $lastWeek;
                        } elseif ($dateRange === 'month') {
                            $lastMonth = date('Y-m-d', strtotime('-30 days'));
                            $legacyWhereClause .= " AND DATE(o.ordered_at) >= ?";
                            $legacyParams[] = $lastMonth;
                        }
                    }
                    
                    // Legacy orders don't have status, so we don't filter by status
                    // But skip them if filtering for specific status other than "received"
                    if (!empty($statusFilter) && $statusFilter !== 'received') {
                        // Don't include legacy orders when filtering by status other than "received"
                    } else {
                        $legacySql = "SELECT o.*, u.name as user_name, u.email, u.phone, 
                                     p.name as product_name, p.price, p.category, p.image_url
                                     FROM `order` o
                                     JOIN user u ON o.user_id = u.id
                                     JOIN product p ON o.product_id = p.id
                                     WHERE 1=1 $legacyWhereClause
                                     ORDER BY o.ordered_at DESC";
                        
                        $legacyStmt = $this->db->prepare($legacySql);
                        $legacyStmt->execute($legacyParams);
                        $legacyOrders = $legacyStmt->fetchAll();
                        
                        // Legacy orders have status "Completed" by default
                        foreach ($legacyOrders as &$order) {
                            $order['status'] = 'received';
                        }
                    }
                }
            } catch (\PDOException $e) {
                error_log("Error fetching legacy orders: " . $e->getMessage());
                // Continue without legacy orders
            }
            
            // Merge both types of orders
            $allOrders = array_merge($newOrders, $legacyOrders);
            
            // Sort all orders by date
            usort($allOrders, function($a, $b) {
                $dateA = new \DateTime($a['ordered_at']);
                $dateB = new \DateTime($b['ordered_at']);
                return $dateB <=> $dateA; // Descending order (newest first)
            });
            
            // Get order details for new format orders
            foreach ($allOrders as &$order) {
                if (isset($order['is_new_format']) && $order['is_new_format']) {
                    // Fetch order items
                    $itemsSql = "SELECT oi.*, p.name as product_name, p.category, p.image_url
                                FROM order_items oi
                                LEFT JOIN product p ON oi.product_id = p.id
                                WHERE oi.order_id = ?";
                    $itemsStmt = $this->db->prepare($itemsSql);
                    $itemsStmt->execute([$order['id']]);
                    $order['items'] = $itemsStmt->fetchAll();
                }
            }
            
            require_once BASE_PATH . '/src/Helpers/View.php';
            \App\Helpers\View::output('manager/orders', [
                'pageTitle' => 'Order Management',
                'metaDescription' => 'Manage all customer orders',
                'orders' => $allOrders
            ]);
        } catch (\PDOException $e) {
            error_log("Error fetching orders: " . $e->getMessage());
            $_SESSION['flash_message'] = [
                'type' => 'danger',
                'text' => 'An error occurred while fetching orders.'
            ];
            header('Location: home');
            exit;
        }
    }
} 