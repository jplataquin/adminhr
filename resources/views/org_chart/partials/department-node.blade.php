@props(['division'])

@php
    $divKey = 'div-' . $division['id'];
    $hasDepts = !empty($division['departments']);
@endphp

<li class="org-tree-item" data-division-id="{{ $division['id'] }}" data-search-text="{{ strtolower($division['name'] . ' ' . $division['code']) }}">
    <!-- Level 1: Division -->
    <div class="org-node-container d-inline-flex align-items-center">
        @if($hasDepts)
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
            <div class="card-body d-flex align-items-center gap-2.5">
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
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Level 2: Departments List -->
    @if($hasDepts)
        <ul class="org-tree-children ps-4" x-show="isNodeOpen('{{ $divKey }}')" x-collapse.duration.200ms>
            @foreach($division['departments'] as $dept)
                @php
                    $deptKey = 'dept-' . $division['id'] . '-' . $dept['id'];
                    $hasEmployees = !empty($dept['employees']);
                @endphp
                <li class="org-tree-item" data-department-id="{{ $dept['id'] }}" data-search-text="{{ strtolower($dept['name'] . ' ' . $dept['code']) }}">
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
                            <div class="card-body d-flex align-items-center gap-2">
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

                    <!-- Level 3: Employees List {Employee Name - Employment Status - Position} -->
                    @if($hasEmployees)
                        <ul class="org-tree-children ps-4" x-show="isNodeOpen('{{ $deptKey }}')" x-collapse.duration.200ms>
                            @foreach($dept['employees'] as $emp)
                                @php
                                    $initials = strtoupper(substr($emp['firstname'] ?? 'E', 0, 1) . substr($emp['lastname'] ?? '', 0, 1));
                                    $statusClass = match($emp['employment_status']) {
                                        'REGU' => 'bg-success-subtle text-success border border-success-subtle',
                                        'PROB' => 'bg-warning-subtle text-warning border border-warning-subtle',
                                        'CONT' => 'bg-info-subtle text-info border border-info-subtle',
                                        default => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
                                    };
                                @endphp
                                <li class="org-tree-item" data-employee-id="{{ $emp['id'] }}" data-search-text="{{ strtolower($emp['name'] . ' ' . $emp['employment_status_label'] . ' ' . $emp['position'] . ' ' . $emp['formatted_id']) }}">
                                    <!-- Leaf Node: No Circle Bullet -->
                                    <div class="org-node-container d-inline-flex align-items-center">
                                        <div class="card org-card org-card-employee border-0 shadow-sm" :class="{ 'org-card-matched': isMatch('{{ strtolower($emp['name'] . ' ' . $emp['employment_status_label'] . ' ' . $emp['position'] . ' ' . $emp['formatted_id']) }}') }">
                                            <div class="card-body d-flex align-items-center gap-2">
                                                <!-- Avatar / Photo -->
                                                <div class="flex-shrink-0">
                                                    @if($emp['photo_url'])
                                                        <img src="{{ $emp['photo_url'] }}"
                                                             alt="{{ $emp['name'] }}"
                                                             class="rounded-circle object-fit-cover border"
                                                             style="width: 26px; height: 26px;">
                                                    @else
                                                        <div class="avatar-circle rounded-circle bg-primary-subtle text-primary fw-bold d-flex align-items-center justify-content-center border border-primary-subtle"
                                                             style="width: 26px; height: 26px; font-size: 0.7rem;">
                                                            {{ $initials }}
                                                        </div>
                                                    @endif
                                                </div>

                                                <!-- {Employee Name - Employment Status - Position} -->
                                                <div class="d-flex flex-wrap align-items-center gap-1.5 lh-1 text-truncate">
                                                    <a href="{{ url('/employee/' . $emp['id']) }}" class="fw-bold text-decoration-none text-body small text-truncate" title="View employee details">
                                                        {{ $emp['name'] }}
                                                    </a>
                                                    <span class="text-secondary small">-</span>
                                                    <span class="badge {{ $statusClass }} px-1.5 py-0" style="font-size: 0.65rem;">
                                                        {{ $emp['employment_status_label'] }}
                                                    </span>
                                                    <span class="text-secondary small">-</span>
                                                    <span class="fw-medium text-body-secondary small text-truncate">
                                                        <i class="bi bi-briefcase me-1 text-primary"></i>{{ $emp['position'] }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </li>
            @endforeach
        </ul>
    @endif
</li>
