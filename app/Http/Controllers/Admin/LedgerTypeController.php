<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LedgerType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LedgerTypeController extends Controller
{
    public function index()
    {
        $types = LedgerType::orderBy('id', 'asc')->paginate(15);
        return view('admin.ledger_types.index', compact('types'));
    }

    public function create()
    {
        return view('admin.ledger_types.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'text' => [
                'required',
                'string',
                'max:255',
                Rule::unique('ledger_types')->whereNull('deleted_at'),
            ],
        ]);

        LedgerType::create([
            'text' => $request->text,
        ]);

        return redirect()->route('admin.ledger-types.index')->with('success', 'Ledger Type created successfully.');
    }

    public function edit(LedgerType $ledgerType)
    {
        return view('admin.ledger_types.edit', compact('ledgerType'));
    }

    public function update(Request $request, LedgerType $ledgerType)
    {
        $request->validate([
            'text' => [
                'required',
                'string',
                'max:255',
                Rule::unique('ledger_types')->ignore($ledgerType->id)->whereNull('deleted_at'),
            ],
        ]);

        if ($ledgerType->isProtected() && $request->text !== $ledgerType->text) {
            return redirect()->route('admin.ledger-types.index')->with('error', "Cannot rename protected ledger type: {$ledgerType->text}.");
        }

        $ledgerType->update([
            'text' => $request->text,
        ]);

        return redirect()->route('admin.ledger-types.index')->with('success', 'Ledger Type updated successfully.');
    }

    public function destroy(LedgerType $ledgerType)
    {
        if ($ledgerType->isProtected()) {
            return redirect()->route('admin.ledger-types.index')->with('error', "Cannot delete protected ledger type: {$ledgerType->text}.");
        }

        try {
            $ledgerType->delete();
            return redirect()->route('admin.ledger-types.index')->with('success', 'Ledger Type deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->route('admin.ledger-types.index')->with('error', $e->getMessage());
        }
    }
}
