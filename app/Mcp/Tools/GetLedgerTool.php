<?php

namespace App\Mcp\Tools;

use App\Models\Ledger;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;

#[Name('get_ledger')]
#[Description('Retrieve details of a specific ledger by ID, including balance, totals, and approved entries.')]
class GetLedgerTool extends Tool
{
    protected string $name = 'get_ledger';

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()
                ->description('The ledger ID.')
                ->required(),
        ];
    }

    public function handle(Request $request): Response
    {
        $id = (int) $request->get('id');

        $ledger = Ledger::with(['Account', 'LedgerType', 'Entries'])->find($id);

        if (!$ledger) {
            return Response::error("Ledger not found with ID {$id}.");
        }

        $totalCredit = (float) $ledger->getTotalCredit(['APRV']);
        $totalDebit = (float) $ledger->getTotalDebit(['APRV']);
        $balance = $totalCredit - $totalDebit;
        $totalQuantity = (float) $ledger->getTotalQuantity(['APRV']);

        return Response::json([
            'ledger' => $ledger,
            'totals' => [
                'total_credit' => $totalCredit,
                'total_debit' => $totalDebit,
                'balance' => $balance,
                'total_quantity' => $totalQuantity,
            ],
        ]);
    }
}
