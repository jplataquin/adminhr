<x-app-layout>

    <div class="border border-1 rounded-lg shadow relative m-10">

        <div class="flex items-start justify-between p-5 border-b rounded-t">
            <h3 class="text-xl font-semibold dark:text-white">
                Review Ledger Entry
            </h3>
        </div>

        <div class="p-6 space-y-6">
            <form id="form" autocomplete="off">
                <div class="grid grid-cols-12 gap-6">
                    <div class="col-span-12 mb-3">
                        <x-text-input label="Account" name="account" id="account" disabled="true" value="{{$ledger_account->name}}"></x-text-input>
                    </div>
                    <div class="col-span-12 mb-3">
                        <x-text-input label="Ledger" name="ledger" id="ledger" disabled="true" value="{{$ledger->name}}"></x-text-input>
                    </div>
                    

                    <div class="lg:col-span-6 md:col-span-6 col-span-12 mb-3">
                        <x-text-input label="Status" name="status" id="status" disabled="true" value="{{$ledger_entry->status}}"></x-text-input>
                    </div>

                    <div class="lg:col-span-6 md:col-span-6 col-span-12 mb-3">
                        <x-text-input label="Date"  name="date" id="date" disabled="true" value="{{$ledger_entry->date}}"></x-text-input>
                    </div>

                    <div class="lg:col-span-6 md:col-span-6 col-span-12 mb-3">
                        <x-select-input label="Type" name="type" id="type" disabled="true">
                            @foreach($ledger_entry->type_options() as $value=>$text)
                                <option value="{{$value}}" @if($ledger_entry->type == $value) selected @endif>{{$text}}</option>
                            @endforeach
                        </x-select-input>
                    </div>

                    

                    <div class="lg:col-span-6 md:col-span-6 col-span-12 mb-3">
                        <x-select-input label="Tag" name="tag" id="tag" disabled="true">
                            @foreach($ledger_entry->tag_options() as $value=>$text)
                                <option value="{{$value}}" @if($ledger_entry->tag == $value) selected @endif>{{$text}}</option>
                            @endforeach
                        </x-select-input>
                    </div>


                    <div class="lg:col-span-6 md:col-span-6 col-span-12 mb-3">
                        <x-text-input label="Quantity ({{$ledger->unit}})" name="quantity" id="quantity" disabled="true" value="{{$ledger_entry->quantity}}"></x-text-input>
                    </div>

                    <div class="lg:col-span-6 md:col-span-6 col-span-12 mb-3">
                        <x-text-input label="Unit Amount"  name="unit_amount" id="unit_amount" disabled="true" value="{{$ledger_entry->unit_amount}}"></x-text-input>
                    </div>

                    <div class="col-span-12 mb-3">
                        <x-text-input label="Amount" name="amount" id="amount" disabled="true" value="{{$ledger_entry->amount()}}"></x-text-input>
                    </div>

                    <div class="col-span-12 mb-3">
                        <x-textarea-input label="Particular" name="particular" id="particular" disabled="true">{{$ledger_entry->particular}}</x-textarea-input>
                    </div>

                    <div class="col-span-12 mb-3">
                        <x-record-meta :record="$ledger_entry"></x-record-meta>
                    </div>
                </div>
            </form>
        </div>

        <div class="p-6 border-t border-gray-200 rounded-b flow-root">
             
            <x-review-controls :record="$ledger_entry"></x-review-controls>
            
        </div>

    </div>

    <script type="module">
        

        controls.onApproveDeleteClick = ()=>{
            alert('approve');
        }
        
        controls.onRejectDeleteClick  = ()=>{

            $ui.confirm('Reject deletion of this Ledger Entry?').then(action=>{

                if(!action.isConfirmed){
                    return false;
                }

                $ui.blockUI();

                $_POST('/api/review/ledger/entry/delete/reject',{
                    id: '{{$ledger_entry->id}}'
                }).then(reply=>{
                    $ui.unblockUI();

                    if(reply.status <= 0){
                        return $ui.showError(reply);
                    }

                    $reload();
                });
            });
        }


        controls.onApproveDeleteClick  = ()=>{
            
            $ui.confirm('Approve deletion of this Ledger Entry?').then(action=>{

                if(!action.isConfirmed){
                    return false;
                }

                $ui.blockUI();

                $_POST('/api/review/ledger/entry/delete/approve',{
                    id: '{{$ledger_entry->id}}'
                }).then(reply=>{
                    $ui.unblockUI();

                    if(reply.status <= 0){
                        return $ui.showError(reply);
                    }

                    $url('/review/ledger/entries');
                });
            });
        }
        
        controls.onApproveClick = ()=>{
            
            $ui.confirm('Approve this Ledger Entry?').then(action=>{

                if(!action.isConfirmed){
                    return false;
                }

                $ui.blockUI();

                $_POST('/api/review/ledger/entry/approve',{
                    id: '{{$ledger_entry->id}}'
                }).then(reply=>{
                    $ui.unblockUI();

                    if(reply.status <= 0){
                        return $ui.showError(reply);
                    }

                    $reload();
                });
            });

            
        }

        controls.onRejectClick = ()=>{
            
            $ui.blockUI();

            $ui.confirm('Reject this Ledger Entry?').then(action=>{

                if(!action.isConfirmed){
                    return false;
                }

                $_POST('/api/review/ledger/reject',{
                    id: '{{$ledger_entry->id}}'
                }).then(reply=>{
                    $ui.unblockUI();

                    if(reply.status <= 0){
                        return $ui.showError(reply);
                    }

                    $reload();
                });
            });
        }
        
        
        controls.onCancelClick = ()=>{
            $url('/review/ledger/entries');
        }

    </script>
</x-app-layout>