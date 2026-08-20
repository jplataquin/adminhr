<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Alert;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AlertController extends Controller
{
    public function index(Request $request)
    {
        $statusFilter = $request->input('status', '');
        $typeFilter = $request->input('document_type', '');
        $search = $request->input('search', '');

        $query = Alert::with('employee')->orderBy('expiry_date', 'asc');

        if ($statusFilter !== '') {
            $query->where('status', $statusFilter);
        }

        if ($typeFilter !== '') {
            $query->where('document_type', $typeFilter);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('employee', function ($eq) use ($search) {
                      $eq->where('firstname', 'like', "%{$search}%")
                        ->orWhere('lastname', 'like', "%{$search}%");
                  });
            });
        }

        $alerts = $query->paginate(15)->withQueryString();

        // Get distinct document types for filters / datalists
        $documentTypes = Alert::distinct()->pluck('document_type')->filter()->values();

        // Count metrics for cards
        $expiredCount = Alert::where('status', 'Expired')->count();
        $warningCount = Alert::where('status', 'Warning')->count();
        $activeCount = Alert::where('status', 'Active')->count();

        return view('alerts/index', [
            'alerts' => $alerts,
            'documentTypes' => $documentTypes,
            'expiredCount' => $expiredCount,
            'warningCount' => $warningCount,
            'activeCount' => $activeCount,
            'statusFilter' => $statusFilter,
            'typeFilter' => $typeFilter,
            'search' => $search,
        ]);
    }

    public function create()
    {
        $employees = Employee::orderBy('lastname')->orderBy('firstname')->get();
        $documentTypes = Alert::distinct()->pluck('document_type')->filter()->values();

        return view('alerts/create', [
            'employees' => $employees,
            'documentTypes' => $documentTypes,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'document_type' => 'required|string|max:255',
            'employee_id' => 'nullable|exists:employees,id',
            'expiry_date' => 'required|date',
            'alert_days_before' => 'required|integer|min:0',
            'description' => 'nullable|string',
        ]);

        $expiryDate = Carbon::parse($validated['expiry_date']);
        $alertDays = (int) $validated['alert_days_before'];
        $today = Carbon::today();

        // Determine status
        $status = 'Active';
        if ($today->greaterThanOrEqualTo($expiryDate)) {
            $status = 'Expired';
        } elseif ($today->greaterThanOrEqualTo($expiryDate->copy()->subDays($alertDays))) {
            $status = 'Warning';
        }

        Alert::create(array_merge($validated, [
            'status' => $status,
            'created_by' => Auth::id(),
        ]));

        return redirect()->route('alerts.index')->with('success', 'Alert registered successfully.');
    }

    public function edit(Alert $alert)
    {
        $employees = Employee::orderBy('lastname')->orderBy('firstname')->get();
        $documentTypes = Alert::distinct()->pluck('document_type')->filter()->values();

        return view('alerts/edit', [
            'alert' => $alert,
            'employees' => $employees,
            'documentTypes' => $documentTypes,
        ]);
    }

    public function update(Request $request, Alert $alert)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'document_type' => 'required|string|max:255',
            'employee_id' => 'nullable|exists:employees,id',
            'expiry_date' => 'required|date',
            'alert_days_before' => 'required|integer|min:0',
            'description' => 'nullable|string',
        ]);

        $expiryDate = Carbon::parse($validated['expiry_date']);
        $alertDays = (int) $validated['alert_days_before'];
        $today = Carbon::today();

        // Determine status
        $status = 'Active';
        if ($today->greaterThanOrEqualTo($expiryDate)) {
            $status = 'Expired';
        } elseif ($today->greaterThanOrEqualTo($expiryDate->copy()->subDays($alertDays))) {
            $status = 'Warning';
        }

        $alert->update(array_merge($validated, [
            'status' => $status,
            'updated_by' => Auth::id(),
        ]));

        return redirect()->route('alerts.index')->with('success', 'Alert updated successfully.');
    }

    public function destroy(Alert $alert)
    {
        $alert->deleted_by = Auth::id();
        $alert->save();
        $alert->delete();

        return redirect()->route('alerts.index')->with('success', 'Alert deleted successfully.');
    }

    public function renew(Request $request, Alert $alert)
    {
        $validated = $request->validate([
            'expiry_date' => 'required|date|after:today',
            'alert_days_before' => 'required|integer|min:0',
        ]);

        $expiryDate = Carbon::parse($validated['expiry_date']);
        $alertDays = (int) $validated['alert_days_before'];
        $today = Carbon::today();

        // Recalculate status (should typically be Active since date must be in future, but we check threshold anyway)
        $status = 'Active';
        if ($today->greaterThanOrEqualTo($expiryDate->copy()->subDays($alertDays))) {
            $status = 'Warning';
        }

        $alert->update([
            'expiry_date' => $validated['expiry_date'],
            'alert_days_before' => $validated['alert_days_before'],
            'status' => $status,
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route('alerts.index')->with('success', 'Alert renewed successfully.');
    }
}
