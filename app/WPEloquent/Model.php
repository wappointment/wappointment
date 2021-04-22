<?php

namespace Wappointment\WPEloquent;

use Illuminate\Database\Eloquent\Model as Eloquent;

/**
 * Model Class
 *
 * @package WeDevs\ERP\Framework
 */
abstract class Model extends Eloquent
{

    /**
     * @param array $attributes
     */
    public function __construct(array $attributes = array())
    {
        static::$resolver = new Resolver();

        parent::__construct($attributes);
    }

    /**
     * Get the database connection for the model.
     *
     * @return Database
     */
    public function getConnection()
    {
        return Database::instance();
    }

    /**
     * Get the table associated with the model.
     *
     * Append the WordPress table prefix with the table name if
     * no table name is provided
     *
     * @return string
     */
    public function getTable()
    {
        if (isset($this->table)) {
            return $this->getConnection()->db->prefix . $this->table;
        }

        $table = str_replace('\\', '', snake_case(str_plural(class_basename($this))));

        return $this->getConnection()->db->prefix . $table;
    }


    public function belongsToMany(
        $related,
        $table = null,
        $foreignPivotKey = null,
        $relatedPivotKey = null,
        $parentKey = null,
        $relatedKey = null,
        $relation = null
    ) {
        $table = $this->getConnection()->db->prefix . $table;
        return parent::belongsToMany(
            $related,
            $table,
            $foreignPivotKey,
            $relatedPivotKey,
            $parentKey,
            $relatedKey,
            $relation
        );
    }
    /**
     * Get a new query builder instance for the connection.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    protected function newBaseQueryBuilder()
    {

        $connection = $this->getConnection();

        return new Builder(
            $connection,
            $connection->getQueryGrammar(),
            $connection->getPostProcessor()
        );
    }
}
