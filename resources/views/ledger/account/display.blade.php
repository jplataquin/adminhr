<x-ledger-layout>
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.4.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">

<div id="pageDoc">
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-body py-3 d-flex justify-content-between align-items-center">
            <h3 class="h5 mb-0">
                Ledger Account
            </h3>
            <a href="{{ route('ledger.account.logs', $ledger_account->id) }}" class="btn btn-outline-info btn-sm" id="showUpdateLogsBtn">
                <i class="bi bi-clock-history me-1"></i> Show Update Logs
            </a>
        </div>

        <div class="card-body p-4">
            <form id="form" autocomplete="off">
                <div class="mb-3">
                    <x-text-input class="editable" label="Name" name="name" id="name" disabled="true" value="{{$ledger_account->name}}"></x-text-input>
                </div>
                <div class="mb-3">
                    <x-text-input label="Status" name="status" id="status" disabled="true" value="{{$ledger_account->status}}"></x-text-input>
                </div>
                <div class="mb-3">
                    <label for="employee_id" class="form-label text-white">Employee (Searchable)</label>
                    <select id="employee_id" name="employee_id" class="form-control" placeholder="Search for an employee...">
                        @if($ledger_account->Employee)
                            <option value="{{$ledger_account->Employee->id}}" selected>{{$ledger_account->Employee->firstname}} {{$ledger_account->Employee->lastname}}</option>
                        @endif
                    </select>
                </div>

                <div class="mb-3">
                    <x-record-meta :record="$ledger_account"></x-record-meta>
                </div>
            </form>
        </div>

        <div class="card-footer bg-body py-3 border-top">
            <x-display-controls status="{{$ledger_account->status}}">
                   @if($ledger_account->status == 'PEND')
                        <x-slot:right>
                            <x-primary-button class="me-2" id="reviewLinkBtn" >Review Link</x-primary-button>
                        </x-slot>
                    @endif
            </x-display-controls>
        </div>
    </div>
    

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-body py-3 d-flex justify-content-between align-items-center">
            <h3 class="h5 mb-0">
                Ledgers
            </h3>
            <button class="btn btn-primary btn-sm" id="createBtn">Create Ledger</button>
        </div>
            
        <div class="card-body">
            <div class="mb-4">
                <x-text-input id="search" mode="2" label="Search"></x-text-input>
            </div>
            
            <div id="list" class="mt-4"></div>

            <div class="text-center mt-4 pt-3 border-top">
                <button class="btn btn-secondary" id="showMoreBtn">Show More</button>
            </div>
        </div>
    </div>
