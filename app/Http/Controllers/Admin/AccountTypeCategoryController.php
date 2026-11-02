<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccountTypeCategory;
use App\Models\LedgerAccount;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AccountTypeCategoryController extends Controller
{
    public function index()
    {
        $categories = AccountTypeCategory::orderBy('id', 'asc')->paginate(15);
        return view('admin.account_type_categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.account_type_categories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('account_type_categories')->whereNull('deleted_at'),
            ],
        ]);

        AccountTypeCategory::create([
            'name' => trim($request->name),
        ]);

        return redirect()->route('admin.account-type-categories.index')->with('success', 'Account Type Category created successfully.');
    }

    public function edit(AccountTypeCategory $accountTypeCategory)
    {
        return view('admin.account_type_categories.edit', compact('accountTypeCategory'));
    }

    public function update(Request $request, AccountTypeCategory $accountTypeCategory)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('account_type_categories')->ignore($accountTypeCategory->id)->whereNull('deleted_at'),
            ],
        ]);

        $oldName = $accountTypeCategory->name;
        $newName = trim($request->name);

        if ($oldName !== $newName) {
            // Update associated ledger accounts to keep them in sync
            LedgerAccount::where('type_category', $oldName)->update([
                'type_category' => $newName,
            ]);
        }

        $accountTypeCategory->update([
            'name' => $newName,
        ]);

        return redirect()->route('admin.account-type-categories.index')->with('success', 'Account Type Category updated successfully.');
    }

    public function destroy(AccountTypeCategory $accountTypeCategory)
    {
        try {
            $accountTypeCategory->delete();
            return redirect()->route('admin.account-type-categories.index')->with('success', 'Account Type Category deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->route('admin.account-type-categories.index')->with('error', $e->getMessage());
        }
    }
}
