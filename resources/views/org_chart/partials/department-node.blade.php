@props(['division'])

@php
    $divKey = 'div-' . $division['id'];
    $hasDepts = !empty($division['departments']);
@endphp

<li class="org-tree-item" data-division-id="{{ $division['id'] }}" data-search-text="{{ strtolower($division['name'] . ' ' . $division['code']) }}">
    <!-- Division Level Node -->
    <div class="org-node-container d-inline-flex align-items-center mb-2">
        @if($hasDepts)
            <button type="button"
                    class="btn btn-sm btn-outline-secondary org-toggle-btn me-2 p-0 d-inline-flex align-items-center justify-content-center"
                    @click="toggleNode('{{ $divKey }}')"
                    aria-label="Toggle division">
                <i class="bi fs-6" :class="isNodeOpen('{{ $divKey }}') ? 'bi-chevron-down' : 'bi-chevron-right'"></i>
            </button>
        @else
            <span class="org-leaf-bullet me-2"></span>
        @endif

        <div class="card org-card org-card-division border-0 shadow-sm" :class="{ 'org-card-matched': isMatch('{{ strtolower($division['name'] . ' ' . $division['code']) }}') }">
            <div class="card-body p-2 d-flex align-items-center gap-3">
                <div class="rounded-3 p-2 bg-primary text-white d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                    <i class="bi bi-buildings fs-5"></i>
                </div>
                <div class="min-w-0 flex-grow-1">
                    <div class="d-flex align-items-center gap-2">
                        <span class="fw-bold fs-6 text-body text-truncate">{{ $division['name'] }}</span>
                        <span class="badge bg-primary-subtle text-primary">{{ $division['code'] }}</span>
                    </div>
                    <div class="small text-secondary">
                        <span>{{ count($division['departments']) }} departments</span>
                        <span class="mx-1">•</span>
                        <span>{{ $division['employees_count'] }} employees</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Departments List -->
    @if($hasDepts)
        <ul class="org-tree-children ps-4" x-show="isNodeOpen('{{ $divKey }}')" x-collapse.duration.200ms>
            @foreach($division['departments'] as $dept)
                @php
                    $deptKey = 'dept-' . $division['id'] . '-' . $dept['id'];
                    $hasPositions = !empty($dept['positions']);
                @endphp
                <li class="org-tree-item" data-department-id="{{ $dept['id'] }}" data-search-text="{{ strtolower($dept['name'] . ' ' . $dept['code']) }}">
                    <div class="org-node-container d-inline-flex align-items-center mb-2">
                        @if($hasPositions)
                            <button type="button"
                                    class="btn btn-sm btn-outline-secondary org-toggle-btn me-2 p-0 d-inline-flex align-items-center justify-content-center"
                                    @click="toggleNode('{{ $deptKey }}')"
                                    aria-label="Toggle department">
                                <i class="bi fs-6" :class="isNodeOpen('{{ $deptKey }}') ? 'bi-chevron-down' : 'bi-chevron-right'"></i>
                            </button>
                        @else
                            <span class="org-leaf-bullet me-2"></span>
                        @endif

                        <div class="card org-card org-card-dept border-0 shadow-sm" :class="{ 'org-card-matched': isMatch('{{ strtolower($dept['name'] . ' ' . $dept['code']) }}') }">
                            <div class="card-body p-2 d-flex align-items-center gap-3">
                                <div class="rounded-3 p-2 bg-info-subtle text-info d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                                    <i class="bi bi-diagram-3 fs-6"></i>
                                </div>
                                <div class="min-w-0 flex-grow-1">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="fw-semibold text-body text-truncate">{{ $dept['name'] }}</span>
                                        <span class="badge bg-secondary-subtle text-secondary small">{{ $dept['code'] }}</span>
                                    </div>
                                    <div class="small text-secondary">
                                        <span>{{ count($dept['positions']) }} positions</span>
                                        <span class="mx-1">•</span>
                                        <span>{{ $dept['employees_count'] }} employees</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Positions List -->
                    @if($hasPositions)
                        <ul class="org-tree-children ps-4" x-show="isNodeOpen('{{ $deptKey }}')" x-collapse.duration.200ms>
                            @foreach($dept['positions'] as $pos)
                                @php
                                    $posKey = 'pos-' . $deptKey . '-' . $pos['id'];
                                    $hasEmployees = !empty($pos['employees']);
                                @endphp
                                <li class="org-tree-item" data-position-id="{{ $pos['id'] }}" data-search-text="{{ strtolower($pos['name'] . ' ' . $pos['code']) }}">
                                    <div class="org-node-container d-inline-flex align-items-center mb-2">
                                        @if($hasEmployees)
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-secondary org-toggle-btn me-2 p-0 d-inline-flex align-items-center justify-content-center"
                                                    @click="toggleNode('{{ $posKey }}')"
                                                    aria-label="Toggle position">
                                                <i class="bi fs-6" :class="isNodeOpen('{{ $posKey }}') ? 'bi-chevron-down' : 'bi-chevron-right'"></i>
                                            </button>
                                        @else
                                            <span class="org-leaf-bullet me-2"></span>
                                        @endif

                                        <div class="card org-card org-card-position border-0 shadow-sm" :class="{ 'org-card-matched': isMatch('{{ strtolower($pos['name'] . ' ' . $pos['code']) }}') }">
                                            <div class="card-body p-2 d-flex align-items-center gap-3">
                                                <div class="rounded-3 p-2 bg-secondary-subtle text-secondary d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                                    <i class="bi bi-briefcase fs-6"></i>
                                                </div>
                                                <div class="min-w-0 flex-grow-1">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <span class="fw-semibold text-body small text-truncate">{{ $pos['name'] }}</span>
                                                        <span class="badge bg-light text-dark border small">{{ $pos['employees_count'] }} employees</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Employees in this position -->
                                    @if($hasEmployees)
                                        <ul class="org-tree-children ps-4" x-show="isNodeOpen('{{ $posKey }}')" x-collapse.duration.200ms>
                                            @foreach($pos['employees'] as $emp)
                                                @php
                                                    $initials = strtoupper(substr($emp['name'] ?? 'E', 0, 1));
                                                    $statusClass = match($emp['employment_status']) {
                                                        'REGU' => 'bg-success-subtle text-success border border-success-subtle',
                                                        'PROB' => 'bg-warning-subtle text-warning border border-warning-subtle',
                                                        'CONT' => 'bg-info-subtle text-info border border-info-subtle',
                                                        default => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
                                                    };
                                                @endphp
                                                <li class="org-tree-item" data-employee-id="{{ $emp['id'] }}" data-search-text="{{ strtolower($emp['name'] . ' ' . $emp['formatted_id']) }}">
                                                    <div class="org-node-container d-inline-flex align-items-center mb-2">
                                                        <span class="org-leaf-bullet me-2"></span>
                                                        <div class="card org-card border-0 shadow-sm" :class="{ 'org-card-matched': isMatch('{{ strtolower($emp['name'] . ' ' . $emp['formatted_id']) }}') }">
                                                            <div class="card-body p-2 d-flex align-items-center gap-2">
                                                                <div class="avatar-circle rounded-circle bg-primary-subtle text-primary fw-bold d-flex align-items-center justify-content-center border border-primary-subtle"
                                                                     style="width: 34px; height: 34px; font-size: 0.85rem;">
                                                                    {{ $initials }}
                                                                </div>
                                                                <div>
                                                                    <div class="d-flex align-items-center gap-2">
                                                                        <a href="{{ url('/employee/' . $emp['id']) }}" class="fw-bold text-decoration-none text-body small text-truncate">
                                                                            {{ $emp['name'] }}
                                                                        </a>
                                                                        <span class="badge bg-secondary-subtle text-secondary small">#{{ $emp['formatted_id'] }}</span>
                                                                        <span class="badge {{ $statusClass }} small">{{ $emp['employment_status'] }}</span>
                                                                    </div>
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
            @endforeach
        </ul>
    @endif
</li>
