<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AlertDocumentType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AlertDocumentTypeController extends Controller
{
    public function index()
    {
        $types = AlertDocumentType::paginate(15);
        return view('admin.alert_document_types.index', compact('types'));
    }

    public function create()
    {
        return view('admin.alert_document_types.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('alert_document_types')->whereNull('deleted_at'),
            ],
        ]);

        AlertDocumentType::create([
            'name' => $request->name,
        ]);

        return redirect()->route('admin.alert-document-types.index')->with('success', 'Alert Document Type created successfully.');
    }

    public function edit(AlertDocumentType $alertDocumentType)
    {
        return view('admin.alert_document_types.edit', compact('alertDocumentType'));
    }

    public function update(Request $request, AlertDocumentType $alertDocumentType)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('alert_document_types')->ignore($alertDocumentType->id)->whereNull('deleted_at'),
            ],
        ]);

        $alertDocumentType->update([
            'name' => $request->name,
        ]);

        return redirect()->route('admin.alert-document-types.index')->with('success', 'Alert Document Type updated successfully.');
    }

    public function destroy(AlertDocumentType $alertDocumentType)
    {
        $alertDocumentType->delete();

        return redirect()->route('admin.alert-document-types.index')->with('success', 'Alert Document Type deleted successfully.');
    }
}
