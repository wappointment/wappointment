<?php

namespace Wappointment\Controllers;

class PingController
{
    public function ping()
    {
        return true;
    }

    public function pingEditor()
    {
        return 'correct response';
    }

    public function pingAuthor()
    {
        return true;
    }

    public function pingAdmin()
    {
        return true;
    }
}
