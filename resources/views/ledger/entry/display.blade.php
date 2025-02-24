<x-app-layout>

    <div class="border border-1 rounded-lg shadow relative m-10">

        <div class="flex items-start justify-between p-5 border-b rounded-t">
            <h3 class="text-xl font-semibold dark:text-white">
                Ledger Entry
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
                        <x-text-input label="Date" class="editable" name="date" id="date" disabled="true" value="{{$ledger_entry->date}}"></x-text-input>
                    </div>

                    <div class="lg:col-span-6 md:col-span-6 col-span-12 mb-3">
                        <x-select-input label="Type" class="editable" name="type" id="type" disabled="true">
                            @foreach($ledger_entry->type_options() as $value=>$text)
                                <option value="{{$value}}" @if($ledger_entry->type == $value) selected @endif>{{$text}}</option>
                            @endforeach
                        </x-select-input>
                    </div>

                    

                    <div class="lg:col-span-6 md:col-span-6 col-span-12 mb-3">
                        <x-select-input label="Tag" class="editable" name="tag" id="tag" disabled="true">
                            @foreach($ledger_entry->tag_options() as $value=>$text)
                                <option value="{{$value}}" @if($ledger_entry->tag == $value) selected @endif>{{$text}}</option>
                            @endforeach
                        </x-select-input>
                    </div>


                    <div class="lg:col-span-6 md:col-span-6 col-span-12 mb-3">
                        <x-text-input label="Quantity ({{$ledger->unit}})" class="editable" name="quantity" id="quantity" disabled="true" value="{{$ledger_entry->quantity}}"></x-text-input>
                    </div>

                    <div class="lg:col-span-6 md:col-span-6 col-span-12 mb-3">
                        <x-text-input label="Unit Amount" class="editable" name="unit_amount" id="unit_amount" disabled="true" value="{{$ledger_entry->unit_amount}}"></x-text-input>
                    </div>

                    <div class="col-span-12 mb-3">
                        <x-text-input label="Amount" name="amount" id="amount" disabled="true" value="{{$ledger_entry->amount()}}"></x-text-input>
                    </div>

                    <div class="col-span-12 mb-3">
                        <x-textarea-input label="Particular" class="editable" name="particular" id="particular" disabled="true">{{$ledger_entry->particular}}</x-textarea-input>
                    </div>

                    <div class="col-span-12 mb-3">
                        <x-record-meta :record="$ledger_entry"></x-record-meta>
                    </div>
                </div>
            </form>
        </div>

        <div class="p-6 border-t border-gray-200 rounded-b flow-root">
             
            <x-display-controls status="{{$ledger_entry->status}}">
                @if($ledger_entry->status == 'PEND')
                    <x-slot:right>
                        <x-primary-button class="me-2" id="reviewLinkBtn" >Review Link</x-primary-button>
                    </x-slot>
                @endif
            </x-display-controls>
            
        </div>

    </div>

    <script type="module">
        import {$q} from '/adarna.js';

        if(typeof reviewLinkBtn != 'undefined'){

            reviewLinkBtn.onclick = async ()=>{
                let test = await $copyToClipboard('{{ url("/review/ledger/entry/".$ledger_entry->id); }}');
                if(test){
                    alert('Review Link for "Ledger Entry: {{$ledger_entry->id}}" copied!');
                }else{
                    alert('Failed to copy');
                }
            }
        }

        $numbersOnlyInput([
            quantity,
            unit_amount
        ],{precision:2});

        $dateOnlyInput(date);

        function calculate(){
            amount.value = $numberFormat( $pureNumber(quantity.value) * $pureNumber(unit_amount.value),2);
        }

        quantity.onkeyup = ()=>{
           calculate();
        }

        unit_amount.onkeyup = ()=>{
           calculate();
        }

        
        controls.onCancelClick = ()=>{
            $url('/ledger/{{$ledger->id}}');
        }


        controls.onRequestDeleteClick = ()=>{
            $ui.blockUI();
            $ui.confirm('Request Delete for this Ledger Entry?').then(action=>{

                if(!action.isConfirmed){
                    return false;
                }

                $_POST('/api/ledger/entry/request/delete/',{
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

        @if($ledger_entry->status == 'PEND')

            controls.onEditCancel = ()=>{
                return $url('/ledger/{{$ledger->id}}');
            }

            controls.onEditClick = ()=>{
                $q('.editable').items().map(item=>{
                    item.prevValue = item.value;
                    item.disabled = false;
                });
            }

            controls.onUpdateCancel = ()=>{
                
                $q('.editable').items().map(item=>{
                    item.value      = item.prevValue;
                    item.prevValue  = '';
                    item.disabled   = true;
                });

                calculate();
            }

            controls.onUpdateClick = ()=>{

                $ui.blockUI();

                unit_amount.value = $pureDecimal(unit_amount.value,2);

                $_POST('/api/ledger/entry/update/{{$ledger_entry->id}}',form).then(reply=>{

                    $ui.unblockUI();
                
                    if(reply.status <= 0){
                        return $ui.showError(reply);
                    }

                    $reload();
                });
            }

            controls.onDeleteClick = ()=>{
                
                $ui.blockUI();
                $ui.confirm('Delete this Ledger Entry?').then(action=>{

                    if(!action.isConfirmed){
                        return false;
                    }

                    $_POST('/api/ledger/entry/delete/',{
                        id: '{{$ledger_entry->id}}'
                    }).then(reply=>{

                        $ui.unblockUI();
                        
                        if(reply.status <= 0){
                            return $ui.showError(reply);
                        }

                        $url('/ledger/{{$ledger->id}}');
                    });
                });
            }

        @endif

        calculate();
    </script>
</x-app-layout>