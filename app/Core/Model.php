<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOStatement;

abstract class Model
{
    protected static string $table = '';
    protected static string $pk    = 'id';
    protected static bool $hasUpdatedAt = true;

    public static function find(int $id): ?array
    {
        return static::row('SELECT * FROM ' . static::$table . ' WHERE ' . static::$pk . ' = ?', [$id]);
    }

    public static function findBy(string $col, mixed $val): ?array
    {
        return static::row('SELECT * FROM ' . static::$table . ' WHERE ' . $col . ' = ?', [$val]);
    }

    public static function findByUuid(string $uuid): ?array
    {
        return static::findBy('uuid', $uuid);
    }

    public static function all(string $order = 'id DESC', int $limit = 1000): array
    {
        return static::rows(
            'SELECT * FROM ' . static::$table . ' ORDER BY ' . $order . ' LIMIT ' . $limit
        );
    }

    public static function create(array $data): int
    {
        $data = static::timestamps($data, false);

        $columns = array_keys($data);
        $placeholders = array_fill(0, count($columns), '?');

        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            static::$table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        static::query($sql, array_values($data));

        return (int) Database::get()->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $data = static::timestamps($data, true);

        $set = implode(', ', array_map(fn (string $col) => "{$col} = ?", array_keys($data)));
        $params = array_values($data);
        $params[] = $id;

        $sql = sprintf('UPDATE %s SET %s WHERE %s = ?', static::$table, $set, static::$pk);

        return static::query($sql, $params)->rowCount() > 0;
    }

    public static function delete(int $id): bool
    {
        $sql = sprintf('DELETE FROM %s WHERE %s = ?', static::$table, static::$pk);

        return static::query($sql, [$id])->rowCount() > 0;
    }

    public static function count(string $where = '1', array $params = []): int
    {
        $row = static::row('SELECT COUNT(*) AS total FROM ' . static::$table . ' WHERE ' . $where, $params);

        return (int) ($row['total'] ?? 0);
    }

    public static function paginate(int $page, int $perPage, string $where = '1', array $params = []): array
    {
        $page = max(1, $page);
        $perPage = max(1, $perPage);
        $offset = ($page - 1) * $perPage;

        $total = static::count($where, $params);
        $pages = (int) ceil($total / $perPage);

        $sql = 'SELECT * FROM ' . static::$table . ' WHERE ' . $where
            . ' ORDER BY ' . static::$pk . ' DESC LIMIT ' . $perPage . ' OFFSET ' . $offset;

        $data = static::rows($sql, $params);

        return [
            'data'  => $data,
            'total' => $total,
            'pages' => $pages,
            'page'  => $page,
        ];
    }

    protected static function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = Database::get()->prepare($sql);
        $stmt->execute($params);

        return $stmt;
    }

    protected static function row(string $sql, array $params = []): ?array
    {
        $result = static::query($sql, $params)->fetch(PDO::FETCH_ASSOC);

        return $result ?: null;
    }

    protected static function rows(string $sql, array $params = []): array
    {
        return static::query($sql, $params)->fetchAll(PDO::FETCH_ASSOC);
    }

    protected static function timestamps(array $data, bool $update = false): array
    {
        $now = date('Y-m-d H:i:s');
        $hasUpdatedAt = static::$hasUpdatedAt ?? true;

        if ($update) {
            if ($hasUpdatedAt) $data['updated_at'] = $now;
        } else {
            $data['created_at'] = $data['created_at'] ?? $now;
            if ($hasUpdatedAt) $data['updated_at'] = $data['updated_at'] ?? $now;
        }

        return $data;
    }
}
