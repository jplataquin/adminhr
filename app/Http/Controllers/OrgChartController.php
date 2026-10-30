<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Division;
use App\Models\Employee;
use App\Models\Position;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrgChartController extends Controller
{
    /**
     * Display the organization chart page.
     */
    public function index()
    {
        $stats = [
            'total_employees'   => Employee::count(),
            'total_divisions'   => Division::count(),
            'total_departments' => Department::count(),
            'total_positions'   => Position::count(),
        ];

        $reportingTree = $this->buildReportingTree();
        $departmentTree = $this->buildDepartmentTree();

        return view('org_chart/index', [
            'stats'          => $stats,
            'reportingTree'  => $reportingTree,
            'departmentTree' => $departmentTree,
        ]);
    }

    /**
     * Return organization chart data as JSON.
     */
    public function data(): JsonResponse
    {
        $stats = [
            'total_employees'   => Employee::count(),
            'total_divisions'   => Division::count(),
            'total_departments' => Department::count(),
            'total_positions'   => Position::count(),
        ];

        return response()->json([
            'status' => 1,
            'data'   => [
                'stats'           => $stats,
                'reporting_tree'  => $this->buildReportingTree(),
                'department_tree' => $this->buildDepartmentTree(),
            ],
        ]);
    }

    /**
     * Build the hierarchical supervisor reporting tree.
     */
    public function buildReportingTree(): array
    {
        $employees = Employee::with(['division', 'department', 'position'])
            ->orderBy('firstname')
            ->orderBy('lastname')
            ->get();

        if ($employees->isEmpty()) {
            return [];
        }

        $nodes = [];
        $childrenMap = [];

        foreach ($employees as $emp) {
            $fullName = trim(implode(' ', array_filter([
                $emp->prefix,
                $emp->firstname,
                $emp->middlename,
                $emp->lastname,
                $emp->suffix,
            ])));

            $nodes[$emp->id] = [
                'id'                 => $emp->id,
                'formatted_id'       => str_pad((string) $emp->id, 6, '0', STR_PAD_LEFT),
                'name'               => $fullName ?: 'Unnamed Employee',
                'firstname'          => $emp->firstname,
                'lastname'           => $emp->lastname,
                'photo'              => $emp->photo,
                'photo_url'          => $emp->photo ? url('/employee/photo/' . $emp->photo) : null,
                'supervisor_id'      => $emp->supervisor_employee_id,
                'position'           => $emp->position?->name ?? 'Unassigned Position',
                'department'         => $emp->department?->name ?? 'Unassigned Department',
                'division'           => $emp->division?->name ?? 'Unassigned Division',
                'employment_status'  => $emp->employment_status,
                'duty_status'        => $emp->duty_status,
                'email'              => $emp->email,
                'mobile_no'          => $emp->mobile_no,
                'subordinates'       => [],
                'direct_reports'     => 0,
                'total_subordinates' => 0,
            ];

            $supId = $emp->supervisor_employee_id ?: 0;
            $childrenMap[$supId][] = $emp->id;
        }

        // Identify root nodes:
        // 1. Employees without a supervisor (supervisor_id null or 0)
        // 2. Employees whose supervisor does not exist in active employees (e.g. deleted supervisor)
        $rootIds = [];
        foreach ($nodes as $id => $node) {
            $supId = $node['supervisor_id'];
            if (!$supId || !isset($nodes[$supId])) {
                $rootIds[] = $id;
            }
        }

        // Guard against cycles: collect visited nodes
        $visited = [];
        $tree = [];

        foreach ($rootIds as $rootId) {
            if (!isset($visited[$rootId])) {
                $tree[] = $this->attachSubordinates($rootId, $nodes, $childrenMap, $visited);
            }
        }

        // If any employees remain unvisited due to isolated cycles, attach them to roots
        foreach ($nodes as $id => $node) {
            if (!isset($visited[$id])) {
                $tree[] = $this->attachSubordinates($id, $nodes, $childrenMap, $visited);
            }
        }

        return $tree;
    }

    /**
     * Recursively attach subordinates to a node.
     */
    protected function attachSubordinates(int $id, array &$nodes, array &$childrenMap, array &$visited): array
    {
        $visited[$id] = true;
        $node = $nodes[$id];
        $children = $childrenMap[$id] ?? [];
        $subordinates = [];
        $totalCount = 0;

        foreach ($children as $childId) {
            if (!isset($visited[$childId]) && isset($nodes[$childId])) {
                $childNode = $this->attachSubordinates($childId, $nodes, $childrenMap, $visited);
                $subordinates[] = $childNode;
                $totalCount += 1 + $childNode['total_subordinates'];
            }
        }

        $node['subordinates'] = $subordinates;
        $node['direct_reports'] = count($subordinates);
        $node['total_subordinates'] = $totalCount;

        return $node;
    }

    /**
     * Build the organizational hierarchy tree: Division -> Department -> Employee.
     */
    public function buildDepartmentTree(): array
    {
        $divisions = Division::with([
            'departments.employees' => function ($q) {
                $q->orderBy('firstname')->orderBy('lastname');
            },
            'departments.employees.position',
        ])->orderBy('name')->get();

        $tree = [];

        foreach ($divisions as $division) {
            $divTotalEmployees = 0;
            $deptList = [];

            foreach ($division->departments as $department) {
                $empList = [];
                foreach ($department->employees as $emp) {
                    $statusKey = $emp->employment_status;
                    $statusLabel = match($statusKey) {
                        'REGU' => 'Regular',
                        'PROB' => 'Probationary',
                        'CONT' => 'Contractual',
                        'RSGN' => 'Resigned',
                        'LAOF' => 'Laid Off',
                        'AWOL' => 'AWOL',
                        'TRMN' => 'Terminated',
                        'RETR' => 'Retired',
                        'DECE' => 'Deceased',
                        default => $statusKey,
                    };

                    $fullName = trim(implode(' ', array_filter([
                        $emp->prefix,
                        $emp->firstname,
                        $emp->middlename,
                        $emp->lastname,
                        $emp->suffix,
                    ])));

                    $empList[] = [
                        'id'                      => $emp->id,
                        'formatted_id'            => str_pad((string) $emp->id, 6, '0', STR_PAD_LEFT),
                        'name'                    => $fullName ?: 'Unnamed Employee',
                        'firstname'               => $emp->firstname,
                        'lastname'                => $emp->lastname,
                        'employment_status'       => $emp->employment_status,
                        'employment_status_label' => $statusLabel,
                        'position'                => $emp->position?->name ?? 'Unassigned Position',
                        'department'              => $department->name,
                        'division'                => $division->name,
                        'photo'                   => $emp->photo,
                        'photo_url'               => $emp->photo ? url('/employee/photo/' . $emp->photo) : null,
                        'duty_status'             => $emp->duty_status,
                        'email'                   => $emp->email,
                        'mobile_no'               => $emp->mobile_no,
                    ];
                }

                $posCount = count($empList);
                $divTotalEmployees += $posCount;

                $deptList[] = [
                    'id'              => $department->id,
                    'name'            => $department->name,
                    'code'            => $department->code,
                    'employees'       => $empList,
                    'employees_count' => $posCount,
                ];
            }

            $tree[] = [
                'id'              => $division->id,
                'name'            => $division->name,
                'code'            => $division->code,
                'departments'     => $deptList,
                'employees_count' => $divTotalEmployees,
            ];
        }

        // Also check if there are unassigned employees (division_id is null or department_id is null)
        $unassigned = Employee::where(function ($q) {
            $q->whereNull('division_id')->orWhereNull('department_id');
        })->with(['position', 'division', 'department'])->orderBy('firstname')->get();

        if ($unassigned->isNotEmpty()) {
            $unassignedList = [];
            foreach ($unassigned as $emp) {
                $statusKey = $emp->employment_status;
                $statusLabel = match($statusKey) {
                    'REGU' => 'Regular',
                    'PROB' => 'Probationary',
                    'CONT' => 'Contractual',
                    'RSGN' => 'Resigned',
                    'LAOF' => 'Laid Off',
                    'AWOL' => 'AWOL',
                    'TRMN' => 'Terminated',
                    'RETR' => 'Retired',
                    'DECE' => 'Deceased',
                    default => $statusKey,
                };

                $fullName = trim(implode(' ', array_filter([
                    $emp->prefix,
                    $emp->firstname,
                    $emp->middlename,
                    $emp->lastname,
                    $emp->suffix,
                ])));

                $unassignedList[] = [
                    'id'                      => $emp->id,
                    'formatted_id'            => str_pad((string) $emp->id, 6, '0', STR_PAD_LEFT),
                    'name'                    => $fullName ?: 'Unnamed Employee',
                    'firstname'               => $emp->firstname,
                    'lastname'                => $emp->lastname,
                    'employment_status'       => $emp->employment_status,
                    'employment_status_label' => $statusLabel,
                    'position'                => $emp->position?->name ?? 'Unassigned Position',
                    'department'              => 'Unassigned',
                    'division'                => 'Unassigned',
                    'photo'                   => $emp->photo,
                    'photo_url'               => $emp->photo ? url('/employee/photo/' . $emp->photo) : null,
                    'duty_status'             => $emp->duty_status,
                    'email'                   => $emp->email,
                    'mobile_no'               => $emp->mobile_no,
                ];
            }

            $tree[] = [
                'id'              => 0,
                'name'            => 'Unassigned Division / Department',
                'code'            => 'UNASSIGNED',
                'departments'     => [
                    [
                        'id'              => 0,
                        'name'            => 'General / Unassigned',
                        'code'            => 'UNASSIGNED',
                        'employees'       => $unassignedList,
                        'employees_count' => count($unassignedList),
                    ],
                ],
                'employees_count' => count($unassignedList),
            ];
        }

        return $tree;
    }
}
