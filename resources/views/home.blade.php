@extends('layouts.attendance')

@section('title', 'Employee Attendance System')

@section('navigation')
    <li><a href="{{ route('home') }}" class="active">Home</a></li>
    <li><a href="{{ route('contact') }}">Message</a></li>
    <li><a href="{{ route('employee.info') }}">Emp_Info</a></li>
    <li><a href="{{ route('admin.login') }}">Admin</a></li>
@endsection

@push('styles')
<style>
    .homepage {
        background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), 
                    url('{{ asset('images/group-afro-americans-working-together.jpg') }}');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        min-height: 70vh;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
        color: white;
        border-radius: 15px;
        margin: 2rem 0;
    }

    .iinf p:first-child {
        font-size: 3rem;
        font-weight: 700;
        margin-bottom: 1rem;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
    }

    .iinf p:last-child {
        font-size: 1.5rem;
        font-weight: 400;
        margin-bottom: 2rem;
        text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
    }

    .regis {
        background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
        color: white;
        border: none;
        padding: 15px 40px;
        font-size: 1.2rem;
        font-weight: 600;
        border-radius: 30px;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-block;
    }

    .regis:hover {
        background: linear-gradient(135deg, #45a049 0%, #4CAF50 100%);
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(76, 175, 80, 0.3);
        color: white;
    }

    .address {
        background: white;
        padding: 2rem;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        margin: 2rem 0;
        text-align: center;
    }

    .add-inf p {
        font-size: 1.1rem;
        color: #333;
        margin: 0;
    }

    .add-inf span {
        font-weight: 600;
        color: #667eea;
    }

    .features {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 2rem;
        margin: 3rem 0;
    }

    .feature-card {
        background: white;
        padding: 2rem;
        border-radius: 15px;
        text-align: center;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }

    .feature-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    }

    .feature-card i {
        font-size: 3rem;
        color: #667eea;
        margin-bottom: 1rem;
    }

    .feature-card h3 {
        color: #333;
        margin-bottom: 1rem;
        font-weight: 600;
    }

    .feature-card p {
        color: #666;
        line-height: 1.6;
    }
</style>
@endpush

@section('content')
<section class="homepage">
    <div class="iinf">
        <p>Welcome to our app</p>
        <p>Manage Employees Attendance with QR-code</p>
    </div>
    <a href="{{ route('admin.login') }}" class="regis">Get Started</a>
</section>

<div class="features">
    <div class="feature-card">
        <i class="fas fa-qrcode"></i>
        <h3>QR Code Scanning</h3>
        <p>Quick and easy attendance tracking using QR code technology. Each employee gets a unique QR code for seamless check-in and check-out.</p>
    </div>
    
    <div class="feature-card">
        <i class="fas fa-chart-line"></i>
        <h3>Real-time Reports</h3>
        <p>Generate comprehensive attendance reports with real-time data. Track employee attendance patterns and productivity metrics.</p>
    </div>
    
    <div class="feature-card">
        <i class="fas fa-users"></i>
        <h3>Employee Management</h3>
        <p>Efficiently manage employee information, departments, and attendance records all in one centralized system.</p>
    </div>
</div>

<div class="address">
    <div class="add-inf">
        <p><span>Email:</span> tcheumanisinakjusto@gmail.com</p>
    </div>
</div>
@endsection
