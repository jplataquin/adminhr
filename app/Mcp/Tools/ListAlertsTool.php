<?php

namespace App\Mcp\Tools;

use App\Models\Alert;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;

#[Name('list_alerts')]
#[Description('Retrieve a paginated list of employee document alerts with optional filtering by status, document type, or employee ID.')]
class ListAlertsTool extends Tool
{
    protected string $name = 'list_alerts';

    public function schema(JsonSchema $schema): array
    {
        return [
            'status' => $schema->string()
                ->description('Filter by alert status (e.g. active, pending, resolved).'),
            'type' => $schema->string()
                ->description('Filter by document type name.'),
            'employee_id' => $schema->integer()
                ->description('Filter alerts for a specific employee ID.'),
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
        $status = $request->get('status');
        $type = $request->get('type');
        $employeeId = $request->get('employee_id');

        $builder = Alert::with('employee');

        if (!empty($status)) {
            $builder->where('status', $status);
        }

        if (!empty($type)) {
            $builder->where('document_type', $type);
        }

        if (!empty($employeeId)) {
            $builder->where('employee_id', $employeeId);
        }

        $total = $builder->count();
        $offset = ($page - 1) * $limit;
        $alerts = $builder->orderBy('id', 'DESC')->skip($offset)->take($limit)->get();

        return Response::json([
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'count' => $alerts->count(),
            'data' => $alerts,
        ]);
    }
}
