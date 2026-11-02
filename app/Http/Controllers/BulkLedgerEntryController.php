<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\BulkLedgerEntry;
use App\Models\LedgerEntry;
use App\Models\Ledger;
use App\Models\LedgerAccount;

class BulkLedgerEntryController extends Controller
{
    public function list()
    {
        return view('bulk_ledger_entry/list');
    }

    public function _list(Request $request)
    {
        $page    = (int) $request->input('page') ?: 1;
        $limit   = (int) $request->input('limit') ?: 10;
        $orderBy = $request->input('order_by') ?: 'id';
        $order   = $request->input('order') ?: 'DESC';
        $query   = trim($request->input('query') ?? '');
        $status  = $request->input('status') ?? '';

        $table = BulkLedgerEntry::with(['CreatedByUser', 'ApprovedByUser', 'Entries']);

        if ($query !== '') {
            $table = $table->where('description', 'LIKE', '%' . $query . '%');
        }

        if ($status !== '') {
            $table = $table->where('status', '=', $status);
        }

        $totalCount = $table->count();
        $offset = ($page - 1) * $limit;
        $rows = $table->orderBy($orderBy, $order)->skip($offset)->take($limit)->get();

        foreach ($rows as $row) {
            $row->total_amount = $row->total_amount;
            $row->total_amount_formatted = $row->total_amount_formatted;
            $row->entries_count = $row->Entries->count();
        }

        return response()->json([
            'status'  => 1,
            'message' => '',
            'data'    => [
                'rows'        => $rows,
                'total_count' => $totalCount,
                'page'        => $page,
                'limit'       => $limit,
            ]
        ]);
    }

    public function create()
    {
        $ledger_entry = new LedgerEntry();
        $accounts = LedgerAccount::with(['Ledgers' => function ($q) {
            $q->where('status', 'APRV')->with('LedgerType');
        }])->where('status', 'APRV')->orderBy('name', 'asc')->get();

        return view('bulk_ledger_entry/create', [
            'ledger_entry' => $ledger_entry,
            'accounts'     => $accounts,
        ]);
    }

    public function _create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'description'                => 'nullable|string|max:1000',
            'entries'                    => 'required|array|min:1',
            'entries.*.ledger_account_id'=> 'required|exists:ledger_accounts,id',
            'entries.*.ledger_id'        => 'required|exists:ledgers,id',
            'entries.*.type'             => 'required|in:CRED,DEBI',
            'entries.*.tag'              => 'required|in:REGU,FLOA',
            'entries.*.particular'       => 'required|string|max:255',
            'entries.*.quantity'         => 'required|numeric|min:0.01',
            'entries.*.unit_amount'      => 'required|numeric|min:0.01',
            'entries.*.date'             => 'required|date',
        ], [
            'entries.required' => 'At least one ledger entry is required.',
            'entries.min'      => 'At least one ledger entry is required.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => -2,
                'message' => 'Failed Validation',
                'data'    => $validator->messages()
            ]);
        }

        $userId = Auth::id();
        $entriesData = $request->input('entries');

        try {
            $bulk = DB::transaction(function () use ($request, $entriesData, $userId) {
                $bulk = new BulkLedgerEntry();
                $bulk->status = 'PEND';
                $bulk->description = $request->input('description') ?? '';
                $bulk->created_by = $userId;
                $bulk->save();

                foreach ($entriesData as $item) {
                    $entry = new LedgerEntry();
                    $entry->bulk_ledger_entry_id = $bulk->id;
                    $entry->ledger_id = $item['ledger_id'];
                    $entry->status = 'PEND';
                    $entry->type = $item['type'];
                    $entry->tag = $item['tag'];
                    $entry->particular = $item['particular'];
                    $entry->quantity = $item['quantity'];
                    $entry->unit_amount = $item['unit_amount'];
                    $entry->date = $item['date'];
                    $entry->created_by = $userId;
                    $entry->save();
                }

                return $bulk;
            });

            return response()->json([
                'status'  => 1,
                'message' => 'Bulk Ledger Entry created successfully.',
                'data'    => [
                    'id' => $bulk->id,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => -1,
                'message' => 'Failed to create bulk ledger entry: ' . $e->getMessage(),
                'data'    => []
            ]);
        }
    }

    public function display($id)
    {
        $id = (int) $id;
        $bulk_ledger_entry = BulkLedgerEntry::with([
            'CreatedByUser',
            'ApprovedByUser',
            'RejectedByUser',
            'Entries.Ledger.Account',
            'Entries.Ledger.LedgerType',
        ])->findOrFail($id);

        return view('bulk_ledger_entry/display', [
            'bulk_ledger_entry' => $bulk_ledger_entry,
        ]);
    }
}
