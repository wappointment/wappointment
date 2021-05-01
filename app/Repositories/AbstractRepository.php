<?php

namespace Wappointment\Repositories;

use Wappointment\Services\Flag;

abstract class AbstractRepository implements RepositoryInterface
{
    public $cache_key = '';
    public $expiration = 0;

    public function get()
    {
        $cached_result = get_transient($this->getCacheKey());
        return empty($cached_result) ? $this->init() : $cached_result;
    }

    /**
     * Initing the cache only supposed to run once
     *
     * @return void
     */
    protected function init()
    {
        $testFlag = 'cached_' . $this->getCacheKey();
        $cache = null;
        if (!Flag::get($testFlag)) {
            $cache = $this->cache();
            Flag::save($testFlag, true);
        }
        return $cache;
    }

    public function cache()
    {
        $data = $this->query();
        if (!empty($data)) {
            set_transient($this->getCacheKey(), $data, $this->expiration);
        }
        return $data;
    }

    public function clear()
    {
        delete_transient($this->getCacheKey());
    }

    public function refresh()
    {
        $this->clear();
        $this->cache();
    }

    private function getCacheKey()
    {
        return 'wappo_' . $this->cache_key;
    }
}
