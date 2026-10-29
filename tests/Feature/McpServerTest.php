<?php

use App\Models\Alert;
use App\Models\Employee;
use App\Models\Ledger;
use App\Models\LedgerAccount;
use App\Models\LedgerType;
use App\Models\User;
use App\Mcp\Servers\AdminHrServer;
use App\Mcp\Tools\ListEmployeesTool;
use App\Mcp\Tools\GetEmployeeTool;
use App\Mcp\Tools\ListLedgerAccountsTool;
use App\Mcp\Tools\GetLedgerAccountTool;
use App\Mcp\Tools\ListLedgersTool;
use App\Mcp\Tools\GetLedgerTool;
use App\Mcp\Tools\ListAlertsTool;
use App\Mcp\Tools\GetAlertTool;

it('requires authentication for MCP server endpoint', function () {
    $response = $this->postJson('/api/mcp', [
        'jsonrpc' => '2.0',
        'id' => 1,
        'method' => 'tools/list',
    ]);

    $response->assertStatus(401);
});

it('lists all 8 registered read-only tools on AdminHrServer with their schemas and filters', function () {
    $tools = AdminHrServer::tools();

    $tools->assertRegistered([
        ListEmployeesTool::class,
        GetEmployeeTool::class,
        ListLedgerAccountsTool::class,
        GetLedgerAccountTool::class,
        ListLedgersTool::class,
        GetLedgerTool::class,
        ListAlertsTool::class,
        GetAlertTool::class,
    ]);

    // Verify list_employees schema includes employment_status
    $listEmployees = app(ListEmployeesTool::class);
    $employeesSchema = $listEmployees->toArray()['inputSchema']['properties'];
    expect($employeesSchema)->toHaveKey('employment_status');
    expect($employeesSchema)->toHaveKey('query');
    expect($employeesSchema)->toHaveKey('division_id');
    expect($employeesSchema)->toHaveKey('department_id');

    // Verify list_ledger_accounts schema includes employee_id
    $listAccounts = app(ListLedgerAccountsTool::class);
    $accountsSchema = $listAccounts->toArray()['inputSchema']['properties'];
    expect($accountsSchema)->toHaveKey('employee_id');

    // Verify list_ledgers schema includes ledger_type_id
    $listLedgers = app(ListLedgersTool::class);
    $ledgersSchema = $listLedgers->toArray()['inputSchema']['properties'];
    expect($ledgersSchema)->toHaveKey('ledger_type_id');
});

it('filters employees by employment_status and query in list_employees tool', function () {
    $user = User::factory()->create();

    $empRegular = new Employee();
    $empRegular->prefix = 'Mr';
    $empRegular->firstname = 'John';
    $empRegular->middlename = 'A';
    $empRegular->lastname = 'Doe';
    $empRegular->suffix = '';
    $empRegular->email = 'john.doe@example.com';
    $empRegular->mobile_no = '09123456789';
    $empRegular->birthdate = '1990-01-01';
    $empRegular->gender = 'M';
    $empRegular->marital_status = 'SING';
    $empRegular->current_address = 'Address';
    $empRegular->permanent_address = 'Address';
    $empRegular->educational_attainment = 'COL';
    $empRegular->employment_status = 'REGU';
    $empRegular->duty_status = 'ONDU';
    $empRegular->employment_start_date = '2020-01-01';
    $empRegular->created_by = $user->id;
    $empRegular->save();

    $empProbation = new Employee();
    $empProbation->prefix = 'Ms';
    $empProbation->firstname = 'Jane';
    $empProbation->middlename = 'B';
    $empProbation->lastname = 'Smith';
    $empProbation->suffix = '';
    $empProbation->email = 'jane.smith@example.com';
    $empProbation->mobile_no = '09123456788';
    $empProbation->birthdate = '1992-02-02';
    $empProbation->gender = 'F';
    $empProbation->marital_status = 'SING';
    $empProbation->current_address = 'Address';
    $empProbation->permanent_address = 'Address';
    $empProbation->educational_attainment = 'COL';
    $empProbation->employment_status = 'PROB';
    $empProbation->duty_status = 'ONDU';
    $empProbation->employment_start_date = '2025-01-01';
    $empProbation->created_by = $user->id;
    $empProbation->save();

    // 1. Filter by employment_status = 'REGU'
    $response = AdminHrServer::tool(ListEmployeesTool::class, [
        'employment_status' => 'REGU',
    ]);
    $response->assertOk()
        ->assertSee('John')
        ->assertDontSee('Jane');

    // 2. Filter by employment_status = 'PROB'
    $responseProb = AdminHrServer::tool(ListEmployeesTool::class, [
        'employment_status' => 'PROB',
    ]);
    $responseProb->assertOk()
        ->assertSee('Jane')
        ->assertDontSee('John');

    // 3. Search by query = 'Jane'
    $responseQuery = AdminHrServer::tool(ListEmployeesTool::class, [
        'query' => 'Jane',
    ]);
    $responseQuery->assertOk()
        ->assertSee('Smith')
        ->assertDontSee('John');
});

