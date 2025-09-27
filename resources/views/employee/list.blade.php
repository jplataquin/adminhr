<x-app-layout>
    <div id="pageDoc" class="border border-1 rounded-lg shadow relative m-10">

        <div class="flex items-start justify-between p-5 border-b rounded-t">
            <h3 class="text-xl font-semibold dark:text-white">
                Employees
            </h3>
            <button class="text-white bg-cyan-600 hover:bg-cyan-700 focus:ring-4 focus:ring-cyan-200 font-medium rounded-lg text-sm px-5 py-2.5 text-center" id="createRecordBtn">Create Record</button>
        </div>
        <div class="ps-6 pe-6 pt-6 space-y-6">
            <x-text-input id="search" mode="2" label="Search"></x-text-input>
        </div>
        <div class="p-6 space-y-6">
            <div id="list"></div>
        </div>

        <div class="p-6 border-t border-gray-200 rounded-b flow-root text-center">
            <button class="text-white bg-cyan-600 hover:bg-cyan-700 focus:ring-4 focus:ring-cyan-200 font-medium rounded-lg text-sm px-5 py-2.5 text-center" id="showMoreBtn">Show More</button>
        </div>
    </div>

    <script type="module">
        import {$el, $q, Template} from '/adarna.js';

        const t             = new Template();

        const createRecordBtn = $q('#createRecordBtn').first();

        let page            = 1;
        let order           = 'ASC';
        let orderBy         = 'firstname';
        
        createRecordBtn.onclick = (e)=>{
            document.location.href = '/employee/create';
        }

        search.value = '';

        pageDoc.reinitalize = ()=>{
            page       = 1;
            order      = 'ASC';
            orderBy    = 'firstname';
            $el.clear(list);
            showMoreBtn.classList.remove('hidden');
        }

        function renderRows(data){
            
            data.map(item=>{

                if(!item.suffix){
                    item.suffix = '';
                }

                if(!item.middlename){
                    item.middlename = '';
                }

                let name = item.firstname+' '+item.middlename+' '+item.lastname+' '+item.suffix;

                let row = t.div({class:'border rounded-t p-5 mb-3 cursor-pointer'},()=>{
                    t.h3({class:"text-sm font-semibold dark:text-white"}, name.trim() );
                    t.h6({class:'dark:text-white text-sm'}, String(item.id).padStart(6,'0') );
                    t.span({class:'text-xs dark:text-white'},item.employment_status);
                });

                row.onclick = ()=>{
                    $url('/employee/'+item.id);
                }

                $el.append(row).to(list);
                
            });

        }

        pageDoc.showData = ()=>{


            $_GET('/api/employees',{
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


</x-app-layout>