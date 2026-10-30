@props(['node', 'level' => 0])

@php
    $nodeKey = 'rep-' . $node['id'];
    $hasChildren = !empty($node['subordinates']);
    $initials = strtoupper(substr($node['firstname'] ?? 'E', 0, 1) . substr($node['lastname'] ?? '', 0, 1));
    $statusClass = match($node['employment_status']) {
        'REGU' => 'bg-success-subtle text-success border border-success-subtle',
        'PROB' => 'bg-warning-subtle text-warning border border-warning-subtle',
        'CONT' => 'bg-info-subtle text-info border border-info-subtle',
        default => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
    };
@endphp

<li class="org-tree-item" data-employee-id="{{ $node['id'] }}" data-search-text="{{ strtolower($node['name'] . ' ' . $node['position'] . ' ' . $node['department'] . ' ' . $node['division'] . ' ' . $node['formatted_id']) }}">
    <div class="org-node-container d-inline-flex align-items-center">
        @if($hasChildren)
            <button type="button"
                    class="btn btn-sm btn-outline-secondary org-toggle-btn me-2 p-0 d-inline-flex align-items-center justify-content-center"
                    @click="toggleNode('{{ $nodeKey }}')"
                    :title="isNodeOpen('{{ $nodeKey }}') ? 'Collapse subordinates' : 'Expand subordinates'"
                    aria-label="Toggle node">
                <i class="bi" :class="isNodeOpen('{{ $nodeKey }}') ? 'bi-chevron-down' : 'bi-chevron-right'"></i>
            </button>
        @else
            <span class="org-leaf-bullet me-2"></span>
        @endif

        <div class="card org-card border-0 shadow-sm" :class="{ 'org-card-matched': isMatch('{{ strtolower($node['name'] . ' ' . $node['position'] . ' ' . $node['department'] . ' ' . $node['formatted_id']) }}') }">
            <div class="card-body d-flex align-items-center gap-2.5">
                <!-- Avatar / Photo -->
                <div class="flex-shrink-0">
                    @if($node['photo_url'])
                        <img src="{{ $node['photo_url'] }}"
                             alt="{{ $node['name'] }}"
                             class="rounded-circle object-fit-cover border"
                             style="width: 32px; height: 32px;">
                    @else
                        <div class="avatar-circle rounded-circle bg-primary-subtle text-primary fw-bold d-flex align-items-center justify-content-center border border-primary-subtle"
                             style="width: 32px; height: 32px; font-size: 0.75rem;">
                            {{ $initials }}
                        </div>
                    @endif
                </div>

                <!-- Info -->
                <div class="min-w-0 flex-grow-1">
                    <div class="d-flex align-items-center gap-1.5 lh-1 mb-1">
                        <a href="{{ url('/employee/' . $node['id']) }}" class="fw-bold text-decoration-none text-body text-truncate employee-name-link small" title="View employee details">
                            {{ $node['name'] }}
                        </a>
                        <span class="badge bg-secondary-subtle text-secondary font-monospace px-1 py-0" style="font-size: 0.65rem;">#{{ $node['formatted_id'] }}</span>
                        <span class="badge {{ $statusClass }} px-1 py-0" style="font-size: 0.65rem;">{{ $node['employment_status'] }}</span>
                    </div>

                    <div class="d-flex flex-wrap align-items-center gap-1.5 lh-1 text-secondary" style="font-size: 0.72rem;">
                        <span class="fw-medium text-body-secondary"><i class="bi bi-briefcase me-1 text-primary"></i>{{ $node['position'] }}</span>
                        <span class="text-white-50">•</span>
                        <span><i class="bi bi-diagram-3 me-1 text-info"></i>{{ $node['department'] }}</span>
                    </div>
                </div>

                <!-- Children / Subordinate Count Badge -->
                @if($hasChildren)
                    <div class="flex-shrink-0 ms-1.5">
                        <button type="button"
                                class="btn btn-sm btn-outline-info py-0 px-1.5 d-flex align-items-center gap-1 rounded-pill"
                                style="font-size: 0.68rem;"
                                @click="toggleNode('{{ $nodeKey }}')"
                                title="{{ $node['direct_reports'] }} direct, {{ $node['total_subordinates'] }} total reports">
                            <i class="bi bi-people-fill"></i>
                            <span class="fw-bold">{{ $node['direct_reports'] }}</span>
                            <span class="text-muted d-none d-sm-inline">({{ $node['total_subordinates'] }})</span>
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if($hasChildren)
        <ul class="org-tree-children ps-4" x-show="isNodeOpen('{{ $nodeKey }}')" x-collapse.duration.200ms>
            @foreach($node['subordinates'] as $subordinate)
                @include('org_chart.partials.reporting-node', ['node' => $subordinate, 'level' => $level + 1])
            @endforeach
        </ul>
    @endif
</li>
