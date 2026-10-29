<?php

namespace App\Mcp\Tools;

use App\Models\Employee;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;

#[Name('get_employee')]
#[Description('Retrieve detailed profile information for a specific employee by ID.')]
class GetEmployeeTool extends Tool
{
    protected string $name = 'get_employee';

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()
                ->description('The employee ID.')
                ->required(),
        ];
    }

    public function handle(Request $request): Response
    {
        $id = (int) $request->get('id');

        $employee = Employee::with(['division', 'department', 'position'])->find($id);

        if (!$employee) {
            return Response::error("Employee not found with ID {$id}.");
        }

        return Response::json($employee);
    }
}
