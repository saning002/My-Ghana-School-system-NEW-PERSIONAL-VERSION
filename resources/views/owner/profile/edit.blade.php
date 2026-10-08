@extends('owner.layout')
@section('title','My Profile')
@section('page-title','My Profile')

@section('content')
<div class="row g-4">
    <div class="col-lg-6">
        <div class="owner-card">
            <div class="card-header">Profile Information</div>
            <div class="card-body">
                <form method="POST" action="{{ route('owner.profile.update') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Name</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $owner->name) }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Email</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $owner->email) }}" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <hr class="my-3">
                    <p class="fw-semibold mb-3" style="color:#1e293b;">Change Password</p>
                    <div class="mb-3">
                        <label class="form-label">Current Password</label>
                        <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" autocomplete="current-password">
                        @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" autocomplete="new-password">
                        <div class="form-text">Min 8 characters, must include letters and numbers.</div>
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" name="password_confirmation" class="form-control" autocomplete="new-password">
                    </div>

                    <button type="submit" class="btn btn-owner-primary"><i class="fas fa-save me-1"></i> Save Changes</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="owner-card mb-4">
            <div class="card-header">Account Info</div>
            <div class="card-body">
                @foreach([
                    'Last Login'    => $owner->last_login_at?->format('d M Y H:i') ?? 'Never',
                    'Last Login IP' => $owner->last_login_ip ?? '—',
                    'Member Since'  => $owner->created_at->format('d M Y'),
                    '2FA Status'    => $owner->two_factor_enabled ? '✅ Enabled' : '❌ Disabled',
                ] as $label => $value)
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span style="color:#64748b;font-size:0.875rem;">{{ $label }}</span>
                    <strong style="font-size:0.875rem;">{{ $value }}</strong>
                </div>
                @endforeach
            </div>
        </div>

        {{-- 2FA Section --}}
        <div class="owner-card">
            <div class="card-header d-flex align-items-center gap-2">
                <i class="fas fa-shield-alt text-warning"></i> Two-Factor Authentication
            </div>
            <div class="card-body">
                @if($owner->two_factor_enabled)
                    <div class="alert alert-success" style="border-radius:8px;font-size:0.875rem;">
                        <i class="fas fa-check-circle me-1"></i> 2FA is currently <strong>enabled</strong>.
                    </div>
                    <form method="POST" action="{{ route('owner.profile.update') }}">
                        @csrf
                        <input type="hidden" name="name" value="{{ $owner->name }}">
                        <input type="hidden" name="email" value="{{ $owner->email }}">
                        <input type="hidden" name="disable_2fa" value="1">
                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Disable 2FA? This reduces your account security.')">
                            Disable 2FA
                        </button>
                    </form>
                @else
                    <p style="font-size:0.875rem;color:#64748b;">Add an extra layer of security to your owner account using a TOTP authenticator app (Google Authenticator, Authy).</p>
                    <a href="#" class="btn btn-sm btn-owner-primary" onclick="alert('2FA setup coming soon — install pragmarx/google2fa-laravel to enable.')">
                        <i class="fas fa-shield-alt me-1"></i> Enable 2FA
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
