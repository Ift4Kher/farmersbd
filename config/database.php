<?php
// ============================================================
// FarmersBD — PDO Database Connection (Singleton)
// ============================================================

class Database {
    private static ?PDO $instance = null;

    private function __construct() {}
    private function __clone() {}

    public static function getInstance(): PDO {
        if (self::$instance === null) {
            $host    = env('DB_HOST',     'localhost');
            $port    = env('DB_PORT',     '3306');
            $dbname  = env('DB_DATABASE', 'farmersbd');
            $user    = env('DB_USERNAME', 'root');
            $pass    = env('DB_PASSWORD', '');
            $charset = env('DB_CHARSET',  'utf8mb4');

            $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci'",
            ];

            try {
                self::$instance = new PDO($dsn, $user, $pass, $options);
            } catch (PDOException $e) {
                // Never expose credentials or internal errors to output
                error_log('Database connection failed: ' . $e->getMessage());
                if (APP_DEBUG) {
                    die('<div style="color:red;padding:20px">Database connection failed: ' 
                        . htmlspecialchars($e->getMessage()) . '</div>');
                }
                die('<div style="color:red;padding:20px">ডেটাবেস সংযোগ ব্যর্থ হয়েছে। অনুগ্রহ করে পরে আবার চেষ্টা করুন।</div>');
            }
        }

        return self::$instance;
    }
}

/**
 * Convenience function to get the PDO instance.
 */
function db(): PDO {
    return Database::getInstance();
}
