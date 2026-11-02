<?php

use App\Models\Department;
use App\Models\Division;
use App\Models\Employee;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

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
    $response->assertSee('style="list-style-type: none;"', false);
    $response->assertSee('style="gap: 20px;"', false);
});

it('applies list-style-type none on lists and 20px gap on org icon and node name flex containers', function () {
    $division = Division::create(['name' => 'Operations Division', 'code' => 'OPS_' . uniqid()]);
    $department = Department::create(['division_id' => $division->id, 'name' => 'Logistics Unit', 'code' => 'LOG_' . uniqid()]);

    $response = $this->actingAs($this->user)->get('/organization-chart');

    $response->assertOk();
    $response->assertSee('style="list-style-type: none;"', false);
    $response->assertSee('style="gap: 20px;"', false);
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

it('successfully executes buildDepartmentTree without ordering on positions table', function () {
    $division = Division::create(['name' => 'Div Test', 'code' => 'DIV_' . uniqid()]);
    $dept = Department::create(['division_id' => $division->id, 'name' => 'Dept Test', 'code' => 'DEPT_' . uniqid()]);
    $pos1 = Position::create(['department_id' => $dept->id, 'name' => 'Pos A', 'code' => 'P1_' . uniqid()]);
    $pos2 = Position::create(['department_id' => $dept->id, 'name' => 'Pos B', 'code' => 'P2_' . uniqid()]);

    $emp1 = new Employee();
    $emp1->firstname = 'Zack';
    $emp1->lastname = 'Taylor';
    $emp1->birthdate = '1990-01-01';
    $emp1->gender = 'M';
    $emp1->religion = 'None';
    $emp1->marital_status = 'SING';
    $emp1->current_address = 'Address';
    $emp1->permanent_address = 'Address';
    $emp1->educational_attainment = 'BD';
    $emp1->division_id = $division->id;
    $emp1->department_id = $dept->id;
    $emp1->position_id = $pos1->id;
    $emp1->employment_status = 'REGU';
    $emp1->duty_status = 'ONDU';
    $emp1->employment_start_date = '2020-01-01';
    $emp1->created_by = $this->user->id;
    $emp1->save();

    $emp2 = new Employee();
    $emp2->firstname = 'Adam';
    $emp2->lastname = 'Park';
    $emp2->birthdate = '1992-02-02';
    $emp2->gender = 'M';
    $emp2->religion = 'None';
    $emp2->marital_status = 'SING';
    $emp2->current_address = 'Address';
    $emp2->permanent_address = 'Address';
    $emp2->educational_attainment = 'BD';
    $emp2->division_id = $division->id;
    $emp2->department_id = $dept->id;
    $emp2->position_id = $pos2->id;
    $emp2->employment_status = 'PROB';
    $emp2->duty_status = 'ONDU';
    $emp2->employment_start_date = '2021-01-01';
    $emp2->created_by = $this->user->id;
    $emp2->save();

    $controller = new \App\Http\Controllers\OrgChartController();
    $tree = $controller->buildDepartmentTree();

    $divNode = collect($tree)->firstWhere('name', 'Div Test');
    expect($divNode)->not->toBeNull();
    $deptNode = collect($divNode['departments'])->firstWhere('name', 'Dept Test');
    expect($deptNode)->not->toBeNull();
    expect(count($deptNode['employees']))->toBe(2);
    // Verified employees are ordered by firstname (Adam, then Zack)
    expect($deptNode['employees'][0]['name'])->toContain('Adam Park');
    expect($deptNode['employees'][0]['position'])->toBe('Pos B');
    expect($deptNode['employees'][1]['name'])->toContain('Zack Taylor');
    expect($deptNode['employees'][1]['position'])->toBe('Pos A');
});

it('places employees without department directly under their division node', function () {
    $division = Division::create(['name' => 'Corporate Division', 'code' => 'CORPDIV_' . uniqid()]);
    $dept = Department::create(['division_id' => $division->id, 'name' => 'Finance Dept', 'code' => 'FIN_' . uniqid()]);
    $pos1 = Position::create(['department_id' => $dept->id, 'name' => 'VP Corporate', 'code' => 'VP_' . uniqid()]);
    $pos2 = Position::create(['department_id' => $dept->id, 'name' => 'Accountant', 'code' => 'ACC_' . uniqid()]);

    // Employee 1: Direct in division, NO department assigned
    $directEmp = new Employee();
    $directEmp->firstname = 'Rachel';
    $directEmp->lastname = 'Green';
    $directEmp->birthdate = '1988-05-05';
    $directEmp->gender = 'F';
    $directEmp->religion = 'None';
    $directEmp->marital_status = 'SING';
    $directEmp->current_address = 'Address';
    $directEmp->permanent_address = 'Address';
    $directEmp->educational_attainment = 'BD';
    $directEmp->division_id = $division->id;
    $directEmp->department_id = null;
    $directEmp->position_id = $pos1->id;
    $directEmp->employment_status = 'REGU';
    $directEmp->duty_status = 'ONDU';
    $directEmp->employment_start_date = '2019-01-01';
    $directEmp->created_by = $this->user->id;
    $directEmp->save();

    // Employee 2: Assigned to Finance Dept
    $deptEmp = new Employee();
    $deptEmp->firstname = 'Monica';
    $deptEmp->lastname = 'Geller';
    $deptEmp->birthdate = '1989-06-06';
    $deptEmp->gender = 'F';
    $deptEmp->religion = 'None';
    $deptEmp->marital_status = 'SING';
    $deptEmp->current_address = 'Address';
    $deptEmp->permanent_address = 'Address';
    $deptEmp->educational_attainment = 'BD';
    $deptEmp->division_id = $division->id;
    $deptEmp->department_id = $dept->id;
    $deptEmp->position_id = $pos2->id;
    $deptEmp->employment_status = 'REGU';
    $deptEmp->duty_status = 'ONDU';
    $deptEmp->employment_start_date = '2020-02-02';
    $deptEmp->created_by = $this->user->id;
    $deptEmp->save();

    $controller = new \App\Http\Controllers\OrgChartController();
    $tree = $controller->buildDepartmentTree();

    $corpDiv = collect($tree)->firstWhere('name', 'Corporate Division');
    expect($corpDiv)->not->toBeNull();
    expect($corpDiv['employees_count'])->toBe(2);
    expect($corpDiv['direct_employees_count'])->toBe(1);
    expect(count($corpDiv['direct_employees']))->toBe(1);
    expect($corpDiv['direct_employees'][0]['name'])->toContain('Rachel Green');
    expect($corpDiv['direct_employees'][0]['position'])->toBe('VP Corporate');
    expect($corpDiv['direct_employees'][0]['department'])->toBe('None');

    $finDept = collect($corpDiv['departments'])->firstWhere('name', 'Finance Dept');
    expect($finDept)->not->toBeNull();
    expect(count($finDept['employees']))->toBe(1);
    expect($finDept['employees'][0]['name'])->toContain('Monica Geller');

    // Verify page render includes both and shows direct employees count badge
    $response = $this->actingAs($this->user)->get('/organization-chart');
    $response->assertOk();
    $response->assertSee('Rachel Green');
    $response->assertSee('Monica Geller');
    $response->assertSee('1 direct');

    // Direct employee under division appears before department employees
    $content = $response->getContent();
    $posRachel = strpos($content, 'Rachel Green');
    $posMonica = strpos($content, 'Monica Geller');
    expect($posRachel)->toBeLessThan($posMonica);
});

it('renders floating employee card markup and hover handlers on employee names', function () {
    $division = Division::create(['name' => 'HQ Operations', 'code' => 'HQ_' . uniqid()]);
    $dept = Department::create(['division_id' => $division->id, 'name' => 'HQ Dept', 'code' => 'HQD_' . uniqid()]);
    $pos = Position::create(['department_id' => $dept->id, 'name' => 'Coordinator', 'code' => 'COORD_' . uniqid()]);

    $emp = new Employee();
    $emp->firstname = 'Chandler';
    $emp->lastname = 'Bing';
    $emp->birthdate = '1987-04-08';
    $emp->gender = 'M';
    $emp->religion = 'None';
    $emp->marital_status = 'MARD';
    $emp->current_address = 'Address';
    $emp->permanent_address = 'Address';
    $emp->educational_attainment = 'BD';
    $emp->division_id = $division->id;
    $emp->department_id = null;
    $emp->position_id = $pos->id;
    $emp->employment_status = 'REGU';
    $emp->duty_status = 'ONDU';
    $emp->employment_start_date = '2018-05-01';
    $emp->created_by = $this->user->id;
    $emp->save();

    $response = $this->actingAs($this->user)->get('/organization-chart');
    $response->assertOk();
    $response->assertSee('employee-floating-card', false);
    $response->assertSee('showHoverCard', false);
    $response->assertSee('hideHoverCard', false);
    $response->assertSee('cancelHideHoverCard', false);
    $response->assertSee('Employment Status');
    $response->assertSee('Duty Status');
    $response->assertSee('Division');
    $response->assertSee('Department');
});
