<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Division;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::with('division')->paginate(15);
        return view('admin.departments.index', compact('departments'));
    }

    public function create()
    {
        $divisions = Division::all();
        return view('admin.departments.create', compact('divisions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'division_id' => 'required|exists:divisions,id',
            'name' => 'required|string|max:255',
            'code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('departments')->where(function ($query) use ($request) {
                    return $query->where('division_id', $request->division_id)->whereNull('deleted_at');
                }),
            ],
        ]);

        Department::create([
            'division_id' => $request->division_id,
            'name' => $request->name,
            'code' => strtoupper($request->code),
        ]);

        return redirect()->route('admin.departments.index')->with('success', 'Department created successfully.');
    }

    public function edit(Department $department)
    {
        $divisions = Division::all();
        return view('admin.departments.edit', compact('department', 'divisions'));
    }

    public function update(Request $request, Department $department)
    {
        $request->validate([
            'division_id' => 'required|exists:divisions,id',
            'name' => 'required|string|max:255',
            'code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('departments')->ignore($department->id)->where(function ($query) use ($request) {
                    return $query->where('division_id', $request->division_id)->whereNull('deleted_at');
                }),
            ],
        ]);

        $department->update([
            'division_id' => $request->division_id,
            'name' => $request->name,
            'code' => strtoupper($request->code),
        ]);

        return redirect()->route('admin.departments.index')->with('success', 'Department updated successfully.');
    }

    public function destroy(Department $department)
    {
        $department->delete();

        return redirect()->route('admin.departments.index')->with('success', 'Department deleted successfully.');
    }
}
