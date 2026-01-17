<?php
/**
 * Database Connection Configuration
 * Health & Sanitation Management System
 */

// Load secure configuration
require_once __DIR__ . '/../config/config.php';

// Database configuration from environment
define('DB_HOST', Config::get('DB_HOST', 'localhost:3306'));
define('DB_USERNAME', Config::get('DB_USERNAME', 'root'));
define('DB_PASSWORD', Config::get('DB_PASSWORD', ''));
define('DB_NAME', Config::get('DB_NAME', 'gsm_health_system'));

// Create connection class
class Database
{
    private $host = DB_HOST;
    private $username = DB_USERNAME;
    private $password = DB_PASSWORD;
    private $database = DB_NAME;
    private $connection;

    public function __construct()
    {
        $this->connect();
        $this->ensureSchema();
    }

    private function connect()
    {
        try {
            $this->connection = new PDO(
                "mysql:host={$this->host};dbname={$this->database};charset=utf8mb4",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    // Contact: create a new message
    public function createContactMessage($data)
    {
        try {
            $sql = "INSERT INTO contact_messages (first_name, last_name, email, phone, subject, message, status) 
                    VALUES (:first_name, :last_name, :email, :phone, :subject, :message, 'new')";
            $stmt = $this->connection->prepare($sql);
            return $stmt->execute([
                ':first_name' => $data['first_name'],
                ':last_name' => $data['last_name'],
                ':email' => $data['email'],
                ':phone' => $data['phone'],
                ':subject' => $data['subject'],
                ':message' => $data['message'],
            ]);
        } catch (PDOException $e) {
            error_log("Create contact message error: " . $e->getMessage());
            return false;
        }
    }

    // Contact: fetch messages (optionally filter by status or search)
    public function getContactMessages($status = null, $search = null)
    {
        try {
            $sql = "SELECT * FROM contact_messages";
            $conds = [];
            $params = [];
            if ($status) {
                $conds[] = "status = :status";
                $params[':status'] = $status;
            }
            if ($search) {
                $conds[] = "(first_name LIKE :q OR last_name LIKE :q OR email LIKE :q OR phone LIKE :q OR subject LIKE :q OR message LIKE :q)";
                $params[':q'] = "%" . $search . "%";
            }
            if ($conds) {
                $sql .= " WHERE " . implode(' AND ', $conds);
            }
            $sql .= " ORDER BY created_at DESC";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get contact messages error: " . $e->getMessage());
            return [];
        }
    }

    // Ensure required tables exist
    private function ensureSchema()
    {
        try {
            // Users table (minimal, if not already created elsewhere)
            $this->connection->exec("CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                first_name VARCHAR(50) NOT NULL,
                last_name VARCHAR(50) NOT NULL,
                email VARCHAR(100) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                role ENUM('admin','doctor','nurse','health_worker','citizen') DEFAULT 'citizen',
                status ENUM('active','inactive','pending') DEFAULT 'active',
                profile_picture VARCHAR(255) NULL,
                phone VARCHAR(20) NULL,
                address TEXT NULL,
                date_of_birth DATE NULL,
                gender ENUM('male','female','other') NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

            // Ensure 'inspector' role exists in users.role enum
            try {
                $col = $this->connection->query("SHOW COLUMNS FROM users LIKE 'role'")->fetch(PDO::FETCH_ASSOC);
                if ($col && isset($col['Type']) && stripos($col['Type'], "'inspector'") === false) {
                    $this->connection->exec("ALTER TABLE users MODIFY COLUMN role ENUM('admin','doctor','nurse','inspector','health_worker','citizen') DEFAULT 'citizen'");
                }
            } catch (PDOException $e) {
                // ignore if cannot alter
            }

            // Ensure 'status' enum supports 'blocked' and ensure last_login column exists
            try {
                $col = $this->connection->query("SHOW COLUMNS FROM users LIKE 'status'")->fetch(PDO::FETCH_ASSOC);
                if ($col && isset($col['Type']) && stripos($col['Type'], "'blocked'") === false) {
                    $this->connection->exec("ALTER TABLE users MODIFY COLUMN status ENUM('active','blocked','inactive','pending') DEFAULT 'active'");
                }
            } catch (PDOException $e) {
                // ignore if cannot alter
            }
            try {
                $col = $this->connection->query("SHOW COLUMNS FROM users LIKE 'last_login'");
                if ($col->rowCount() === 0) {
                    $this->connection->exec("ALTER TABLE users ADD COLUMN last_login DATETIME NULL AFTER updated_at");
                }
            } catch (PDOException $e) {
                // ignore if cannot alter
            }

            // Ensure verification_status column exists on users
            try {
                $check = $this->connection->query("SHOW COLUMNS FROM users LIKE 'verification_status'");
                if ($check->rowCount() === 0) {
                    $this->connection->exec("ALTER TABLE users ADD COLUMN verification_status ENUM('unverified','pending','verified','rejected') DEFAULT 'unverified' AFTER gender");
                }
            } catch (PDOException $e) {
                // ignore if column already exists or insufficient privileges
            }

            // Appointments table
            $this->connection->exec("CREATE TABLE IF NOT EXISTS appointments (
                id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT NOT NULL,
                first_name VARCHAR(50) NOT NULL,
                middle_name VARCHAR(50),
                last_name VARCHAR(50) NOT NULL,
                email VARCHAR(100) NOT NULL,
                phone VARCHAR(20) NOT NULL,
                birth_date DATE NOT NULL,
                gender ENUM('male', 'female', 'other', 'prefer-not-to-say') NOT NULL,
                civil_status ENUM('single', 'married', 'divorced', 'widowed') NOT NULL,
                address TEXT NOT NULL,
                appointment_type VARCHAR(100) NOT NULL,
                preferred_date DATE NOT NULL,
                health_concerns TEXT NOT NULL,
                medical_history TEXT NOT NULL,
                current_medications TEXT,
                allergies TEXT,
                emergency_contact_name VARCHAR(100) NOT NULL,
                emergency_contact_phone VARCHAR(20) NOT NULL,
                status ENUM('pending', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

            // Ensure soft-delete column on appointments
            try {
                $col = $this->connection->query("SHOW COLUMNS FROM appointments LIKE 'deleted_at'");
                if ($col->rowCount() === 0) {
                    $this->connection->exec("ALTER TABLE appointments ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL AFTER updated_at");
                }
                // Ensure assigned_to and checked_in_at columns exist
                $col = $this->connection->query("SHOW COLUMNS FROM appointments LIKE 'assigned_to'");
                if ($col->rowCount() === 0) {
                    $this->connection->exec("ALTER TABLE appointments ADD COLUMN assigned_to INT NULL AFTER status");
                }
                $col = $this->connection->query("SHOW COLUMNS FROM appointments LIKE 'checked_in_at'");
                if ($col->rowCount() === 0) {
                    $this->connection->exec("ALTER TABLE appointments ADD COLUMN checked_in_at TIMESTAMP NULL AFTER assigned_to");
                }
                // Ensure index on deleted_at for faster filtering
                $idx = $this->connection->query("SHOW INDEX FROM appointments WHERE Key_name = 'idx_appointments_deleted_at'");
                if ($idx->rowCount() === 0) {
                    $this->connection->exec("ALTER TABLE appointments ADD INDEX idx_appointments_deleted_at (deleted_at)");
                }
            } catch (PDOException $e) {
                // ignore if cannot alter
            }

            // Service requests table
            $this->connection->exec("CREATE TABLE IF NOT EXISTS service_requests (
                id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT NOT NULL,
                service_type VARCHAR(100) NOT NULL,
                full_name VARCHAR(150) NOT NULL,
                email VARCHAR(100) NOT NULL,
                phone VARCHAR(20) NOT NULL,
                address TEXT NOT NULL,
                service_details TEXT NOT NULL,
                preferred_date DATE,
                urgency ENUM('low','medium','high','emergency') DEFAULT 'medium',
                status ENUM('pending','in_progress','completed','cancelled') DEFAULT 'pending',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

            // Ensure soft-delete column on service_requests
            try {
                $col = $this->connection->query("SHOW COLUMNS FROM service_requests LIKE 'deleted_at'");
                if ($col->rowCount() === 0) {
                    $this->connection->exec("ALTER TABLE service_requests ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL AFTER updated_at");
                }
                // Ensure assigned_to and checked_in_at columns exist
                $col = $this->connection->query("SHOW COLUMNS FROM service_requests LIKE 'assigned_to'");
                if ($col->rowCount() === 0) {
                    $this->connection->exec("ALTER TABLE service_requests ADD COLUMN assigned_to INT NULL AFTER status");
                }
                $col = $this->connection->query("SHOW COLUMNS FROM service_requests LIKE 'checked_in_at'");
                if ($col->rowCount() === 0) {
                    $this->connection->exec("ALTER TABLE service_requests ADD COLUMN checked_in_at TIMESTAMP NULL AFTER assigned_to");
                }
                // Ensure status_remarks and payment_status columns exist on service_requests
                $col = $this->connection->query("SHOW COLUMNS FROM service_requests LIKE 'status_remarks'");
                if ($col->rowCount() === 0) {
                    $this->connection->exec("ALTER TABLE service_requests ADD COLUMN status_remarks TEXT NULL AFTER status");
                }
                $col = $this->connection->query("SHOW COLUMNS FROM service_requests LIKE 'payment_status'");
                if ($col->rowCount() === 0) {
                    $this->connection->exec("ALTER TABLE service_requests ADD COLUMN payment_status ENUM('unpaid','paid','for_verification') DEFAULT 'unpaid' AFTER status_remarks");
                }
                // Ensure assigned_inspector_id exists
                $col = $this->connection->query("SHOW COLUMNS FROM service_requests LIKE 'assigned_inspector_id'");
                if ($col->rowCount() === 0) {
                    $this->connection->exec("ALTER TABLE service_requests ADD COLUMN assigned_inspector_id INT NULL AFTER payment_status");
                    $this->connection->exec("ALTER TABLE service_requests ADD COLUMN assigned_inspector_at TIMESTAMP NULL AFTER assigned_inspector_id");
                }
                // Ensure index on deleted_at for faster filtering
                $idx = $this->connection->query("SHOW INDEX FROM service_requests WHERE Key_name = 'idx_service_requests_deleted_at'");
                if ($idx->rowCount() === 0) {
                    $this->connection->exec("ALTER TABLE service_requests ADD INDEX idx_service_requests_deleted_at (deleted_at)");
                }
            } catch (PDOException $e) {
                // ignore if cannot alter
            }

            // Sanitary Permit Applications (structured fields for business-permit)
            $this->connection->exec("CREATE TABLE IF NOT EXISTS sanitary_permit_applications (
                id INT PRIMARY KEY AUTO_INCREMENT,
                service_request_id INT NULL,
                user_id INT NOT NULL,
                app_type VARCHAR(50) NULL,
                industry VARCHAR(50) NULL,
                sub_industry VARCHAR(100) NULL,
                business_line VARCHAR(150) NULL,
                establishment_name VARCHAR(255) NOT NULL,
                establishment_address TEXT,
                owner_name VARCHAR(150) NULL,
                mayor_permit VARCHAR(100) NULL,
                total_employees INT NULL,
                employees_with_health_cert INT NULL,
                employees_without_health_cert INT NULL,
                ppe_personnel INT NULL,
                status ENUM('pending','in_progress','completed','cancelled') DEFAULT 'pending',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (service_request_id) REFERENCES service_requests(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

            // Sanitary Permit Workflow Steps
            $this->connection->exec("CREATE TABLE IF NOT EXISTS sanitary_permit_steps (
                id INT PRIMARY KEY AUTO_INCREMENT,
                application_id INT NULL,
                user_id INT NOT NULL,
                step ENUM('form_filing','submission','payment','inspection','issuance') NOT NULL,
                status ENUM('pending','completed','rejected','rescheduled') DEFAULT 'pending',
                details TEXT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (application_id) REFERENCES sanitary_permit_applications(id) ON DELETE SET NULL,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

            // Sanitary Permit Documents Table (for better file tracking)
            $this->connection->exec("CREATE TABLE IF NOT EXISTS sanitary_permit_documents (
                id INT AUTO_INCREMENT PRIMARY KEY,
                application_id INT NOT NULL,
                document_type VARCHAR(100) NOT NULL,
                file_path TEXT NOT NULL,
                status ENUM('pending', 'verified', 'rejected') DEFAULT 'pending',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (application_id) REFERENCES sanitary_permit_applications(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

            // User verifications table
            $this->connection->exec("CREATE TABLE IF NOT EXISTS user_verifications (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                document_type VARCHAR(50) NOT NULL,
                file_path VARCHAR(255) NOT NULL,
                status ENUM('pending','verified','rejected') DEFAULT 'pending',
                notes TEXT NULL,
                reviewed_by INT NULL,
                reviewed_at TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

            // Contact messages table
            $this->connection->exec("CREATE TABLE IF NOT EXISTS contact_messages (
                id INT AUTO_INCREMENT PRIMARY KEY,
                first_name VARCHAR(50) NOT NULL,
                last_name VARCHAR(50) NOT NULL,
                email VARCHAR(100) NOT NULL,
                phone VARCHAR(20) NOT NULL,
                subject VARCHAR(100) NOT NULL,
                message TEXT NOT NULL,
                status ENUM('new','read','archived') DEFAULT 'new',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

            $this->connection->exec("CREATE TABLE IF NOT EXISTS login_otps (
                id INT AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(150) NOT NULL,
                otp_hash VARCHAR(255) NOT NULL,
                attempts TINYINT UNSIGNED DEFAULT 0,
                expires_at DATETIME NOT NULL,
                verified_at DATETIME NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX login_otps_email_idx (email),
                INDEX login_otps_expires_idx (expires_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

            // Audit logs table
            $this->connection->exec("CREATE TABLE IF NOT EXISTS audit_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NULL,
                role VARCHAR(20) NULL,
                action VARCHAR(50) NOT NULL,
                details TEXT NULL,
                ip VARCHAR(45) NULL,
                user_agent TEXT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX (user_id), INDEX (role), INDEX (action), INDEX (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

            // Family Tracking: Dependents Table
            $this->connection->exec("CREATE TABLE IF NOT EXISTS dependents (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                first_name VARCHAR(50) NOT NULL,
                last_name VARCHAR(50) NOT NULL,
                date_of_birth DATE NOT NULL,
                place_of_birth VARCHAR(255) NULL,
                gender ENUM('male','female','other') NULL,
                relationship VARCHAR(50) NULL,
                fic_status ENUM('active', 'inactive') DEFAULT 'active',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

            // Family Tracking: Immunizations Table
            $this->connection->exec("CREATE TABLE IF NOT EXISTS immunizations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                dependent_id INT NOT NULL,
                vaccine_name VARCHAR(100) NOT NULL,
                dose_number INT NOT NULL,
                batch_number VARCHAR(50) NULL,
                administered_by INT NULL,
                date_administered DATE NULL,
                date_due DATE NULL,
                status ENUM('scheduled', 'administered', 'overdue') DEFAULT 'scheduled',
                remarks TEXT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (dependent_id) REFERENCES dependents(id) ON DELETE CASCADE,
                FOREIGN KEY (administered_by) REFERENCES users(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

            // Family Tracking: Nutrition Table
            $this->connection->exec("CREATE TABLE IF NOT EXISTS nutrition_records (
                id INT AUTO_INCREMENT PRIMARY KEY,
                dependent_id INT NOT NULL,
                weight_kg DECIMAL(5,2) NULL,
                height_cm DECIMAL(5,2) NULL,
                status VARCHAR(50) DEFAULT 'optimal',
                visit_date DATE NOT NULL,
                next_visit_date DATE NULL,
                remarks TEXT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (dependent_id) REFERENCES dependents(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
        } catch (PDOException $e) {
            error_log('Schema ensure error: ' . $e->getMessage());
        }
    }

    public function getConnection()
    {
        return $this->connection;
    }

    // User authentication methods
    public function registerUser($first_name, $last_name, $email, $password, $role = 'citizen')
    {
        try {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO users (first_name, last_name, email, password, role, created_at) 
                    VALUES (:first_name, :last_name, :email, :password, :role, NOW())";

            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(':first_name', $first_name);
            $stmt->bindParam(':last_name', $last_name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':role', $role);

            if ($stmt->execute()) {
                return $this->connection->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            error_log("Registration error: " . $e->getMessage());
            return false;
        }
    }

    public function loginUser($email, $password)
    {
        try {
            $sql = "SELECT id, first_name, last_name, email, password, role, status 
                    FROM users WHERE email = :email";

            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Check if user is active
                if ($user['status'] !== 'active') {
                    return ['error' => 'Account is not active. Please contact administrator.'];
                }
                // Update last_login timestamp
                try {
                    $up = $this->connection->prepare("UPDATE users SET last_login = NOW() WHERE id = :id");
                    $up->execute([':id' => (int) $user['id']]);
                } catch (PDOException $e) {
                    // ignore last_login update errors
                }

                // Remove password from returned data
                unset($user['password']);
                return $user;
            }

            return false;
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            return false;
        }
    }

    public function getUserById($user_id)
    {
        try {
            $sql = "SELECT id, first_name, last_name, email, role, status, verification_status, 
                           profile_picture, phone, address, date_of_birth, gender, created_at 
                    FROM users WHERE id = :user_id";

            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();

            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Get user error: " . $e->getMessage());
            return false;
        }
    }

    public function getUserByEmail($email)
    {
        try {
            $sql = "SELECT id, first_name, last_name, email, role, status, verification_status, 
                           profile_picture, phone, address, date_of_birth, gender, created_at 
                    FROM users WHERE email = :email";

            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Get user by email error: " . $e->getMessage());
            return false;
        }
    }

    public function updateUser($user_id, $data)
    {
        try {
            $fields = [];
            $params = [':user_id' => $user_id];

            foreach ($data as $key => $value) {
                if ($key !== 'id' && $key !== 'password') {
                    $fields[] = "{$key} = :{$key}";
                    $params[":{$key}"] = $value;
                }
            }

            if (empty($fields)) {
                return false;
            }

            $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = :user_id";

            $stmt = $this->connection->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Update user error: " . $e->getMessage());
            return false;
        }
    }

    // Update password with current password verification
    public function updateUserPassword($user_id, $current_password, $new_password)
    {
        try {
            $stmt = $this->connection->prepare("SELECT password FROM users WHERE id = :id");
            $stmt->execute([':id' => $user_id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row)
                return ['ok' => false, 'error' => 'User not found'];
            if (!password_verify($current_password, $row['password'])) {
                return ['ok' => false, 'error' => 'Current password is incorrect'];
            }
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $up = $this->connection->prepare("UPDATE users SET password = :p, updated_at = NOW() WHERE id = :id");
            $up->execute([':p' => $hashed, ':id' => $user_id]);
            return ['ok' => true];
        } catch (PDOException $e) {
            error_log("Update password error: " . $e->getMessage());
            return ['ok' => false, 'error' => 'Server error'];
        }
    }

    // Log audit events
    public function logAudit($user_id, $role, $action, $details = null)
    {
        try {
            $ip = $_SERVER['REMOTE_ADDR'] ?? null;
            $ua = $_SERVER['HTTP_USER_AGENT'] ?? null;
            $stmt = $this->connection->prepare("INSERT INTO audit_logs (user_id, role, action, details, ip, user_agent) VALUES (:uid, :role, :action, :details, :ip, :ua)");
            $stmt->execute([
                ':uid' => $user_id,
                ':role' => $role,
                ':action' => $action,
                ':details' => $details,
                ':ip' => $ip,
                ':ua' => $ua,
            ]);
            return true;
        } catch (PDOException $e) {
            error_log("Log audit error: " . $e->getMessage());
            return false;
        }
    }

    // Fetch audit logs with optional filters
    public function getAuditLogs($filters = [])
    {
        try {
            $sql = "SELECT al.*, u.first_name, u.last_name, u.email FROM audit_logs al LEFT JOIN users u ON al.user_id = u.id";
            $where = [];
            $params = [];
            if (!empty($filters['role'])) {
                $where[] = 'al.role = :role';
                $params[':role'] = $filters['role'];
            }
            if (!empty($filters['user_id'])) {
                $where[] = 'al.user_id = :uid';
                $params[':uid'] = (int) $filters['user_id'];
            }
            if (!empty($filters['action'])) {
                $where[] = 'al.action = :action';
                $params[':action'] = $filters['action'];
            }
            if (!empty($filters['date_from'])) {
                $where[] = 'al.created_at >= :df';
                $params[':df'] = $filters['date_from'] . ' 00:00:00';
            }
            if (!empty($filters['date_to'])) {
                $where[] = 'al.created_at <= :dt';
                $params[':dt'] = $filters['date_to'] . ' 23:59:59';
            }
            if (!empty($filters['q'])) {
                $where[] = '(al.details LIKE :q OR u.first_name LIKE :q OR u.last_name LIKE :q OR u.email LIKE :q)';
                $params[':q'] = '%' . $filters['q'] . '%';
            }
            if ($where) {
                $sql .= ' WHERE ' . implode(' AND ', $where);
            }
            $sql .= ' ORDER BY al.created_at DESC';
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get audit logs error: " . $e->getMessage());
            return [];
        }
    }

    // Fetch audit logs with pagination (limit/offset)
    public function getAuditLogsPaginated($filters = [], $limit = 10, $offset = 0)
    {
        try {
            $sql = "SELECT al.*, u.first_name, u.last_name, u.email FROM audit_logs al LEFT JOIN users u ON al.user_id = u.id";
            $where = [];
            $params = [];
            if (!empty($filters['role'])) {
                $where[] = 'al.role = :role';
                $params[':role'] = $filters['role'];
            }
            if (!empty($filters['user_id'])) {
                $where[] = 'al.user_id = :uid';
                $params[':uid'] = (int) $filters['user_id'];
            }
            if (!empty($filters['action'])) {
                $where[] = 'al.action = :action';
                $params[':action'] = $filters['action'];
            }
            if (!empty($filters['date_from'])) {
                $where[] = 'al.created_at >= :df';
                $params[':df'] = $filters['date_from'] . ' 00:00:00';
            }
            if (!empty($filters['date_to'])) {
                $where[] = 'al.created_at <= :dt';
                $params[':dt'] = $filters['date_to'] . ' 23:59:59';
            }
            if (!empty($filters['q'])) {
                $where[] = '(al.details LIKE :q OR u.first_name LIKE :q OR u.last_name LIKE :q OR u.email LIKE :q)';
                $params[':q'] = '%' . $filters['q'] . '%';
            }
            if ($where) {
                $sql .= ' WHERE ' . implode(' AND ', $where);
            }
            $sql .= ' ORDER BY al.created_at DESC LIMIT :lim OFFSET :off';
            $stmt = $this->connection->prepare($sql);
            foreach ($params as $k => $v) {
                $stmt->bindValue($k, $v);
            }
            $stmt->bindValue(':lim', (int) $limit, PDO::PARAM_INT);
            $stmt->bindValue(':off', (int) $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get audit logs (paginated) error: " . $e->getMessage());
            return [];
        }
    }

    // Count audit logs for given filters
    public function countAuditLogs($filters = [])
    {
        try {
            $sql = "SELECT COUNT(*) AS cnt FROM audit_logs al LEFT JOIN users u ON al.user_id = u.id";
            $where = [];
            $params = [];
            if (!empty($filters['role'])) {
                $where[] = 'al.role = :role';
                $params[':role'] = $filters['role'];
            }
            if (!empty($filters['user_id'])) {
                $where[] = 'al.user_id = :uid';
                $params[':uid'] = (int) $filters['user_id'];
            }
            if (!empty($filters['action'])) {
                $where[] = 'al.action = :action';
                $params[':action'] = $filters['action'];
            }
            if (!empty($filters['date_from'])) {
                $where[] = 'al.created_at >= :df';
                $params[':df'] = $filters['date_from'] . ' 00:00:00';
            }
            if (!empty($filters['date_to'])) {
                $where[] = 'al.created_at <= :dt';
                $params[':dt'] = $filters['date_to'] . ' 23:59:59';
            }
            if (!empty($filters['q'])) {
                $where[] = '(al.details LIKE :q OR u.first_name LIKE :q OR u.last_name LIKE :q OR u.email LIKE :q)';
                $params[':q'] = '%' . $filters['q'] . '%';
            }
            if ($where) {
                $sql .= ' WHERE ' . implode(' AND ', $where);
            }
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? (int) $row['cnt'] : 0;
        } catch (PDOException $e) {
            error_log("Count audit logs error: " . $e->getMessage());
            return 0;
        }
    }

    public function emailExists($email, $exclude_id = null)
    {
        try {
            $sql = "SELECT id FROM users WHERE email = :email";
            $params = [':email' => $email];

            if ($exclude_id) {
                $sql .= " AND id != :exclude_id";
                $params[':exclude_id'] = $exclude_id;
            }

            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);

            return $stmt->fetch() !== false;
        } catch (PDOException $e) {
            error_log("Email check error: " . $e->getMessage());
            return false;
        }
    }

    // Create appointment
    public function createAppointment($appointmentData)
    {
        try {
            // Gate: only verified users can create
            if (!$this->isUserVerified($appointmentData['user_id'])) {
                return false;
            }
            $sql = "INSERT INTO appointments (
                user_id, first_name, middle_name, last_name, email, phone, 
                birth_date, gender, civil_status, address, appointment_type, 
                preferred_date, health_concerns, medical_history, current_medications, 
                allergies, emergency_contact_name, emergency_contact_phone
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $this->connection->prepare($sql);
            return $stmt->execute([
                $appointmentData['user_id'],
                $appointmentData['first_name'],
                $appointmentData['middle_name'],
                $appointmentData['last_name'],
                $appointmentData['email'],
                $appointmentData['phone'],
                $appointmentData['birth_date'],
                $appointmentData['gender'],
                $appointmentData['civil_status'],
                $appointmentData['address'],
                $appointmentData['appointment_type'],
                $appointmentData['preferred_date'],
                $appointmentData['health_concerns'],
                $appointmentData['medical_history'],
                $appointmentData['current_medications'],
                $appointmentData['allergies'],
                $appointmentData['emergency_contact_name'],
                $appointmentData['emergency_contact_phone']
            ]);
        } catch (PDOException $e) {
            error_log("Appointment creation error: " . $e->getMessage());
            return false;
        }
    }

    // Create service request
    public function createServiceRequest($serviceData)
    {
        try {
            // Gate: only verified users can create
            if (!$this->isUserVerified((int) $serviceData['user_id'])) {
                return false;
            }
            $sql = "INSERT INTO service_requests (
                user_id, service_type, full_name, email, phone, 
                address, service_details, preferred_date, urgency, payment_status
            ) VALUES (:user_id, :service_type, :full_name, :email, :phone, :address, :service_details, :preferred_date, :urgency, :payment_status)";

            $stmt = $this->connection->prepare($sql);
            $stmt->bindValue(':user_id', (int) $serviceData['user_id'], PDO::PARAM_INT);
            $stmt->bindValue(':service_type', (string) $serviceData['service_type'], PDO::PARAM_STR);
            $stmt->bindValue(':full_name', (string) $serviceData['full_name'], PDO::PARAM_STR);
            $stmt->bindValue(':email', (string) $serviceData['email'], PDO::PARAM_STR);
            $stmt->bindValue(':phone', (string) $serviceData['phone'], PDO::PARAM_STR);
            $stmt->bindValue(':address', (string) $serviceData['address'], PDO::PARAM_STR);
            $stmt->bindValue(':service_details', (string) $serviceData['service_details'], PDO::PARAM_STR);
            if (!empty($serviceData['preferred_date'])) {
                $stmt->bindValue(':preferred_date', $serviceData['preferred_date'], PDO::PARAM_STR);
            } else {
                $stmt->bindValue(':preferred_date', null, PDO::PARAM_NULL);
            }
            $stmt->bindValue(':urgency', (string) $serviceData['urgency'], PDO::PARAM_STR);
            $stmt->bindValue(':payment_status', (string) ($serviceData['payment_status'] ?? 'unpaid'), PDO::PARAM_STR);

            $ok = $stmt->execute();
            if (!$ok) {
                $info = $stmt->errorInfo();
                error_log('Service request insert failed: ' . implode(' | ', $info));
                return false;
            }
            return (int) $this->connection->lastInsertId();
        } catch (PDOException $e) {
            error_log("Service request creation error: " . $e->getMessage());
            return false;
        }
    }

    // Get service requests by User ID
    public function getServiceRequestsByUserId($user_id)
    {
        try {
            $stmt = $this->connection->prepare("SELECT * FROM service_requests WHERE user_id = :uid AND deleted_at IS NULL ORDER BY created_at DESC");
            $stmt->execute([':uid' => $user_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get service requests by user error: " . $e->getMessage());
            return [];
        }
    }

    // Create a structured Sanitary Permit Application record linked to a service_request
    public function createSanitaryPermitApplication($data)
    {
        try {
            $sql = "INSERT INTO sanitary_permit_applications (
                service_request_id, user_id, app_type, industry, sub_industry, business_line,
                establishment_name, establishment_address, owner_name, mayor_permit,
                total_employees, employees_with_health_cert, employees_without_health_cert, ppe_personnel,
                status
            ) VALUES (
                :service_request_id, :user_id, :app_type, :industry, :sub_industry, :business_line,
                :establishment_name, :establishment_address, :owner_name, :mayor_permit,
                :total_employees, :employees_with_health_cert, :employees_without_health_cert, :ppe_personnel,
                :status
            )";

            $stmt = $this->connection->prepare($sql);
            $stmt->bindValue(':service_request_id', !empty($data['service_request_id']) ? (int) $data['service_request_id'] : null, !empty($data['service_request_id']) ? PDO::PARAM_INT : PDO::PARAM_NULL);
            $stmt->bindValue(':user_id', (int) $data['user_id'], PDO::PARAM_INT);
            $stmt->bindValue(':app_type', $data['app_type'] ?? null, $data['app_type'] !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmt->bindValue(':industry', $data['industry'] ?? null, $data['industry'] !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmt->bindValue(':sub_industry', $data['sub_industry'] ?? null, $data['sub_industry'] !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmt->bindValue(':business_line', $data['business_line'] ?? null, $data['business_line'] !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmt->bindValue(':establishment_name', (string) $data['establishment_name'], PDO::PARAM_STR);
            $stmt->bindValue(':establishment_address', $data['establishment_address'] ?? null, $data['establishment_address'] !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmt->bindValue(':owner_name', $data['owner_name'] ?? null, $data['owner_name'] !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmt->bindValue(':mayor_permit', $data['mayor_permit'] ?? null, $data['mayor_permit'] !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmt->bindValue(':total_employees', isset($data['total_employees']) && $data['total_employees'] !== '' ? (int) $data['total_employees'] : null, isset($data['total_employees']) && $data['total_employees'] !== '' ? PDO::PARAM_INT : PDO::PARAM_NULL);
            $stmt->bindValue(':employees_with_health_cert', isset($data['employees_with_health_cert']) && $data['employees_with_health_cert'] !== '' ? (int) $data['employees_with_health_cert'] : null, isset($data['employees_with_health_cert']) && $data['employees_with_health_cert'] !== '' ? PDO::PARAM_INT : PDO::PARAM_NULL);
            $stmt->bindValue(':employees_without_health_cert', isset($data['employees_without_health_cert']) && $data['employees_without_health_cert'] !== '' ? (int) $data['employees_without_health_cert'] : null, isset($data['employees_without_health_cert']) && $data['employees_without_health_cert'] !== '' ? PDO::PARAM_INT : PDO::PARAM_NULL);
            $stmt->bindValue(':ppe_personnel', isset($data['ppe_personnel']) && $data['ppe_personnel'] !== '' ? (int) $data['ppe_personnel'] : null, isset($data['ppe_personnel']) && $data['ppe_personnel'] !== '' ? PDO::PARAM_INT : PDO::PARAM_NULL);
            $stmt->bindValue(':status', $data['status'] ?? 'pending', PDO::PARAM_STR);

            $ok = $stmt->execute();
            if (!$ok) {
                return false;
            }
            return (int) $this->connection->lastInsertId();
        } catch (PDOException $e) {
            error_log('Sanitary permit application creation error: ' . $e->getMessage());
            return false;
        }
    }

    // Create a Sanitary Permit Step record (workflow)
    // List sanitary permit applications for a user
    public function getUserSanitaryPermitApplications($user_id)
    {
        try {
            $sql = "SELECT a.*, 
                           (SELECT step FROM sanitary_permit_steps WHERE application_id = a.id ORDER BY created_at DESC LIMIT 1) as current_step,
                           (SELECT status FROM sanitary_permit_steps WHERE application_id = a.id ORDER BY created_at DESC LIMIT 1) as step_status
                    FROM sanitary_permit_applications a 
                    WHERE a.user_id = ? AND a.deleted_at IS NULL
                    ORDER BY a.created_at DESC";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([$user_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get sanitary permit applications error: " . $e->getMessage());
            return [];
        }
    }

    public function getSanitaryPermitApplicationDetails($app_id)
    {
        try {
            $sql = "SELECT a.*, 
                           s.step, s.status as step_status, s.details as step_details
                    FROM sanitary_permit_applications a
                    LEFT JOIN sanitary_permit_steps s ON a.id = s.application_id
                    WHERE a.id = ? 
                    ORDER BY s.id DESC LIMIT 1";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([$app_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get sanitary permit application details error: " . $e->getMessage());
            return false;
        }
    }

    // Soft-delete a sanitary permit application
    public function deleteSanitaryPermitApplication($app_id, $user_id)
    {
        try {
            $sql = "UPDATE sanitary_permit_applications SET deleted_at = CURRENT_TIMESTAMP WHERE id = ? AND user_id = ?";
            $stmt = $this->connection->prepare($sql);
            return $stmt->execute([$app_id, $user_id]);
        } catch (PDOException $e) {
            error_log("Delete sanitary permit application error: " . $e->getMessage());
            return false;
        }
    }

    public function createSanitaryPermitStep($data)
    {
        try {
            $sql = "INSERT INTO sanitary_permit_steps (
                application_id, user_id, step, status, details
            ) VALUES (
                :application_id, :user_id, :step, :status, :details
            )";

            $stmt = $this->connection->prepare($sql);
            if (!empty($data['application_id'])) {
                $stmt->bindValue(':application_id', (int) $data['application_id'], PDO::PARAM_INT);
            } else {
                $stmt->bindValue(':application_id', null, PDO::PARAM_NULL);
            }
            $stmt->bindValue(':user_id', (int) $data['user_id'], PDO::PARAM_INT);
            $stmt->bindValue(':step', (string) $data['step'], PDO::PARAM_STR);
            $stmt->bindValue(':status', (string) ($data['status'] ?? 'pending'), PDO::PARAM_STR);
            $stmt->bindValue(':details', isset($data['details']) ? (string) $data['details'] : null, isset($data['details']) ? PDO::PARAM_STR : PDO::PARAM_NULL);

            $ok = $stmt->execute();
            if (!$ok) {
                return false;
            }
            return (int) $this->connection->lastInsertId();
        } catch (PDOException $e) {
            error_log('Sanitary permit step creation error: ' . $e->getMessage());
            return false;
        }
    }

    // Get dependents for a user
    public function getDependents($user_id)
    {
        try {
            $stmt = $this->connection->prepare("SELECT * FROM dependents WHERE user_id = :user_id ORDER BY created_at DESC");
            $stmt->execute([':user_id' => $user_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error fetching dependents: ' . $e->getMessage());
            return [];
        }
    }

    // Add a dependent
    public function addDependent($user_id, $data)
    {
        try {
            $sql = "INSERT INTO dependents (user_id, first_name, last_name, date_of_birth, place_of_birth, gender, relationship) 
                    VALUES (:user_id, :first_name, :last_name, :date_of_birth, :place_of_birth, :gender, :relationship)";
            $stmt = $this->connection->prepare($sql);
            return $stmt->execute([
                ':user_id' => $user_id,
                ':first_name' => $data['first_name'],
                ':last_name' => $data['last_name'],
                ':date_of_birth' => $data['date_of_birth'],
                ':place_of_birth' => $data['place_of_birth'] ?? null,
                ':gender' => $data['gender'] ?? null,
                ':relationship' => $data['relationship'] ?? null
            ]);
        } catch (PDOException $e) {
            error_log('Error adding dependent: ' . $e->getMessage());
            return false;
        }
    }

    // Get immunization records for a dependent
    public function getImmunizations($dependent_id)
    {
        try {
            $sql = "SELECT i.*, CONCAT(u.first_name, ' ', u.last_name) as health_worker_name 
                    FROM immunizations i 
                    LEFT JOIN users u ON i.administered_by = u.id 
                    WHERE i.dependent_id = :dependent_id 
                    ORDER BY i.date_administered DESC, i.date_due ASC";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([':dependent_id' => $dependent_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error fetching immunizations: ' . $e->getMessage());
            return [];
        }
    }

    // Add immunization record
    public function addImmunization($dependent_id, $data)
    {
        try {
            $sql = "INSERT INTO immunizations (dependent_id, vaccine_name, dose_number, batch_number, administered_by, date_administered, date_due, status, remarks) 
                    VALUES (:dependent_id, :vaccine_name, :dose_number, :batch_number, :administered_by, :date_administered, :date_due, :status, :remarks)";
            $stmt = $this->connection->prepare($sql);
            return $stmt->execute([
                ':dependent_id' => $dependent_id,
                ':vaccine_name' => $data['vaccine_name'],
                ':dose_number' => $data['dose_number'],
                ':batch_number' => $data['batch_number'] ?? null,
                ':administered_by' => $data['administered_by'] ?? null,
                ':date_administered' => $data['date_administered'] ?? null,
                ':date_due' => $data['date_due'] ?? null,
                ':status' => $data['status'] ?? 'scheduled',
                ':remarks' => $data['remarks'] ?? null
            ]);
        } catch (PDOException $e) {
            error_log('Error adding immunization: ' . $e->getMessage());
            return false;
        }
    }

    // Update immunization record
    public function updateImmunization($id, $data)
    {
        try {
            $fields = [
                'vaccine_name' => ':vaccine_name',
                'dose_number' => ':dose_number',
                'batch_number' => ':batch_number',
                'date_administered' => ':date_administered',
                'date_due' => ':date_due',
                'status' => ':status',
                'remarks' => ':remarks'
            ];
            $updates = [];
            foreach ($fields as $col => $param) {
                if (isset($data[$col])) {
                    $updates[] = "$col = $param";
                }
            }
            if (empty($updates))
                return false;

            $sql = "UPDATE immunizations SET " . implode(', ', $updates) . " WHERE id = :id";
            $stmt = $this->connection->prepare($sql);
            $params = [':id' => $id];
            foreach ($fields as $col => $param) {
                if (isset($data[$col])) {
                    $params[$param] = $data[$col];
                }
            }
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log('Error updating immunization: ' . $e->getMessage());
            return false;
        }
    }

    // Get nutrition records for a dependent
    public function getNutritionRecords($dependent_id)
    {
        try {
            $stmt = $this->connection->prepare("SELECT * FROM nutrition_records WHERE dependent_id = :dependent_id ORDER BY visit_date DESC");
            $stmt->execute([':dependent_id' => $dependent_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error fetching nutrition records: ' . $e->getMessage());
            return [];
        }
    }

    // Add nutrition record
    public function addNutritionRecord($dependent_id, $data)
    {
        try {
            $sql = "INSERT INTO nutrition_records (dependent_id, weight_kg, height_cm, status, visit_date, next_visit_date, remarks) 
                    VALUES (:dependent_id, :weight_kg, :height_cm, :status, :visit_date, :next_visit_date, :remarks)";
            $stmt = $this->connection->prepare($sql);
            return $stmt->execute([
                ':dependent_id' => $dependent_id,
                ':weight_kg' => $data['weight_kg'],
                ':height_cm' => $data['height_cm'],
                ':status' => $data['status'] ?? 'optimal',
                ':visit_date' => $data['visit_date'],
                ':next_visit_date' => $data['next_visit_date'] ?? null,
                ':remarks' => $data['remarks'] ?? null
            ]);
        } catch (PDOException $e) {
            error_log('Error adding nutrition record: ' . $e->getMessage());
            return false;
        }
    }

    // Update nutrition record
    public function updateNutritionRecord($id, $data)
    {
        try {
            $fields = [
                'weight_kg' => ':weight_kg',
                'height_cm' => ':height_cm',
                'status' => ':status',
                'visit_date' => ':visit_date',
                'next_visit_date' => ':next_visit_date',
                'remarks' => ':remarks'
            ];
            $updates = [];
            foreach ($fields as $col => $param) {
                if (isset($data[$col])) {
                    $updates[] = "$col = $param";
                }
            }
            if (empty($updates))
                return false;

            $sql = "UPDATE nutrition_records SET " . implode(', ', $updates) . " WHERE id = :id";
            $stmt = $this->connection->prepare($sql);
            $params = [':id' => $id];
            foreach ($fields as $col => $param) {
                if (isset($data[$col])) {
                    $params[$param] = $data[$col];
                }
            }
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log('Error updating nutrition record: ' . $e->getMessage());
            return false;
        }
    }

    // Delete immunization
    public function deleteImmunization($id)
    {
        try {
            $sql = "DELETE FROM immunizations WHERE id = :id";
            $stmt = $this->connection->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            error_log('Error deleting immunization: ' . $e->getMessage());
            return false;
        }
    }

    // Delete nutrition record
    public function deleteNutritionRecord($id)
    {
        try {
            $sql = "DELETE FROM nutrition_records WHERE id = :id";
            $stmt = $this->connection->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            error_log('Error deleting nutrition record: ' . $e->getMessage());
            return false;
        }
    }

    // Update dependent
    public function updateDependent($id, $data)
    {
        try {
            $fields = [
                'first_name' => ':first_name',
                'last_name' => ':last_name',
                'date_of_birth' => ':date_of_birth',
                'place_of_birth' => ':place_of_birth',
                'gender' => ':gender',
                'relationship' => ':relationship',
                'fic_status' => ':fic_status'
            ];
            $updates = [];
            foreach ($fields as $col => $param) {
                if (isset($data[$col])) {
                    $updates[] = "$col = $param";
                }
            }
            if (empty($updates))
                return false;

            $sql = "UPDATE dependents SET " . implode(', ', $updates) . " WHERE id = :id";
            $stmt = $this->connection->prepare($sql);
            $params = [':id' => $id];
            foreach ($fields as $col => $param) {
                if (isset($data[$col])) {
                    $params[$param] = $data[$col];
                }
            }
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log('Error updating dependent: ' . $e->getMessage());
            return false;
        }
    }

    // Get Disease Reports for a user
    public function getUserDiseaseReports($user_id)
    {
        try {
            $sql = "SELECT * FROM disease_reports WHERE user_id = ? ORDER BY created_at DESC";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([$user_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Table might not exist yet, return empty
            return [];
        }
    }

    // Get user appointments
    public function getUserAppointments($user_id)
    {
        try {
            $sql = "SELECT * FROM appointments WHERE user_id = ? AND deleted_at IS NULL ORDER BY created_at DESC";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([$user_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get appointments error: " . $e->getMessage());
            return [];
        }
    }

    // Get user service requests
    public function getUserServiceRequests($user_id)
    {
        try {
            $sql = "SELECT * FROM service_requests WHERE user_id = ? AND deleted_at IS NULL ORDER BY created_at DESC";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([$user_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get service requests error: " . $e->getMessage());
            return [];
        }
    }

    // Cancel appointment
    public function cancelAppointment($appointment_id, $user_id)
    {
        try {
            $sql = "UPDATE appointments SET status = 'cancelled', updated_at = CURRENT_TIMESTAMP 
                    WHERE id = ? AND user_id = ? AND status IN ('pending') AND deleted_at IS NULL";
            $stmt = $this->connection->prepare($sql);
            $ok = $stmt->execute([$appointment_id, $user_id]);
            return $ok && ($stmt->rowCount() > 0);
        } catch (PDOException $e) {
            error_log("Cancel appointment error: " . $e->getMessage());
            return false;
        }
    }

    // Cancel service request
    public function cancelServiceRequest($request_id, $user_id)
    {
        try {
            $sql = "UPDATE service_requests SET status = 'cancelled', updated_at = CURRENT_TIMESTAMP 
                    WHERE id = ? AND user_id = ? AND status IN ('pending') AND deleted_at IS NULL";
            $stmt = $this->connection->prepare($sql);
            $ok = $stmt->execute([$request_id, $user_id]);
            return $ok && ($stmt->rowCount() > 0);
        } catch (PDOException $e) {
            error_log("Cancel service request error: " . $e->getMessage());
            return false;
        }
    }

    // Verification helpers
    public function isUserVerified($user_id)
    {
        try {
            $stmt = $this->connection->prepare("SELECT role, verification_status FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $row = $stmt->fetch();
            if (!$row)
                return false;

            // Staff roles are automatically verified
            $staffRoles = ['admin', 'inspector', 'health_worker', 'nurse', 'doctor'];
            if (in_array($row['role'], $staffRoles)) {
                return true;
            }

            return $row['verification_status'] === 'verified';
        } catch (PDOException $e) {
            error_log("Check verified error: " . $e->getMessage());
            return false;
        }
    }

    public function submitUserVerification($user_id, $document_type, $file_path)
    {
        try {
            $this->connection->beginTransaction();
            $stmt = $this->connection->prepare("INSERT INTO user_verifications (user_id, document_type, file_path, status, created_at, updated_at) VALUES (:uid, :dtype, :f, 'pending', NOW(), NOW())");
            $stmt->execute([':uid' => $user_id, ':dtype' => $document_type, ':f' => $file_path]);
            $this->connection->prepare("UPDATE users SET verification_status = 'pending' WHERE id = ?")->execute([$user_id]);
            $this->connection->commit();
            return true;
        } catch (PDOException $e) {
            $this->connection->rollBack();
            error_log("Submit verification error: " . $e->getMessage());
            return false;
        }
    }

    public function listUserVerifications($status = null)
    {
        try {
            $sql = "SELECT v.*, u.first_name, u.last_name, u.email FROM user_verifications v JOIN users u ON v.user_id = u.id";
            if ($status) {
                $sql .= " WHERE v.status = :status";
            }
            $sql .= " ORDER BY v.created_at DESC";
            $stmt = $this->connection->prepare($sql);
            if ($status) {
                $stmt->bindParam(':status', $status);
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("List verifications error: " . $e->getMessage());
            return [];
        }
    }

    public function updateUserVerificationStatus($verification_id, $status, $admin_id = null, $note = null)
    {
        try {
            $this->connection->beginTransaction();
            $stmt = $this->connection->prepare("UPDATE user_verifications SET status = :st, reviewed_by = :rb, reviewed_at = NOW(), notes = :nt WHERE id = :id");
            $stmt->execute([':st' => $status, ':rb' => $admin_id, ':nt' => $note, ':id' => $verification_id]);

            // Sync user's verification_status
            $ownerStmt = $this->connection->prepare("SELECT user_id FROM user_verifications WHERE id = :id");
            $ownerStmt->execute([':id' => $verification_id]);
            $row = $ownerStmt->fetch();
            if ($row) {
                $vs = ($status === 'verified') ? 'verified' : (($status === 'rejected') ? 'rejected' : 'unverified');
                $this->connection->prepare("UPDATE users SET verification_status = :vs WHERE id = :uid")->execute([':vs' => $vs, ':uid' => $row['user_id']]);
            }

            $this->connection->commit();
            return true;
        } catch (PDOException $e) {
            $this->connection->rollBack();
            error_log("Update verification status error: " . $e->getMessage());
            return false;
        }
    }

    // Get single immunization record
    public function getImmunizationById($id)
    {
        try {
            $stmt = $this->connection->prepare("SELECT * FROM immunizations WHERE id = :id");
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }

    // Get single nutrition record
    public function getNutritionRecordById($id)
    {
        try {
            $stmt = $this->connection->prepare("SELECT * FROM nutrition_records WHERE id = :id");
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }

    // Get upcoming reminders for all dependents of a user
    public function getFamilyUpcomingReminders($user_id)
    {
        try {
            $reminders = [];
            // Get upcoming immunizations
            $sqlImm = "SELECT i.vaccine_name, i.date_due, d.first_name 
                      FROM immunizations i 
                      JOIN dependents d ON i.dependent_id = d.id 
                      WHERE d.user_id = :uid AND i.status = 'scheduled' AND i.date_due IS NOT NULL 
                      AND i.date_due >= CURRENT_DATE 
                      ORDER BY i.date_due ASC LIMIT 5";
            $stmt = $this->connection->prepare($sqlImm);
            $stmt->execute([':uid' => $user_id]);
            $imms = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($imms as $i) {
                $reminders[] = [
                    'type' => 'immunization',
                    'name' => $i['first_name'],
                    'detail' => $i['vaccine_name'] . ' due on ' . date('M d', strtotime($i['date_due'])),
                    'date' => $i['date_due']
                ];
            }

            // Get upcoming nutrition visits
            $sqlNutri = "SELECT n.next_visit_date, d.first_name 
                        FROM (SELECT dependent_id, MAX(visit_date) as last_visit FROM nutrition_records GROUP BY dependent_id) latest
                        JOIN nutrition_records n ON n.dependent_id = latest.dependent_id AND n.visit_date = latest.last_visit
                        JOIN dependents d ON n.dependent_id = d.id
                        WHERE d.user_id = :uid AND n.next_visit_date IS NOT NULL 
                        AND n.next_visit_date >= CURRENT_DATE 
                        ORDER BY n.next_visit_date ASC LIMIT 5";
            $stmt = $this->connection->prepare($sqlNutri);
            $stmt->execute([':uid' => $user_id]);
            $nutris = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($nutris as $n) {
                $reminders[] = [
                    'type' => 'nutrition',
                    'name' => $n['first_name'],
                    'detail' => 'Growth check due on ' . date('M d', strtotime($n['next_visit_date'])),
                    'date' => $n['next_visit_date']
                ];
            }

            usort($reminders, function ($a, $b) {
                return strtotime($a['date']) - strtotime($b['date']);
            });

            return array_slice($reminders, 0, 5);
        } catch (PDOException $e) {
            return [];
        }
    }

    // Role-based redirect helper
    // Search dependents (for health workers)
    // Search patients (citizens or dependents) - For staff use
    public function searchPatients($query)
    {
        try {
            $q = "%$query%";
            // Search users (role: citizen) and their dependents in one go
            // We return unique users who match the query OR have dependents matching the query
            $sql = "SELECT DISTINCT u.id as user_id, u.first_name, u.last_name, u.email 
                    FROM users u
                    LEFT JOIN dependents d ON u.id = d.user_id
                    WHERE u.role = 'citizen' 
                    AND (
                        u.id = :id
                        OR u.first_name LIKE :q1
                        OR u.last_name LIKE :q2
                        OR u.email LIKE :q3 
                        OR d.first_name LIKE :q4
                        OR d.last_name LIKE :q5
                    )
                    LIMIT 20";
            $stmt = $this->connection->prepare($sql);
            $id = is_numeric($query) ? (int) $query : -1;
            $stmt->execute([
                ':id' => $id,
                ':q1' => $q,
                ':q2' => $q,
                ':q3' => $q,
                ':q4' => $q,
                ':q5' => $q
            ]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error searching patients: ' . $e->getMessage());
            return [];
        }
    }

    public static function getRoleRedirect($role)
    {
        $redirects = [
            'admin' => 'admin/DashboardOverview_new.php',
            'doctor' => 'doctor/doctor.php',
            'nurse' => 'nurse/nurse.php',
            'citizen' => 'citizen/citizen.php',
            'inspector' => 'inspection/inspector.php',
            'health_worker' => 'health_worker/worker.php'
        ];

        return isset($redirects[$role]) ? $redirects[$role] : 'citizen/citizen.php';
    }
}

// Create database instance
$database = new Database();
$db = $database->getConnection();

// Helper functions
function startSecureSession()
{
    // Check if headers have already been sent
    if (headers_sent()) {
        error_log("Warning: Cannot start session - headers already sent");
        return false;
    }

    if (session_status() === PHP_SESSION_NONE) {
        session_start();

        // Regenerate session ID for security (only if session is active)
        if (session_status() === PHP_SESSION_ACTIVE && !isset($_SESSION['initiated'])) {
            session_regenerate_id(true);
            $_SESSION['initiated'] = true;
        }
    }
    return true;
}

function isLoggedIn()
{
    return isset($_SESSION['user_id']) && isset($_SESSION['role']);
}

function requireLogin()
{
    if (!isLoggedIn()) {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $ctype = $_SERVER['CONTENT_TYPE'] ?? '';
        $isApi = (strpos($uri, '/api/') !== false) || (stripos($accept, 'application/json') !== false) || (stripos($ctype, 'application/json') !== false);
        if ($isApi) {
            if (!headers_sent())
                header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit();
        }
        header('Location: index.php');
        exit();
    }
}

function requireRole($required_role)
{
    requireLogin();
    if ($_SESSION['role'] !== $required_role) {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $ctype = $_SERVER['CONTENT_TYPE'] ?? '';
        $isApi = (strpos($uri, '/api/') !== false) || (stripos($accept, 'application/json') !== false) || (stripos($ctype, 'application/json') !== false);
        if ($isApi) {
            if (!headers_sent())
                header('Content-Type: application/json');
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Forbidden']);
            exit();
        }
        header('Location: ' . Database::getRoleRedirect($_SESSION['role']));
        exit();
    }
}

function redirectByRole($role)
{
    $redirect_url = Database::getRoleRedirect($role);
    header("Location: {$redirect_url}");
    exit();
}

// SQL to create users table (run this once to set up the database)
/*
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'doctor', 'nurse', 'citizen') DEFAULT 'citizen',
    status ENUM('active', 'inactive', 'pending') DEFAULT 'active',
    profile_picture VARCHAR(255) NULL,
    phone VARCHAR(20) NULL,
    address TEXT NULL,
    date_of_birth DATE NULL,
    gender ENUM('male', 'female', 'other') NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default admin user (password: admin123)
INSERT INTO users (first_name, last_name, email, password, role) 
VALUES ('Admin', 'User', 'admin@healthsanitation.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Create appointments table
CREATE TABLE IF NOT EXISTS appointments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    middle_name VARCHAR(50),
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    birth_date DATE NOT NULL,
    gender ENUM('male', 'female', 'other', 'prefer-not-to-say') NOT NULL,
    civil_status ENUM('single', 'married', 'divorced', 'widowed') NOT NULL,
    address TEXT NOT NULL,
    appointment_type VARCHAR(100) NOT NULL,
    preferred_date DATE NOT NULL,
    health_concerns TEXT NOT NULL,
    medical_history TEXT NOT NULL,
    current_medications TEXT,
    allergies TEXT,
    emergency_contact_name VARCHAR(100) NOT NULL,
    emergency_contact_phone VARCHAR(20) NOT NULL,
    status ENUM('pending', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create service_requests table
CREATE TABLE IF NOT EXISTS service_requests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    service_type VARCHAR(100) NOT NULL,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address TEXT NOT NULL,
    service_details TEXT NOT NULL,
    preferred_date DATE,
    urgency ENUM('low', 'medium', 'high', 'emergency') DEFAULT 'medium',
    status ENUM('pending', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
*/