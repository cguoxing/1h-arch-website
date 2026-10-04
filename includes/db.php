<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

function oneh_db_configured(): bool
{
    return ONEH_DB_HOST !== '' && ONEH_DB_NAME !== '' && ONEH_DB_USER !== '';
}

function oneh_db(): ?mysqli
{
    static $db = null;
    static $attempted = false;

    if ($attempted) {
        return $db;
    }

    $attempted = true;
    if (!oneh_db_configured()) {
        return null;
    }

    mysqli_report(MYSQLI_REPORT_OFF);
    $port = defined('DB_PORT') ? (int) DB_PORT : 3306;
    $db = @new mysqli(ONEH_DB_HOST, ONEH_DB_USER, ONEH_DB_PASS, ONEH_DB_NAME, $port);
    if ($db->connect_errno) {
        $db = null;
        return null;
    }

    $db->set_charset(ONEH_DB_CHARSET);
    return $db;
}

function oneh_bind_params(mysqli_stmt $stmt, string $types, array $params): bool
{
    if ($types === '') {
        return true;
    }
    $refs = [$types];
    foreach ($params as $key => $value) {
        $refs[] = &$params[$key];
    }
    return call_user_func_array([$stmt, 'bind_param'], $refs);
}

function oneh_stmt_rows(mysqli_stmt $stmt): array
{
    $meta = $stmt->result_metadata();
    if (!$meta) {
        return [];
    }
    $row = [];
    $refs = [];
    while ($field = $meta->fetch_field()) {
        $row[$field->name] = null;
        $refs[] = &$row[$field->name];
    }
    call_user_func_array([$stmt, 'bind_result'], $refs);

    $rows = [];
    while ($stmt->fetch()) {
        $copy = [];
        foreach ($row as $key => $value) {
            $copy[$key] = $value;
        }
        $rows[] = $copy;
    }
    return $rows;
}

function oneh_column_exists(string $table, string $column): bool
{
    $rows = oneh_db_rows("SHOW COLUMNS FROM `$table` LIKE ?", 's', [$column]);
    return !empty($rows);
}

function oneh_db_value(string $sql, string $types = '', array $params = [])
{
    $db = oneh_db();
    if (!$db) {
        return null;
    }

    $stmt = $db->prepare($sql);
    if (!$stmt) {
        return null;
    }
    oneh_bind_params($stmt, $types, $params);
    if (!$stmt->execute()) {
        return null;
    }
    $rows = oneh_stmt_rows($stmt);
    if (!$rows) {
        return null;
    }
    $first = reset($rows[0]);
    return $first === false ? null : $first;
}

function oneh_db_rows(string $sql, string $types = '', array $params = []): array
{
    $db = oneh_db();
    if (!$db) {
        return [];
    }

    $stmt = $db->prepare($sql);
    if (!$stmt) {
        return [];
    }
    oneh_bind_params($stmt, $types, $params);
    if (!$stmt->execute()) {
        return [];
    }
    return oneh_stmt_rows($stmt);
}

const ONEH_SCHEMA_VERSION = '2026-10-04';

/**
 * 建表 / 补列。已是最新版本时只做 1 次轻量查询直接返回，
 * 避免每次打开后台都执行十几条 SHOW COLUMNS。
 */
