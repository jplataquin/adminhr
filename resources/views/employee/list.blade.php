<x-app-layout>
    <div id="pageDoc" class="card shadow-sm border-0 m-4">

        <div class="card-header bg-body d-flex align-items-center justify-content-between py-3">
            <h3 class="h5 mb-0">
                Employees
            </h3>
            <div>
                <a href="{{ route('org-chart') }}" class="btn btn-outline-info btn-sm me-2" id="orgChartBtn"><i class="bi bi-diagram-3 me-1"></i> Org Chart</a>
                <button class="btn btn-success btn-sm me-2" id="bulkUpdateBtn">Bulk Update</button>
                <button class="btn btn-primary btn-sm me-2" id="exportCSVBtn">Export CSV</button>
                <button class="btn btn-primary btn-sm" id="createRecordBtn">Create Record</button>
            </div>
        </div>

        <div class="card-body">
            @php
                $statusConfig = [
                    'REGU' => ['slug' => 'regular', 'label' => 'Regular Employees', 'icon' => 'bi-person-check-fill', 'color' => 'success'],
                    'PROB' => ['slug' => 'probationary', 'label' => 'Probationary Employees', 'icon' => 'bi-person-badge-fill', 'color' => 'warning'],
                    'CONT' => ['slug' => 'contractual', 'label' => 'Contractual Employees', 'icon' => 'bi-file-earmark-person-fill', 'color' => 'info'],
                    'RSGN' => ['slug' => 'resigned', 'label' => 'Resigned Employees', 'icon' => 'bi-box-arrow-right', 'color' => 'secondary'],
                    'LAOF' => ['slug' => 'laid-off', 'label' => 'Laid Off Employees', 'icon' => 'bi-person-dash-fill', 'color' => 'danger'],
                    'AWOL' => ['slug' => 'awol', 'label' => 'AWOL Employees', 'icon' => 'bi-person-x-fill', 'color' => 'danger'],
                    'TRMN' => ['slug' => 'terminated', 'label' => 'Terminated Employees', 'icon' => 'bi-person-slash', 'color' => 'danger'],
                    'RETR' => ['slug' => 'retired', 'label' => 'Retired Employees', 'icon' => 'bi-award-fill', 'color' => 'primary'],
                    'DECE' => ['slug' => 'deceased', 'label' => 'Deceased Employees', 'icon' => 'bi-moon-fill', 'color' => 'dark'],
                ];
            @endphp

            <!-- Employee Summary Metric Cards -->
            <div class="row g-3 mb-4" id="employee-stats-cards">
                <div class="col-12 col-sm-6 col-md-4 col-xl-3">
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

                @foreach(App\Models\Employee::employment_status_options() as $key => $label)
                @php
                    $cfg = $statusConfig[$key] ?? [
                        'slug' => strtolower($key),
                        'label' => $label . ' Employees',
                        'icon' => 'bi-person-fill',
                        'color' => 'secondary'
                    ];
                @endphp
                <div class="col-12 col-sm-6 col-md-4 col-xl-3">
                    <div class="card h-100 border-0 shadow-sm cursor-pointer stat-card" id="card-{{ $cfg['slug'] }}-employees" data-status="{{ $key }}" role="button" title="Filter {{ strtolower($cfg['label']) }}">
                        <div class="card-body p-3 d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-secondary small text-uppercase fw-semibold d-block">{{ $cfg['label'] }}</span>
                                <h3 class="h4 fw-bold mb-0 mt-1 stat-count-val" data-status-key="{{ $key }}" data-slug="{{ $cfg['slug'] }}" id="stat-{{ $cfg['slug'] }}-count">{{ number_format($stats[$key] ?? 0) }}</h3>
                            </div>
                            <div class="rounded-3 p-3 bg-{{ $cfg['color'] }}-subtle text-{{ $cfg['color'] }} fs-4 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="bi {{ $cfg['icon'] }}"></i>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
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
                    if (elTotal) elTotal.textContent = Number(s.total || 0).toLocaleString();

                    document.querySelectorAll('.stat-count-val').forEach(el => {
                        const key = el.getAttribute('data-status-key');
                        if (key && s[key] !== undefined) {
                            el.textContent = Number(s[key] || 0).toLocaleString();
                        }
                    });
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
