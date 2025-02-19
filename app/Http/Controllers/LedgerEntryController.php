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


class LedgerEntryController extends Controller
{


    public function _create(Request $request,$ledger_id){
        
        $ledger_id      = (int) $ledger_id;
        $ledger         = Ledger::find($ledger_id);
        $ledger_entry   = new LedgerEntry();

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

        $validator = Validator::make($request->all(),[
            'particular' => [
                'required',
                'max:800'
            ],
            'tag' => [
                'required',
                'in:'.$this->format_in( $ledger_entry->tag_options() )
            ],
            'type' => [
                'required',
                'in:'.$this->format_in( $ledger_entry->type_options() )
            ],
            'quantity' => [
                'required',
                'numeric',
                'gt:0'
            ],
            'unit_amount' =>[
                'required',
                'decimal:2',
                'gt:0'
            ],
            'date' =>[
                'required',
                'date_format:Y-m-d'
            ]
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    => -2,
                'message'   => 'Failed Validation',
                'data'      => $validator->messages()
            ]);
        }

        $particular     = $request->input('particular');
        $tag            = $request->input('tag');
        $type           = $request->input('type');
        $quantity       = $request->input('quantity');
        $unit_amount    = $request->input('unit_amount');
        $date           = $request->input('date');
        
        $user_id = Auth::user()->id;


        $ledger_entry->ledger_id = $ledger_id;
        
        $ledger_entry->particular     = $particular;
        $ledger_entry->tag            = $tag;
        $ledger_entry->type           = $type;
        $ledger_entry->quantity       = $quantity;
        $ledger_entry->unit_amount    = $unit_amount;
        $ledger_entry->date           = $date;
        $ledger_entry->status         = 'PEND';
        
        $ledger_entry->created_by     = $user_id;

        $ledger_entry->save();

        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => [
                'id' => $ledger_entry->id
            ]
        ]);
    }

    
    public function display($id){

        $id = (int) $id;

        $ledger_entry   = LedgerEntry::findOrFail($id);
        $ledger         = $ledger_entry->Ledger;
        $ledger_account = $ledger->Account;

        return view('ledger/entry/display',[
            'ledger_account'    => $ledger_account,
            'ledger'            => $ledger,
            'ledger_entry'      => $ledger_entry
        ]);
    }


    public function _list(Request $request,$ledger_id){

        //todo check role

        $page       = (int) $request->input('page')     ?? 1;
        $limit      = (int) $request->input('limit')    ?? 10;
        $orderBy    = $request->input('order_by')       ?? 'id';
        $order      = $request->input('order')          ?? 'DESC';
        $query      = $request->input('query')          ?? '';
        $status     = $request->input('status')         ?? '';
        $ledger_id  = (int) $ledger_id;
        $result     = [];

        $table = new LedgerEntry();

        $table = $table->where('deleted_at',null);
        $table = $table->where('ledger_id',$ledger_id);

        if($query != ''){
            $table = $table->where('particular','LIKE','%'.$query.'%');
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

        $ledger = Ledger::find($ledger_id);

        $total_credit   = $ledger->getTotalCredit(['APRV']);
        $total_debit    = $ledger->getTotalDebit(['APRV']);
        $total_quantity = $ledger->getTotalQuantity(['APRV']);

        return response()->json([
            'status' => 1,
            'message'=>'',
            'data'=> [
                'rows' => $result,
                'total_credit'      => $total_credit,
                'total_debit'       => $total_debit,
                'total_quantity'    => $total_quantity 
            ]
        ]);
    }

    public function _update(Request $request, $id){
        
        $id = (int) $id;

        $ledger_entry = LedgerEntry::find($id);

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
                'message'   => 'Entry cannot be updated (status: '.$ledger_entry->status.')',
                'data'      => []
            ]);
        }

        $validator = Validator::make($request->all(),[
            'particular' => [
                'required',
                'max:800'
            ],
            'tag' => [
                'required',
                'in:'.$this->format_in( $ledger_entry->tag_options() )
            ],
            'type' => [
                'required',
                'in:'.$this->format_in( $ledger_entry->type_options() )
            ],
            'quantity' => [
                'required',
                'numeric',
                'gt:0'
            ],
            'unit_amount' =>[
                'required',
                'decimal:2',
                'gt:0'
            ],
            'date' =>[
                'required',
                'date_format:Y-m-d'
            ]
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    => -2,
                'message'   => 'Failed Validation',
                'data'      => $validator->messages()
            ]);
        }

        $user_id = Auth::user()->id;
        
        $particular     = $request->input('particular');
        $tag            = $request->input('tag');
        $type           = $request->input('type');
        $quantity       = $request->input('quantity');
        $unit_amount    = $request->input('unit_amount');
        $date           = $request->input('date');
        
        $ledger_entry->particular     = $particular;
        $ledger_entry->tag            = $tag;
        $ledger_entry->type           = $type;
        $ledger_entry->quantity       = $quantity;
        $ledger_entry->unit_amount    = $unit_amount;
        $ledger_entry->date           = $date;
        
        $ledger_entry->updated_by      = $user_id;

        $ledger_entry->save();


        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => []
        ]);
    }


    public function _delete(Request $request){
        
        $id = (int) $request->input('id');

        $ledger_entry = LedgerEntry::find($id);

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
                'message'   => 'Entry cannot be updated (status: '.$ledger_entry->status.')',
                'data'      => []
            ]);
        }

        $user_id = Auth::user()->id;
        
        $ledger_entry->deleted_by = $user_id;
        
        $ledger_entry->save();

        $ledger_entry->delete();

        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => []
        ]);
    }
}