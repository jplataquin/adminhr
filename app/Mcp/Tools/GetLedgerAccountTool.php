<?php

namespace App\Mcp\Tools;

use App\Models\LedgerAccount;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;

#[Name('get_ledger_account')]
#[Description('Retrieve details of a specific ledger account by ID.')]
class GetLedgerAccountTool extends Tool
{
    protected string $name = 'get_ledger_account';

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()
                ->description('The ledger account ID.')
                ->required(),
        ];
    }

    public function handle(Request $request): Response
    {
        $id = (int) $request->get('id');

        $account = LedgerAccount::with(['Employee', 'Ledgers.LedgerType'])->find($id);

        if (!$account) {
            return Response::error("Ledger Account not found with ID {$id}.");
        }

        return Response::json($account);
    }
}
