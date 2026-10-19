@extends('owner.layout')

@section('title', 'Seed Test Data — ' . $tenant->name)

@section('content')
<div class="container py-4" style="max-width:900px;">

    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-1">
                <i class="fas fa-seedling me-2 text-success"></i>
                Test Data Seeded
            </h4>
            <p class="text-muted mb-0">School: <strong>{{ $tenant->name }}</strong> ({{ $tenant->slug }})</p>
        </div>
        <a href="{{ route('owner.schools.show', $tenant) }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back to School
        </a>
    </div>

    @if($exitCode === 0)
    <div class="alert alert-success" style="border-radius:12px;">
        <i class="fas fa-check-circle me-2"></i>
        <strong>Success!</strong> Test data has been seeded into {{ $tenant->name }}'s database.
    </div>
    @else
    <div class="alert alert-danger" style="border-radius:12px;">
        <i class="fas fa-exclamation-circle me-2"></i>
        <strong>Error!</strong> Seeder exited with code {{ $exitCode }}. Check the output below.
    </div>
    @endif

    <div class="card border-0 shadow-sm" style="border-radius:12px;">
        <div class="card-header bg-dark text-white py-2">
            <i class="fas fa-terminal me-2"></i> Seeder Output
        </div>
        <div class="card-body p-3" style="background:#1e1e2e;max-height:600px;overflow-y:auto;">
            <pre style="color:#a6e3a1;font-family:'Cascadia Code','Fira Code',monospace;font-size:0.8rem;white-space:pre-wrap;">{{ $output }}</pre>
        </div>
    </div>

    <div class="mt-3">
        <a href="{{ route('owner.schools.show', $tenant) }}" class="btn btn-primary">
            <i class="fas fa-school me-1"></i> View School Details
        </a>
    </div>
</div>
@endsection
