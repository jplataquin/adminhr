<x-app-layout>

    <div class="card shadow-sm border-0 m-4">
        <div class="card-header bg-body py-3">
            <h3 class="h5 mb-0">
                Review Ledger
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
            <x-review-controls :record='$ledger'></x-review-controls>
        </div>
    </div>

    <div class="card shadow-sm border-0 m-4 mt-5">
        <div class="card-header bg-body py-3">
            <h3 class="h5 mb-0">Entries</h3>
        </div>

        <div class="card-body">
            <div class="row text-center mb-4 g-2">
                <div class="col-3">
                    <div class="p-2 border rounded bg-body-tertiary">
                        <h4 id="total_credit" class="h5 mb-1">{{ number_format($total_credit,2) }}</h4>
                        <small class="text-success text-uppercase fw-bold">(+) Credit</small>
                    </div>
                </div>
                <div class="col-3">
                    <div class="p-2 border rounded bg-body-tertiary">
                        <h4 id="running_amount" class="h5 mb-1">{{ number_format($total_amount,2) }}</h4>
                        <small class="text-primary text-uppercase fw-bold">Amount</small>
                    </div>
                </div>
                <div class="col-3">
                    <div class="p-2 border rounded bg-body-tertiary">
                        <h4 id="running_quantity" class="h5 mb-1">{{ number_format($total_quantity,2) }}</h4>
                        <small class="text-info text-uppercase fw-bold">{{$ledger->unit}}</small>
                    </div>
                </div>
                <div class="col-3">
                    <div class="p-2 border rounded bg-body-tertiary">
                        <h4 id="total_debit" class="h5 mb-1">{{ number_format($total_debit,2) }}</h4>
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
