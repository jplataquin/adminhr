<?php

namespace App\Mcp\Tools;

use App\Models\Employee;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;

#[Name('list_employees')]
#[Description('Retrieve a paginated list of employees with optional filtering by employment status, division, department, or search query.')]
class ListEmployeesTool extends Tool
{
    protected string $name = 'list_employees';

    public function schema(JsonSchema $schema): array
    {
        return [
            'query' => $schema->string()
                ->description('Search query matching employee first, middle, or last name.'),
            'employment_status' => $schema->string()
                ->enum(['REGU', 'PROB', 'RSGN', 'LAOF', 'AWOL', 'TRMN', 'RETR', 'DECE'])
                ->description('Filter by employment status: REGU (Regular), PROB (Probation), RSGN (Resigned), LAOF (Laid Off), AWOL (AWOL), TRMN (Terminated), RETR (Retired), DECE (Deceased).'),
            'division_id' => $schema->integer()
                ->description('Filter by organizational division ID.'),
            'department_id' => $schema->integer()
                ->description('Filter by organizational department ID.'),
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
        $query = $request->get('query');
        $employmentStatus = $request->get('employment_status');
        $divisionId = $request->get('division_id');
        $departmentId = $request->get('department_id');

        $builder = Employee::with(['division', 'department', 'position']);

        if (!empty($query)) {
            $builder->where(function ($q) use ($query) {
                $q->where('firstname', 'LIKE', "%{$query}%")
                    ->orWhere('middlename', 'LIKE', "%{$query}%")
                    ->orWhere('lastname', 'LIKE', "%{$query}%");
            });
        }

        if (!empty($employmentStatus)) {
            $builder->where('employment_status', $employmentStatus);
        }

        if (!empty($divisionId)) {
            $builder->where('division_id', $divisionId);
        }

        if (!empty($departmentId)) {
            $builder->where('department_id', $departmentId);
        }

        $total = $builder->count();
        $offset = ($page - 1) * $limit;
        $employees = $builder->orderBy('id', 'ASC')->skip($offset)->take($limit)->get();

        return Response::json([
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'count' => $employees->count(),
            'data' => $employees,
        ]);
    }
}
