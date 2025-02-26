<?php

namespace App\Http\Controllers\Review;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Validation\Rule;
use App\Models\LedgerAccount;
use App\Models\Ledger;
use App\Models\LedgerEntry;


class LedgerEntryController extends Controller
{


    public function _approve(Request $request){
        
        $id             = (int) $request->input('id');
        $ledger_entry         = LedgerEntry::find($id);

        if(!$ledger_entry){
            return response()->json([
                'status'    => -2,
                'message'   => 'Failed Validation',
                'data'      => [
                    'Ledger Entry' =>[
                        'Record not found'
                    ]
                ]
            ]);
        }

        if($ledger_entry->status != 'PEND'){
            return response()->json([
                'status'    => 0,
                'message'   => 'Cannot approve Ledger Entry (Status:'.$ledger_entry->status.')',
                'data'      => []
            ]);
        }
        
        $user_id = Auth::user()->id;


        $ledger_entry->status          = 'APRV';
        $ledger_entry->approved_by     = $user_id;
        $ledger_entry->approved_at     = Carbon::now();

        $ledger_entry->save();

        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => []
        ]);
    }


    public function _reject(Request $request){
        
        $id             = (int) $request->input('id');
        $ledger_entry   = LedgerEntry::find($id);

        if(!$ledger_entry){
            return response()->json([
                'status'    => -2,
                'message'   => 'Failed Validation',
                'data'      => [
                    'Ledger Entry' =>[
                        'Record not found'
                    ]
                ]
            ]);
        }

        if($ledger_entry->status != 'PEND'){
            return response()->json([
                'status'    => 0,
                'message'   => 'Cannot reject Ledger Entry (Status:'.$ledger_entry->status.')',
                'data'      => []
            ]);
        }
        
        $user_id = Auth::user()->id;

        $ledger_entry->status          = 'REJC';
        $ledger_entry->rejected_by     = $user_id;
        $ledger_entry->rejected_at     = Carbon::now();

        $ledger_entry->save();

        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => []
        ]);
    }



    public function _reject_request_delete(Request $request){
        
        $id             = (int) $request->input('id');
        $ledger_entry   = LedgerEntry::find($id);

        if(!$ledger_entry){
            return response()->json([
                'status'    => -2,
                'message'   => 'Failed Validation',
                'data'      => [
                    'Ledger Entry' =>[
                        'Record not found'
                    ]
                ]
            ]);
        }

        if($ledger_entry->status != 'RDEL'){
            return response()->json([
                'status'    => 0,
                'message'   => 'Cannot reject Ledger Entry (Status:'.$ledger_entry->status.')',
                'data'      => []
            ]);
        }
        
        $user_id = Auth::user()->id;

        $ledger_entry->status          = 'APRV';
        $ledger_entry->rejected_request_delete_by     = $user_id;
        $ledger_entry->rejected_request_delete_at     = Carbon::now();

        $ledger_entry->save();

        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => []
        ]);
    }

    public function _approve_request_delete(Request $request){
        
        $id             = (int) $request->input('id');
        $ledger_entry   = LedgerEntry::find($id);

        if(!$ledger_entry){
            return response()->json([
                'status'    => -2,
                'message'   => 'Failed Validation',
                'data'      => [
                    'Ledger Entry' =>[
                        'Record not found'
                    ]
                ]
            ]);
        }

        if($ledger_entry->status != 'RDEL'){
            return response()->json([
                'status'    => 0,
                'message'   => 'Cannot reject Ledger Entry (Status:'.$ledger_entry->status.')',
                'data'      => []
            ]);
        }
        
        $user_id = Auth::user()->id;

        $ledger_entry->status         = 'DELE';
        $ledger_entry->deleted_by     = $user_id;
        $ledger_entry->save();
        
        $ledger_entry->delete();

        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => []
        ]);
    }

    public function list(){
         return view('review/ledger/entry/list');
    }
    
    public function display($id){

        $id = (int) $id;

        $ledger_entry   = LedgerEntry::findOrFail($id);

        $ledger         = $ledger_entry->Ledger;
        $ledger_account = $ledger->Account;

        $total_credit   = $ledger->getTotalCredit(['APRV']);
        $total_debit    = $ledger->getTotalDebit(['APRV']);
        $total_amount   = $total_credit - $total_debit;
        $total_quantity = $ledger->getTotalQuantity(['APRV']);

        return view('review/ledger/entry/display',[
            'ledger'            => $ledger,
            'ledger_entry'      => $ledger_entry,
            'ledger_account'    => $ledger_account,
            'total_credit'      => $total_credit,
            'total_debit'       => $total_debit,
            'total_amount'      => $total_amount,
            'total_quantity'    => $total_quantity 
        ]);
    }

    public function _list(Request $request){
        
        $page               = (int) $request->input('page')         ?? 1;
        $limit              = (int) $request->input('limit')        ?? 10;
        $orderBy            = $request->input('order_by')           ?? 'id';
        $order              = $request->input('order')              ?? 'DESC';
        $query              = $request->input('query')              ?? '';
        $status             = $request->input('status')             ?? '';
        $ledger_account_id  = $request->input('ledger_account_id')  ?? 0;
        $ledger_id          = $request->input('ledger_id')          ?? 0;
        $result     = [];

        $table = new LedgerEntry();

        if($query != ''){
            $table = $table->where('particular','LIKE','%'.$query.'%');
        }
        
        $table = $table->where('status','PEND')->orWhere('status','RDEL')->with('Ledger');
        
        if($ledger_id){
            $table = $table->where('ledger_id',$ledger_id);
        }

        if($ledger_account_id){

            $in = DB::table('ledgers')->select('id')->where('ledger_account_id', $ledger_account_id);
            $table = $table->whereIn('ledger_id',$in);
        }

        if($limit > 0){
            $page   = ($page-1) * $limit;
            
            $result = $table->orderBy($orderBy,$order)->skip($page)->take($limit)->get();
            
        }else{

            $result = $table->orderBy($orderBy,$order)->take($limit)->get();
        }


        return response()->json([
            'status' => 1,
            'message'=>'',
            'data'=> [
                'rows' => $result,
            ]
        ]);

    }


}