</div>

    <script type="module">
        import {$q} from '/adarna.js';
        import TomSelect from 'https://esm.sh/tom-select@2.4.1';

        const tomSelectInstance = new TomSelect("#employee_id", {
            valueField: 'id',
            labelField: 'name',
            searchField: ['name', 'firstname', 'lastname'],
            placeholder: 'Search and select an employee...',
            allowEmptyOption: true,
            preload: 'focus',
            load: function(query, callback) {
                var url = '/api/employees?query=' + encodeURIComponent(query || '') + '&limit=20';
                fetch(url, {
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                    .then(response => response.json())
                    .then(json => {
                        if (!json.data || !json.data.rows) {
                            return callback();
                        }
                        var mapped = json.data.rows.map(e => ({
                            id: e.id,
                            name: [e.firstname, e.middlename, e.lastname].filter(Boolean).join(' '),
                            firstname: e.firstname || '',
                            lastname: e.lastname || ''
                        }));
                        callback(mapped);
                    }).catch(()=>{
                        callback();
                    });
            }
        });

        tomSelectInstance.disable();

        const reviewLinkBtn = document.getElementById('reviewLinkBtn');
        if(reviewLinkBtn){

            reviewLinkBtn.onclick = async ()=>{
                let test = await $copyToClipboard('{{ url("/review/ledger/account/".$ledger_account->id); }}');
                if(test){
                    alert('Review Link for "Ledger: {{$ledger_account->id}}" copied!');
                }else{
                    alert('Failed to copy');
                }
            }
        }

        controls.onCancelClick = ()=>{
            $url('/ledger/accounts');
            return false;
        }

        controls.onEditClick = ()=>{
            $q('.editable').items().map(item=>{
                item.prevValue = item.value;
                item.disabled = false;
            });
            tomSelectInstance.enable();
        }

        controls.onUpdateCancel = ()=>{
            
            $q('.editable').items().map(item=>{
                item.value      = item.prevValue;
                item.prevValue  = '';
                item.disabled   = true;
            });
            tomSelectInstance.setValue('{{ $ledger_account->employee_id ?? "" }}');
            tomSelectInstance.disable();
        }


        controls.onUpdateClick = ()=>{

            $ui.blockUI();

            $_POST('/api/ledger/account/update/{{$ledger_account->id}}',form).then(reply=>{

                $ui.unblockUI();

                if(reply.status <= 0){
                    return $ui.showError(reply);
                }

                $reload();
            });
        }

        controls.onRequestDeleteClick = ()=>{
            $ui.blockUI();
            $ui.confirm('You want to request the deletion of this Ledger Account?').then(action=>{

                if(!action.isConfirmed){
                    return false;
                }

                $_POST('/api/ledger/account/request/delete/',{
                    id: '{{$ledger_account->id}}'
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
            $ui.confirm('This will revert the Ledger Account to status Pending?').then(action=>{

                if(!action.isConfirmed){
                    return false;
                }

                $ui.blockUI();

                $_POST('/api/ledger/account/revert',{
                    id: '{{$ledger_account->id}}'
                }).then(reply=>{
                    $ui.unblockUI();

                    if(reply.status <= 0){
                        return $ui.showError(reply);
                    }

                    $reload();
                });
            });
        }


        controls.onDeleteClick = ()=>{

            $ui.blockUI();
                
            $_POST('/api/ledger/account/delete/',{
                id: '{{$ledger_account->id}}'
            }).then(reply=>{

                $ui.unblockUI();
                
                if(reply.status <= 0){
                    return $ui.showError(reply);
                }

                $url('/ledger/accounts');
            });
        }

    </script>

    <script type="module">
        
        import {$el, Template} from '/adarna.js';

        const t             = new Template();

        let page            = 1;
        let order           = 'DESC';
        let orderBy         = 'id';
        
        search.value = '';

        pageDoc.reinitalize = ()=>{
            page       = 1;
            order      = 'DESC';
            orderBy    = 'id';
            $el.clear(list);
            showMoreBtn.classList.remove('d-none');
        }

        function renderRows(data){
            
            data.map(item=>{

                let row = t.div({class:'card mb-3 cursor-pointer shadow-sm'},()=>{
                    t.div({class:'card-body d-flex justify-content-between align-items-center'},()=>{
                        t.div(()=>{
                            t.h5({class:"h6 mb-0"},item.name);
                            if(item.ledger_type && item.ledger_type.text){
                                t.small({class:'text-muted'}, item.ledger_type.text);
                            }
                        });
                        t.span({class:'badge bg-info text-dark'},item.status);
                    });
                });

                row.onclick = ()=>{
                    $url('/ledger/'+item.id);
                }

                $el.append(row).to(list);
                
            });

        }

        pageDoc.showData = ()=>{


            $_GET('/api/ledgers',{
                query: search.value,
                page: page,
                order: order,
                order_by: orderBy,
                limit: 10,
                ledger_account_id:"{{$ledger_account->id}}"
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
            });
        }

        showMoreBtn.onclick = ()=>{
            pageDoc.showData();
        }


        let searchThrottle = null;

        search.onkeyup = (e)=>{

            if(searchThrottle){
                clearTimeout(searchThrottle);
            }

            if(e.keyCode == 13){

                pageDoc.reinitalize();
                pageDoc.showData();
                return false;
            }

            searchThrottle = setTimeout(()=>{
                pageDoc.reinitalize();
                pageDoc.showData();
            },1000);
        }

        pageDoc.reinitalize();
        pageDoc.showData();
    </script>

    @php
        $type_options = ['' => ' -- Select Ledger Type -- '];
        if (isset($ledger_types)) {
            foreach ($ledger_types as $type) {
                $type_options[$type->id] = $type->text;
            }
        }
    @endphp

    <script type="module">
        import {Template} from '/adarna.js';

        const t = new Template();

        const ledger_name           = $t.text_input();
        const ledger_type           = $t.select(@json($type_options));
        const ledger_description    = $t.textarea();
        const ledger_template       = $t.textarea();
        const ledger_unit           = $t.text_input();
        const create_ledger_btn     = $t.button('Create');
        
        ledger_name.className        = 'form-control';
        ledger_type.className        = 'form-select';
        ledger_description.className = 'form-control';
        ledger_template.className    = 'form-control';
        ledger_unit.className        = 'form-control';
        create_ledger_btn.className  = 'btn btn-primary float-end';

        const create_ledger_form = t.div({class: 'p-4 mb-4'}, ()=>{

            t.div({class:'mb-3'},(el)=>{
                el.append($t.label('Name', 'form-label'));
                el.append(ledger_name);
            });

            t.div({class:'mb-3'},(el)=>{
                el.append($t.label('Ledger Type', 'form-label'));
                el.append(ledger_type);
            });

            t.div({class:'mb-3'},(el)=>{
                el.append($t.label('Description', 'form-label'));
                el.append(ledger_description);
            });

            t.div({class:'mb-3'},(el)=>{
                el.append($t.label('Template', 'form-label'));
                el.append(ledger_template);
            });

            t.div({class:'mb-4'},(el)=>{
                el.append($t.label('Unit', 'form-label'));
                el.append(ledger_unit);
            });

            t.div({class: 'clearfix'},(el)=>{
                el.append(create_ledger_btn);
            });
        });

        createBtn.onclick = ()=>{

            ledger_name.value           = '';
            ledger_type.value           = '';
            ledger_description.value    = '';
            $drawerModal.content('Create Ledger',create_ledger_form);
            $drawerModal.open();
        }


        create_ledger_btn.onclick = ()=>{

            $_POST('/api/ledger/{{$ledger_account->id}}/create',{
                name: ledger_name.value,
                ledger_type_id: ledger_type.value,
                description: ledger_description.value,
                template: ledger_template.value,
                unit: ledger_unit.value
            }).then(reply=>{

                if(reply.status <= 0){
                    $ui.showError(reply);
                    return false;
                }

                
                $drawerModal.close();
                
                pageDoc.reinitalize();
                pageDoc.showData();

            });
        }
    </script>
</x-ledger-layout>
