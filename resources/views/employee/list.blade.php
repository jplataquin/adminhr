<x-app-layout>
    <div id="pageDoc" class="card shadow-sm border-0 m-4">

        <div class="card-header bg-body d-flex align-items-center justify-content-between py-3">
            <h3 class="h5 mb-0">
                Employees
            </h3>
            <div>
                <button class="btn btn-success btn-sm me-2" id="bulkUpdateBtn">Bulk Update</button>
                <button class="btn btn-primary btn-sm me-2" id="exportCSVBtn">Export CSV</button>
                <button class="btn btn-primary btn-sm" id="createRecordBtn">Create Record</button>
            </div>
        </div>

        <div class="card-body">
            <!-- Employee Summary Metric Cards -->
            <div class="row g-3 mb-4" id="employee-stats-cards">
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card h-100 border-0 shadow-sm cursor-pointer stat-card active-stat" id="card-total-employees" data-status="" role="button" title="Show all employees">
                        <div class="card-body p-3 d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-secondary small text-uppercase fw-semibold d-block">Total Employees</span>
                                <h3 class="h4 fw-bold mb-0 mt-1" id="stat-total-count">{{ number_format($stats['total'] ?? 0) }}</h3>
                            </div>
                            <div class="rounded-3 p-3 bg-primary-subtle text-primary fs-4 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="bi bi-people-fill"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card h-100 border-0 shadow-sm cursor-pointer stat-card" id="card-regular-employees" data-status="REGU" role="button" title="Filter regular employees">
                        <div class="card-body p-3 d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-secondary small text-uppercase fw-semibold d-block">Regular Employees</span>
                                <h3 class="h4 fw-bold mb-0 mt-1" id="stat-regular-count">{{ number_format($stats['regular'] ?? 0) }}</h3>
                            </div>
                            <div class="rounded-3 p-3 bg-success-subtle text-success fs-4 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="bi bi-person-check-fill"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card h-100 border-0 shadow-sm cursor-pointer stat-card" id="card-probationary-employees" data-status="PROB" role="button" title="Filter probationary employees">
                        <div class="card-body p-3 d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-secondary small text-uppercase fw-semibold d-block">Probationary Employees</span>
                                <h3 class="h4 fw-bold mb-0 mt-1" id="stat-probationary-count">{{ number_format($stats['probationary'] ?? 0) }}</h3>
                            </div>
                            <div class="rounded-3 p-3 bg-warning-subtle text-warning fs-4 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="bi bi-person-badge-fill"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card h-100 border-0 shadow-sm cursor-pointer stat-card" id="card-contractual-employees" data-status="CONT" role="button" title="Filter contractual employees">
                        <div class="card-body p-3 d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-secondary small text-uppercase fw-semibold d-block">Contractual Employees</span>
                                <h3 class="h4 fw-bold mb-0 mt-1" id="stat-contractual-count">{{ number_format($stats['contractual'] ?? 0) }}</h3>
                            </div>
                            <div class="rounded-3 p-3 bg-info-subtle text-info fs-4 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="bi bi-file-earmark-person-fill"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <x-text-input id="search" mode="2" label="Search"></x-text-input>
            </div>

            <div class="row g-3 mb-4">
                 <div class="col-md-6">
                    <x-select-input label="Division" id="division_id">
                        <option value=""> - </option>
                        @foreach($employee->division_options() as $val=>$text)
                            <option value="{{$val}}">{{$text}}</option>
                        @endforeach
                    </x-select-input>
                </div>
                <div class="col-md-6">
                    <x-select-input label="Department" id="department_id" dependon="#division_id">
                        <option value=""> - </option>
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
        const bulkUpdateBtn     = $q('#bulkUpdateBtn').first();
        const division_id       = $q('#division_id').first();
        const department_id     = $q('#department_id').first();

        let page            = 1;
        let order           = 'ASC';
        let orderBy         = 'firstname';
        let activeStatus    = '';

        // Wire stat cards to interactive status filtering
        document.querySelectorAll('.stat-card').forEach(card => {
            card.onclick = () => {
                const targetStatus = card.getAttribute('data-status') || '';
                if (activeStatus === targetStatus && targetStatus !== '') {
                    activeStatus = '';
                } else {
                    activeStatus = targetStatus;
                }

                document.querySelectorAll('.stat-card').forEach(c => {
                    const s = c.getAttribute('data-status') || '';
                    if (s === activeStatus) {
                        c.classList.add('active-stat');
                    } else {
                        c.classList.remove('active-stat');
                    }
                });

                pageDoc.reinitalize();
                pageDoc.showData();
            };
        });
        
        createRecordBtn.onclick = (e)=>{
            document.location.href = '/employee/create';
        }

        bulkUpdateBtn.onclick = (e)=>{
            document.location.href = '/employees/bulk-update';
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

                let badgeClass = 'bg-secondary text-white';
                if(item.employment_status === 'REGU'){
                    badgeClass = 'bg-success text-white';
                }else if(item.employment_status === 'PROB'){
                    badgeClass = 'bg-warning text-dark';
                }else if(item.employment_status === 'CONT'){
                    badgeClass = 'bg-info text-dark';
                }

                let row = t.div({class:'card mb-3 cursor-pointer shadow-sm'},()=>{
                    t.div({class:'card-body'},()=>{
                        t.h5({class:"h6 mb-1"}, name.trim() );
                        t.div({class:'text-secondary small mb-1'}, String(item.id).padStart(6,'0') );
                        t.span({class:'badge ' + badgeClass}, item.employment_status);
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
                query               : search.value,
                employment_status   : activeStatus,
                page                : page,
                order               : order,
                order_by            : orderBy,
                limit               : 10,
                division_id         : division_id.value,
                department_id       : department_id.value
            }).then(reply=>{
                
                if(reply.status <= 0 ){
                    $ui.showMsg(reply);
                    return false;
                };

                if(reply.data && reply.data.stats){
                    const s = reply.data.stats;
                    const elTotal = document.getElementById('stat-total-count');
                    const elReg = document.getElementById('stat-regular-count');
                    const elProb = document.getElementById('stat-probationary-count');
                    const elCont = document.getElementById('stat-contractual-count');
                    if (elTotal) elTotal.textContent = Number(s.total || 0).toLocaleString();
                    if (elReg) elReg.textContent = Number(s.regular || 0).toLocaleString();
                    if (elProb) elProb.textContent = Number(s.probationary || 0).toLocaleString();
                    if (elCont) elCont.textContent = Number(s.contractual || 0).toLocaleString();
                }

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

        division_id.onchange = () => {
            pageDoc.reinitalize();
            pageDoc.showData();
        }

        department_id.onchange = () => {
            pageDoc.reinitalize();
            pageDoc.showData();
        }

        pageDoc.reinitalize();
        pageDoc.showData();
    </script>


</x-app-layout>
