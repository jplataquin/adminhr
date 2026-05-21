<x-app-layout>

    <div class="card shadow-sm border-0 m-4">
        <div class="card-header bg-body py-3">
            <h3 class="h5 mb-0">
                Ledger
            </h3>
        </div>

        <div class="card-body p-4">
            <form id="form" autocomplete="off">
                <div class="mb-3">
                    <x-text-input label="Account" name="account" id="account" disabled="true" value="{{$ledger_account->name}}"></x-text-input>
                </div>

                <div class="mb-3">
                    <x-text-input label="Name" name="name" class="editable" id="name" disabled="true" value="{{$ledger->name}}"></x-text-input>
                </div>
                <div class="mb-3">
                    <x-text-input label="Status" name="status" id="status" disabled="true" value="{{$ledger->status}}"></x-text-input>
                </div>
                <div class="mb-3">
                    <x-textarea-input label="Description" class="editable" name="description" id="description" disabled="true">
                    {{$ledger->description}}
                    </x-text-input>
                </div>

                <div class="mb-3">
                    <x-textarea-input label="Template" class="editable" name="template" id="template" disabled="true">
                    {{$ledger->template}}
                    </x-text-input>
                </div>

                <div class="mb-3">
                    <x-text-input label="Unit" class="editable" name="unit" id="unit" disabled="true" value="{{$ledger->unit}}"></x-text-input>
                </div>
                
                <div class="mb-3">
                    <x-record-meta :record="$ledger"></x-record-meta>
                </div>
            </form> 
        </div>

        <div class="card-footer bg-body py-3 border-top">
            <x-display-controls status="{{$ledger->status}}">
                @if($ledger->status == 'PEND')
                    <x-slot:right>
                        <x-primary-button class="me-2" id="reviewLinkBtn" >Review Link</x-primary-button>
                    </x-slot>
                @endif
            </x-display-controls>
        </div>
    </div>

    <div class="card shadow-sm border-0 m-4 mt-5">
        <div class="card-header bg-body py-3 d-flex justify-content-between align-items-center">
            <h3 class="h5 mb-0">Entries</h3>
            <div>
                <button class="btn btn-primary btn-sm me-2" id="printBtn">Print</button>
                <button class="btn btn-primary btn-sm" id="addBtn">Add Entry</button>
            </div>
        </div>

        <div class="card-body">
            <div class="row text-center mb-4 g-2">
                <div class="col-3">
                    <div class="p-2 border rounded bg-body-tertiary">
                        <h4 id="total_credit" class="h5 mb-1">0.00</h4>
                        <small class="text-success text-uppercase fw-bold">(+) Credit</small>
                    </div>
                </div>
                <div class="col-3">
                    <div class="p-2 border rounded bg-body-tertiary">
                        <h4 id="running_amount" class="h5 mb-1">0.00</h4>
                        <small class="text-primary text-uppercase fw-bold">Amount</small>
                    </div>
                </div>
                <div class="col-3">
                    <div class="p-2 border rounded bg-body-tertiary">
                        <h4 id="running_quantity" class="h5 mb-1">0.00</h4>
                        <small class="text-info text-uppercase fw-bold">{{$ledger->unit}}</small>
                    </div>
                </div>
                <div class="col-3">
                    <div class="p-2 border rounded bg-body-tertiary">
                        <h4 id="total_debit" class="h5 mb-1">0.00</h4>
                        <small class="text-danger text-uppercase fw-bold">(-) Debit</small>
                    </div>
                </div>
            </div>    

            <div class="table-responsive">
                <table class="table table-hover align-middle small">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center">Status</th>
                            <th class="text-center">Tag</th>
                            <th>Particular</th>
                            <th class="text-center">Date</th>
                            <th class="text-center">Quantity</th>
                            <th class="text-center">Unit Amount</th>
                            <th class="text-center text-success">(+) Credit</th>
                            <th class="text-center text-danger">(-) Debit</th>
                        </tr>
                    </thead>
                    <tbody id="list">
                    </tbody>
                </table>
            </div>

            <div class="text-center mt-3 pt-3 border-top">
                <button class="btn btn-secondary" id="showMoreBtn">Show More</button>
            </div>
        </div>
    </div>

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


        controls.onRequestDeleteClick = ()=>{
            $ui.blockUI();
            $ui.confirm('You want to request the deletion of this Ledger?').then(action=>{

                if(!action.isConfirmed){
                    return false;
                }

                $_POST('/api/ledger/request/delete/',{
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
        
        controls.onRevertClick = () =>{
            $ui.confirm('This will revert the Ledger to status Pending?').then(action=>{

                if(!action.isConfirmed){
                    return false;
                }

                $ui.blockUI();

                $_POST('/api/ledger/revert',{
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

    </script>

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
        
        // Apply bootstrap classes to manually created elements
        entry_particular.className  = 'form-control';
        entry_tag.className         = 'form-select';
        entry_type.className        = 'form-select';
        entry_quantity.className    = 'form-control';
        entry_unit_amount.className = 'form-control';
        entry_amount.className      = 'form-control';
        entry_date.className        = 'form-control';
        entry_add_btn.className     = 'btn btn-primary float-end';

        entry_particular.value = `{{$ledger->template}}`;

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

        const add_entry_form = t.div({class:'p-4 space-y-4 mb-4'},()=>{

            t.div({class:'row g-3'},()=>{

                t.div({class:'col-12'},(el)=>{
                    el.append($t.label('Particular','form-label'));
                    el.append(entry_particular);
                });

                t.div({class:'col-md-6'},(el)=>{
                    el.append($t.label('Tag','form-label'));
                    el.append(entry_tag);
                });

                t.div({class:'col-md-6'},(el)=>{
                    el.append($t.label('Type','form-label'));
                    el.append(entry_type);
                });

                t.div({class:'col-md-6'},(el)=>{
                    el.append($t.label('Date (yyyy-mm-dd)','form-label'));
                    el.append(entry_date);
                });

                t.div({class:'col-md-6'},(el)=>{
                    el.append($t.label('Quantity ({{$ledger->unit}})','form-label'));
                    el.append(entry_quantity);
                });

                t.div({class:'col-md-6'},(el)=>{
                    el.append($t.label('Unit Amount','form-label'));
                    el.append(entry_unit_amount);
                });

                t.div({class:'col-md-6'},(el)=>{
                    el.append($t.label('Amount','form-label'));
                    el.append(entry_amount);
                });

            });//div

            t.div({class: 'mt-4 clearfix'},(el)=>{
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

                let row = t.tr({class:'cursor-pointer'},()=>{

                    t.td({class:'text-center'},item.status);
                    t.td({class:'text-center'},item.tag);
                    t.td(item.particular.replace(/\n/g, "<br />"));
                    t.td({class:'text-center'},item.date);
                    t.td({class:'text-center'},''+item.quantity);
                    t.td({class:'text-center'},''+item.unit_amount);
                    
                    let credit = '0.00';
                    let debit  = '0.00';
                    
                    if(item.type == 'CRED'){
                        credit = $numberFormat(item.unit_amount * item.quantity,2);
                    }else{
                        debit = $numberFormat(item.unit_amount * item.quantity,2);
                    }

                    t.td({class:'text-center text-success fw-bold'},credit);
                    t.td({class:'text-center text-danger fw-bold'},debit);

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
                    showMoreBtn.classList.add('d-none');
                }
                
                total_credit.innerText      = $numberFormat(reply.data.total_credit,2);
                total_debit.innerText       = $numberFormat(reply.data.total_debit,2);
                running_quantity.innerText  = $numberFormat(reply.data.total_quantity);
                running_amount.innerText    = $numberFormat(reply.data.total_credit - reply.data.total_debit,2);
            });
        }
    
        showMoreBtn.onclick = ()=>{
            showData();
        }

        reinitalize();
        showData();
    </script>
</x-app-layout>
