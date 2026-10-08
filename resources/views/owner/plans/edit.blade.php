@extends('owner.layout')
@section('title','Edit Plan')
@section('page-title','Edit Plan: ' . $plan->name)
@section('topbar-actions')
    <a href="{{ route('owner.plans.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Back</a>
@endsection
@section('content')
<form method="POST" action="{{ route('owner.plans.update', $plan) }}">
    @csrf @method('PUT')
    @include('owner.plans._form')
    <div class="mt-4 d-flex gap-2">
        <button type="submit" class="btn btn-owner-primary"><i class="fas fa-save me-1"></i> Save Changes</button>
        <a href="{{ route('owner.plans.index') }}" class="btn btn-outline-secondary">Cancel</a>
    </div>
</form>
@endsection
