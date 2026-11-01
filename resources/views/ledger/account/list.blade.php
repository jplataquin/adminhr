<x-ledger-layout>
    <div id="pageDoc" class="card shadow-sm border-0 mb-4">

        <div class="card-header bg-body py-3 d-flex align-items-center justify-content-between">
            <h3 class="h5 mb-0">
                Ledger Accounts
            </h3>
            <button class="btn btn-primary btn-sm" id="createAccountBtn">Create Account</button>
        </div>

        <div class="card-body">
            <!-- Account Category Type Filter Cards -->
            <div class="row g-3 mb-4" id="category-filter-cards">
                <!-- All Accounts Card -->
                <div class="col-12 col-sm-6 col-md-4 col-xl-3">
                    <div class="card h-100 border-0 shadow-sm cursor-pointer stat-card active-stat category-card" data-category="" role="button" title="Show all accounts">
                        <div class="card-body p-3 d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-secondary small text-uppercase fw-semibold d-block">All Categories</span>
                                <h3 class="h4 fw-bold mb-0 mt-1" id="stat-total-count">{{ number_format($totalAccountsCount ?? 0) }}</h3>
                            </div>
                            <div class="rounded-3 p-3 bg-primary-subtle text-primary fs-4 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px;">
                                <i class="bi bi-grid-fill"></i>
                            </div>
                        </div>
                    </div>
                </div>

                @if(isset($categoriesWithCounts))
                    @foreach($categoriesWithCounts as $catItem)
                        @php
                            $catVal = $catItem['value'] ?? $catItem['name'];
                            $colorMap = [
                                0 => ['bg-info-subtle text-info', 'bi-tags-fill'],
                                1 => ['bg-success-subtle text-success', 'bi-briefcase-fill'],
                                2 => ['bg-warning-subtle text-warning', 'bi-person-badge-fill'],
                                3 => ['bg-danger-subtle text-danger', 'bi-shield-shaded'],
                            ];
                            [$iconColorClass, $icon] = $colorMap[$loop->index % 4];
                        @endphp
                        <div class="col-12 col-sm-6 col-md-4 col-xl-3">
                            <div class="card h-100 border-0 shadow-sm cursor-pointer stat-card category-card" data-category="{{ $catVal }}" role="button" title="Filter by {{ $catItem['name'] }}">
                                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                                    <div class="text-truncate me-2">
                                        <span class="text-secondary small text-uppercase fw-semibold d-block text-truncate">{{ $catItem['name'] }}</span>
                                        <h3 class="h4 fw-bold mb-0 mt-1">{{ number_format($catItem['count'] ?? 0) }}</h3>
                                    </div>
                                    <div class="rounded-3 p-3 {{ $iconColorClass }} fs-4 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px;">
                                        <i class="bi {{ $icon }}"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>

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
        let activeCategory  = '';

        // Wire category cards to filter accounts list
        document.querySelectorAll('.category-card').forEach(card => {
            card.onclick = () => {
                const targetCategory = card.getAttribute('data-category') || '';
                if (activeCategory === targetCategory && targetCategory !== '') {
                    activeCategory = '';
                } else {
                    activeCategory = targetCategory;
                }

                document.querySelectorAll('.category-card').forEach(c => {
                    const cat = c.getAttribute('data-category') || '';
                    if (cat === activeCategory) {
                        c.classList.add('active-stat');
                    } else {
                        c.classList.remove('active-stat');
                    }
                });

                pageDoc.reinitalize();
                pageDoc.showData();
            };
        });

        // Initialize activeCategory from URL parameter if provided
        const urlParams = new URLSearchParams(window.location.search);
        const initialCategory = urlParams.get('type_category');
        if (initialCategory) {
            activeCategory = initialCategory;
            document.querySelectorAll('.category-card').forEach(c => {
                const cat = c.getAttribute('data-category') || '';
                if (cat === activeCategory) {
                    c.classList.add('active-stat');
                } else {
                    c.classList.remove('active-stat');
                }
            });
        }
        
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
                        t.div({class:'d-flex align-items-center gap-2'},()=>{
                            t.h5({class:"h6 mb-0"},item.name);
                            if(item.type_category){
                                t.span({class:'badge bg-secondary'},item.type_category);
                            }
                        });
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
                type_category: activeCategory,
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
        @php
        $category_options = ['' => '-- Select Type Category (Optional) --'];
        if (isset($type_categories)) {
            foreach ($type_categories as $cat) {
                $category_options[$cat->name] = $cat->name;
            }
        }
        @endphp
        const type_category = $t.select(@json($category_options));
        const description   = $t.textarea();
        const create_btn    = $t.button('Create');

        name.className = 'form-control';
        type_category.className = 'form-select';
        description.className = 'form-control';
        create_btn.className = 'btn btn-primary float-end';

        const create_ledger_account_form = t.div({class:'p-4 mb-4'},()=>{

            t.div({class:'row g-3 mb-4'},()=>{

                t.div({class:'col-12'},(el)=>{
                    el.append($t.label('Name','form-label'));
                    el.append(name);
                });             

                t.div({class:'col-12'},(el)=>{
                    el.append($t.label('Type Category','form-label'));
                    el.append(type_category);
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
                name: name.value,
                type_category: type_category.value
            }).then(reply=>{

                $ui.unblockUI();

                if(reply.status <= 0){
                    return $ui.showError(reply);
                }

                pageDoc.reinitalize();
                pageDoc.showData();
                $drawerModal.close();

                if (window.$ledgerReloadSidebar) {
                    window.$ledgerReloadSidebar(reply.data ? reply.data.id : null);
                }
            });
            
        }
    </script>
</x-ledger-layout>
