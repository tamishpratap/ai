@extends('member.layouts.main')
@section('title', 'Geneology')
@section('container')
    <!-- MAIN-CONTENT -->
    <div class="main-content app-content">
        <div class="container-fluid">
            <!-- Breadcrumbs / Page Title -->
            <div class="row">
                <div class="mb-3 mt-3">
                    <h5 class="fw-medium mb-0">Geneology</h5>
                    <div class="text-muted fs-13 op-8 mb-3">
                        Check your Level-Wise Team Geneology
                    </div>
                </div>

                @if (session()->has('searchMsg'))
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                        <i class="fe fe-alert-circle me-1"></i> {{ session('searchMsg') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if (session()->has('failedMsg'))
                    <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
                        <i class="fe fe-alert-triangle me-1"></i> {{ session('failedMsg') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="col-xl-12">
                    <div class="level-genealogy-container">
                        <!-- Search Members Bar -->
                        <div class="level-search-banner">
                            <div class="row align-items-center justify-content-between g-2">
                                <div class="col-md-5">
                                    <h6 class="level-search-title">
                                        <i class="fe fe-search"></i>
                                        Search Team Members
                                    </h6>
                                </div>
                                <div class="col-md-5 col-lg-4">
                                    <form action="{{ route('searchGenelogy') }}" method="post" class="m-0">
                                        @csrf
                                        <div class="input-group">
                                            <input type="text" class="form-control form-control-sm level-search-input"
                                                placeholder="Enter Member ID (e.g. GAB123456)" name="searchid"
                                                value="{{ request('searchid') }}" required>
                                            <button class="btn btn-sm level-search-btn" type="submit">
                                                <i class="fe fe-arrow-right"></i> Search
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Reroot / Downline Notice if viewing non-root -->
                        @if (empty($is_root_view) || $d_data['memberid'] !== $data['memberid'])
                            <div class="level-reroot-banner">
                                <div>
                                    <i class="fe fe-info me-1"></i> Viewing team tree for member:
                                    <strong>{{ $d_data['memberid'] }}</strong> ({{ $d_data['name'] ?: 'Member' }})
                                </div>
                                <a href="{{ url('/member/team/geneology') }}" class="level-back-root-btn">
                                    <i class="fe fe-corner-up-left"></i> Return to My Tree
                                </a>
                            </div>
                        @endif

                        <!-- Level Summary Bar & Toolbar -->
                        <div class="level-stats-bar">
                            <div class="level-stats-chips">
                                <div class="level-stat-pill stat-total">
                                    <span>Total Team:</span>
                                    <strong>{{ $totalMembersCount ?? 0 }}</strong>
                                </div>
                                <div class="level-stat-pill">
                                    <span>Max Depth:</span>
                                    <strong>{{ $maxLevel ?? 0 }} {{ ($maxLevel ?? 0) === 1 ? 'Level' : 'Levels' }}</strong>
                                </div>
                                @if (!empty($levels))
                                    @foreach ($levels as $lvlIndex => $lvlMembers)
                                        <div class="level-stat-pill">
                                            <span class="lvl-num">L{{ $lvlIndex }}:</span>
                                            <strong>{{ count($lvlMembers) }}</strong>
                                        </div>
                                    @endforeach
                                @endif
                            </div>

                            <div class="level-toolbar">
                                <button type="button" class="level-tool-btn" id="treeZoomIn" title="Zoom In">
                                    <i class="fe fe-plus"></i>
                                </button>
                                <button type="button" class="level-tool-btn" id="treeZoomOut" title="Zoom Out">
                                    <i class="fe fe-minus"></i>
                                </button>
                                <button type="button" class="level-tool-btn" id="treeResetZoom" title="Reset Zoom">
                                    <i class="fe fe-refresh-cw"></i>
                                </button>
                                <button type="button" class="level-tool-btn" id="treeExpandAll" title="Expand All Branches">
                                    <i class="fe fe-maximize-2"></i>
                                </button>
                                <button type="button" class="level-tool-btn" id="treeCollapseAll" title="Collapse All Branches">
                                    <i class="fe fe-minimize-2"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Level-Wise Tree Viewport -->
                        <div class="level-tree-viewport" id="levelTreeViewport">
                            <div class="level-tree-canvas" id="levelTreeCanvas">
                                @if (!empty($tree))
                                    <ul class="level-tree-root-list">
                                        @include('member.team.partials.tree-node', ['node' => $tree])
                                    </ul>
                                @else
                                    <div class="level-empty-state">
                                        <div class="level-empty-icon">
                                            <i class="fe fe-users"></i>
                                        </div>
                                        <h5>No Team Members Found</h5>
                                        <p>No team members found under this member.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End:: row-1 -->
        </div>
    </div>
    <!-- End::app-content -->

    <!-- Floating Member Details Popup Overlay (Portal Layer) -->
    <div class="genealogy-popup-backdrop" id="genealogyPopupBackdrop"></div>
    <div class="genealogy-floating-popup" id="genealogyFloatingPopup" role="dialog" aria-hidden="true">
        <button type="button" class="popup-close-btn" id="genealogyPopupCloseBtn" aria-label="Close">
            <i class="fe fe-x"></i>
        </button>
        <div class="level-popup-header">
            <div class="popup-avatar">
                <img src="" id="popAvatar" alt="Avatar" onerror="this.src='{{ asset('uploads/avatar.jpg') }}'">
            </div>
            <div class="popup-user-meta">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="level-tag" id="popLevelTag">LEVEL</span>
                    <span class="level-node-status" id="popStatus">Active</span>
                </div>
                <h5 class="popup-name" id="popName">Member Name</h5>
                <span class="popup-id" id="popId">GAB123456</span>
            </div>
        </div>
        <div class="level-popup-body">
            <div class="popup-row">
                <span class="popup-label">Sponsor ID</span>
                <span class="popup-value" id="popSponsor">-</span>
            </div>
            <div class="popup-row">
                <span class="popup-label">Package</span>
                <span class="popup-value" id="popPackage">$ 0.00</span>
            </div>
            <div class="popup-row">
                <span class="popup-label">Direct</span>
                <span class="popup-value" id="popDirect">0</span>
            </div>
            <div class="popup-row">
                <span class="popup-label">Self Biz</span>
                <span class="popup-value" id="popSelfBiz">$ 0.00</span>
            </div>
            <div class="popup-row">
                <span class="popup-label">Wallet</span>
                <span class="popup-value" id="popWallet">$ 0.00</span>
            </div>
            <div class="popup-row">
                <span class="popup-label">Fund Wallet</span>
                <span class="popup-value" id="popFundWallet">$ 0.00</span>
            </div>
        </div>
        <div class="level-popup-footer">
            <a href="#" class="popup-tree-btn" id="popViewTreeBtn">
                <i class="fe fe-git-pull-request me-1"></i> View Genealogy
            </a>
        </div>
    </div>

    @push('user-scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const canvas = document.getElementById('levelTreeCanvas');
                const viewport = document.getElementById('levelTreeViewport');
                const popup = document.getElementById('genealogyFloatingPopup');
                const backdrop = document.getElementById('genealogyPopupBackdrop');
                const closeBtn = document.getElementById('genealogyPopupCloseBtn');

                // Popup Elements
                const popAvatar = document.getElementById('popAvatar');
                const popLevelTag = document.getElementById('popLevelTag');
                const popStatus = document.getElementById('popStatus');
                const popName = document.getElementById('popName');
                const popId = document.getElementById('popId');
                const popSponsor = document.getElementById('popSponsor');
                const popPackage = document.getElementById('popPackage');
                const popDirect = document.getElementById('popDirect');
                const popSelfBiz = document.getElementById('popSelfBiz');
                const popWallet = document.getElementById('popWallet');
                const popFundWallet = document.getElementById('popFundWallet');
                const popViewTreeBtn = document.getElementById('popViewTreeBtn');

                let activeCard = null;
                let showTimer = null;
                let hideTimer = null;
                let isMouseOverPopup = false;
                let currentScale = 1;
                const minScale = 0.4;
                const maxScale = 1.6;
                const scaleStep = 0.15;

                function applyScale(scale) {
                    currentScale = Math.min(Math.max(scale, minScale), maxScale);
                    if (canvas) {
                        canvas.style.transform = `scale(${currentScale})`;
                    }
                    if (activeCard && popup && popup.classList.contains('is-visible')) {
                        updatePopupPosition(activeCard);
                    }
                }

                // Zoom Controls
                const btnZoomIn = document.getElementById('treeZoomIn');
                const btnZoomOut = document.getElementById('treeZoomOut');
                const btnReset = document.getElementById('treeResetZoom');
                const btnExpandAll = document.getElementById('treeExpandAll');
                const btnCollapseAll = document.getElementById('treeCollapseAll');

                if (btnZoomIn) {
                    btnZoomIn.addEventListener('click', () => applyScale(currentScale + scaleStep));
                }
                if (btnZoomOut) {
                    btnZoomOut.addEventListener('click', () => applyScale(currentScale - scaleStep));
                }
                if (btnReset) {
                    btnReset.addEventListener('click', () => {
                        applyScale(1);
                        if (viewport) {
                            viewport.scrollLeft = (viewport.scrollWidth - viewport.clientWidth) / 2;
                        }
                    });
                }

                // Expand / Collapse All
                if (btnExpandAll) {
                    btnExpandAll.addEventListener('click', () => {
                        document.querySelectorAll('.level-tree-node-item.is-collapsed').forEach(node => {
                            node.classList.remove('is-collapsed');
                        });
                        if (activeCard) updatePopupPosition(activeCard);
                    });
                }
                if (btnCollapseAll) {
                    btnCollapseAll.addEventListener('click', () => {
                        document.querySelectorAll('.level-tree-node-item.has-children').forEach(node => {
                            if (!node.classList.contains('is-root-node')) {
                                node.classList.add('is-collapsed');
                            }
                        });
                        hidePopup();
                    });
                }

                // Individual Branch Toggles
                document.querySelectorAll('.level-toggle-btn').forEach(btn => {
                    btn.addEventListener('click', function (e) {
                        e.stopPropagation();
                        const nodeItem = this.closest('.level-tree-node-item');
                        if (nodeItem) {
                            nodeItem.classList.toggle('is-collapsed');
                        }
                        if (activeCard) {
                            setTimeout(() => updatePopupPosition(activeCard), 50);
                        }
                    });
                });

                // ==========================================
                // FLOATING POPUP INTELLIGENT POSITIONING
                // ==========================================
                function showPopupForCard(card) {
                    if (!card || !popup) return;
                    activeCard = card;

                    // Populate Data
                    const d = card.dataset;
                    if (popAvatar) popAvatar.src = d.avatar || '{{ asset('uploads/avatar.jpg') }}';
                    if (popLevelTag) {
                        popLevelTag.textContent = d.level || 'LEVEL';
                        popLevelTag.className = 'level-tag ' + (d.levelClass || '');
                    }
                    if (popStatus) {
                        popStatus.textContent = d.status || 'Active';
                        popStatus.className = 'level-node-status ' + (d.statusClass || '');
                    }
                    if (popName) popName.textContent = d.name || 'Member';
                    if (popId) popId.textContent = d.memberId || '';
                    if (popSponsor) popSponsor.textContent = d.sponsor || '-';
                    if (popPackage) popPackage.textContent = d.package || '$ 0.00';
                    if (popDirect) popDirect.textContent = d.direct || '0';
                    if (popSelfBiz) popSelfBiz.textContent = d.selfbiz || '$ 0.00';
                    if (popWallet) popWallet.textContent = d.wallet || '$ 0.00';
                    if (popFundWallet) popFundWallet.textContent = d.fundwallet || '$ 0.00';
                    if (popViewTreeBtn) popViewTreeBtn.href = d.viewUrl || '#';

                    updatePopupPosition(card);

                    popup.classList.add('is-visible');
                    popup.setAttribute('aria-hidden', 'false');
                    if (window.innerWidth <= 600 && backdrop) {
                        backdrop.classList.add('is-visible');
                    }
                }

                function updatePopupPosition(card) {
                    if (!card || !popup) return;

                    const isMobile = window.innerWidth <= 600;
                    if (isMobile) {
                        popup.classList.add('is-mobile-sheet');
                        popup.style.top = '';
                        popup.style.left = '';
                        popup.style.right = '';
                        popup.style.bottom = '';
                        return;
                    }

                    popup.classList.remove('is-mobile-sheet');
                    if (backdrop) backdrop.classList.remove('is-visible');

                    const rect = card.getBoundingClientRect();
                    const popWidth = popup.offsetWidth || 285;
                    const popHeight = popup.offsetHeight || 330;
                    const gap = 14;
                    const margin = 14;

                    const spaceRight = window.innerWidth - rect.right;
                    const spaceLeft = rect.left;
                    const spaceBottom = window.innerHeight - rect.bottom;
                    const spaceTop = rect.top;

                    let targetLeft = 0;
                    let targetTop = 0;

                    // Intelligent placement preference: Right -> Left -> Bottom -> Top
                    if (spaceRight >= popWidth + gap + margin) {
                        // Place on Right Side
                        targetLeft = rect.right + gap;
                        targetTop = rect.top + (rect.height - popHeight) / 2;
                    } else if (spaceLeft >= popWidth + gap + margin) {
                        // Place on Left Side
                        targetLeft = rect.left - popWidth - gap;
                        targetTop = rect.top + (rect.height - popHeight) / 2;
                    } else if (spaceBottom >= popHeight + gap + margin) {
                        // Place Below
                        targetLeft = rect.left + (rect.width - popWidth) / 2;
                        targetTop = rect.bottom + gap;
                    } else if (spaceTop >= popHeight + gap + margin) {
                        // Place Above
                        targetLeft = rect.left + (rect.width - popWidth) / 2;
                        targetTop = rect.top - popHeight - gap;
                    } else {
                        // Fallback: Place on side with maximum room
                        if (spaceRight >= spaceLeft) {
                            targetLeft = rect.right + gap;
                        } else {
                            targetLeft = rect.left - popWidth - gap;
                        }
                        targetTop = rect.top + (rect.height - popHeight) / 2;
                    }

                    // Keep within viewport boundaries
                    targetLeft = Math.max(margin, Math.min(window.innerWidth - popWidth - margin, targetLeft));
                    targetTop = Math.max(margin, Math.min(window.innerHeight - popHeight - margin, targetTop));

                    popup.style.top = `${Math.round(targetTop)}px`;
                    popup.style.left = `${Math.round(targetLeft)}px`;
                    popup.style.right = 'auto';
                    popup.style.bottom = 'auto';
                }

                function hidePopup() {
                    if (!popup) return;
                    popup.classList.remove('is-visible');
                    popup.setAttribute('aria-hidden', 'true');
                    if (backdrop) backdrop.classList.remove('is-visible');
                    activeCard = null;
                }

                // Hover & Click Events on Member Cards
                document.querySelectorAll('.level-node-card').forEach(card => {
                    // Hover desktop
                    card.addEventListener('mouseenter', function () {
                        if (window.innerWidth <= 600) return; // Tap only on mobile
                        if (this.dataset.temporarilyClosed === 'true') return;

                        clearTimeout(hideTimer);
                        clearTimeout(showTimer);
                        showTimer = setTimeout(() => {
                            showPopupForCard(this);
                        }, 120);
                    });

                    card.addEventListener('mouseleave', function () {
                        delete this.dataset.temporarilyClosed;
                        clearTimeout(showTimer);
                        hideTimer = setTimeout(() => {
                            if (!isMouseOverPopup) {
                                hidePopup();
                            }
                        }, 200);
                    });

                    // Click / Tap (desktop & mobile)
                    card.addEventListener('click', function (e) {
                        // Don't trigger if clicked avatar view link or direct toggle
                        if (e.target.closest('.level-toggle-btn, .level-node-avatar-link')) return;
                        delete this.dataset.temporarilyClosed;
                        clearTimeout(showTimer);
                        clearTimeout(hideTimer);
                        showPopupForCard(this);
                    });
                });

                // Popup Hover Keep-Alive
                if (popup) {
                    popup.addEventListener('mouseenter', () => {
                        if (window.innerWidth <= 600) return;
                        isMouseOverPopup = true;
                        clearTimeout(hideTimer);
                    });

                    popup.addEventListener('mouseleave', () => {
                        if (window.innerWidth <= 600) return;
                        isMouseOverPopup = false;
                        hideTimer = setTimeout(() => {
                            hidePopup();
                        }, 180);
                    });

                    popup.addEventListener('click', (e) => {
                        e.stopPropagation();
                    });
                }

                // Close Button
                if (closeBtn) {
                    closeBtn.addEventListener('click', (e) => {
                        e.stopPropagation();
                        if (activeCard) {
                            activeCard.dataset.temporarilyClosed = 'true';
                        }
                        hidePopup();
                    });
                }

                // Backdrop Click
                if (backdrop) {
                    backdrop.addEventListener('click', () => {
                        hidePopup();
                    });
                }

                // Global Click Outside & Escape Key
                document.addEventListener('click', (e) => {
                    if (!e.target.closest('.level-node-card, .genealogy-floating-popup')) {
                        hidePopup();
                    }
                });

                document.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape') {
                        hidePopup();
                    }
                });

                // Reposition on Scroll / Pan
                if (viewport) {
                    let isDown = false;
                    let startX, startY, scrollLeft, scrollTop;

                    viewport.addEventListener('scroll', () => {
                        if (activeCard && popup && popup.classList.contains('is-visible') && window.innerWidth > 600) {
                            const rect = activeCard.getBoundingClientRect();
                            const vRect = viewport.getBoundingClientRect();
                            if (rect.bottom < vRect.top || rect.top > vRect.bottom || rect.right < vRect.left || rect.left > vRect.right) {
                                hidePopup();
                            } else {
                                updatePopupPosition(activeCard);
                            }
                        }
                    });

                    viewport.addEventListener('mousedown', (e) => {
                        if (e.target.closest('button, a, input, .level-node-card')) return;
                        isDown = true;
                        viewport.classList.add('is-dragging');
                        startX = e.pageX - viewport.offsetLeft;
                        startY = e.pageY - viewport.offsetTop;
                        scrollLeft = viewport.scrollLeft;
                        scrollTop = viewport.scrollTop;
                    });

                    window.addEventListener('mouseup', () => {
                        isDown = false;
                        if (viewport) viewport.classList.remove('is-dragging');
                    });

                    viewport.addEventListener('mousemove', (e) => {
                        if (!isDown) return;
                        e.preventDefault();
                        const x = e.pageX - viewport.offsetLeft;
                        const y = e.pageY - viewport.offsetTop;
                        const walkX = (x - startX) * 1.2;
                        const walkY = (y - startY) * 1.2;
                        viewport.scrollLeft = scrollLeft - walkX;
                        viewport.scrollTop = scrollTop - walkY;
                    });

                    // Initial horizontal center
                    setTimeout(() => {
                        viewport.scrollLeft = (viewport.scrollWidth - viewport.clientWidth) / 2;
                    }, 100);
                }

                window.addEventListener('resize', () => {
                    if (activeCard && popup && popup.classList.contains('is-visible')) {
                        updatePopupPosition(activeCard);
                    }
                });
            });
        </script>
    @endpush
@endsection
