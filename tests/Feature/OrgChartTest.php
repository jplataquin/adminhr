<?php

use App\Models\Department;
use App\Models\Division;
use App\Models\Employee;
use App\Models\Position;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('requires authentication to access the organization chart', function () {
    $response = $this->get('/organization-chart');
    $response->assertRedirect('/login');
});

it('redirects /org-chart to /organization-chart', function () {
    $response = $this->actingAs($this->user)->get('/org-chart');
    $response->assertRedirect('/organization-chart');
});

it('renders the organization chart page with summary statistics and tree containers', function () {
    $division = Division::create(['name' => 'Executive Office', 'code' => 'EXECOF_' . uniqid()]);
    $department = Department::create(['division_id' => $division->id, 'name' => 'General Admin', 'code' => 'GENADM_' . uniqid()]);
    $position = Position::create(['department_id' => $department->id, 'name' => 'Director', 'code' => 'DIR_' . uniqid()]);

    // Create CEO (root supervisor)
    $ceo = new Employee();
    $ceo->firstname = 'Elena';
    $ceo->lastname = 'Reyes';
    $ceo->birthdate = '1980-01-01';
    $ceo->gender = 'F';
    $ceo->religion = 'None';
    $ceo->marital_status = 'SING';
    $ceo->current_address = 'Address';
    $ceo->permanent_address = 'Address';
    $ceo->educational_attainment = 'PG';
    $ceo->division_id = $division->id;
    $ceo->department_id = $department->id;
    $ceo->position_id = $position->id;
    $ceo->employment_status = 'REGU';
    $ceo->duty_status = 'ONDU';
    $ceo->employment_start_date = '2015-01-01';
    $ceo->created_by = $this->user->id;
    $ceo->supervisor_employee_id = null;
    $ceo->save();

    // Create Manager (reports to CEO)
    $manager = new Employee();
    $manager->firstname = 'Marcus';
    $manager->lastname = 'Vance';
    $manager->birthdate = '1985-05-15';
    $manager->gender = 'M';
    $manager->religion = 'None';
    $manager->marital_status = 'MARD';
    $manager->current_address = 'Address';
    $manager->permanent_address = 'Address';
    $manager->educational_attainment = 'BD';
    $manager->division_id = $division->id;
    $manager->department_id = $department->id;
    $manager->position_id = $position->id;
    $manager->employment_status = 'REGU';
    $manager->duty_status = 'ONDU';
    $manager->employment_start_date = '2018-03-01';
    $manager->created_by = $this->user->id;
    $manager->supervisor_employee_id = $ceo->id;
    $manager->save();

    // Create Staff (reports to Manager)
    $staff = new Employee();
    $staff->firstname = 'Chloe';
    $staff->lastname = 'Diaz';
    $staff->birthdate = '1998-09-20';
    $staff->gender = 'F';
    $staff->religion = 'None';
    $staff->marital_status = 'SING';
    $staff->current_address = 'Address';
    $staff->permanent_address = 'Address';
    $staff->educational_attainment = 'BD';
    $staff->division_id = $division->id;
    $staff->department_id = $department->id;
    $staff->position_id = $position->id;
    $staff->employment_status = 'PROB';
    $staff->duty_status = 'ONDU';
    $staff->employment_start_date = '2025-01-10';
    $staff->created_by = $this->user->id;
    $staff->supervisor_employee_id = $manager->id;
    $staff->save();

    $response = $this->actingAs($this->user)->get('/organization-chart');

    $response->assertOk();
    $response->assertSee('Organization Chart');
    $response->assertSee('Divisions &amp; Departments', false);
    $response->assertSee('Elena Reyes');
    $response->assertSee('Regular');
    $response->assertSee('Director');
    $response->assertSee('Marcus Vance');
    $response->assertSee('Chloe Diaz');
    $response->assertSee('Probationary');
    $response->assertSee('id="stat-total-employees"', false);
    $response->assertSee('id="reporting-root-tree"', false);
    $response->assertSee('id="department-root-tree"', false);
    $response->assertSee('id="expandAllBtn"', false);
    $response->assertSee('id="collapseAllBtn"', false);
    $response->assertSee('id="orgChartSearch"', false);
    $response->assertSee('bi-caret-right-fill');
    $response->assertSee('bi-caret-down-fill');
    $response->assertDontSee('org-leaf-bullet');
    $response->assertSee('openNodes: {}', false);
});

