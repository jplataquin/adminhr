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


class LedgerController extends Controller
{


    public function _approve(Request $request){
        
        $id             = (int) $request->input('id');
        $ledger         = Ledger::find($id);

        if(!$ledger){
            return response()->json([
                'status'    => -2,
                'message'   => 'Failed Validation',
                'data'      => [
                    'Ledger' =>[
                        'Record not found'
                    ]
                ]
            ]);
        }

        if($ledger->status != 'PEND'){
            return response()->json([
                'status'    => 0,
                'message'   => 'Cannot approve Ledger (Status:'.$ledger->status.')',
                'data'      => []
            ]);
        }
        
        $user_id = Auth::user()->id;


        $ledger->status          = 'APRV';
        $ledger->approved_by     = $user_id;
        $ledger->approved_at     = Carbon::now();

        $ledger->save();

        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => []
        ]);
    }


    public function _reject(Request $request){
        
        $id             = (int) $request->input('id');
        $ledger         = Ledger::find($id);

        if(!$ledger){
            return response()->json([
                'status'    => -2,
                'message'   => 'Failed Validation',
                'data'      => [
                    'Ledger' =>[
                        'Record not found'
                    ]
                ]
            ]);
        }

        if($ledger->status != 'PEND'){
            return response()->json([
                'status'    => 0,
                'message'   => 'Cannot reject Ledger (Status:'.$ledger->status.')',
                'data'      => []
            ]);
        }
        
        $user_id = Auth::user()->id;

        $ledger->status          = 'REJC';
        $ledger->rejected_by     = $user_id;
        $ledger->rejected_at     = Carbon::now();

        $ledger->save();

        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => []
        ]);
    }

    public function list(){
         return view('review/ledger/list');
    }
    
    public function display($id){

        $id = (int) $id;

        $ledger         = Ledger::findOrFail($id);

        $ledger_account = $ledger->Account;

        $ledger_entry   = new LedgerEntry();

        $total_credit   = $ledger->getTotalCredit(['APRV']);
        $total_debit    = $ledger->getTotalDebit(['APRV']);
        $total_amount   = $total_credit - $total_debit;
        $total_quantity = $ledger->getTotalQuantity(['APRV']);

        return view('review/ledger/display',[
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
        
        $page               = (int) $request->input('page')     ?? 1;
        $limit              = (int) $request->input('limit')    ?? 10;
        $orderBy            = $request->input('order_by')       ?? 'id';
        $order              = $request->input('order')          ?? 'DESC';
        $query              = $request->input('query')          ?? '';
        $status             = $request->input('status')         ?? '';
        $result     = [];

        $table = new Ledger();

        $table = $table->where('status','PEND')->orWhere('status','RDEL')->with('Account');

        if($query != ''){
            $table = $table->where('name','LIKE','%'.$query.'%');
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
     
    public function _reject_request_delete(Request $request){
        
        $id         = (int) $request->input('id');
        $ledger     = Ledger::find($id);

        if(!$ledger){
            return response()->json([
                'status'    => -2,
                'message'   => 'Failed Validation',
                'data'      => [
                    'Ledger' =>[
                        'Record not found'
                    ]
                ]
            ]);
        }

        if($ledger->status != 'RDEL'){
            return response()->json([
                'status'    => 0,
                'message'   => 'Cannot update Ledger (Status:'.$ledger->status.')',
                'data'      => []
            ]);
        }
        
        $user_id = Auth::user()->id;

        $ledger->status                         = 'APRV';
        $ledger->rejected_request_delete_by     = $user_id;
        $ledger->rejected_request_delete_at     = Carbon::now();

        $ledger->save();

        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => []
        ]);
    }

    public function _approve_request_delete(Request $request){
        
        $id         = (int) $request->input('id');
        $ledger     = Ledger::find($id);

        if(!$ledger){
            return response()->json([
                'status'    => -2,
                'message'   => 'Failed Validation',
                'data'      => [
                    'Ledger' =>[
                        'Record not found'
                    ]
                ]
            ]);
        }

        if($ledger->status != 'RDEL'){
            return response()->json([
                'status'    => 0,
                'message'   => 'Cannot update Ledger Account (Status:'.$ledger->status.')',
                'data'      => []
            ]);
        }
        
        $user_id = Auth::user()->id;

        $ledger->status         = 'DELE';
        $ledger->deleted_by     = $user_id;
        $ledger->save();
        
        $ledger->delete();

        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => []
        ]);
    }

    public function _revert(Request $request){
        
        $id             = (int) $request->input('id');
        $ledger         = Ledger::find($id);

        if(!$ledger){
            return response()->json([
                'status'    => -2,
                'message'   => 'Failed Validation',
                'data'      => [
                    'Ledger' =>[
                        'Record not found'
                    ]
                ]
            ]);
        }

        if(!in_array($ledger->status,['APRV','REJC'])){
            return response()->json([
                'status'    => 0,
                'message'   => 'Cannot update Ledger (Status:'.$ledger->status.')',
                'data'      => []
            ]);
        }
        
        $user_id = Auth::user()->id;


        $ledger->status          = 'PEND';
        $ledger->save();

        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => []
        ]);
    }
}