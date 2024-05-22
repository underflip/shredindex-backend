<?php

namespace nocio\headstart\classes;

use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Support\Facades\Log;

class ConnectionResolver implements ConnectionResolverInterface
{

    public function connection($name = null)
    {
        Log::info($name);
    }

    public function getDefaultConnection()
    {
        return 'mysql';
    }

    public function setDefaultConnection($name)
    {
        Log::info($name);
    }
}
