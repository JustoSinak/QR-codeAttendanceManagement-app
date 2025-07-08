@extends('layouts.attendance')

@section('title', 'Employee Information')

@section('navigation')
    <li><a href="{{ route('home') }}">Home</a></li>
    <li><a href="{{ route('contact') }}">Message</a></li>
    <li><a href="{{ route('employee.info') }}" class="active">Emp_Info</a></li>
    <li><a href="{{ route('admin.login') }}">Admin</a></li>
@endsection

@push('styles')
<style>
    .employee-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 2rem 0;
    }

    .info-header {
        text-align: center;
        margin-bottom: 3rem;
        background: white;
        padding: 3rem;
        border-radius: 20px;
        box-shadow: 0 15px 35px rgba(0,0,0,0.1);
    }

    .info-header h2 {
        color: #333;
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 1rem;
    }

    .info-header p {
        color: #666;
        font-size: 1.2rem;
        line-height: 1.6;
    }

    .employees-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 2rem;
        margin-bottom: 3rem;
    }

    .employee-card {
        background: white;
        padding: 2rem;
        border-radius: 20px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        text-align: center;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .employee-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .employee-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.15);
    }

    .employee-avatar {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
        font-size: 2.5rem;
        color: white;
        font-weight: 600;
    }

    .employee-name {
        font-size: 1.5rem;
        font-weight: 700;
        color: #333;
        margin-bottom: 0.5rem;
    }

    .employee-department {
        color: #667eea;
        font-weight: 600;
        font-size: 1.1rem;
        margin-bottom: 1rem;
    }

    .employee-details {
        background: #f8f9fa;
        padding: 1.5rem;
        border-radius: 15px;
        margin: 1.5rem 0;
    }

    .detail-item {
        display: flex;
        align-items: center;
        margin-bottom: 1rem;
        font-size: 0.95rem;
    }

    .detail-item:last-child {
        margin-bottom: 0;
    }

    .detail-item i {
        width: 20px;
        color: #667eea;
        margin-right: 0.75rem;
    }

    .detail-item span {
        color: #666;
    }

    .stats-section {
        background: white;
        padding: 2rem;
        border-radius: 20px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        text-align: center;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 2rem;
        margin-top: 2rem;
    }

    .stat-item {
        padding: 1.5rem;
        background: #f8f9fa;
        border-radius: 15px;
        transition: all 0.3s ease;
    }

    .stat-item:hover {
        background: #667eea;
        color: white;
        transform: translateY(-5px);
    }

    .stat-number {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .stat-label {
        font-size: 1rem;
        opacity: 0.8;
    }

    .no-employees {
        text-align: center;
        padding: 4rem 2rem;
        background: white;
        border-radius: 20px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }

    .no-employees i {
        font-size: 4rem;
        color: #dee2e6;
        margin-bottom: 2rem;
    }

    .no-employees h3 {
        color: #666;
        margin-bottom: 1rem;
    }

    .no-employees p {
        color: #999;
        font-size: 1.1rem;
    }

    .admin-link {
        display: inline-block;
        margin-top: 2rem;
        padding: 1rem 2rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        text-decoration: none;
        border-radius: 25px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .admin-link:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        color: white;
    }
</style>
@endpush

@section('content')
<div class="employee-container">
    <div class="info-header">
        <h2>
            <i class="fas fa-users me-3"></i>
            Employee Directory
        </h2>
        <p>Meet our dedicated team members and their information</p>
    </div>

    <!-- Sample employees (in a real app, this would come from the database) -->
    <div class="employees-grid">
        <div class="employee-card">
            <div class="employee-avatar">
                KA
            </div>
            <div class="employee-name">KOUAYE ALPHONSE</div>
            <div class="employee-department">Development</div>
            <div class="employee-details">
                <div class="detail-item">
                    <i class="fas fa-id-badge"></i>
                    <span>ID: 2024DEV244K</span>
                </div>
                <div class="detail-item">
                    <i class="fas fa-envelope"></i>
                    <span>kouaye5376@gmail.com</span>
                </div>
                <div class="detail-item">
                    <i class="fas fa-phone"></i>
                    <span>690312654</span>
                </div>
                <div class="detail-item">
                    <i class="fas fa-venus-mars"></i>
                    <span>Male</span>
                </div>
            </div>
        </div>

        <div class="employee-card">
            <div class="employee-avatar">
                SJ
            </div>
            <div class="employee-name">Sinak Justo</div>
            <div class="employee-department">Development</div>
            <div class="employee-details">
                <div class="detail-item">
                    <i class="fas fa-id-badge"></i>
                    <span>ID: 2024DEV500S</span>
                </div>
                <div class="detail-item">
                    <i class="fas fa-envelope"></i>
                    <span>sinakjusto@gmail.com</span>
                </div>
                <div class="detail-item">
                    <i class="fas fa-phone"></i>
                    <span>45633212</span>
                </div>
                <div class="detail-item">
                    <i class="fas fa-venus-mars"></i>
                    <span>Male</span>
                </div>
            </div>
        </div>

        <div class="employee-card">
            <div class="employee-avatar">
                TS
            </div>
            <div class="employee-name">TCHEUMANI SINAK</div>
            <div class="employee-department">Web Designer</div>
            <div class="employee-details">
                <div class="detail-item">
                    <i class="fas fa-id-badge"></i>
                    <span>ID: 2024WEB176T</span>
                </div>
                <div class="detail-item">
                    <i class="fas fa-envelope"></i>
                    <span>tcheumanisinakjusto@gmail.com</span>
                </div>
                <div class="detail-item">
                    <i class="fas fa-phone"></i>
                    <span>680312765</span>
                </div>
                <div class="detail-item">
                    <i class="fas fa-venus-mars"></i>
                    <span>Male</span>
                </div>
            </div>
        </div>
    </div>

    <div class="stats-section">
        <h3>
            <i class="fas fa-chart-bar me-2"></i>
            Company Statistics
        </h3>
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-number">3</div>
                <div class="stat-label">Total Employees</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">2</div>
                <div class="stat-label">Departments</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">{{ date('Y') }}</div>
                <div class="stat-label">Established</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">100%</div>
                <div class="stat-label">QR Enabled</div>
            </div>
        </div>
        
        <a href="{{ route('admin.login') }}" class="admin-link">
            <i class="fas fa-cog me-2"></i>
            Access Admin Panel
        </a>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Add some interactive effects
    $('.employee-card').each(function(index) {
        $(this).css({
            'opacity': '0',
            'transform': 'translateY(30px)'
        });
        
        setTimeout(() => {
            $(this).css({
                'transition': 'all 0.6s ease',
                'opacity': '1',
                'transform': 'translateY(0)'
            });
        }, index * 200);
    });
    
    // Animate stats on scroll
    const statsSection = $('.stats-section');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                $('.stat-number').each(function() {
                    const $this = $(this);
                    const target = parseInt($this.text().replace('%', ''));
                    let current = 0;
                    const increment = target / 50;
                    
                    const timer = setInterval(() => {
                        current += increment;
                        if (current >= target) {
                            current = target;
                            clearInterval(timer);
                        }
                        $this.text(Math.floor(current) + ($this.text().includes('%') ? '%' : ''));
                    }, 30);
                });
                observer.unobserve(entry.target);
            }
        });
    });
    
    if (statsSection.length) {
        observer.observe(statsSection[0]);
    }
});
</script>
@endpush
@endsection