it('retrieves employee profile by ID using get_employee tool', function () {
    $user = User::factory()->create();

    $emp = new Employee();
    $emp->prefix = 'Mr';
    $emp->firstname = 'Alice';
    $emp->middlename = 'C';
    $emp->lastname = 'Wonder';
    $emp->suffix = '';
    $emp->email = 'alice@example.com';
    $emp->mobile_no = '09123456787';
    $emp->birthdate = '1995-05-05';
    $emp->gender = 'F';
    $emp->marital_status = 'SING';
    $emp->current_address = 'Address';
    $emp->permanent_address = 'Address';
    $emp->educational_attainment = 'COL';
    $emp->employment_status = 'REGU';
    $emp->duty_status = 'ONDU';
    $emp->employment_start_date = '2021-01-01';
    $emp->created_by = $user->id;
    $emp->save();

    $response = AdminHrServer::tool(GetEmployeeTool::class, [
        'id' => $emp->id,
    ]);
    $response->assertOk()
        ->assertSee('Alice')
        ->assertSee('Wonder');

    // Not found test
    $notFoundResponse = AdminHrServer::tool(GetEmployeeTool::class, [
        'id' => 999999,
    ]);
    $notFoundResponse->assertHasErrors([
        'Employee not found with ID 999999.',
    ]);
});

it('filters ledger accounts by employee_id using list_ledger_accounts tool', function () {
    $user = User::factory()->create();

    $empA = new Employee();
    $empA->prefix = 'Mr';
    $empA->firstname = 'EmpA';
    $empA->middlename = '';
    $empA->lastname = 'One';
    $empA->suffix = '';
    $empA->email = 'empa@example.com';
    $empA->mobile_no = '09123456786';
    $empA->birthdate = '1990-01-01';
    $empA->gender = 'M';
    $empA->marital_status = 'SING';
    $empA->current_address = 'Address';
    $empA->permanent_address = 'Address';
    $empA->educational_attainment = 'COL';
    $empA->employment_status = 'REGU';
    $empA->duty_status = 'ONDU';
    $empA->employment_start_date = '2020-01-01';
    $empA->created_by = $user->id;
    $empA->save();

    $empB = new Employee();
    $empB->prefix = 'Ms';
    $empB->firstname = 'EmpB';
    $empB->middlename = '';
    $empB->lastname = 'Two';
    $empB->suffix = '';
    $empB->email = 'empb@example.com';
    $empB->mobile_no = '09123456785';
    $empB->birthdate = '1991-01-01';
    $empB->gender = 'F';
    $empB->marital_status = 'SING';
    $empB->current_address = 'Address';
    $empB->permanent_address = 'Address';
    $empB->educational_attainment = 'COL';
    $empB->employment_status = 'REGU';
    $empB->duty_status = 'ONDU';
    $empB->employment_start_date = '2020-01-01';
    $empB->created_by = $user->id;
    $empB->save();

    $accA = new LedgerAccount();
    $accA->name = 'Account for EmpA';
    $accA->employee_id = $empA->id;
    $accA->status = 'APRV';
    $accA->created_by = $user->id;
    $accA->save();

    $accB = new LedgerAccount();
    $accB->name = 'Account for EmpB';
    $accB->employee_id = $empB->id;
    $accB->status = 'APRV';
    $accB->created_by = $user->id;
    $accB->save();

    // Filter by employee_id of EmpA
    $response = AdminHrServer::tool(ListLedgerAccountsTool::class, [
        'employee_id' => $empA->id,
    ]);
    $response->assertOk()
        ->assertSee('Account for EmpA')
        ->assertDontSee('Account for EmpB');

    // Get ledger account details
    $getAccResponse = AdminHrServer::tool(GetLedgerAccountTool::class, [
        'id' => $accA->id,
    ]);
    $getAccResponse->assertOk()
        ->assertSee('Account for EmpA');
});

