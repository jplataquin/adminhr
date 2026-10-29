<?php

namespace App\Mcp\Tools;

use App\Models\Alert;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;

#[Name('get_alert')]
#[Description('Retrieve details of a specific alert by ID.')]
class GetAlertTool extends Tool
{
    protected string $name = 'get_alert';

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()
                ->description('The alert ID.')
                ->required(),
        ];
    }

    public function handle(Request $request): Response
    {
        $id = (int) $request->get('id');

        $alert = Alert::with('employee')->find($id);

        if (!$alert) {
            return Response::error("Alert not found with ID {$id}.");
        }

        return Response::json($alert);
    }
}
