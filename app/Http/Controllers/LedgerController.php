<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Query\Builder;
use Illuminate\Validation\Rule;
use App\Models\LedgerAccount;
use App\Models\Ledger;
use App\Models\LedgerEntry;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class LedgerController extends Controller
{


    public function _create(Request $request,$id){
        
        $id             = (int) $id;
        $ledger_account = LedgerAccount::find($id);

        if(!$ledger_account){
            return response()->json([
                'status'    => -2,
                'message'   => 'Failed Validation',
                'data'      => [
                    'Account' =>[
                        'Record not found'
                    ]
                ]
            ]);
        }

        if($ledger_account->status != 'APRV'){
            return response()->json([
                'status'    => -2,
                'message'   => 'Failed Validation',
                'data'      => [
                    'Ledger Account' =>[
                        'Status not yet approved'
                    ]
                ]
            ]);
        }

        $validator = Validator::make($request->all(),[
            'name' => [
                'required',
                'max:255',
                Rule::unique('ledgers','name')->where(function (Builder $query) use ($id) { 
                    $query->where('ledger_account_id', $id); 
                })
            ],
            'description' => [
                'required',
                'max:800'
            ],
            'template' => [
                'required',
                'max:600'
            ],
            'unit' => [
                'required'
            ]
            
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    => -2,
                'message'   => 'Failed Validation',
                'data'      => $validator->messages()
            ]);
        }

        
        $name           = $request->input('name');
        $description    = $request->input('description');
        $template       = $request->input('template');
        $unit           = $request->input('unit');
        
        $user_id = Auth::user()->id;

        $ledger = new Ledger();

        $ledger->ledger_account_id = $id;
        
        $ledger->name           = $name;
        $ledger->description    = $description;
        $ledger->template       = $template;
        $ledger->unit           = $unit;
        $ledger->status         = 'PEND';
        $ledger->created_by     = $user_id;

        $ledger->save();

        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => [
                'id' => $ledger->id
            ]
        ]);
    }

    
    public function display($id){

        $id = (int) $id;

        $ledger = Ledger::findOrFail($id);

        $ledger_account = $ledger->Account;

        $ledger_entry = new LedgerEntry();

        return view('ledger/display',[
            'ledger'            => $ledger,
            'ledger_entry'      => $ledger_entry,
            'ledger_account'    => $ledger_account
        ]);
    }

    public function _update(Request $request, $id){
        
        $id = (int) $id;

        $ledger = Ledger::find($id);

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
                'message'   => 'Ledger cannot be updated (status: '.$ledger->status.')',
                'data'      => []
            ]);
        }

        $validator = Validator::make($request->all(),[
            'name' => [
                'required',
                'max:255',
                Rule::unique('ledgers','name')->where(fn (Builder $query) => $query->where('ledger_account_id', $id)->where('id','!=',$id))
            ],
            'description' => [
                'required',
                'max:800'
            ],
            'template' => [
                'required',
                'max:600'
            ],
            'unit' => [
                'required'
            ]
            
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    => -2,
                'message'   => 'Failed Validation',
                'data'      => $validator->messages()
            ]);
        }

        $name           = $request->input('name');
        $description    = $request->input('description');
        $template       = $request->input('template');
        $unit           = $request->input('unit');
       

        $user_id = Auth::user()->id;
        
        
        $ledger->name           = $name;
        $ledger->description    = $description;
        $ledger->template       = $template;
        $ledger->unit           = $unit;
        $ledger->updated_by     = $user_id;

        $ledger->save();


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

        if(!in_array($ledger->status,['APRV'])){
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

    public function _request_delete(Request $request){

        $id     = (int) $request->input('id');

        $ledger = Ledger::find($id);

        if(!$ledger_account){
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

        if($ledger_account->status != 'APRV'){
            return response()->json([
                'status'    => 0,
                'message'   => 'Cannot update Ledger (status: '.$ledger->status.')',
                'data'      => []
            ]);
        }


        $user_id = Auth::user()->id;
        
        $ledger->status            = 'RDEL';
        $ledger->request_delete_by = $user_id;
        $ledger->request_delete_at = Carbon::now();
        
        $ledger->save();

        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => []
        ]);
    }

    public function _delete(Request $request){
        
        $id = (int) $request->input('id');

        $ledger = Ledger::find($id);

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
                'message'   => 'Ledger cannot be updated (status: '.$ledger->status.')',
                'data'      => []
            ]);
        }

        //Delete all Entries
        $entries = $ledger->Entries();

        if($entries){
            return response()->json([
                'status'    => 0,
                'message'   => 'Cannot delete Ledger with existing entries, Try to request delete instead',
                'data'      => []
            ]);
        }

        $user_id = Auth::user()->id;

        DB::beginTransaction();

        try{
    

            foreach($entries as $entry){
                $entry->status     = 'DELE';
                $entry->deleted_by = $user_id;
                $entry->save();
                $entry->delete();
            }

            //Delete Ledger
            $ledger->status     = 'DELE';
            $ledger->deleted_by = $user_id;
            $ledger->save();
            $ledger->delete();
            
            DB::commit();
            
        }catch(\Exception $e){
            DB::rollback();
        }
        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => []
        ]);
    }


    public function _list(Request $request){
        
        $page               = (int) $request->input('page')     ?? 1;
        $limit              = (int) $request->input('limit')    ?? 10;
        $ledger_account_id  = (int) $request->input('ledger_account_id') ?? 0;
        $orderBy            = $request->input('order_by')       ?? 'id';
        $order              = $request->input('order')          ?? 'DESC';
        $query              = $request->input('query')          ?? '';
        $status             = $request->input('status')         ?? '';
        $result     = [];

        $table = new Ledger();

        $table = $table->where('ledger_account_id',$ledger_account_id);

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

    public function print($id){

        $id = (int) $id;

        $ledger = Ledger::findOrFail($id);

        $account = $ledger->Account;

        $entries = $ledger->Entries()->where('status','APRV')->get();

        return view('ledger/print',[
            'ledger'     => $ledger,
            'account'    => $account,
            'entries'    => $entries
        ]);
    }
}