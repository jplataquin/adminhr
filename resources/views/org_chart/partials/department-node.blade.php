@props(['division'])

@php
    $divKey = 'div-' . $division['id'];
    $hasDepts = !empty($division['departments']);
    $hasDirectEmployees = !empty($division['direct_employees']);
    $hasChildren = $hasDepts || $hasDirectEmployees;
@endphp

<li class="org-tree-item" data-division-id="{{ $division['id'] }}" data-search-text="{{ strtolower($division['name'] . ' ' . $division['code']) }}" style="list-style-type: none;">
    <!-- Level 1: Division -->
    <div class="org-node-container d-inline-flex align-items-center">
        @if($hasChildren)
            <button type="button"
                    class="btn btn-sm btn-outline-secondary org-toggle-btn me-2 p-0 d-inline-flex align-items-center justify-content-center"
                    @click="toggleNode('{{ $divKey }}')"
                    :title="isNodeOpen('{{ $divKey }}') ? 'Collapse division' : 'Expand division'"
                    aria-label="Toggle division">
                <i class="bi" :class="isNodeOpen('{{ $divKey }}') ? 'bi-caret-down-fill' : 'bi-caret-right-fill'"></i>
            </button>
        @else
            <span class="org-leaf-spacer me-2"></span>
        @endif

        <div class="card org-card org-card-division border-0 shadow-sm" :class="{ 'org-card-matched': isMatch('{{ strtolower($division['name'] . ' ' . $division['code']) }}') }">
            <div class="card-body d-flex align-items-center" style="gap: 20px;">
                <div class="rounded-2 p-1 bg-primary text-white d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                    <i class="bi bi-buildings small"></i>
                </div>
                <div class="min-w-0 flex-grow-1">
                    <div class="d-flex align-items-center gap-1.5 lh-1 mb-1">
                        <span class="fw-bold small text-body text-truncate">{{ $division['name'] }}</span>
                        <span class="badge bg-primary-subtle text-primary px-1.5 py-0" style="font-size: 0.65rem;">{{ $division['code'] }}</span>
                    </div>
                    <div class="text-secondary lh-1" style="font-size: 0.72rem;">
                        <span>{{ count($division['departments']) }} departments</span>
                        <span class="mx-1">•</span>
                        <span>{{ $division['employees_count'] }} employees</span>
                        @if($hasDirectEmployees)
                            <span class="mx-1">•</span>
                            <span>{{ count($division['direct_employees']) }} direct</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Level 2: Children (Departments + Direct Employees with no department) -->
    @if($hasChildren)
        <ul class="org-tree-children ps-4 list-unstyled" x-show="isNodeOpen('{{ $divKey }}')" x-collapse.duration.200ms style="list-style-type: none;">
            {{-- Departments List --}}
            @foreach($division['departments'] as $dept)
                @php
                    $deptKey = 'dept-' . $division['id'] . '-' . $dept['id'];
                    $hasEmployees = !empty($dept['employees']);
                @endphp
                <li class="org-tree-item" data-department-id="{{ $dept['id'] }}" data-search-text="{{ strtolower($dept['name'] . ' ' . $dept['code']) }}" style="list-style-type: none;">
                    <div class="org-node-container d-inline-flex align-items-center">
                        @if($hasEmployees)
                            <button type="button"
                                    class="btn btn-sm btn-outline-secondary org-toggle-btn me-2 p-0 d-inline-flex align-items-center justify-content-center"
                                    @click="toggleNode('{{ $deptKey }}')"
                                    :title="isNodeOpen('{{ $deptKey }}') ? 'Collapse department' : 'Expand department'"
                                    aria-label="Toggle department">
                                <i class="bi" :class="isNodeOpen('{{ $deptKey }}') ? 'bi-caret-down-fill' : 'bi-caret-right-fill'"></i>
                            </button>
                        @else
                            <span class="org-leaf-spacer me-2"></span>
                        @endif

                        <div class="card org-card org-card-dept border-0 shadow-sm" :class="{ 'org-card-matched': isMatch('{{ strtolower($dept['name'] . ' ' . $dept['code']) }}') }">
                            <div class="card-body d-flex align-items-center" style="gap: 20px;">
                                <div class="rounded-2 p-1 bg-info-subtle text-info d-flex align-items-center justify-content-center" style="width: 26px; height: 26px;">
                                    <i class="bi bi-diagram-3 small"></i>
                                </div>
                                <div class="min-w-0 flex-grow-1">
                                    <div class="d-flex align-items-center gap-1.5 lh-1 mb-1">
                                        <span class="fw-semibold small text-body text-truncate">{{ $dept['name'] }}</span>
                                        <span class="badge bg-secondary-subtle text-secondary px-1 py-0" style="font-size: 0.65rem;">{{ $dept['code'] }}</span>
                                    </div>
                                    <div class="text-secondary lh-1" style="font-size: 0.72rem;">
                                        <span>{{ $dept['employees_count'] }} employees</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Level 3: Department Employees List -->
                    @if($hasEmployees)
                        <ul class="org-tree-children ps-4 list-unstyled" x-show="isNodeOpen('{{ $deptKey }}')" x-collapse.duration.200ms style="list-style-type: none;">
                            @foreach($dept['employees'] as $emp)
                                @include('org_chart.partials.employee-leaf-node', ['emp' => $emp])
                            @endforeach
                        </ul>
                    @endif
                </li>
            @endforeach

            {{-- Direct Employees without Department (Direct Children of Division) --}}
            @if($hasDirectEmployees)
                @foreach($division['direct_employees'] as $emp)
                    @include('org_chart.partials.employee-leaf-node', ['emp' => $emp])
                @endforeach
            @endif
        </ul>
    @endif
</li>
