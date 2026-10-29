<?php

namespace App\Mcp\Servers;

use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;
use App\Mcp\Tools\ListEmployeesTool;
use App\Mcp\Tools\GetEmployeeTool;
use App\Mcp\Tools\ListLedgerAccountsTool;
use App\Mcp\Tools\GetLedgerAccountTool;
use App\Mcp\Tools\ListLedgersTool;
use App\Mcp\Tools\GetLedgerTool;
use App\Mcp\Tools\ListAlertsTool;
use App\Mcp\Tools\GetAlertTool;

#[Name('AdminHR MCP Server')]
#[Version('1.0.0')]
#[Instructions('Provides read-only access to organizational records including employees, ledger accounts, ledgers, and alerts.')]
class AdminHrServer extends Server
{
    protected array $tools = [
        ListEmployeesTool::class,
        GetEmployeeTool::class,
        ListLedgerAccountsTool::class,
        GetLedgerAccountTool::class,
        ListLedgersTool::class,
        GetLedgerTool::class,
        ListAlertsTool::class,
        GetAlertTool::class,
    ];

    protected array $resources = [];

    protected array $prompts = [];
}