function oneh_install_schema(bool $force = false): bool
{
    static $done = false;
    if ($done && !$force) {
        return true;
    }
    $db = oneh_db();
    if (!$db) {
        return false;
    }
    if (!$force) {
        $version = oneh_db_value("SELECT value_json FROM site_settings WHERE setting_key = '_schema_version'");
        if ($version !== null && json_decode((string) $version, true) === ONEH_SCHEMA_VERSION) {
            $done = true;
            return true;
        }
    }

    $queries = [
        "CREATE TABLE IF NOT EXISTS admins (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(80) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        "CREATE TABLE IF NOT EXISTS projects (
            id VARCHAR(120) PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            category VARCHAR(180) DEFAULT '',
            type VARCHAR(80) DEFAULT '',
            types_json TEXT,
            city VARCHAR(120) DEFAULT '',
            year VARCHAR(20) DEFAULT '',
            area VARCHAR(180) DEFAULT '',
            role VARCHAR(180) DEFAULT '',
            image VARCHAR(255) DEFAULT '',
            link VARCHAR(255) DEFAULT '',
            wechat_link VARCHAR(500) DEFAULT '',
            summary TEXT,
            lead_text TEXT,
            overview_title TEXT,
            overview TEXT,
            status VARCHAR(30) DEFAULT 'draft',
            is_featured TINYINT(1) DEFAULT 0,
            sort_order INT DEFAULT 100,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        "CREATE TABLE IF NOT EXISTS project_images (
            id INT AUTO_INCREMENT PRIMARY KEY,
            project_id VARCHAR(120) NOT NULL,
            src VARCHAR(255) NOT NULL,
            caption VARCHAR(255) DEFAULT '',
            sort_order INT DEFAULT 100,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX (project_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        "CREATE TABLE IF NOT EXISTS site_settings (
            setting_key VARCHAR(120) PRIMARY KEY,
            value_json MEDIUMTEXT,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        "CREATE TABLE IF NOT EXISTS inquiries (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(120) NOT NULL,
            company VARCHAR(200) DEFAULT '',
            email VARCHAR(200) NOT NULL,
            phone VARCHAR(60) DEFAULT '',
            project_type VARCHAR(120) DEFAULT '',
            brief TEXT,
            ip VARCHAR(64) DEFAULT '',
            user_agent VARCHAR(255) DEFAULT '',
            status VARCHAR(20) DEFAULT 'new',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX (status),
            INDEX (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    ];

    foreach ($queries as $query) {
        if (!$db->query($query)) {
            return false;
        }
    }

    $projectColumns = [
        'category' => "ALTER TABLE projects ADD category VARCHAR(180) DEFAULT ''",
        'type' => "ALTER TABLE projects ADD type VARCHAR(80) DEFAULT ''",
        'types_json' => "ALTER TABLE projects ADD types_json TEXT",
        'city' => "ALTER TABLE projects ADD city VARCHAR(120) DEFAULT ''",
        'year' => "ALTER TABLE projects ADD year VARCHAR(20) DEFAULT ''",
        'area' => "ALTER TABLE projects ADD area VARCHAR(180) DEFAULT ''",
        'role' => "ALTER TABLE projects ADD role VARCHAR(180) DEFAULT ''",
        'image' => "ALTER TABLE projects ADD image VARCHAR(255) DEFAULT ''",
        'link' => "ALTER TABLE projects ADD link VARCHAR(255) DEFAULT ''",
        'wechat_link' => "ALTER TABLE projects ADD wechat_link VARCHAR(500) DEFAULT ''",
        'summary' => "ALTER TABLE projects ADD summary TEXT",
        'lead_text' => "ALTER TABLE projects ADD lead_text TEXT",
        'overview_title' => "ALTER TABLE projects ADD overview_title TEXT",
        'overview' => "ALTER TABLE projects ADD overview TEXT",
        'status' => "ALTER TABLE projects ADD status VARCHAR(30) DEFAULT 'draft'",
        'is_featured' => "ALTER TABLE projects ADD is_featured TINYINT(1) DEFAULT 0",
        'sort_order' => "ALTER TABLE projects ADD sort_order INT DEFAULT 100",
        'created_at' => "ALTER TABLE projects ADD created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP",
        'updated_at' => "ALTER TABLE projects ADD updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP",
    ];
    foreach ($projectColumns as $column => $alterSql) {
        if (!oneh_column_exists('projects', $column)) {
            $db->query($alterSql);
        }
    }

    $adminCount = (int) oneh_db_value("SELECT COUNT(*) FROM admins WHERE username = ?", 's', [ONEH_BOOTSTRAP_ADMIN_USER]);
    if ($adminCount === 0) {
        $hash = password_hash(ONEH_BOOTSTRAP_ADMIN_PASS, PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO admins (username, password_hash) VALUES (?, ?)");
        $username = ONEH_BOOTSTRAP_ADMIN_USER;
        $stmt->bind_param('ss', $username, $hash);
        $stmt->execute();
    }

    $versionJson = json_encode(ONEH_SCHEMA_VERSION);
    $stmt = $db->prepare("REPLACE INTO site_settings (setting_key, value_json) VALUES ('_schema_version', ?)");
    if ($stmt) {
        $stmt->bind_param('s', $versionJson);
        $stmt->execute();
    }
    $done = true;
    return true;
}
