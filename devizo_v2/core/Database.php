<?php
/**
 * DEVIZO v2.0 - Database Class
 *
 * PDO wrapper with query builder and connection management
 * Optimized for shared hosting with connection pooling
 */

if (!defined('DEVIZO_APP')) {
    die('Direct access not permitted');
}

class Database {

    private static $instance = null;
    private $connection = null;
    private $statementCache = [];

    /**
     * Private constructor (Singleton pattern)
     */
    private function __construct() {
        $this->connect();
    }

    /**
     * Get database instance (Singleton)
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Get PDO connection
     */
    public function getConnection() {
        if ($this->connection === null) {
            $this->connect();
        }
        return $this->connection;
    }

    /**
     * Connect to database
     */
    private function connect() {
        try {
            $dsn = sprintf(
                "mysql:host=%s;dbname=%s;charset=%s",
                DB_HOST,
                DB_NAME,
                DB_CHARSET
            );

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => true, // Connection pooling for shared hosting
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
            ];

            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);

            log_message('Database connection established', 'info');

        } catch (PDOException $e) {
            log_message('Database connection failed: ' . $e->getMessage(), 'error');

            if (DEBUG_MODE) {
                die("Database connection failed: " . $e->getMessage());
            } else {
                die("Database connection failed. Please contact administrator.");
            }
        }
    }

    /**
     * Prepare statement with caching
     */
    public function prepare($sql) {
        $hash = md5($sql);

        if (!isset($this->statementCache[$hash])) {
            try {
                $this->statementCache[$hash] = $this->connection->prepare($sql);
            } catch (PDOException $e) {
                log_message('SQL Prepare Error: ' . $e->getMessage() . ' | SQL: ' . $sql, 'error');
                throw $e;
            }
        }

        return $this->statementCache[$hash];
    }

    /**
     * Execute query
     */
    public function query($sql) {
        try {
            return $this->connection->query($sql);
        } catch (PDOException $e) {
            log_message('SQL Query Error: ' . $e->getMessage() . ' | SQL: ' . $sql, 'error');
            throw $e;
        }
    }

    /**
     * Execute prepared statement
     */
    public function execute($sql, $params = []) {
        try {
            $stmt = $this->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            log_message('SQL Execute Error: ' . $e->getMessage() . ' | SQL: ' . $sql, 'error');
            throw $e;
        }
    }

    /**
     * Fetch single row
     * Can be used with direct SQL or query builder
     */
    public function fetchOne($sql = null, $params = []) {
        // If called from query builder (no SQL provided)
        if ($sql === null && !empty($this->qb['select'])) {
            $sql = $this->buildSelectQuery();
            $params = $this->getQueryParams();
            $this->resetQueryBuilder();
        }

        $stmt = $this->execute($sql, $params);
        return $stmt->fetch();
    }

    /**
     * Fetch all rows
     * Can be used with direct SQL or query builder
     */
    public function fetchAll($sql = null, $params = []) {
        // If called from query builder (no SQL provided)
        if ($sql === null && !empty($this->qb['select'])) {
            $sql = $this->buildSelectQuery();
            $params = $this->getQueryParams();
            $this->resetQueryBuilder();
        }

        $stmt = $this->execute($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * Fetch single column value
     * Can be used with direct SQL or query builder
     */
    public function fetchColumn($sql = null, $params = [], $column = 0) {
        // If called from query builder (no SQL provided)
        if ($sql === null && !empty($this->qb['select'])) {
            $sql = $this->buildSelectQuery();
            $params = $this->getQueryParams();
            $this->resetQueryBuilder();
        }

        $stmt = $this->execute($sql, $params);
        return $stmt->fetchColumn($column);
    }

    /**
     * Insert data
     */
    public function insert($table, $data) {
        $keys = array_keys($data);
        $fields = implode(', ', $keys);
        $placeholders = ':' . implode(', :', $keys);

        $sql = "INSERT INTO `{$table}` ({$fields}) VALUES ({$placeholders})";

        $stmt = $this->execute($sql, $data);

        return $this->connection->lastInsertId();
    }

    /**
     * Update data
     */
    public function update($table, $data, $where, $whereParams = []) {
        $set = [];
        foreach (array_keys($data) as $key) {
            $set[] = "`{$key}` = :{$key}";
        }
        $setClause = implode(', ', $set);

        $sql = "UPDATE `{$table}` SET {$setClause} WHERE {$where}";

        $params = array_merge($data, $whereParams);
        $stmt = $this->execute($sql, $params);

        return $stmt->rowCount();
    }

    /**
     * Delete data
     */
    public function delete($table, $where, $whereParams = []) {
        $sql = "DELETE FROM `{$table}` WHERE {$where}";
        $stmt = $this->execute($sql, $whereParams);
        return $stmt->rowCount();
    }

    /**
     * Get last insert ID
     */
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }

    /**
     * Begin transaction
     */
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }

    /**
     * Commit transaction
     */
    public function commit() {
        return $this->connection->commit();
    }

    /**
     * Rollback transaction
     */
    public function rollback() {
        return $this->connection->rollBack();
    }

    /**
     * Check if in transaction
     */
    public function inTransaction() {
        return $this->connection->inTransaction();
    }

    /**
     * Escape string
     */
    public function escape($value) {
        return $this->connection->quote($value);
    }

    /**
     * Count rows in table with optional conditions
     */
    public function count($table, $where = '1=1', $params = []) {
        $sql = "SELECT COUNT(*) FROM `{$table}` WHERE {$where}";
        return (int) $this->fetchColumn($sql, $params);
    }

    /**
     * Check if record exists
     */
    public function exists($table, $where, $params = []) {
        return $this->count($table, $where, $params) > 0;
    }

    /**
     * Get table columns
     */
    public function getColumns($table) {
        $sql = "SHOW COLUMNS FROM `{$table}`";
        return $this->fetchAll($sql);
    }

    /**
     * Truncate table
     */
    public function truncate($table) {
        $sql = "TRUNCATE TABLE `{$table}`";
        return $this->query($sql);
    }

    /**
     * Close connection
     */
    public function close() {
        $this->connection = null;
        $this->statementCache = [];
    }

    /**
     * Query Builder - SELECT
     */
    private $qb = [
        'select' => '*',
        'from' => '',
        'join' => [],
        'where' => [],
        'groupBy' => '',
        'having' => '',
        'orderBy' => '',
        'limit' => '',
        'offset' => ''
    ];

    public function select($columns = '*') {
        $this->qb['select'] = is_array($columns) ? implode(', ', $columns) : $columns;
        return $this;
    }

    public function from($table) {
        $this->qb['from'] = $table;
        return $this;
    }

    public function join($table, $condition, $type = 'INNER') {
        // Handle table alias (e.g., 'roluri r' -> `roluri` r)
        if (preg_match('/^(\S+)\s+(.+)$/', $table, $matches)) {
            $table = "`{$matches[1]}` {$matches[2]}";
        } else {
            $table = "`{$table}`";
        }

        $this->qb['join'][] = "{$type} JOIN {$table} ON {$condition}";
        return $this;
    }

    public function leftJoin($table, $condition) {
        return $this->join($table, $condition, 'LEFT');
    }

    public function rightJoin($table, $condition) {
        return $this->join($table, $condition, 'RIGHT');
    }

    public function where($condition, $params = []) {
        $this->qb['where'][] = ['condition' => $condition, 'params' => $params, 'type' => 'AND'];
        return $this;
    }

    public function orWhere($condition, $params = []) {
        $this->qb['where'][] = ['condition' => $condition, 'params' => $params, 'type' => 'OR'];
        return $this;
    }

    public function groupBy($column) {
        $this->qb['groupBy'] = $column;
        return $this;
    }

    public function having($condition) {
        $this->qb['having'] = $condition;
        return $this;
    }

    public function orderBy($column, $direction = 'ASC') {
        $this->qb['orderBy'] = "{$column} {$direction}";
        return $this;
    }

    public function limit($limit) {
        $this->qb['limit'] = (int) $limit;
        return $this;
    }

    public function offset($offset) {
        $this->qb['offset'] = (int) $offset;
        return $this;
    }

    public function get() {
        $sql = $this->buildSelectQuery();
        $params = $this->getQueryParams();

        $result = $this->fetchAll($sql, $params);
        $this->resetQueryBuilder();

        return $result;
    }

    public function first() {
        $this->limit(1);
        $result = $this->get();
        return !empty($result) ? $result[0] : null;
    }

    private function buildSelectQuery() {
        // Handle table alias (e.g., 'utilizatori u' -> `utilizatori` u)
        $from = $this->qb['from'];
        if (preg_match('/^(\S+)\s+(.+)$/', $from, $matches)) {
            $from = "`{$matches[1]}` {$matches[2]}";
        } else {
            $from = "`{$from}`";
        }

        $sql = "SELECT {$this->qb['select']} FROM {$from}";

        if (!empty($this->qb['join'])) {
            $sql .= ' ' . implode(' ', $this->qb['join']);
        }

        if (!empty($this->qb['where'])) {
            $whereClauses = [];
            foreach ($this->qb['where'] as $i => $where) {
                $prefix = $i === 0 ? 'WHERE' : $where['type'];
                $whereClauses[] = "{$prefix} ({$where['condition']})";
            }
            $sql .= ' ' . implode(' ', $whereClauses);
        }

        if ($this->qb['groupBy']) {
            $sql .= " GROUP BY {$this->qb['groupBy']}";
        }

        if ($this->qb['having']) {
            $sql .= " HAVING {$this->qb['having']}";
        }

        if ($this->qb['orderBy']) {
            $sql .= " ORDER BY {$this->qb['orderBy']}";
        }

        if ($this->qb['limit']) {
            $sql .= " LIMIT {$this->qb['limit']}";
        }

        if ($this->qb['offset']) {
            $sql .= " OFFSET {$this->qb['offset']}";
        }

        return $sql;
    }

    private function getQueryParams() {
        $params = [];
        if (!empty($this->qb['where'])) {
            foreach ($this->qb['where'] as $where) {
                if (!empty($where['params'])) {
                    $params = array_merge($params, $where['params']);
                }
            }
        }
        return $params;
    }

    private function resetQueryBuilder() {
        $this->qb = [
            'select' => '*',
            'from' => '',
            'join' => [],
            'where' => [],
            'groupBy' => '',
            'having' => '',
            'orderBy' => '',
            'limit' => '',
            'offset' => ''
        ];
    }

    /**
     * Prevent cloning
     */
    private function __clone() {}

    /**
     * Prevent unserialization
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

/**
 * Helper function to get database instance
 */
function db() {
    return Database::getInstance();
}
