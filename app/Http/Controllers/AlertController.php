<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Alert;
use App\Models\Employee;
use App\Models\AlertDocumentType;
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
        $documentTypes = AlertDocumentType::orderBy('name')->pluck('name');

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
        $documentTypes = AlertDocumentType::orderBy('name')->pluck('name');

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
            'reference' => 'nullable|string|max:250',
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
        $documentTypes = AlertDocumentType::orderBy('name')->pluck('name');

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
            'reference' => 'nullable|string|max:250',
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

    public function showUploadForm()
    {
        return view('alerts/upload');
    }

    public function downloadSample()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="alerts_bulk_upload_sample.csv"',
        ];

        $validTypes = AlertDocumentType::orderBy('name')->pluck('name')->toArray();
        $validTypesList = implode(', ', $validTypes);

        $callback = function () use ($validTypesList) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Title', 'Document Type', 'Employee ID', 'Expiry Date', 'Alert Days Before', 'Description', 'Reference']);
            fputcsv($file, ['Visa Renewal - John Doe', 'Visa', '1', Carbon::now()->addMonths(6)->format('Y-m-d'), '30', 'John Doe Visa Expiry alert', 'REF-VISA-01']);
            fputcsv($file, ['Driving License - Jane Smith', 'License', '', Carbon::now()->addDays(15)->format('Y-m-d'), '15', 'Jane Smith driving license renewal alert', 'REF-DL-02']);
            fputcsv($file, []);
            fputcsv($file, ['NOTE: Document Type must match one of the following valid options:']);
            fputcsv($file, [$validTypesList]);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function previewUpload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        $file = $request->file('file');
        $parsedAlerts = $this->parseExcelFile($file);

        if (empty($parsedAlerts)) {
            return redirect()->back()->withErrors(['file' => 'No valid data found in the uploaded file.']);
        }

        $employees = Employee::orderBy('lastname')->orderBy('firstname')->get(['id', 'firstname', 'lastname']);
        $documentTypes = AlertDocumentType::orderBy('name')->pluck('name');
        $existingTitles = Alert::whereNull('deleted_at')->pluck('title')->toArray();

        return view('alerts/preview', [
            'parsedAlerts' => $parsedAlerts,
            'employees' => $employees,
            'documentTypes' => $documentTypes,
            'existingTitles' => $existingTitles,
        ]);
    }

    public function bulkStore(Request $request)
    {
        $request->validate([
            'alerts' => 'required|string',
        ]);

        $alerts = json_decode($request->input('alerts'), true);

        if (!is_array($alerts)) {
            return redirect()->back()->withErrors(['alerts' => 'Invalid data format.']);
        }

        $errors = [];
        $titlesInUpload = [];
        $existingTitles = Alert::whereNull('deleted_at')->pluck('title')->toArray();
        $validDocumentTypes = AlertDocumentType::pluck('name')->toArray();

        foreach ($alerts as $index => $alert) {
            $rowNum = $index + 1;
            $title = trim($alert['title'] ?? '');

            if ($title === '') {
                $errors[] = "Row {$rowNum}: Title (Item Name) is required.";
                continue;
            }

            if (in_array($title, $titlesInUpload)) {
                $errors[] = "Row {$rowNum}: Item Name '{$title}' is duplicated in the uploaded list.";
            } else {
                $titlesInUpload[] = $title;
            }

            if (in_array($title, $existingTitles)) {
                $errors[] = "Row {$rowNum}: Item Name '{$title}' already exists in the system.";
            }

            if (empty($alert['document_type'] ?? '')) {
                $errors[] = "Row {$rowNum}: Document type is required.";
            } elseif (!in_array($alert['document_type'], $validDocumentTypes)) {
                $errors[] = "Row {$rowNum}: Document type '{$alert['document_type']}' is unrecognized.";
            }

            if (empty($alert['expiry_date'] ?? '')) {
                $errors[] = "Row {$rowNum}: Expiry date is required.";
            } elseif (!strtotime($alert['expiry_date'])) {
                $errors[] = "Row {$rowNum}: Expiry date is invalid.";
            }
        }

        if (!empty($errors)) {
            return redirect()->back()->withInput()->withErrors($errors);
        }

        foreach ($alerts as $alert) {
            $expiryDate = Carbon::parse($alert['expiry_date']);
            $alertDays = (int) ($alert['alert_days_before'] ?? 30);
            $today = Carbon::today();

            $status = 'Active';
            if ($today->greaterThanOrEqualTo($expiryDate)) {
                $status = 'Expired';
            } elseif ($today->greaterThanOrEqualTo($expiryDate->copy()->subDays($alertDays))) {
                $status = 'Warning';
            }

            Alert::create([
                'title' => trim($alert['title']),
                'document_type' => trim($alert['document_type']),
                'employee_id' => !empty($alert['employee_id']) ? (int) $alert['employee_id'] : null,
                'expiry_date' => $expiryDate->format('Y-m-d'),
                'alert_days_before' => $alertDays,
                'description' => $alert['description'] ?? null,
                'reference' => !empty($alert['reference']) ? trim($alert['reference']) : null,
                'status' => $status,
                'created_by' => Auth::id(),
            ]);
        }

        return redirect()->route('alerts.index')->with('success', count($alerts) . ' alerts imported successfully.');
    }

    private function parseExcelFile($file)
    {
        $sheets = \Maatwebsite\Excel\Facades\Excel::toArray([], $file);
        $rows = $sheets[0] ?? [];
        if (empty($rows)) {
            return [];
        }

        $headers = array_map(function ($h) {
            return strtolower(trim(str_replace(['_', ' ', '-'], '', $h)));
        }, $rows[0]);

        $data = [];
        $count = count($rows);
        for ($i = 1; $i < $count; $i++) {
            $row = $rows[$i];
            if (empty(array_filter($row))) {
                continue;
            }

            $item = [
                'title' => '',
                'document_type' => '',
                'employee_id' => null,
                'expiry_date' => '',
                'alert_days_before' => 30,
                'description' => '',
                'reference' => '',
            ];

            foreach ($headers as $index => $header) {
                $value = $row[$index] ?? null;
                if ($value === null) {
                    continue;
                }

                if (in_array($header, ['title', 'itemname', 'name', 'item'])) {
                    $item['title'] = trim($value);
                } elseif (in_array($header, ['documenttype', 'type', 'doctype'])) {
                    $item['document_type'] = trim($value);
                } elseif (in_array($header, ['employeeid', 'employee', 'linkedemployee', 'empid'])) {
                    $item['employee_id'] = is_numeric($value) ? (int)$value : null;
                } elseif (in_array($header, ['expirydate', 'expiry', 'date', 'exp'])) {
                    if (is_numeric($value)) {
                        try {
                            $dateValue = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value);
                            $item['expiry_date'] = $dateValue->format('Y-m-d');
                        } catch (\Exception $e) {
                            $item['expiry_date'] = $value;
                        }
                    } else {
                        $item['expiry_date'] = trim($value);
                    }
                } elseif (in_array($header, ['alertdaysbefore', 'alertdays', 'days', 'threshold'])) {
                    $item['alert_days_before'] = is_numeric($value) ? (int)$value : 30;
                } elseif (in_array($header, ['description', 'details', 'notes', 'desc'])) {
                    $item['description'] = trim($value);
                } elseif (in_array($header, ['reference', 'ref', 'refnum', 'referenceno'])) {
                    $item['reference'] = trim($value);
                }
            }

            $data[] = $item;
        }

        return $data;
    }
}
