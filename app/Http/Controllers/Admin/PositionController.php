<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PositionController extends Controller
{
    public function index()
    {
        $positions = Position::with('department.division')->paginate(15);
        return view('admin.positions.index', compact('positions'));
    }

    public function create()
    {
        $divisions = \App\Models\Division::with('departments')->get();
        return view('admin.positions.create', compact('divisions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'department_id' => 'required|exists:departments,id',
            'name' => 'required|string|max:255',
        ]);

        $baseCode = strtoupper(str_replace(' ', '_', preg_replace('/[^A-Za-z0-9 ]/', '', $request->name)));
        $baseCode = substr($baseCode, 0, 100);
        $code = $baseCode . '_' . strtoupper(bin2hex(random_bytes(4)));

        Position::create([
            'department_id' => $request->department_id,
            'name' => $request->name,
            'code' => $code,
        ]);

        return redirect()->route('admin.positions.index')->with('success', 'Position created successfully.');
    }

    public function edit(Position $position)
    {
        $divisions = \App\Models\Division::with('departments')->get();
        return view('admin.positions.edit', compact('position', 'divisions'));
    }

    public function update(Request $request, Position $position)
    {
        $request->validate([
            'department_id' => 'required|exists:departments,id',
            'name' => 'required|string|max:255',
        ]);

        $position->update([
            'department_id' => $request->department_id,
            'name' => $request->name,
        ]);

        return redirect()->route('admin.positions.index')->with('success', 'Position updated successfully.');
    }

    public function destroy(Position $position)
    {
        $position->delete();

        return redirect()->route('admin.positions.index')->with('success', 'Position deleted successfully.');
    }
}
