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


class LedgerAccountController extends Controller
{


    public function _approve(Request $request){
        
        $id                     = (int) $request->input('id');
        $ledger_account         = LedgerAccount::find($id);

        if(!$ledger_account){
            return response()->json([
                'status'    => -2,
                'message'   => 'Failed Validation',
                'data'      => [
                    'Ledger Account' =>[
                        'Record not found'
                    ]
                ]
            ]);
        }

        if($ledger_account->status != 'PEND'){
            return response()->json([
                'status'    => 0,
                'message'   => 'Cannot approve Ledger Account (Status:'.$ledger_account->status.')',
                'data'      => []
            ]);
        }
        
        $user_id = Auth::user()->id;


        $ledger_account->status          = 'APRV';
        $ledger_account->approved_by     = $user_id;
        $ledger_account->approved_at     = Carbon::now();

        $ledger_account->save();

        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => []
        ]);
    }


    public function _reject(Request $request){
        
        $id                     = (int) $request->input('id');
        $ledger_account         = LedgerAccount::find($id);

        if(!$ledger_account){
            return response()->json([
                'status'    => -2,
                'message'   => 'Failed Validation',
                'data'      => [
                    'Ledger Account' =>[
                        'Record not found'
                    ]
                ]
            ]);
        }

        if($ledger_account->status != 'PEND'){
            return response()->json([
                'status'    => 0,
                'message'   => 'Cannot reject Ledger Account (Status:'.$ledger_account->status.')',
                'data'      => []
            ]);
        }
        
        $user_id = Auth::user()->id;

        $ledger_account->status          = 'REJC';
        $ledger_account->rejected_by     = $user_id;
        $ledger_account->rejected_at     = Carbon::now();

        $ledger_account->save();

        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => []
        ]);
    }

    public function _revert(Request $request){
        
        $id                     = (int) $request->input('id');
        $ledger_account         = LedgerAccount::find($id);

        if(!$ledger_account){
            return response()->json([
                'status'    => -2,
                'message'   => 'Failed Validation',
                'data'      => [
                    'Ledger Account' =>[
                        'Record not found'
                    ]
                ]
            ]);
        }

        if(!in_array($ledger_account->status,['APRV','REJC'])){
            return response()->json([
                'status'    => 0,
                'message'   => 'Cannot update Ledger Account (Status:'.$ledger_account->status.')',
                'data'      => []
            ]);
        }
        
        $user_id = Auth::user()->id;


        $ledger_account->status          = 'PEND';
        $ledger_account->save();

        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => []
        ]);
    }

    public function list(){
         return view('review/ledger/account/list');
    }
    
    public function display($id){

        $id = (int) $id;

        $ledger_account = LedgerAccount::findOrFail($id);

        return view('review/ledger/account/display',[
            'ledger_account'    => $ledger_account 
        ]);
    }

    public function _list(Request $request){
        
        $page               = (int) $request->input('page')     ?? 1;
        $limit              = (int) $request->input('limit')    ?? 10;
        $orderBy            = $request->input('order_by')       ?? 'id';
        $order              = $request->input('order')          ?? 'DESC';
        $query              = $request->input('query')          ?? '';
        $status             = $request->input('status')         ?? '';
        $result             = [];

        $table = new LedgerAccount();

        $table = $table->where('status','PEND')->orWhere('status','RDEL');

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
        
        $id                 = (int) $request->input('id');
        $ledger_account     = LedgerAccount::find($id);

        if(!$ledger_account){
            return response()->json([
                'status'    => -2,
                'message'   => 'Failed Validation',
                'data'      => [
                    'Ledger Account' =>[
                        'Record not found'
                    ]
                ]
            ]);
        }

        if($ledger_account->status != 'RDEL'){
            return response()->json([
                'status'    => 0,
                'message'   => 'Cannot update Ledger Account (Status:'.$ledger_entry->status.')',
                'data'      => []
            ]);
        }
        
        $user_id = Auth::user()->id;

        $ledger_account->status                         = 'APRV';
        $ledger_account->rejected_request_delete_by     = $user_id;
        $ledger_account->rejected_request_delete_at     = Carbon::now();

        $ledger_account->save();

        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => []
        ]);
    }

    public function _approve_request_delete(Request $request){
        
        $id                 = (int) $request->input('id');
        $ledger_account     = LedgerAccount::find($id);

        if(!$ledger_account){
            return response()->json([
                'status'    => -2,
                'message'   => 'Failed Validation',
                'data'      => [
                    'Ledger Account' =>[
                        'Record not found'
                    ]
                ]
            ]);
        }

        if($ledger_account->status != 'RDEL'){
            return response()->json([
                'status'    => 0,
                'message'   => 'Cannot update Ledger Account (Status:'.$ledger_account->status.')',
                'data'      => []
            ]);
        }
        
        $user_id = Auth::user()->id;

        $ledger_account->status         = 'DELE';
        $ledger_account->deleted_by     = $user_id;
        $ledger_account->save();
        
        $ledger_account->delete();

        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => []
        ]);
    }
   
}