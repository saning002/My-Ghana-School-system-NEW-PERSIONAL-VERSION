@php
    $tenant = $currentTenant ?? null;
@endphp

@if($tenant && $tenant->isOnTrial())
<div style="
    background: linear-gradient(90deg, #f59e0b, #d97706);
    color: #fff;
    padding: 0.6rem 1.5rem;
    text-align: center;
    font-size: 0.875rem;
    font-weight: 500;
    position: sticky;
    top: 0;
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    <span>
        <strong>Trial Period:</strong>
        @if($tenant->trialDaysRemaining() > 0)
            {{ $tenant->trialDaysRemaining() }} day{{ $tenant->trialDaysRemaining() === 1 ? '' : 's' }} remaining.
        @else
            Your trial expires today.
        @endif
        Contact your administrator to upgrade.
    </span>
</div>
@endif
