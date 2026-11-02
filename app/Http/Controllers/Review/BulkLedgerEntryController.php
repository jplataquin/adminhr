<?php

namespace App\Http\Controllers\Review;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\BulkLedgerEntry;
use App\Models\LedgerEntry;

class BulkLedgerEntryController extends Controller
{
    public function list()
    {
        return view('review/bulk_ledger_entry/list');
    }

    public function _list(Request $request)
    {
        $page    = (int) $request->input('page') ?: 1;
        $limit   = (int) $request->input('limit') ?: 10;
        $orderBy = $request->input('order_by') ?: 'id';
        $order   = $request->input('order') ?: 'DESC';
        $query   = trim($request->input('query') ?? '');
        $status  = $request->input('status') ?? 'PEND';

        $table = BulkLedgerEntry::with(['CreatedByUser', 'ApprovedByUser', 'RejectedByUser', 'Entries']);

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

        return view('review/bulk_ledger_entry/display', [
            'bulk_ledger_entry' => $bulk_ledger_entry,
        ]);
    }

    public function _approve(Request $request)
    {
        $id = (int) $request->input('id');
        $bulk = BulkLedgerEntry::find($id);

        if (!$bulk) {
            return response()->json([
                'status'  => -2,
                'message' => 'Failed Validation',
                'data'    => ['bulk_ledger_entry' => ['Record not found']]
            ]);
        }

        if ($bulk->status !== 'PEND') {
            return response()->json([
                'status'  => 0,
                'message' => 'Cannot approve Bulk Ledger Entry (Status: ' . $bulk->status . ')',
                'data'    => []
            ]);
        }

        $userId = Auth::id();
        $now = Carbon::now();

        DB::transaction(function () use ($bulk, $userId, $now) {
            $bulk->status = 'APRV';
            $bulk->approved_by = $userId;
            $bulk->approved_at = $now;
            $bulk->save();

            // Approve all child ledger entries so they become active
            LedgerEntry::where('bulk_ledger_entry_id', $bulk->id)
                ->where('status', 'PEND')
                ->update([
                    'status'      => 'APRV',
                    'approved_by' => $userId,
                    'approved_at' => $now,
                    'updated_at'  => $now,
                ]);
        });

        return response()->json([
            'status'  => 1,
            'message' => 'Bulk Ledger Entry and associated entries approved successfully.',
            'data'    => []
        ]);
    }

    public function _reject(Request $request)
    {
        $id = (int) $request->input('id');
        $bulk = BulkLedgerEntry::find($id);

        if (!$bulk) {
            return response()->json([
                'status'  => -2,
                'message' => 'Failed Validation',
                'data'    => ['bulk_ledger_entry' => ['Record not found']]
            ]);
        }

        if ($bulk->status !== 'PEND') {
            return response()->json([
                'status'  => 0,
                'message' => 'Cannot reject Bulk Ledger Entry (Status: ' . $bulk->status . ')',
                'data'    => []
            ]);
        }

        $userId = Auth::id();
        $now = Carbon::now();

        DB::transaction(function () use ($bulk, $userId, $now) {
            $bulk->status = 'REJC';
            $bulk->rejected_by = $userId;
            $bulk->rejected_at = $now;
            $bulk->save();

            // Reject all child ledger entries
            LedgerEntry::where('bulk_ledger_entry_id', $bulk->id)
                ->where('status', 'PEND')
                ->update([
                    'status'      => 'REJC',
                    'rejected_by' => $userId,
                    'rejected_at' => $now,
                    'updated_at'  => $now,
                ]);
        });

        return response()->json([
            'status'  => 1,
            'message' => 'Bulk Ledger Entry rejected successfully.',
            'data'    => []
        ]);
    }
}