it('provides organization chart data through the API endpoint with Division -> Department -> Employee structure', function () {
    $division = Division::create(['name' => 'Technology Dev', 'code' => 'TECH_' . uniqid()]);
    $department = Department::create(['division_id' => $division->id, 'name' => 'Engineering Unit', 'code' => 'ENG_' . uniqid()]);
    $position = Position::create(['department_id' => $department->id, 'name' => 'Lead Engineer', 'code' => 'LEAD_' . uniqid()]);

    $lead = new Employee();
    $lead->firstname = 'David';
    $lead->lastname = 'Kim';
    $lead->birthdate = '1987-03-12';
    $lead->gender = 'M';
    $lead->religion = 'None';
    $lead->marital_status = 'SING';
    $lead->current_address = 'Address';
    $lead->permanent_address = 'Address';
    $lead->educational_attainment = 'BD';
    $lead->division_id = $division->id;
    $lead->department_id = $department->id;
    $lead->position_id = $position->id;
    $lead->employment_status = 'REGU';
    $lead->duty_status = 'ONDU';
    $lead->employment_start_date = '2020-01-01';
    $lead->created_by = $this->user->id;
    $lead->supervisor_employee_id = null;
    $lead->save();

    $dev = new Employee();
    $dev->firstname = 'Sarah';
    $dev->lastname = 'Connor';
    $dev->birthdate = '1992-06-18';
    $dev->gender = 'F';
    $dev->religion = 'None';
    $dev->marital_status = 'SING';
    $dev->current_address = 'Address';
    $dev->permanent_address = 'Address';
    $dev->educational_attainment = 'BD';
    $dev->division_id = $division->id;
    $dev->department_id = $department->id;
    $dev->position_id = $position->id;
    $dev->employment_status = 'CONT';
    $dev->duty_status = 'ONDU';
    $dev->employment_start_date = '2024-02-01';
    $dev->created_by = $this->user->id;
    $dev->supervisor_employee_id = $lead->id;
    $dev->save();

    $response = $this->actingAs($this->user)->getJson('/api/organization-chart/data');

    $response->assertOk();
    $response->assertJson([
        'status' => 1,
        'data' => [
            'stats' => [
                'total_employees' => 2,
            ],
        ],
    ]);

    $data = $response->json('data');
    expect($data)->toHaveKey('reporting_tree');
    expect($data)->toHaveKey('department_tree');

    // Test Division -> Department -> Employee hierarchy
    $deptTree = $data['department_tree'];
    $techDiv = collect($deptTree)->firstWhere('name', 'Technology Dev');
    expect($techDiv)->not->toBeNull();
    $engDept = collect($techDiv['departments'])->firstWhere('name', 'Engineering Unit');
    expect($engDept)->not->toBeNull();
    expect($engDept['employees'])->toBeArray();
    expect(count($engDept['employees']))->toBe(2);

    $sarah = collect($engDept['employees'])->firstWhere('firstname', 'Sarah');
    expect($sarah)->not->toBeNull();
    expect($sarah['name'])->toContain('Sarah Connor');
    expect($sarah['employment_status_label'])->toBe('Contractual');
    expect($sarah['position'])->toBe('Lead Engineer');

    // Root in reporting tree should be David Kim with Sarah Connor as subordinate
    $reportingTree = $data['reporting_tree'];
    expect(count($reportingTree))->toBe(1);
    expect($reportingTree[0]['name'])->toContain('David Kim');
    expect($reportingTree[0]['direct_reports'])->toBe(1);
    expect($reportingTree[0]['subordinates'][0]['name'])->toContain('Sarah Connor');
});

it('gracefully handles supervisor cycles without infinite recursion', function () {
    $division = Division::first() ?? Division::create(['name' => 'General', 'code' => 'GEN001']);
    $department = Department::first() ?? Department::create(['division_id' => $division->id, 'name' => 'Admin', 'code' => 'ADM001']);
    $position = Position::first() ?? Position::create(['department_id' => $department->id, 'name' => 'Officer', 'code' => 'OFF001']);

    $empA = new Employee();
    $empA->firstname = 'Alpha';
    $empA->lastname = 'User';
    $empA->birthdate = '1990-01-01';
    $empA->gender = 'M';
    $empA->religion = 'None';
    $empA->marital_status = 'SING';
    $empA->current_address = 'Address';
    $empA->permanent_address = 'Address';
    $empA->educational_attainment = 'BD';
    $empA->division_id = $division->id;
    $empA->department_id = $department->id;
    $empA->position_id = $position->id;
    $empA->employment_status = 'REGU';
    $empA->duty_status = 'ONDU';
    $empA->employment_start_date = '2020-01-01';
    $empA->created_by = $this->user->id;
    $empA->supervisor_employee_id = null;
    $empA->save();

    $empB = new Employee();
    $empB->firstname = 'Beta';
    $empB->lastname = 'User';
    $empB->birthdate = '1991-01-01';
    $empB->gender = 'F';
    $empB->religion = 'None';
    $empB->marital_status = 'SING';
    $empB->current_address = 'Address';
    $empB->permanent_address = 'Address';
    $empB->educational_attainment = 'BD';
    $empB->division_id = $division->id;
    $empB->department_id = $department->id;
    $empB->position_id = $position->id;
    $empB->employment_status = 'REGU';
    $empB->duty_status = 'ONDU';
    $empB->employment_start_date = '2020-01-01';
    $empB->created_by = $this->user->id;
    $empB->supervisor_employee_id = $empA->id;
    $empB->save();

    // Now introduce a cycle: A reports to B while B reports to A
    $empA->supervisor_employee_id = $empB->id;
    $empA->save();

    $controller = new \App\Http\Controllers\OrgChartController();
    $tree = $controller->buildReportingTree();

    expect(count($tree))->toBeGreaterThanOrEqual(1);
    // Ensure both employees are present without crashing or infinite recursion
    $allIds = [];
    $collectIds = function ($node) use (&$collectIds, &$allIds) {
        $allIds[] = $node['id'];
        foreach ($node['subordinates'] as $child) {
            $collectIds($child);
        }
    };
    foreach ($tree as $root) {
        $collectIds($root);
    }

    expect($allIds)->toContain($empA->id);
    expect($allIds)->toContain($empB->id);
});
