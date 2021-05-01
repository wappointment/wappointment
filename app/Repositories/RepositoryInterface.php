<?php

namespace Wappointment\Repositories;

interface RepositoryInterface
{
    public function get();
    public function cache();
    public function clear();
    public function refresh();
    public function query();
}
