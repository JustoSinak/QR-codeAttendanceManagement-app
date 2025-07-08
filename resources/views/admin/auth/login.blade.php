@extends('layouts.attendance')

@section('title', 'Admin Login')

@section('navigation')
    <li><a href="{{ route('home') }}">Home</a></li>
    <li><a href="{{ route('contact') }}">Message</a></li>
    <li><a href="{{ route('employee.info') }}">Emp_Info</a></li>
    <li><a href="{{ route('admin.login') }}" class="active">Admin</a></li>
@endsection

@push('styles')
<style>
    .login-container {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 70vh;
        padding: 2rem 0;
    }

    .login-card {
        background: white;
        padding: 3rem;
        border-radius: 20px;
        box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        width: 100%;
        max-width: 450px;
        position: relative;
        overflow: hidden;
    }

    .login-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 5px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .login-header {
        text-align: center;
        margin-bottom: 2rem;
    }

    .login-header h2 {
        color: #333;
        font-size: 2rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .login-header p {
        color: #666;
        font-size: 1rem;
    }

    .form-group {
        margin-bottom: 1.5rem;
        position: relative;
    }

    .form-control {
        width: 100%;
        padding: 1rem 1rem 1rem 3rem;
        border: 2px solid #e1e5e9;
        border-radius: 10px;
        font-size: 1rem;
        transition: all 0.3s ease;
        background: #f8f9fa;
    }

    .form-control:focus {
        outline: none;
        border-color: #667eea;
        background: white;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .form-icon {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #667eea;
        font-size: 1.1rem;
    }

    .login-btn {
        width: 100%;
        padding: 1rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 10px;
        font-size: 1.1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-top: 1rem;
    }

    .login-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
    }

    .login-btn:active {
        transform: translateY(0);
    }

    .back-link {
        text-align: center;
        margin-top: 2rem;
    }

    .back-link a {
        color: #667eea;
        text-decoration: none;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .back-link a:hover {
        color: #764ba2;
        text-decoration: underline;
    }

    .error-message {
        color: #dc3545;
        font-size: 0.875rem;
        margin-top: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
</style>
@endpush

@section('content')
<div class="login-container">
    <div class="login-card">
        <div class="login-header">
            <h2>Admin Login</h2>
            <p>Access the attendance management system</p>
        </div>

        <form method="POST" action="{{ route('admin.login') }}">
            @csrf
            
            <div class="form-group">
                <i class="fas fa-user form-icon"></i>
                <input 
                    type="text" 
                    name="admin_id" 
                    class="form-control @error('admin_id') is-invalid @enderror" 
                    placeholder="Admin ID (e.g., 2024JUS371)"
                    value="{{ old('admin_id') }}"
                    required
                    autofocus
                >
                @error('admin_id')
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i>
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="form-group">
                <i class="fas fa-lock form-icon"></i>
                <input 
                    type="password" 
                    name="password" 
                    class="form-control @error('password') is-invalid @enderror" 
                    placeholder="Password"
                    required
                >
                @error('password')
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i>
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <button type="submit" class="login-btn">
                <i class="fas fa-sign-in-alt me-2"></i>
                Login to Dashboard
            </button>
        </form>

        <div class="back-link">
            <a href="{{ route('home') }}">
                <i class="fas fa-arrow-left me-1"></i>
                Back to Home
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Add some interactive effects
    document.addEventListener('DOMContentLoaded', function() {
        const inputs = document.querySelectorAll('.form-control');
        
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('focused');
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.classList.remove('focused');
            });
        });
    });
</script>
@endpush
