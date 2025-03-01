<x-app-layout>
<div id="pageDoc">
    <div class="border border-1 rounded-lg shadow relative m-10">

        <div class="flex items-start justify-between p-5 border-b rounded-t">
            <h3 class="text-xl font-semibold dark:text-white">
                Ledger Account
            </h3>
        </div>

        <div class="p-6 space-y-6">
            <form id="form" autocomplete="off">
                <div class="col-span-12 sm:col-span-12 mb-3">
                    <x-text-input class="editable" label="Name" name="name" id="name" disabled="true" value="{{$ledger_account->name}}"></x-text-input>
                </div>
                <div class="col-span-12 sm:col-span-12 mb-3">
                    <x-text-input label="Status" name="status" id="status" disabled="true" value="{{$ledger_account->status}}"></x-text-input>
                </div>

                <div class="col-span-12 mb-3">
                    <x-record-meta :record="$ledger_account"></x-record-meta>
                </div>

            </form>
        </div>

        <div class="p-6 border-t border-gray-200 rounded-b flow-root">
       
            <x-display-controls status="{{$ledger_account->status}}"></x-display-controls>
            <script type="module">
                import {$q} from '/adarna.js';

                controls.onCancelClick = ()=>{
                    $url('/ledger/accounts');
                    return false;
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
        </div>

    </div>
    

    <div class="border border-1 rounded-lg shadow relative m-10">

        <div class="flex items-start justify-between p-5 border-b rounded-t">
            <h3 class="text-xl font-semibold dark:text-white">
                Ledgers
            </h3>

            <button class="float-right text-white bg-cyan-600 hover:bg-cyan-700 focus:ring-4 focus:ring-cyan-200 font-medium rounded-lg text-sm px-5 py-2.5 text-center" id="createBtn">Create Ledger</button>
        
        </div>
            
        <div class="pt-6 ps-6 pe-6 space-y-6">
            <x-text-input id="search" mode="2" label="Search"></x-text-input>
        </div>
        

        <div class="p-6 space-y-6">
            <div id="list"></div>
        </div>

        <div class="p-6 border-t border-gray-200 rounded-b flow-root text-center">
            <button class="text-white bg-cyan-600 hover:bg-cyan-700 focus:ring-4 focus:ring-cyan-200 font-medium rounded-lg text-sm px-5 py-2.5 text-center" id="showMoreBtn">Show More</button>
        </div>
    </div>
</div>

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
            showMoreBtn.classList.remove('hidden');
        }

        function renderRows(data){
            
            data.map(item=>{

                let row = t.div({class:'border rounded-t p-5 mb-3 cursor-pointer'},()=>{
                    t.h3({class:"text-sm font-semibold dark:text-white"},item.name);
                    t.span({class:'text-xs dark:text-white'},item.status);
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
                    showMoreBtn.classList.add('hidden');
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

        pageDoc.reinitalize();
        pageDoc.showData();
    </script>

    <script type="module">
        import {Template} from '/adarna.js';

        const t = new Template();

        const ledger_name           = $t.text_input();
        const ledger_description    = $t.textarea();
        const ledger_template       = $t.textarea();
        const ledger_unit           = $t.text_input();
        const create_ledger_btn     = $t.button('Create');
        
        create_ledger_btn.classList.add('float-right');

        const create_ledger_form = t.div(()=>{

            t.div({class:'mb-3'},(el)=>{
                el.append($t.label('Name'));
                el.append(ledger_name);
            });

            t.div({class:'mb-5'},(el)=>{
                el.append($t.label('Description'));

                el.append(ledger_description);
            });

            t.div({class:'mb-5'},(el)=>{
                el.append($t.label('Template'));

                el.append(ledger_template);
            });

            t.div({class:'mb-5'},(el)=>{
                el.append($t.label('Unit'));

                el.append(ledger_unit);
            });

            t.div({},(el)=>{
                el.append(create_ledger_btn);
            });
        });

        createBtn.onclick = ()=>{

            ledger_name.value           = '';
            ledger_description.value    = '';
            $drawerModal.content('Create Ledger',create_ledger_form);
            $drawerModal.open();
        }


        create_ledger_btn.onclick = ()=>{

            $_POST('/api/ledger/{{$ledger_account->id}}/create',{
                name: ledger_name.value,
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
</x-app-layout>