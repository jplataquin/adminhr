<x-app-layout>
    <div class="py-4 px-3 px-md-4" x-data="orgChartHandler()">
        <!-- Page Header Card -->
        <div class="card shadow-sm border-0 mb-4" id="org-chart-header">
            <div class="card-body p-3 p-md-4">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div>
                        <div class="d-flex align-items-center mb-1" style="gap: 20px;">
                            <div class="rounded-3 p-2 bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                <i class="bi bi-diagram-3-fill fs-5"></i>
                            </div>
                            <div>
                                <h1 class="h5 mb-0 fw-bold">Organization Chart</h1>
                                <p class="text-secondary small mb-0">Hierarchical structure: Division &rarr; Department &rarr; Employee (Status &amp; Position)</p>
                            </div>
                        </div>
                    </div>

                    <!-- Mode Switch Buttons -->
                    <div class="d-flex align-items-center gap-2">
                        <div class="btn-group p-1 bg-body-tertiary rounded-3 border" role="group">
                            <button type="button"
                                    class="btn btn-sm rounded-2 d-flex align-items-center gap-1.5 px-3 py-1.5"
                                    :class="activeView === 'department' ? 'btn-primary' : 'btn-ghost text-secondary'"
                                    @click="activeView = 'department'">
                                <i class="bi bi-buildings"></i>
                                <span>Divisions &amp; Departments</span>
                            </button>
                            <button type="button"
                                    class="btn btn-sm rounded-2 d-flex align-items-center gap-1.5 px-3 py-1.5"
                                    :class="activeView === 'reporting' ? 'btn-primary' : 'btn-ghost text-secondary'"
                                    @click="activeView = 'reporting'">
                                <i class="bi bi-diagram-2"></i>
                                <span>Supervisor Reporting</span>
                            </button>
                        </div>
                        <a href="{{ route('employees') }}" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1">
                            <i class="bi bi-arrow-left"></i>
                            <span class="d-none d-sm-inline">Back to List</span>
                        </a>
                    </div>
                </div>

                <!-- Stats summary badges -->
                <div class="row g-2 mt-2.5 pt-2.5 border-top">
                    <div class="col-6 col-md-3">
                        <div class="p-2 rounded-3 bg-body-tertiary border d-flex align-items-center justify-content-between">
                            <div>
                                <span class="small text-secondary text-uppercase fw-semibold d-block" style="font-size: 0.7rem;">Employees</span>
                                <span class="fw-bold fs-6" id="stat-total-employees">{{ number_format($stats['total_employees'] ?? 0) }}</span>
                            </div>
                            <i class="bi bi-people-fill text-primary fs-5"></i>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-2 rounded-3 bg-body-tertiary border d-flex align-items-center justify-content-between">
                            <div>
                                <span class="small text-secondary text-uppercase fw-semibold d-block" style="font-size: 0.7rem;">Divisions</span>
                                <span class="fw-bold fs-6" id="stat-total-divisions">{{ number_format($stats['total_divisions'] ?? 0) }}</span>
                            </div>
                            <i class="bi bi-buildings-fill text-info fs-5"></i>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-2 rounded-3 bg-body-tertiary border d-flex align-items-center justify-content-between">
                            <div>
                                <span class="small text-secondary text-uppercase fw-semibold d-block" style="font-size: 0.7rem;">Departments</span>
                                <span class="fw-bold fs-6" id="stat-total-departments">{{ number_format($stats['total_departments'] ?? 0) }}</span>
                            </div>
                            <i class="bi bi-diagram-3-fill text-warning fs-5"></i>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-2 rounded-3 bg-body-tertiary border d-flex align-items-center justify-content-between">
                            <div>
                                <span class="small text-secondary text-uppercase fw-semibold d-block" style="font-size: 0.7rem;">Positions</span>
                                <span class="fw-bold fs-6" id="stat-total-positions">{{ number_format($stats['total_positions'] ?? 0) }}</span>
                            </div>
                            <i class="bi bi-briefcase-fill text-success fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Controls Toolbar Card -->
        <div class="card shadow-sm border-0 mb-4" id="org-chart-toolbar">
            <div class="card-body p-2 px-3">
                <div class="row g-2 align-items-center justify-content-between">
                    <div class="col-12 col-md-5">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-body-tertiary border-end-0">
                                <i class="bi bi-search text-secondary"></i>
                            </span>
                            <input type="text"
                                   class="form-control bg-body-tertiary border-start-0"
                                   id="orgChartSearch"
                                   placeholder="Filter employee name, status, role, department..."
                                   x-model="search"
                                   @input="handleSearch()"
                                   aria-label="Filter organization tree">
                            <button class="btn btn-outline-secondary"
                                    type="button"
                                    x-show="search.length > 0"
                                    @click="search = ''"
                                    title="Clear search">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                    </div>

                    <div class="col-12 col-md-auto d-flex align-items-center gap-2">
                        <button type="button"
                                class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1"
                                id="expandAllBtn"
                                @click="expandAll()">
                            <i class="bi bi-arrows-expand"></i>
                            <span>Expand All</span>
                        </button>
                        <button type="button"
                                class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1"
                                id="collapseAllBtn"
                                @click="collapseAll()">
                            <i class="bi bi-arrows-collapse"></i>
                            <span>Collapse All</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tree View Container Card -->
        <div class="card shadow-sm border-0" id="org-chart-tree-card">
            <div class="card-header bg-body py-2.5 px-3 d-flex align-items-center justify-content-between">
                <h2 class="h6 mb-0 fw-bold d-flex align-items-center gap-2">
                    <template x-if="activeView === 'department'">
                        <span class="d-inline-flex align-items-center gap-2">
                            <i class="bi bi-buildings text-primary"></i>
                            <span>Division &rarr; Department &rarr; Employee (Status &amp; Position)</span>
                        </span>
                    </template>
                    <template x-if="activeView === 'reporting'">
                        <span class="d-inline-flex align-items-center gap-2">
                            <i class="bi bi-diagram-2 text-primary"></i>
                            <span>Supervisor Reporting Hierarchy</span>
                        </span>
                    </template>
                </h2>

                <div class="small text-secondary">
                    <i class="bi bi-info-circle me-1"></i>
                    <span>Collapsed by default. Click arrow to expand.</span>
                </div>
            </div>

            <div class="card-body p-3 p-md-4 org-chart-canvas">
                <!-- VIEW 1 (Default): Organizational Divisions -> Departments -> Employees -->
                <div x-show="activeView === 'department'" id="department-tree-view">
                    @if(empty($departmentTree))
                        <div class="text-center py-5 text-secondary">
                            <div class="fs-1 mb-2"><i class="bi bi-buildings"></i></div>
                            <p class="mb-0">No divisions or departments found.</p>
                        </div>
                    @else
                        <ul class="org-tree list-unstyled" id="department-root-tree" style="list-style-type: none;">
                            @foreach($departmentTree as $division)
                                @include('org_chart.partials.department-node', ['division' => $division])
                            @endforeach
                        </ul>
                    @endif
                </div>

                <!-- VIEW 2: Supervisor Reporting Hierarchy -->
                <div x-show="activeView === 'reporting'" id="reporting-tree-view">
                    @if(empty($reportingTree))
                        <div class="text-center py-5 text-secondary">
                            <div class="fs-1 mb-2"><i class="bi bi-people"></i></div>
                            <p class="mb-0">No active employees found to display in the reporting tree.</p>
                        </div>
                    @else
                        <ul class="org-tree list-unstyled" id="reporting-root-tree" style="list-style-type: none;">
                            @foreach($reportingTree as $rootNode)
                                @include('org_chart.partials.reporting-node', ['node' => $rootNode, 'level' => 0])
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>

        <!-- Floating Employee Card -->
        <div x-show="hoveredEmployee"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 transform -translate-y-1 scale-95"
             x-transition:enter-end="opacity-100 transform translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100 transform translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 transform -translate-y-1 scale-95"
             class="card shadow-lg border border-primary-subtle rounded-3 position-absolute employee-floating-card"
             :style="`top: ${hoverCardPosition.top}px; left: ${hoverCardPosition.left}px; z-index: 1060; width: 340px;`"
             @mouseenter="cancelHideHoverCard()"
             @mouseleave="hideHoverCard()"
             x-cloak
             style="display: none;">
            <div class="card-body p-3">
                <div class="d-flex align-items-start gap-3">
                    <!-- Larger Thumbnail Photo -->
                    <div class="flex-shrink-0">
                        <template x-if="hoveredEmployee && hoveredEmployee.photo_url">
                            <img :src="hoveredEmployee.photo_url"
                                 :alt="hoveredEmployee.name"
                                 class="rounded-3 border object-fit-cover shadow-sm"
                                 style="width: 72px; height: 72px;">
                        </template>
                        <template x-if="hoveredEmployee && !hoveredEmployee.photo_url">
                            <div class="rounded-3 bg-primary-subtle text-primary fw-bold d-flex align-items-center justify-content-center border border-primary-subtle shadow-sm"
                                 style="width: 72px; height: 72px; font-size: 1.5rem;"
                                 x-text="hoveredEmployee ? (hoveredEmployee.initials || (hoveredEmployee.firstname ? hoveredEmployee.firstname.charAt(0) : 'E')) : 'E'">
                            </div>
                        </template>
                    </div>

                    <!-- Employee Info Header -->
                    <div class="min-w-0 flex-grow-1">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span class="badge bg-secondary-subtle text-secondary font-monospace" style="font-size: 0.7rem;">
                                #<span x-text="hoveredEmployee ? hoveredEmployee.formatted_id : ''"></span>
                            </span>
                            <a :href="hoveredEmployee ? `{{ url('/employee') }}/${hoveredEmployee.id}` : '#'"
                               class="btn btn-sm btn-outline-primary py-0 px-1.5"
                               style="font-size: 0.7rem;"
                               title="Open Profile">
                                <i class="bi bi-box-arrow-up-right me-0.5"></i> View
                            </a>
                        </div>
                        <h6 class="mb-0 fw-bold text-body text-truncate" x-text="hoveredEmployee ? hoveredEmployee.name : ''"></h6>
                        <div class="text-primary small fw-semibold text-truncate mb-1" x-text="hoveredEmployee ? hoveredEmployee.position : ''"></div>
                    </div>
                </div>

                <hr class="my-2.5 opacity-25">

                <!-- Basic Data Grid -->
                <div class="row g-2 small">
                    <div class="col-6">
                        <div class="text-secondary text-uppercase fw-semibold" style="font-size: 0.65rem;">Employment Status</div>
                        <div class="fw-semibold text-body" x-text="hoveredEmployee ? (hoveredEmployee.employment_status_label || hoveredEmployee.employment_status || 'None') : ''"></div>
                    </div>
                    <div class="col-6">
                        <div class="text-secondary text-uppercase fw-semibold" style="font-size: 0.65rem;">Duty Status</div>
                        <div class="fw-semibold text-body" x-text="hoveredEmployee ? (hoveredEmployee.duty_status_label || hoveredEmployee.duty_status || 'None') : ''"></div>
                    </div>
                    <div class="col-6">
                        <div class="text-secondary text-uppercase fw-semibold" style="font-size: 0.65rem;">Division</div>
                        <div class="text-body text-truncate" x-text="hoveredEmployee ? (hoveredEmployee.division || 'None') : ''"></div>
                    </div>
                    <div class="col-6">
                        <div class="text-secondary text-uppercase fw-semibold" style="font-size: 0.65rem;">Department</div>
                        <div class="text-body text-truncate" x-text="hoveredEmployee ? (hoveredEmployee.department || 'None') : ''"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function orgChartHandler() {
            return {
                // Default view is Division -> Department -> Employee
                activeView: 'department',
                search: '',
                // Collapsed by default: empty object means no node is open
                openNodes: {},

                hoveredEmployee: null,
                hoverCardPosition: { top: 0, left: 0 },
                hoverCardTimeout: null,

                showHoverCard(emp, event) {
                    if (this.hoverCardTimeout) clearTimeout(this.hoverCardTimeout);
                    const rect = event.currentTarget.getBoundingClientRect();
                    const cardWidth = 340;
                    const cardHeight = 220;
                    let left = rect.left + window.scrollX;
                    if (left + cardWidth > window.innerWidth - 16) {
                        left = window.innerWidth - cardWidth - 16;
                    }
                    if (left < 16) left = 16;

                    let top = rect.bottom + window.scrollY + 6;
                    if (rect.bottom + cardHeight > window.innerHeight && rect.top - cardHeight > 0) {
                        top = rect.top + window.scrollY - cardHeight - 6;
                    }

                    this.hoverCardPosition = { top, left };
                    this.hoveredEmployee = emp;
                },

                hideHoverCard() {
                    this.hoverCardTimeout = setTimeout(() => {
                        this.hoveredEmployee = null;
                    }, 200);
                },

                cancelHideHoverCard() {
                    if (this.hoverCardTimeout) clearTimeout(this.hoverCardTimeout);
                },

                isNodeOpen(key) {
                    return !!this.openNodes[key];
                },

                toggleNode(key) {
                    this.openNodes[key] = !this.openNodes[key];
                },

                expandAll() {
                    document.querySelectorAll('.org-toggle-btn').forEach(btn => {
                        const onclickStr = btn.getAttribute('@click') || '';
                        const match = onclickStr.match(/'([^']+)'/);
                        if (match && match[1]) {
                            this.openNodes[match[1]] = true;
                        }
                    });
                },

                collapseAll() {
                    // Reset to collapsed by default
                    this.openNodes = {};
                },

                handleSearch() {
                    if (this.search && this.search.trim().length > 1) {
                        const q = this.search.toLowerCase().trim();
                        // Automatically expand branches that match the query
                        document.querySelectorAll('.org-tree-item').forEach(item => {
                            const text = (item.getAttribute('data-search-text') || '').toLowerCase();
                            if (text.includes(q)) {
                                // Find parent toggle buttons and open them
                                let parent = item.parentElement;
                                while (parent && !parent.classList.contains('org-tree')) {
                                    if (parent.classList.contains('org-tree-children')) {
                                        const li = parent.closest('.org-tree-item');
                                        if (li) {
                                            const btn = li.querySelector(':scope > .org-node-container .org-toggle-btn');
                                            if (btn) {
                                                const match = (btn.getAttribute('@click') || '').match(/'([^']+)'/);
                                                if (match && match[1]) {
                                                    this.openNodes[match[1]] = true;
                                                }
                                            }
                                        }
                                    }
                                    parent = parent.parentElement;
                                }
                            }
                        });
                    }
                },

                isMatch(text) {
                    if (!this.search || !this.search.trim()) return false;
                    return text.includes(this.search.toLowerCase().trim());
                }
            };
        }
    </script>
</x-app-layout>
