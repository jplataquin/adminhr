<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Query\Builder;
use Illuminate\Validation\Rule;
use App\Models\LedgerAccount;


class LedgerAccountController extends Controller
{

    public function create(){

        return view('ledger/account/create');
    }

    public function _create(Request $request){

        $validator = Validator::make($request->all(),[
            'name' => [
                'required',
                'max:255',
                Rule::unique('ledger_accounts','name')
            ]
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    => -2,
                'message'   => 'Failed Validation',
                'data'      => $validator->messages()
            ]);
        }

        $name   = $request->input('name');
        $user_id = Auth::user()->id;

        $ledger_account = new LedgerAccount();

        $ledger_account->name       = $name;
        $ledger_account->created_by = $user_id;

        $ledger_account->save();

        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => [
                'id' => $ledger_account->id
            ]
        ]);
    }

    
    public function display($id){

        $id = (int) $id;

        $ledger_account = LedgerAccount::findOrFail($id);

        return view('ledger/account/display',[
            'ledger_account' => $ledger_account
        ]);
    }

    public function list(){

        return view('ledger/account/list');
    }

    public function _list(Request $request){
        
        $page       = (int) $request->input('page')     ?? 1;
        $limit      = (int) $request->input('limit')    ?? 10;
        $orderBy    = $request->input('order_by')       ?? 'id';
        $order      = $request->input('order')          ?? 'DESC';
        $query      = $request->input('query')          ?? '';
        $status     = $request->input('status')         ?? '';
        $result     = [];

        $table = new LedgerAccount();


        if($query != ''){
            $table = $table->where('name','LIKE','%'.$query.'%');
        }

        if($status != ''){
            $table = $table->where('status','=',$status);
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


    public function _update(Request $request, $id){
        
        $id = (int) $id;

        $ledger_account = LedgerAccount::find($id);

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


        $validator = Validator::make($request->all(),[
            'name' => [
                'required',
                'max:255',
                Rule::unique('ledger_accounts','name')->where(fn (Builder $query) => $query->where('id','!=',$id))
            ]
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    => -2,
                'message'   => 'Failed Validation',
                'data'      => $validator->messages()
            ]);
        }

        $name = $request->input('name');
       
        $user_id = Auth::user()->id;
        
        $ledger_account->name           = $name;
        $ledger_account->updated_by     = $user_id;

        $ledger_account->save();


        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => []
        ]);
    }
}