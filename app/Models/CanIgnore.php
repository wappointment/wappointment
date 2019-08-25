<?php

namespace Wappointment\Models;

use Wappointment\Config\Database;

trait CanIgnore
{
    /**
     * Performs a 'replace' query with the data
     * @param  array  $attributes
     * @return bool   t/f for success/failure
     */
    public static function replace(array $attributes = [])
    {
        return static::executeQuery('replace', $attributes);
    }

    /**
     * performs an 'insert ignore' query with the data
     * @param  array  $attributes
     * @return bool   t/f for success/failure
     */
    public static function insertIgnore(array $attributes = [])
    {
        return static::executeQuery('insert ignore', $attributes);
    }

    public static function upsert(array $attributes = [])
    {
        return static::executeQuery('insert', $attributes, true);
    }

    protected static function executeQuery($command, array $attributes, $upsert = false)
    {
        if (!count($attributes)) {
            return true;
        }
        $model = new static();
        if ($model->fireModelEvent('saving') === false) {
            return false;
        }

        $attributes = \WappointmentLv::collect($attributes);
        $first = $attributes->first();
        if (!is_array($first)) {
            $attributes = \WappointmentLv::collect([$attributes->toArray()]);
        }
        $keys = \WappointmentLv::collect($attributes->first())->keys()
            ->transform(function ($key) {
                return '`' . $key . '`';
            });

        $bindings = [];
        $query = $command . ' into ' . Database::capsule()::getTablePrefix() . $model->getTable() . ' (' . $keys->implode(',') . ') values ';
        $inserts = [];
        foreach ($attributes as $data) {
            $qs = [];
            foreach ($data as $attrib => $value) {
                $qs[] = '?';
                if ($attrib == 'title') {
                    $bindings[] = substr($value, 0, 100);
                } else {
                    $bindings[] = $value;
                }
            }

            $inserts[] = '(' . implode(',', $qs) . ')';
        }
        $query .= implode(',', $inserts);

        if ($upsert) {
            $query .= ' ON DUPLICATE KEY UPDATE ';
            foreach ($keys as $i => $key) {
                $query .= " $key = VALUES($key) ";
                if ($i < count($keys) - 1) {
                    $query .= ',';
                }
            }
            $query .= ';';
        }
        $connec = Database::capsule()::connection($model->getConnectionName());
        $rowsInserted = $connec->affectingStatement($query, $bindings);

        $model->fireModelEvent('saved', false);

        return $rowsInserted;
    }
}
