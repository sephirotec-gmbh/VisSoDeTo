<?php

namespace App\Mcp\Servers;

use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('ViSoDeTo')]
#[Version('0.0.1')]
#[Instructions('Read and update ViSoDeTo project graphs, requirements, decisions, tasks, and implementation evidence. Prefer read-only context tools before proposing changes.')]
class ViSoDeToServer extends Server
{
    protected array $tools = [
        //
    ];

    protected array $resources = [
        //
    ];

    protected array $prompts = [
        //
    ];
}
