<?php
declare(strict_types=1);

function ensure_column(PDO $pdo, string $table, string $column, string $definition): void
{
    $q = $pdo->prepare('SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? AND COLUMN_NAME=?');
    $q->execute([$table, $column]);
    if (!(int)$q->fetchColumn()) {
        $pdo->exec("ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$definition}");
    }
}

function migrate_app(PDO $pdo): void
{
    ensure_column($pdo, 'vendors', 'pan', 'VARCHAR(10) NULL AFTER gstin');
    ensure_column($pdo, 'vendors', 'msme_number', 'VARCHAR(30) NULL');
    ensure_column($pdo, 'vendors', 'bank_name', 'VARCHAR(120) NULL');
    ensure_column($pdo, 'vendors', 'bank_account', 'VARCHAR(40) NULL');
    ensure_column($pdo, 'vendors', 'ifsc', 'VARCHAR(11) NULL');
    ensure_column($pdo, 'vendors', 'address', 'VARCHAR(255) NULL');
    ensure_column($pdo, 'vendors', 'blacklisted', 'BOOLEAN NOT NULL DEFAULT FALSE');
    ensure_column($pdo, 'vendors', 'updated_at', 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');
    ensure_column($pdo, 'requisitions', 'product_id', 'BIGINT UNSIGNED NULL');
    ensure_column($pdo, 'requisitions', 'required_date', 'DATE NULL');

    $pdo->exec("CREATE TABLE IF NOT EXISTS customers (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, customer_code VARCHAR(30) NOT NULL UNIQUE,
        name VARCHAR(180) NOT NULL, customer_type VARCHAR(40) NOT NULL DEFAULT 'Corporate',
        gstin VARCHAR(15) NULL UNIQUE, pan VARCHAR(10) NOT NULL, address VARCHAR(255) NULL,
        state_name VARCHAR(100) NULL, pincode VARCHAR(10) NULL, contact_person VARCHAR(120) NULL,
        email VARCHAR(190) NULL, phone VARCHAR(25) NULL, credit_limit DECIMAL(14,2) NOT NULL DEFAULT 0,
        credit_days SMALLINT UNSIGNED NOT NULL DEFAULT 0, risk_category VARCHAR(30) NOT NULL DEFAULT 'Normal',
        office_id BIGINT UNSIGNED NULL, active BOOLEAN NOT NULL DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY(office_id) REFERENCES offices(id), INDEX(name), INDEX(state_name)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $pdo->exec("CREATE TABLE IF NOT EXISTS products (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, sku VARCHAR(40) NOT NULL UNIQUE,
        name VARCHAR(180) NOT NULL, category VARCHAR(80) NULL, hsn_code VARCHAR(12) NULL,
        unit VARCHAR(20) NOT NULL DEFAULT 'units', gst_rate DECIMAL(5,2) NOT NULL DEFAULT 0,
        description TEXT NULL, active BOOLEAN NOT NULL DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX(name), INDEX(category)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $fk=$pdo->query("SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='requisitions' AND COLUMN_NAME='product_id' AND REFERENCED_TABLE_NAME='products'")->fetchColumn();
    if(!(int)$fk) $pdo->exec('ALTER TABLE requisitions ADD CONSTRAINT fk_requisition_product FOREIGN KEY(product_id) REFERENCES products(id)');
    $pdo->exec("CREATE TABLE IF NOT EXISTS sales_orders (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, invoice_no VARCHAR(35) NOT NULL UNIQUE,
        office_id BIGINT UNSIGNED NOT NULL, customer_id BIGINT UNSIGNED NOT NULL,
        product_id BIGINT UNSIGNED NOT NULL, created_by BIGINT UNSIGNED NOT NULL,
        quantity DECIMAL(14,3) NOT NULL, unit_price DECIMAL(14,2) NOT NULL,
        gst_rate DECIMAL(5,2) NOT NULL, taxable_amount DECIMAL(14,2) NOT NULL,
        tax_amount DECIMAL(14,2) NOT NULL, total_amount DECIMAL(14,2) NOT NULL,
        status ENUM('draft','confirmed','dispatched','cancelled') NOT NULL DEFAULT 'confirmed',
        sale_date DATE NOT NULL, notes VARCHAR(500) NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(office_id) REFERENCES offices(id), FOREIGN KEY(customer_id) REFERENCES customers(id),
        FOREIGN KEY(product_id) REFERENCES products(id), FOREIGN KEY(created_by) REFERENCES users(id),
        INDEX(office_id,sale_date), INDEX(customer_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $pdo->exec("CREATE TABLE IF NOT EXISTS product_stock (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, product_id BIGINT UNSIGNED NOT NULL,
        office_id BIGINT UNSIGNED NOT NULL, quantity DECIMAL(14,3) NOT NULL DEFAULT 0,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY(product_id,office_id), FOREIGN KEY(product_id) REFERENCES products(id),
        FOREIGN KEY(office_id) REFERENCES offices(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $pdo->exec("CREATE TABLE IF NOT EXISTS password_resets (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id BIGINT UNSIGNED NOT NULL,
        token_hash CHAR(64) NOT NULL UNIQUE, expires_at DATETIME NOT NULL, used_at DATETIME NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS role_permissions (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, role_id BIGINT UNSIGNED NOT NULL,
        module_code VARCHAR(40) NOT NULL, can_view BOOLEAN NOT NULL DEFAULT FALSE,
        can_create BOOLEAN NOT NULL DEFAULT FALSE, can_edit BOOLEAN NOT NULL DEFAULT FALSE,
        can_approve BOOLEAN NOT NULL DEFAULT FALSE, UNIQUE KEY(role_id,module_code),
        FOREIGN KEY(role_id) REFERENCES roles(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $modules=['dashboard','procurement','vendors','customers','products','inventory','sales','reports','users','offices','audit','role_access','role_master'];
    $defaults=[
        'ADMIN'=>$modules,
        'HO_MANAGER'=>['dashboard','procurement','vendors','customers','products','inventory','sales','reports','audit'],
        'RO_MANAGER'=>['dashboard','procurement','vendors','customers','products','inventory','sales','reports'],
        'DPC_OFFICER'=>['dashboard','procurement','customers','products','inventory','sales','reports'],
        'APPROVER'=>['dashboard','procurement','reports'],
        'AUDITOR'=>['dashboard','reports','audit'],
    ];
    $seed=$pdo->prepare('INSERT IGNORE INTO role_permissions(role_id,module_code,can_view,can_create,can_edit,can_approve) VALUES(?,?,?,?,?,?)');
    foreach($pdo->query('SELECT id,code FROM roles') as $r){
        foreach($modules as $module){$allowed=in_array($module,$defaults[$r['code']]??[],true);$seed->execute([(int)$r['id'],$module,$allowed,$allowed&&in_array($module,['procurement','vendors','customers','products','inventory','sales','users','offices'],true),$allowed&&in_array($module,['vendors','customers','products','inventory','users','offices'],true),$allowed&&in_array($module,['procurement','vendors','customers'],true)]);}
    }
}