it('filters ledgers by ledger_type_id and calculates balance in get_ledger tool', function () {
    $user = User::factory()->create();

    $acc = new LedgerAccount();
    $acc->name = 'Ledger Test Account';
    $acc->status = 'APRV';
    $acc->created_by = $user->id;
    $acc->save();

    $type1 = LedgerType::create(['text' => 'Cash Advance']);
    $type2 = LedgerType::create(['text' => 'Petty Cash']);

    $ledger1 = new Ledger();
    $ledger1->ledger_account_id = $acc->id;
    $ledger1->ledger_type_id = $type1->id;
    $ledger1->name = 'Cash Advance Ledger';
    $ledger1->description = 'Desc 1';
    $ledger1->template = 'Template 1';
    $ledger1->unit = 'PHP';
    $ledger1->status = 'APRV';
    $ledger1->created_by = $user->id;
    $ledger1->save();

    $ledger2 = new Ledger();
    $ledger2->ledger_account_id = $acc->id;
    $ledger2->ledger_type_id = $type2->id;
    $ledger2->name = 'Petty Cash Ledger';
    $ledger2->description = 'Desc 2';
    $ledger2->template = 'Template 2';
    $ledger2->unit = 'PHP';
    $ledger2->status = 'APRV';
    $ledger2->created_by = $user->id;
    $ledger2->save();

    // Filter by ledger_type_id = type1
    $response = AdminHrServer::tool(ListLedgersTool::class, [
        'ledger_type_id' => $type1->id,
    ]);
    $response->assertOk()
        ->assertSee('Cash Advance Ledger')
        ->assertDontSee('Petty Cash Ledger');

    // Get ledger tool test
    $getLedgerResponse = AdminHrServer::tool(GetLedgerTool::class, [
        'id' => $ledger1->id,
    ]);
    $getLedgerResponse->assertOk()
        ->assertSee('Cash Advance Ledger')
        ->assertSee('total_credit')
        ->assertSee('balance');
});

it('lists and retrieves alerts using list_alerts and get_alert tools', function () {
    $user = User::factory()->create();

    $emp = new Employee();
    $emp->prefix = 'Mr';
    $emp->firstname = 'AlertTarget';
    $emp->middlename = '';
    $emp->lastname = 'User';
    $emp->suffix = '';
    $emp->email = 'alert@example.com';
    $emp->mobile_no = '09123456784';
    $emp->birthdate = '1990-01-01';
    $emp->gender = 'M';
    $emp->marital_status = 'SING';
    $emp->current_address = 'Address';
    $emp->permanent_address = 'Address';
    $emp->educational_attainment = 'COL';
    $emp->employment_status = 'REGU';
    $emp->duty_status = 'ONDU';
    $emp->employment_start_date = '2020-01-01';
    $emp->created_by = $user->id;
    $emp->save();

    $alert = new Alert();
    $alert->employee_id = $emp->id;
    $alert->title = 'Passport Expiring';
    $alert->document_type = 'Passport';
    $alert->description = 'Passport expires soon';
    $alert->expiry_date = '2026-12-31';
    $alert->status = 'active';
    $alert->created_by = $user->id;
    $alert->save();

    // List alerts by employee_id
    $listResponse = AdminHrServer::tool(ListAlertsTool::class, [
        'employee_id' => $emp->id,
    ]);
    $listResponse->assertOk()
        ->assertSee('Passport Expiring');

    // Get alert by id
    $getResponse = AdminHrServer::tool(GetAlertTool::class, [
        'id' => $alert->id,
    ]);
    $getResponse->assertOk()
        ->assertSee('Passport Expiring');
});

it('processes authenticated JSON-RPC 2.0 tool call over HTTP POST /api/mcp', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/mcp', [
        'jsonrpc' => '2.0',
        'id' => 101,
        'method' => 'tools/list',
    ]);

    $response->assertStatus(200);
    $response->assertJsonPath('jsonrpc', '2.0');
    $response->assertJsonPath('id', 101);
    $response->assertJsonStructure([
        'result' => [
            'tools',
        ],
    ]);
});
