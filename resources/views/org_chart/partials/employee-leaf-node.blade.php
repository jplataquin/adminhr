@props(['emp'])

@php
    $initials = $emp['initials'] ?? strtoupper(substr($emp['firstname'] ?? 'E', 0, 1) . substr($emp['lastname'] ?? '', 0, 1));
    $statusClass = match($emp['employment_status']) {
        'REGU' => 'bg-success-subtle text-success border border-success-subtle',
        'PROB' => 'bg-warning-subtle text-warning border border-warning-subtle',
        'CONT' => 'bg-info-subtle text-info border border-info-subtle',
        default => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
    };
@endphp

<li class="org-tree-item" data-employee-id="{{ $emp['id'] }}" data-search-text="{{ strtolower($emp['name'] . ' ' . ($emp['employment_status_label'] ?? $emp['employment_status']) . ' ' . $emp['position'] . ' ' . $emp['formatted_id']) }}" style="list-style-type: none;">
    <!-- Leaf Node: No Circle Bullet -->
    <div class="org-node-container d-inline-flex align-items-center">
        <div class="card org-card org-card-employee border-0 shadow-sm" :class="{ 'org-card-matched': isMatch('{{ strtolower($emp['name'] . ' ' . ($emp['employment_status_label'] ?? $emp['employment_status']) . ' ' . $emp['position'] . ' ' . $emp['formatted_id']) }}') }">
            <div class="card-body d-flex align-items-center gap-2.5">
                <!-- Avatar / Photo -->
                <div class="flex-shrink-0" style="margin-right: 10px;">
                    @if($emp['photo_url'])
                        <img src="{{ $emp['photo_url'] }}"
                             alt="{{ $emp['name'] }}"
                             class="rounded-circle object-fit-cover border"
                             style="width: 50px; height: 50px;">
                    @else
                        <div class="avatar-circle rounded-circle bg-primary-subtle text-primary fw-bold d-flex align-items-center justify-content-center border border-primary-subtle"
                             style="width: 50px; height: 50px; font-size: 1.1rem;">
                            {{ $initials }}
                        </div>
                    @endif
                </div>

                <!-- {Employee Name, Position, Employment Status} -->
                <div class="min-w-0 flex-grow-1">
                    <div class="lh-1 text-truncate" style="margin-bottom: 10px;">
                        <a href="{{ url('/employee/' . $emp['id']) }}"
                           class="fw-bold text-decoration-none text-body small text-truncate org-employee-name-link"
                           title="View employee details"
                           @mouseenter="showHoverCard(@js($emp), $event)"
                           @mouseleave="hideHoverCard()">
                            {{ $emp['name'] }}
                        </a>
                    </div>
                    <div class="lh-1 text-truncate" style="margin-bottom: 10px;">
                        <span class="fw-medium text-body-secondary small text-truncate">
                            {{ $emp['position'] }}
                        </span>
                    </div>
                    <div class="lh-1">
                        <span class="badge {{ $statusClass }} px-1.5 py-0" style="font-size: 0.65rem;">
                            {{ $emp['employment_status_label'] ?? $emp['employment_status'] }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</li>
