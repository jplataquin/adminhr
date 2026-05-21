<x-app-layout>

    <div id="pageDoc" class="card shadow-sm border-0 m-4">
        <div class="card-header bg-body py-3">
            <h3 class="h5 mb-0">
                Review Ledgers
            </h3>
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
                    t.div({class:'card-body'},()=>{
                        t.h5({class:'h6 text-secondary mb-1'},item.account.name);
                        t.h4({class:"h5 mb-2"},item.name);
                        t.span({class:'badge bg-info text-dark'},item.status);
                    });
                });

                row.onclick = ()=>{
                    $url('/review/ledger/'+item.id);
                }

                $el.append(row).to(list);
                
            });

        }

        pageDoc.showData = ()=>{


            $_GET('/api/review/ledgers',{
                query: search.value,
                page: page,
                order: order,
                order_by: orderBy,
                limit: 10,
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
</x-app-layout>
