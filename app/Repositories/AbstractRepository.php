<?php

namespace Wappointment\Repositories;

abstract class AbstractRepository implements RepositoryInterface
{
    public $cache_key = '';
    public $expiration = 0;

    public function get()
    {
        $cached_result = get_transient($this->getCacheKey());
        return empty($cached_result) ? $this->cache() : $cached_result;
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
