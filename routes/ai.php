<?php

use App\Mcp\Servers\ViSoDeToServer;
use Laravel\Mcp\Facades\Mcp;

Mcp::local('visodeto', ViSoDeToServer::class);
