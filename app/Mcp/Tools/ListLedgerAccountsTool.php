<?php

namespace App\Mcp\Tools;

use App\Models\LedgerAccount;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;

#[Name('list_ledger_accounts')]
#[Description('Retrieve a paginated list of ledger accounts with optional filtering by linked employee, status, or search query.')]
class ListLedgerAccountsTool extends Tool
{
    protected string $name = 'list_ledger_accounts';

    public function schema(JsonSchema $schema): array
    {
        return [
            'employee_id' => $schema->integer()
                ->description('Filter ledger accounts linked to a specific employee ID.'),
            'query' => $schema->string()
                ->description('Search query matching ledger account name.'),
            'status' => $schema->string()
                ->enum(['PEND', 'APRV', 'REJC', 'RDEL'])
                ->description('Filter by ledger account status: PEND (Pending), APRV (Approved), REJC (Rejected), RDEL (Request Delete).'),
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
        $employeeId = $request->get('employee_id');
        $query = $request->get('query');
        $status = $request->get('status');

        $builder = LedgerAccount::with('Employee');

        if (!empty($employeeId)) {
            $builder->where('employee_id', $employeeId);
        }

        if (!empty($status)) {
            $builder->where('status', $status);
        }

        if (!empty($query)) {
            $builder->where('name', 'LIKE', "%{$query}%");
        }

        $total = $builder->count();
        $offset = ($page - 1) * $limit;
        $accounts = $builder->orderBy('id', 'DESC')->skip($offset)->take($limit)->get();

        return Response::json([
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'count' => $accounts->count(),
            'data' => $accounts,
        ]);
    }
}
