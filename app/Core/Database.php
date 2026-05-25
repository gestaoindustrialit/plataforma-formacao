<?php

namespace App\Core;

use PDO;
use PDOStatement;

class Database
{
    /** @var PDO */
    private $pdo;

    public function __construct(array $config)
    {
        $dsn = 'sqlite:' . $config['database_path'];
        $this->pdo = new PDO($dsn);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $this->pdo->exec('PRAGMA foreign_keys = ON');
    }

    public function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /** @return array|false */
    public function fetch(string $sql, array $params = [])
    {
        return $this->query($sql, $params)->fetch();
    }

    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }

    public function insert(string $table, array $data): int
    {
        $columns = array_keys($data);
        $placeholders = array_map(function ($column) { return ':' . $column; }, $columns);

        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        $this->query($sql, $data);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        $set = implode(', ', array_map(function ($column) { return "$column = :$column"; }, array_keys($data)));
        $sql = sprintf('UPDATE %s SET %s WHERE %s', $table, $set, $where);

        $params = array_merge($data, $whereParams);
        return $this->query($sql, $params)->rowCount();
    }

    public function delete(string $table, string $where, array $params = []): int
    {
        $sql = sprintf('DELETE FROM %s WHERE %s', $table, $where);
        return $this->query($sql, $params)->rowCount();
    }

    public function pdo(): PDO
    {
        return $this->pdo;
    }
}
