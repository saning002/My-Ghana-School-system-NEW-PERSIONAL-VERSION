@php
$backgroundUrl = $student->background_photo_url ?? asset('images/default-bg.jpg');
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Change Password — Student Portal</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <style>
    :root {
      --bg: #F5F0E8;
      --surface: #ffffff;
      --surface2: #fffbf0;
      --border: #fef3c7;
      --text: #111827;
      --text-muted: #6b7280;
      --text-sub: #78350f;
      --accent: #fbbf24;
      --accent2: #f59e0b;
      --accent-dark: #D4A017;
      --accent-text: #111827;
    }

    * { margin: 0; padding: 0; box-sizing: border-box; }
    html { scroll-behavior: smooth; }
    body { 
      font-family: "Inter", sans-serif; 
      background: var(--bg); 
      color: var(--text); 
      min-height: 100vh; 
      position: relative; 
    }

    .portal-bg {
      position: fixed; inset: 0; z-index: 0;
      background-image: url('{{ $backgroundUrl }}');
      background-size: cover; 
      background-position: center; 
      background-repeat: no-repeat;
      background-attachment: fixed;
      pointer-events: none;
    }
    .portal-bg-dim {
      position: fixed; inset: 0; z-index: 1;
      background: rgba(245, 240, 232, 0.85);
      backdrop-filter: blur(10px);
      pointer-events: none;
    }

    .container {
      position: relative; z-index: 2;
      max-width: 600px; margin: 0 auto; padding: 20px;
      min-height: 100vh; display: flex; align-items: center; justify-content: center;
    }

    .card {
      background: var(--surface);
      border-radius: 16px;
      padding: 32px;
      box-shadow: 0 10px 40px rgba(0,0,0,0.1);
      border: 1px solid var(--border);
      width: 100%;
    }

    .card-header {
      display: flex; align-items: center; gap: 12px; margin-bottom: 24px;
    }
    .card-header .icon {
      width: 48px; height: 48px; border-radius: 12px;
      background: linear-gradient(135deg, var(--accent), var(--accent2));
      display: flex; align-items: center; justify-content: center;
      color: white; font-size: 20px;
    }
    .card-header h1 {
      font-size: 20px; font-weight: 800; color: var(--text); margin: 0;
    }
    .card-header p {
      font-size: 12px; color: var(--text-muted); margin: 4px 0 0;
    }

    .form-group {
      margin-bottom: 18px;
    }
    .form-group label {
      display: block; font-size: 12px; font-weight: 700; color: var(--text-sub);
      text-transform: uppercase; letter-spacing: 0.6px; margin-bottom: 8px;
    }
    .form-group input {
      width: 100%; padding: 12px 14px; border: 1.5px solid var(--border);
      border-radius: 10px; font-size: 13px; font-family: inherit;
      background: var(--surface2); transition: all 0.2s;
      outline: none;
    }
    .form-group input:focus {
      border-color: var(--accent);
      background: white;
      box-shadow: 0 0 0 3px rgba(251, 191, 36, 0.1);
    }
    .form-group input::placeholder {
      color: #c4a757;
    }

    .error-message {
      background: rgba(239, 68, 68, 0.1);
      border: 1.5px solid rgba(239, 68, 68, 0.3);
      border-radius: 10px;
      padding: 12px 14px;
      font-size: 12px;
      color: #dc2626;
      margin-bottom: 16px;
      display: flex; align-items: center; gap: 8px;
    }
    .error-message i { font-size: 13px; }

    .success-message {
      background: rgba(34, 197, 94, 0.1);
      border: 1.5px solid rgba(34, 197, 94, 0.3);
      border-radius: 10px;
      padding: 12px 14px;
      font-size: 12px;
      color: #059669;
      margin-bottom: 16px;
      display: flex; align-items: center; gap: 8px;
    }
    .success-message i { font-size: 13px; }

    .button-group {
      display: flex; gap: 12px; margin-top: 24px;
    }
    .button {
      flex: 1; padding: 12px 16px; border: none;
      border-radius: 10px; font-weight: 700; font-size: 13px;
      font-family: inherit; cursor: pointer; transition: all 0.2s;
      text-align: center; text-decoration: none; display: flex;
      align-items: center; justify-content: center; gap: 6px;
    }
    .button.primary {
      background: linear-gradient(135deg, var(--accent), var(--accent2));
      color: var(--accent-text);
    }
    .button.primary:hover {
      transform: translateY(-1px);
      box-shadow: 0 6px 20px rgba(251, 191, 36, 0.3);
    }
    .button.secondary {
      background: var(--surface2);
      color: var(--text-muted);
      border: 1.5px solid var(--border);
    }
    .button.secondary:hover {
      background: var(--border);
    }

    .info-box {
      background: rgba(251, 191, 36, 0.1);
      border: 1.5px solid rgba(251, 191, 36, 0.3);
      border-radius: 10px;
      padding: 12px 14px;
      font-size: 12px;
      color: var(--text-sub);
      margin-top: 16px;
      display: flex; align-items: center; gap: 8px;
    }
    .info-box i { font-size: 13px; }

    @media (max-width: 768px) {
      .card { padding: 24px; }
      .button-group { flex-direction: column; }
    }
  </style>
</head>
<body>

<!-- Background -->
<div class="portal-bg"></div>
<div class="portal-bg-dim"></div>

<!-- Content -->
<div class="container">
  <div class="card">
    
    <!-- Header -->
    <div class="card-header">
      <div class="icon">
        <i class="fas fa-lock"></i>
      </div>
      <div>
        <h1>Change Your Password</h1>
        <p>Update your portal login password</p>
      </div>
    </div>

    <!-- Messages -->
    @if($errors->any())
      @foreach($errors->all() as $error)
        <div class="error-message">
          <i class="fas fa-exclamation-circle"></i>
          {{ $error }}
        </div>
      @endforeach
    @endif

    @if(session('success'))
      <div class="success-message">
        <i class="fas fa-check-circle"></i>
        {{ session('success') }}
      </div>
    @endif

    <!-- Form -->
    <form method="POST" action="{{ route('portal.change-password.update') }}">
      @csrf

      <div class="form-group">
        <label for="current_password">Current Password *</label>
        <input type="password" id="current_password" name="current_password" required
               placeholder="Enter your current password"
               value="{{ old('current_password') }}">
      </div>

      <div class="form-group">
        <label for="new_password">New Password *</label>
        <input type="password" id="new_password" name="new_password" required
               placeholder="Minimum 6 characters"
               value="{{ old('new_password') }}">
      </div>

      <div class="form-group">
        <label for="new_password_confirmation">Confirm New Password *</label>
        <input type="password" id="new_password_confirmation" name="new_password_confirmation" required
               placeholder="Re-enter your new password"
               value="{{ old('new_password_confirmation') }}">
      </div>

      <div class="info-box">
        <i class="fas fa-info-circle"></i>
        <span>Password must be at least 6 characters long. Choose a strong, memorable password.</span>
      </div>

      <div class="button-group">
        <button type="submit" class="button primary">
          <i class="fas fa-save"></i>
          Update Password
        </button>
        <a href="{{ route('portal.dashboard') }}" class="button secondary">
          <i class="fas fa-times"></i>
          Cancel
        </a>
      </div>

    </form>

  </div>
</div>

</body>
</html>
