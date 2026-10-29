<?php

namespace App\Mcp\Tools;

use App\Models\Ledger;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;

#[Name('list_ledgers')]
#[Description('Retrieve a paginated list of ledgers with optional filtering by ledger type, ledger account, status, or search query.')]
class ListLedgersTool extends Tool
{
    protected string $name = 'list_ledgers';

    public function schema(JsonSchema $schema): array
    {
        return [
            'ledger_type_id' => $schema->integer()
                ->description('Filter by ledger type ID.'),
            'ledger_account_id' => $schema->integer()
                ->description('Filter ledgers belonging to a specific ledger account ID.'),
            'status' => $schema->string()
                ->enum(['PEND', 'APRV', 'REJC', 'RDEL', 'CLSD'])
                ->description('Filter by ledger status: PEND, APRV, REJC, RDEL, CLSD.'),
            'query' => $schema->string()
                ->description('Search query matching ledger name.'),
            'limit' => $schema->integer()
                ->min(1)
                ->max(100)
                ->default(15)
                ->description('Maximum records to return per page (default: 15).'),
            'page' => $schema->integer()
                ->min(1)
                ->default(1)
                ->description('Page number for pagination (default: 1).'),
        ];
    }

    public function handle(Request $request): Response
    {
        $limit = (int) $request->get('limit', 15);
        $page = (int) $request->get('page', 1);
        $ledgerTypeId = $request->get('ledger_type_id');
        $ledgerAccountId = $request->get('ledger_account_id');
        $status = $request->get('status');
        $query = $request->get('query');

        $builder = Ledger::with(['Account', 'LedgerType']);

        if (!empty($ledgerTypeId)) {
            $builder->where('ledger_type_id', $ledgerTypeId);
        }

        if (!empty($ledgerAccountId)) {
            $builder->where('ledger_account_id', $ledgerAccountId);
        }

        if (!empty($status)) {
            $builder->where('status', $status);
        }

        if (!empty($query)) {
            $builder->where('name', 'LIKE', "%{$query}%");
        }

        $total = $builder->count();
        $offset = ($page - 1) * $limit;
        $ledgers = $builder->orderBy('id', 'DESC')->skip($offset)->take($limit)->get();

        return Response::json([
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'count' => $ledgers->count(),
            'data' => $ledgers,
        ]);
    }
}
