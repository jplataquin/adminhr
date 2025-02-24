<x-app-layout>

    <div class="border border-1 rounded-lg shadow relative m-10">

        <div class="flex items-start justify-between p-5 border-b rounded-t">
            <h3 class="text-xl font-semibold dark:text-white">
                Ledger
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
                    <x-record-meta :record="$ledger"></x-record-meta>
                </div>
            </form> 
        </div>

        <div class="p-6 border-t border-gray-200 rounded-b flow-root">
            
            <x-display-controls status="{{$ledger->status}}">
                @if($ledger->status == 'PEND')
                    <x-slot:right>
                        <x-primary-button class="me-2" id="reviewLinkBtn" >Review Link</x-primary-button>
                    </x-slot>
                @endif
            </x-display-controls>
            <script type="module">
                import {$q} from '/adarna.js';

                if(typeof reviewLinkBtn != 'undefined'){

                    reviewLinkBtn.onclick = async ()=>{
                        let test = await $copyToClipboard('{{ url("/review/ledger/".$ledger->id); }}');
                        if(test){
                            alert('Review Link for "Ledger: {{$ledger->id}}" copied!');
                        }else{
                            alert('Failed to copy');
                        }
                    }
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
                }

                
                controls.onUpdateClick = ()=>{

                    $ui.blockUI();

                    $_POST('/api/ledger/update/{{$ledger->id}}',form).then(reply=>{

                        $ui.unblockUI();

                        if(reply.status <= 0){
                            return $ui.showError(reply);
                        }

                        $reload();
                    });
                }

                controls.onDeleteClick = ()=>{

                    $ui.blockUI();
                        
                    $_POST('/api/ledger/delete/',{
                        id: '{{$ledger->id}}'
                    }).then(reply=>{

                        $ui.unblockUI();
                        
                        if(reply.status <= 0){
                            return $ui.showError(reply);
                        }

                        $url('/ledger/account/{{$ledger_account->id}}');
                    });
                }

                controls.onCancelClick =() =>{
                    $url('/ledger/account/{{$ledger_account->id}}');
                }

            </script>
        </div>


        
    </div>


    <div class="border border-1 rounded-lg shadow relative m-10">
        
        <div class="flow-root p-5 border-b rounded-t">
            <div class="float-root">
                <div class="float-left">
                    <h3 class="text-xl font-semibold dark:text-white">
                        Entries
                    </h3>
                </div>
                
                <div class="float-right">
                    <button class="me-2 text-white bg-cyan-600 hover:bg-cyan-700 focus:ring-4 focus:ring-cyan-200 font-medium rounded-lg text-sm px-5 py-2.5 text-center" id="printBtn">Print</button>
            
                    <button class="text-white bg-cyan-600 hover:bg-cyan-700 focus:ring-4 focus:ring-cyan-200 font-medium rounded-lg text-sm px-5 py-2.5 text-center" id="addBtn">Add Entry</button>
                </div>
            </div>
        </div>

        <div class="p-6 space-y-6 overflow-x-auto">
            <div class="grid grid-cols-4 gap-4">
                <div class="text-center text-gray-500 dark:text-gray-400">
                    <h4 id="total_credit">0.00</h4>
                    <h6>(+) Credit</h6>
                </div>
                <div class="text-center text-gray-500 dark:text-gray-400">
                    <h4 id="running_amount">0.00</h4>
                    <h6>Amount</h6>
                </div>
                <div class="text-center text-gray-500 dark:text-gray-400">
                    <h4 id="running_quantity">0.00</h4>
                    <h6>{{$ledger->unit}}</h6>
                </div>
                <div class="text-center text-gray-500 dark:text-gray-400">
                    <h4 id="total_debit">0.00</h4>
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

        const entry_particular      = $t.textarea();
        const entry_tag             = $t.select( @json($ledger_entry->tag_options()) );
        const entry_type            = $t.select( @json($ledger_entry->type_options()) );
        const entry_quantity        = $t.text_input();
        const entry_unit_amount     = $t.text_input();
        const entry_amount          = $t.text_input();
        const entry_date            = $t.text_input();
        const entry_add_btn         = $t.button('Add');
        

        entry_particular.value = `{{$ledger->template}}`;

        entry_add_btn.classList.add('float-right');

        $numbersOnlyInput([
            entry_quantity,
            entry_unit_amount
        ],{precision:2});

        $dateOnlyInput(entry_date);

        let dt = $util.dateTime();

        entry_quantity.value        = 1;
        entry_unit_amount.value     = '0.00';
        entry_date.value            = dt.yyyy()+'-'+dt.MM()+'-'+dt.dd();

        entry_amount.disabled       = true;

        entry_amount.placeholder    = '0.00';
        entry_date.placeholder      = 'YYYY-MM-DD';

        function calculateAmount(){
            entry_amount.value = $pureDecimal( $pureNumber(entry_quantity.value) * $pureNumber(entry_unit_amount.value) , 2);
        }

        entry_quantity.onkeyup = ()=>{
            calculateAmount();
        }

        entry_unit_amount.onkeyup = ()=>{
            calculateAmount();
        }

        calculateAmount();

        const add_entry_form = t.div({class:'p-6 space-y-6 mb-10'},()=>{

            t.div({class:'grid grid-cols-12 gap-6 mb-5'},()=>{

                t.div({class:'col-span-12 sm:col-span-12'},(el)=>{
                    el.append($t.label('Particular'));
                    el.append(entry_particular);
                });

                t.div({class:'col-span-12 sm:col-span-12'},(el)=>{
                    el.append($t.label('Tag'));
                    el.append(entry_tag);
                });

                
                t.div({class:'col-span-12 sm:col-span-12'},(el)=>{
                    el.append($t.label('Type'));
                    el.append(entry_type);
                });

                t.div({class:'col-span-12 sm:col-span-12'},(el)=>{
                    el.append($t.label('Date (yyyy-mm-dd)'));
                    el.append(entry_date);
                });

                t.div({class:'col-span-12 sm:col-span-12'},(el)=>{
                    el.append($t.label('Quantity ({{$ledger->unit}})'));
                    el.append(entry_quantity);
                });

                t.div({class:'col-span-12 sm:col-span-12'},(el)=>{
                    el.append($t.label('Unit Amount'));
                    el.append(entry_unit_amount);
                });

                t.div({class:'col-span-12 sm:col-span-12'},(el)=>{
                    el.append($t.label('Amount'));
                    el.append(entry_amount);
                });

                

            });//div

            t.div((el)=>{
                el.append(entry_add_btn);
            });
            
        });

        addBtn.onclick = ()=>{

            $drawerModal.content('Add Entry',add_entry_form);
            $drawerModal.open();
        }


        entry_add_btn.onclick = ()=>{

            $_POST('/api/ledger/{{$ledger->id}}/entry/add',{
                particular: entry_particular.value,
                tag: entry_tag.value,
                type: entry_type.value,
                quantity: $pureNumber(entry_quantity.value),
                unit_amount: $pureDecimal(entry_unit_amount.value,2),
                date: entry_date.value
            }).then(reply=>{

                if(reply.status <= 0){

                    $ui.showError(reply);
                    return false;
                }

                
                $drawerModal.close();

                reinitalize();
                showData();
            });
        }

        printBtn.onclick = (e) => {
            let win = window.open('/ledger/print/{{$ledger->id}}','','width=600,height=600');

            win.focus();
           
        }
        
        /****************************************/

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