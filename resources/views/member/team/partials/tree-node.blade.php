@php
    $hasChildren = !empty($node->children) && count($node->children) > 0;
    $isRoot = isset($node->level) && $node->level === 0;
    $statusText = ($node->status == 'Temp') ? 'Inactive' : $node->status;
    $statusClass = ($node->status == 'Active') ? 'status-active' : 'status-inactive';
    $profileImg = !empty($node->profile_image) ? asset('uploads/' . $node->profile_image) : asset('uploads/avatar.jpg');
    $levelLabel = $isRoot ? 'ROOT' : 'LEVEL ' . $node->level;
    $levelTagClass = $isRoot ? 'root-tag' : 'level-' . $node->level . '-tag';
@endphp

<li class="level-tree-node-item {{ $isRoot ? 'is-root-node' : '' }} {{ $hasChildren ? 'has-children' : 'is-leaf' }}" data-node-id="{{ $node->memberid }}">
    <div class="level-node-wrapper">
        <div class="level-node-card {{ $isRoot ? 'root-card' : '' }} {{ $statusClass }}"
            data-node-id="{{ $node->memberid }}"
            data-member-id="{{ $node->memberid }}"
            data-name="{{ $node->name ?: 'Member' }}"
            data-level="{{ $levelLabel }}"
            data-level-class="{{ $levelTagClass }}"
            data-avatar="{{ $profileImg }}"
            data-status="{{ $statusText }}"
            data-status-class="{{ $statusClass }}"
            data-sponsor="{{ $node->sponsorid ?: '-' }}"
            data-package="$ {{ number_format((float)($node->package ?? 0), 2) }}"
            data-direct="{{ $node->downline ?? count($node->children ?? []) }}"
            data-selfbiz="$ {{ number_format((float)($node->self_biz ?? 0), 2) }}"
            data-wallet="$ {{ number_format((float)($node->wallet ?? 0), 2) }}"
            data-fundwallet="$ {{ number_format((float)($node->p2p_wallet ?? 0), 2) }}"
            data-view-url="{{ url('/member/team/view-geneology') }}/{{ $node->id }}">

            {{-- Level Badge --}}
            <div class="level-node-header">
                @if ($isRoot)
                    <span class="level-tag root-tag"><i class="fe fe-award"></i> ROOT</span>
                @else
                    <span class="level-tag level-{{ $node->level }}-tag">LEVEL {{ $node->level }}</span>
                @endif
            </div>

            {{-- Avatar & Core Info --}}
            <div class="level-node-body">
                <a href="{{ url('/member/team/view-geneology') }}/{{ $node->id }}" class="level-node-avatar-link" title="Click to view genealogy of {{ $node->memberid }}">
                    <div class="level-node-avatar">
                        <img src="{{ $profileImg }}" alt="{{ $node->memberid }}" onerror="this.src='{{ asset('uploads/avatar.jpg') }}'">
                        <span class="status-indicator {{ $statusClass }}"></span>
                    </div>
                </a>

                <div class="level-node-info">
                    <a href="{{ url('/member/team/view-geneology') }}/{{ $node->id }}" class="level-node-id" title="View Genealogy">
                        {{ $node->memberid }}
                    </a>
                    <span class="level-node-status {{ $statusClass }}">{{ $statusText }}</span>
                </div>
            </div>

            {{-- Direct Count / Branch Toggle --}}
            @if ($hasChildren)
                <div class="level-node-footer">
                    <button type="button" class="level-toggle-btn" title="Toggle downline members" data-target="{{ $node->memberid }}">
                        <i class="fe fe-users"></i>
                        <span>{{ count($node->children) }} Direct{{ count($node->children) > 1 ? 's' : '' }}</span>
                        <i class="fe fe-chevron-down toggle-icon"></i>
                    </button>
                </div>
            @endif
        </div>
    </div>

    {{-- Recursive Sub-branches --}}
    @if ($hasChildren)
        <ul class="level-tree-children" id="branch-{{ $node->memberid }}">
            @foreach ($node->children as $child)
                @include('member.team.partials.tree-node', ['node' => $child])
            @endforeach
        </ul>
    @endif
</li>
