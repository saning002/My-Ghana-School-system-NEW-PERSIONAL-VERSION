extends('layouts.app')
@section('title', 'Subscription')

@section('content')
<div class="container py-5" style="max-width:500px;">
    <div class="card border-0 shadow-sm text-center" style="border-radius:16px;">
        <div class="card-body p-5">
            <div style="font-size:3rem;color:#94a3b8;margin-bottom:1rem;">
                <i class="fas fa-school"></i>
            </div>
            <h4 class="fw-bold">Subscription Not Available</h4>
            <p class="text-muted" style="font-size:0.9rem;">
                This school is not connected to the platform billing system yet.
                Please contact the platform owner to set up your subscription.
            </p>
        </div>
    </div>
</div>
@endsection
