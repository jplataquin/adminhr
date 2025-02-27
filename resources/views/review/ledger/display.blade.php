<x-app-layout>

    <div class="border border-1 rounded-lg shadow relative m-10">

        <div class="flex items-start justify-between p-5 border-b rounded-t">
            <h3 class="text-xl font-semibold dark:text-white">
                Review Ledger
            </h3>
        </div>

        <div class="p-6 space-y-6">
            <form id="form" autocomplete="off">
                <div class="col-span-12 sm:col-span-12 mb-3">
                    <x-text-input label="Account" name="account" id="account" disabled="true" value="{{$ledger_account->name}}"></x-text-input>
                </div>

                <div class="col-span-12 sm:col-span-12 mb-3">
                    <x-text-input label="Name" name="name" class="editable" id="name" disabled="true" value="{{$ledger->name}}"></x-text-input>
                </div>
                <div class="col-span-12 sm:col-span-12 mb-3">
                    <x-text-input label="Status" name="status" id="status" disabled="true" value="{{$ledger->status}}"></x-text-input>
                </div>
                <div class="col-span-12 sm:col-span-12 mb-3">
                    <x-textarea-input label="Description" class="editable" name="description" id="description" disabled="true">
                    {{$ledger->description}}
                    </x-text-input>
                </div>

                <div class="col-span-12 sm:col-span-12 mb-3">
                    <x-textarea-input label="Template" class="editable" name="template" id="template" disabled="true">
                    {{$ledger->template}}
                    </x-text-input>
                </div>

                <div class="col-span-12 sm:col-span-12 mb-3">
                    <x-text-input label="Unit" class="editable" name="unit" id="unit" disabled="true" value="{{$ledger->unit}}"></x-text-input>
                </div>
                
                <div class="lg:col-span-12 sm:col-span-12 mb-3">
                    <x-record-meta></x-record-meta>
                </div>
            </form> 
        </div>

        <div class="p-6 border-t border-gray-200 rounded-b flow-root">
    
            <x-review-controls :record='$ledger'></x-review-controls>
            <script type="module">
                controls.onApproveClick = ()=>{
                    
                    $ui.confirm('Approve this Ledger?').then(action=>{

                        if(!action.isConfirmed){
                            return false;
                        }

                        $ui.blockUI();

                        $_POST('/api/review/ledger/approve',{
                            id: '{{$ledger->id}}'
                        }).then(reply=>{
                            $ui.unblockUI();

                            if(reply.status <= 0){
                                return $ui.showError(reply);
                            }

                            $reload();
                        });
                    });

                    
                }

                controls.onApproveDeleteClick = ()=>{

                    $ui.confirm('You want to delete this Ledger and all of its contents?').then(action=>{

                        if(!action.isConfirmed){
                            return false;
                        }

                        $ui.blockUI();

                        $_POST('/api/review/ledger/delete/approve',{
                            id: '{{$ledger->id}}'
                        }).then(reply=>{
                            $ui.unblockUI();

                                if(reply.status <= 0){
                                    return $ui.showError(reply);
                                }

                                $url('/review/ledgers');
                            });
                        });
                    }

                    controls.onRejectDeleteClick  = ()=>{


                        $ui.blockUI();

                        $_POST('/api/review/ledger/delete/reject',{
                            id: '{{$ledger->id}}'
                        }).then(reply=>{
                            $ui.unblockUI();

                            if(reply.status <= 0){
                                return $ui.showError(reply);
                            }

                            $reload();
                        });

                    }

                    controls.onRejectClick = ()=>{

                    $ui.blockUI();

                    $ui.confirm('Reject this Ledger Account?').then(action=>{

                        if(!action.isConfirmed){
                            return false;
                        }

                        $_POST('/api/review/ledger/reject',{
                            id: '{{$ledger->id}}'
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

                    $ui.confirm('Reject this Ledger?').then(action=>{

                        if(!action.isConfirmed){
                            return false;
                        }

                        $_POST('/api/review/ledger/reject',{
                            id: '{{$ledger->id}}'
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
                    $url('/review/ledgers');
                }

            </script>
        </div>


        
    </div>


    <div class="border border-1 rounded-lg shadow relative m-10">
        
        <div class="flex items-start justify-between p-5 border-b rounded-t">
            <h3 class="text-xl font-semibold dark:text-white">
                Entries
            </h3>

        </div>

        <div class="p-6 space-y-6 overflow-x-auto">
            <div class="grid grid-cols-4 gap-4">
                <div class="text-center text-gray-500 dark:text-gray-400">
                    <h4 id="total_credit">{{ number_format($total_credit,2) }}</h4>
                    <h6>(+) Credit</h6>
                </div>
                <div class="text-center text-gray-500 dark:text-gray-400">
                    <h4 id="running_amount">{{ number_format($total_amount,2) }}</h4>
                    <h6>Amount</h6>
                </div>
                <div class="text-center text-gray-500 dark:text-gray-400">
                    <h4 id="running_quantity">{{ number_format($total_quantity,2) }}</h4>
                    <h6>{{$ledger->unit}}</h6>
                </div>
                <div class="text-center text-gray-500 dark:text-gray-400">
                    <h4 id="total_debit">{{ number_format($total_debit,2) }}</h4>
                    <h6>(-) Debit</h6>
                </div>
            </div>    

            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3 w-24 text-center">
                            Status
                        </th>
                        
                        <th scope="col" class="px-6 py-3 w-24 text-center">
                            Tag
                        </th>

                        <th scope="col" class="px-6 py-3 w-80 text-center">
                            Particular
                        </th>
                        <th scope="col" class="px-6 py-3 text-center">
                            Date
                        </th>
                        <th scope="col" class="px-6 py-3 text-center">
                            Quantity
                        </th>
                        <th scope="col" class="px-6 py-3 text-center">
                            Unit Amount
                        </th>
                        <th scope="col" class="px-6 py-3 text-center">
                            (+) Credit
                        </th>
                        <th scope="col" class="px-6 py-3 text-center">
                            (-) Debit
                        </th>
                    </tr>
                </thead>
                <tbody id="list">
                </tbody>
            </table>
        </div>

        <div class="p-6 border-t border-gray-200 rounded-b flow-root text-center">
            <button class="text-white bg-cyan-600 hover:bg-cyan-700 focus:ring-4 focus:ring-cyan-200 font-medium rounded-lg text-sm px-5 py-2.5 text-center" id="showMoreBtn">Show More</button>
        </div>
    </div>

    <script type="module">
        import {Template, $util, $el} from '/adarna.js';

        const t = new Template();

        let page            = 1;
        let order           = 'DESC';
        let orderBy         = 'id';
        
        
        function reinitalize(){
            page       = 1;
            order      = 'DESC';
            orderBy    = 'id';
            $el.clear(list);
            
            total_credit.innerText      = '';
            total_debit.innerText       = '';
            running_amount.innerText    = '';
            running_quantity.innerText  = '';
        }

        function renderRows(data){
            
            data.map(item=>{

                let row = t.tr({class:'bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200 cursor-pointer'},()=>{

                    t.td({class:'text-center align-top'},item.status);
                    t.td({class:'text-center align-top'},item.tag);
                    t.td(item.particular.replace(/\n/g, "<br />"));
                    t.td({class:'text-center align-top'},item.date);
                    t.td({class:'text-center align-top'},''+item.quantity);
                    t.td({class:'text-center align-top'},''+item.unit_amount);
                    
                    let credit = '0.00';
                    let debit  = '0.00';
                    
                    if(item.type == 'CRED'){
                        credit = $numberFormat(item.unit_amount * item.quantity,2);
                    }else{
                        debit = $numberFormat(item.unit_amount * item.quantity,2);
                    }

                    t.td({class:'text-center align-top'},credit);
                    t.td({class:'text-center align-top'},debit);

                });

                row.onclick = ()=>{
                    $url('/ledger/entry/'+item.id);
                }

                $el.append(row).to(list);
                
            });

        }

        function showData(){


            $_GET('/api/ledger/{{$ledger->id}}/entries',{
                query: '',
                page: page,
                order: order,
                status:'APRV',
                order_by: orderBy,
                limit: 10
            }).then(reply=>{

                
                if(reply.status <= 0 ){
                    
                    $ui.showMsg(reply);
                    return false;
                };

                page++;

                if(reply.data.rows.length){
                    renderRows(reply.data.rows); 
                }else{
                    showMoreBtn.style.display = 'none';
                }
                
                total_credit.innerText      = $numberFormat(reply.data.total_credit,2);
                total_debit.innerText       = $numberFormat(reply.data.total_debit,2);
                running_quantity.innerText  = $numberFormat(reply.data.total_quantity);
                running_amount.innerText    = $numberFormat(reply.data.total_credit - reply.data.total_debit,2);
            });
        }
    
        // searchBtn.onclick = ()=>{
        //     showMoreBtn.style.display = 'block';
        //     reinitalize();
        //     showData();
        // }

        showMoreBtn.onclick = ()=>{
            showData();
        }

        // sortSelect.onchange = ()=>{
        //     reinitalize();

        //     let select = parseInt(sortSelect.value);

        //     switch(select){
        //         case 1:
        //             order   = 'ASC';
        //             orderBy = 'name';
        //             break;
        //         case 2:
        //             order   = 'DESC';
        //             orderBy = 'name';
        //             break;
        //         case 3:
        //             order   = 'DESC';
        //             orderBy = 'id';
        //             break;
        //         case 4:
        //             order   = 'ASC';
        //             orderBy = 'id';
        //         break;
        //     }

        //     showData();
        // }

        reinitalize();
        showData();
    </script>
</x-app-layout>