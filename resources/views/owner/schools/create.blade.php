@extends('owner.layout')
@section('title','Add School')
@section('page-title','Add New School')

@section('topbar-actions')
    <a href="{{ route('owner.schools.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back
    </a>
@endsection

@section('content')
<form method="POST" action="{{ route('owner.schools.store') }}">
    @csrf
    @include('owner.schools._form')
    <div class="mt-4 d-flex gap-2">
        <button type="submit" class="btn btn-owner-primary"><i class="fas fa-save me-1"></i> Create School</button>
        <a href="{{ route('owner.schools.index') }}" class="btn btn-outline-secondary">Cancel</a>
    </div>
</form>
@endsection
