<x-app-layout>
    <div id="pageDoc" class="card shadow-sm border-0 m-4">

        <div class="card-header bg-body py-3 d-flex align-items-center justify-content-between">
            <h3 class="h5 mb-0">
                Ledger Accounts
            </h3>
            <button class="btn btn-primary btn-sm" id="createAccountBtn">Create Account</button>
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
                        t.h5({class:"h6 mb-0"},item.name);
                        t.span({class:'badge bg-info text-dark'},item.status);
                    });
                });

                row.onclick = ()=>{
                    $url('/ledger/account/'+item.id);
                }

                $el.append(row).to(list);
                
            });

        }

        pageDoc.showData = ()=>{


            $_GET('/api/ledger/accounts',{
                query: search.value,
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

    <script type="module">
        import {Template} from '/adarna.js';

        const t             = new Template();
        const name          = $t.text_input();
        const description   = $t.textarea();
        const create_btn    = $t.button('Create');

        name.className = 'form-control';
        description.className = 'form-control';
        create_btn.className = 'btn btn-primary float-end';

        const create_ledger_account_form = t.div({class:'p-4 mb-4'},()=>{

            t.div({class:'row g-3 mb-4'},()=>{

                t.div({class:'col-12'},(el)=>{
                    el.append($t.label('Name','form-label'));
                    el.append(name);
                });             

            });//div

            t.div({class: 'clearfix'}, (el)=>{
                el.append(create_btn);
            });

        });

        createAccountBtn.onclick = ()=>{

            $drawerModal.content('Create Ledger Account',create_ledger_account_form);
            $drawerModal.open();
        }


        create_btn.onclick = ()=>{

            
            $ui.blockUI();

            $_POST('/api/ledger/account/create',{
                name: name.value
            }).then(reply=>{

                $ui.unblockUI();

                if(reply.status <= 0){
                    return $ui.showError(reply);
                }

                pageDoc.reinitalize();
                pageDoc.showData();
                $drawerModal.close();
            });
            
        }
    </script>
</x-app-layout>
