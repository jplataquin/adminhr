<x-app-layout>
    <div id="pageDoc" class="card shadow-sm border-0 m-4">

        <div class="card-header bg-body d-flex align-items-center justify-content-between py-3">
            <h3 class="h5 mb-0">
                Employees
            </h3>
            <div>
                <button class="btn btn-primary btn-sm me-2" id="exportCSVBtn">Export CSV</button>
                <button class="btn btn-primary btn-sm" id="createRecordBtn">Create Record</button>
            </div>
        </div>

        <div class="card-body">
            <div class="mb-4">
                <x-text-input id="search" mode="2" label="Search"></x-text-input>
            </div>

            <div class="row g-3 mb-4">
                 <div class="col-md-6">
                    <x-select-input label="Division" id="division">
                        <option value=""> - </option>
                        @foreach($employee->division_options() as $val=>$text)
                            <option value="{{$val}}">{{$text}}</option>
                        @endforeach
                    </x-select-input>
                </div>
                <div class="col-md-6">
                    <x-select-input label="Department" id="department" dependon="#division">
                        @foreach($employee->department_options_grouped() as $group=>$options)
                            @foreach($options as $val=>$text)
                                <option group="{{$group}}" value="{{$val}}">{{$text}}</option>
                            @endforeach
                        @endforeach
                    </x-select-input>
                </div>
            </div>
            
            <div id="list" class="mt-4"></div>

            <div class="text-center mt-4 pt-3 border-top">
                <button class="btn btn-secondary" id="showMoreBtn">Show More</button>
            </div>
        </div>
    </div>

    <script type="module">
        import {$el, $q, Template} from '/adarna.js';

        const t             = new Template();

        const createRecordBtn   = $q('#createRecordBtn').first();
        const exportCSVBtn      = $q('#exportCSVBtn').first();
        const division          = $q('#division').first();
        const department        = $q('#department').first();


        let page            = 1;
        let order           = 'ASC';
        let orderBy         = 'firstname';
        
        createRecordBtn.onclick = (e)=>{
            document.location.href = '/employee/create';
        }

        exportCSVBtn.onclick = (e)=>{
            window.$tab('/employees/export/csv');
        }

        search.value = '';

        pageDoc.reinitalize = ()=>{
            page       = 1;
            order      = 'ASC';
            orderBy    = 'firstname';
            $el.clear(list);
            showMoreBtn.classList.remove('d-none');
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

                let row = t.div({class:'card mb-3 cursor-pointer shadow-sm'},()=>{
                    t.div({class:'card-body'},()=>{
                        t.h5({class:"h6 mb-1"}, name.trim() );
                        t.div({class:'text-secondary small mb-1'}, String(item.id).padStart(6,'0') );
                        t.span({class:'badge bg-info text-dark'},item.employment_status);
                    });
                });

                row.onclick = ()=>{
                    $url('/employee/'+item.id);
                }

                $el.append(row).to(list);
                
            });

        }

        pageDoc.showData = ()=>{


            $_GET('/api/employees',{
                query           : search.value,
                page            : page,
                order           : order,
                order_by        : orderBy,
                limit           : 10,
                division        : division.value,
                department      : department.value
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

        division.onchange = () => {
            
            pageDoc.reinitalize();
            pageDoc.showData();
        }

        department.onchange = () => {
            pageDoc.reinitalize();
            pageDoc.showData();
        }

        pageDoc.reinitalize();
        pageDoc.showData();
    </script>


</x-app-layout>
