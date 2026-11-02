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
use App\Models\LedgerType;
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

        if (!$request->filled('ledger_type_id') && $request->filled('ledger_type')) {
            $request->merge(['ledger_type_id' => $request->input('ledger_type')]);
        }

        $validator = Validator::make($request->all(),[
            'name' => [
                'required',
                'max:255',
                Rule::unique('ledgers','name')->where(function (Builder $query) use ($id) { 
                    $query->where('ledger_account_id', $id); 
                })
            ],
            'ledger_type_id' => [
                'required',
                'exists:ledger_types,id'
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
            
        ], [
            'ledger_type_id.required' => 'The ledger type field is required.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    => -2,
                'message'   => 'Failed Validation',
                'data'      => $validator->messages()
            ]);
        }

        
        $name           = $request->input('name');
        $ledger_type_id = $request->input('ledger_type_id');
        $description    = $request->input('description');
        $template       = $request->input('template');
        $unit           = $request->input('unit');
        
        $user_id = Auth::user()->id;

        $ledger = new Ledger();

        $ledger->ledger_account_id = $id;
        $ledger->ledger_type_id    = (int) $ledger_type_id;
        
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

        $ledger = Ledger::with('LedgerType')->findOrFail($id);

        $ledger_account = $ledger->Account;

        $ledger_entry = new LedgerEntry();

        $ledger_types = LedgerType::orderBy('id', 'asc')->get();
        if ($ledger->ledger_type_id && !$ledger_types->contains('id', $ledger->ledger_type_id)) {
            $deletedType = LedgerType::withTrashed()->find($ledger->ledger_type_id);
            if ($deletedType) {
                $ledger_types->push($deletedType);
            }
        }

        return view('ledger/display',[
            'ledger'            => $ledger,
            'ledger_entry'      => $ledger_entry,
            'ledger_account'    => $ledger_account,
            'ledger_types'      => $ledger_types
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

        $user = Auth::user();
        $isAdmin = $user && $user->is_admin;

        if ($ledger->status != 'PEND' && !($isAdmin && $ledger->status == 'APRV')) {
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
                Rule::unique('ledgers','name')->where(fn (Builder $query) => $query->where('ledger_account_id', $ledger->ledger_account_id)->where('id','!=',$id))
            ],
            'ledger_type_id' => [
                'nullable',
                'exists:ledger_types,id'
            ],
            'ledger_type' => [
                'nullable',
                'exists:ledger_types,id'
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
        
        if ($request->has('ledger_type_id') || $request->has('ledger_type')) {
            $ledger_type_val = $request->input('ledger_type_id') ?? $request->input('ledger_type');
            $ledger->ledger_type_id = $ledger_type_val ? (int) $ledger_type_val : null;
        }

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


    public function search(Request $request)
    {
        $ledger_types = LedgerType::orderBy('text', 'asc')->get();
        $account_categories = \App\Models\AccountTypeCategory::orderBy('name', 'asc')->get();

        return view('ledger/search', [
            'ledger_types' => $ledger_types,
            'account_categories' => $account_categories,
        ]);
    }

    public function _list(Request $request){
        
        $page               = (int) ($request->input('page') ?: 1);
        $limit              = (int) ($request->input('limit') ?: 10);
        $ledger_account_id  = (int) ($request->input('ledger_account_id') ?? 0);
        $orderBy            = $request->input('order_by')       ?? 'id';
        $order              = $request->input('order')          ?? 'DESC';
        $query              = $request->input('query')          ?? '';
        $status             = $request->input('status')         ?? '';
        
        $ledger_type_id     = $request->input('ledger_type_id') ?? '';
        $type_category      = $request->input('type_category')  ?? '';
        $employee_name      = $request->input('employee_name')  ?? '';
        $no_employee        = filter_var($request->input('no_employee'), FILTER_VALIDATE_BOOLEAN);

        $result     = [];

        $table = Ledger::with(['LedgerType', 'Account.Employee']);

        if ($ledger_account_id > 0) {
            $table = $table->where('ledger_account_id', $ledger_account_id);
        }

        if($query != ''){
            $table = $table->where('name','LIKE','%'.$query.'%');
        }

        if($status != ''){
            $table = $table->where('status','=',$status);
        }

        if ($ledger_type_id != '') {
            $table = $table->where('ledger_type_id', $ledger_type_id);
        }

        if ($type_category != '' || $employee_name != '' || $no_employee) {
            $table = $table->whereHas('Account', function ($q) use ($type_category, $employee_name, $no_employee) {
                if ($type_category != '') {
                    $q->where('type_category', $type_category);
                }
                
                if ($no_employee) {
                    $q->where(function ($subQ) {
                        $subQ->whereNull('employee_id')->orWhere('employee_id', 0);
                    });
                } else if ($employee_name != '') {
                    $q->whereHas('Employee', function ($empQ) use ($employee_name) {
                        $empQ->where('firstname', 'LIKE', '%' . $employee_name . '%')
                             ->orWhere('lastname', 'LIKE', '%' . $employee_name . '%');
                    });
                }
            });
        }

        if($limit > 0){
            $page   = ($page-1) * $limit;
            
            $result = $table->orderBy($orderBy,$order)->skip($page)->take($limit)->get();
            
        }else{

            $result = $table->orderBy($orderBy,$order)->take($limit)->get();
        }

        foreach ($result as $ledger) {
            $ledger->total_amount = $ledger->getTotalAmount(['APRV']);
            $ledger->total_amount_formatted = number_format($ledger->total_amount, 2);
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

        $ledger = Ledger::with('LedgerType')->findOrFail($id);

        $account = $ledger->Account;

        $entries = $ledger->Entries()->where('status','APRV')->get();

        return view('ledger/print',[
            'ledger'     => $ledger,
            'account'    => $account,
            'entries'    => $entries
        ]);
    }

    public function logs($id){
        $id = (int) $id;
        $ledger = Ledger::with('Account')->findOrFail($id);
        $logs = $ledger->updateLogs()->with('user')->paginate(15);

        return view('ledger/logs', [
            'ledger'  => $ledger,
            'account' => $ledger->Account,
            'logs'    => $logs
        ]);
    }
}