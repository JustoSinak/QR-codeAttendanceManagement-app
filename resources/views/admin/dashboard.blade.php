@extends('layouts.admin')

@section('title', 'Admin Dashboard')
@section('page-title', 'Dashboard Overview')

@section('content')
<div class="stats-grid">
    <div class="stat-card employees">
        <i class="fas fa-users"></i>
        <h3>{{ $totalEmployees }}</h3>
        <p>Total Employees</p>
    </div>
    
    <div class="stat-card admins">
        <i class="fas fa-user-shield"></i>
        <h3>{{ $totalAdmins }}</h3>
        <p>Total Admins</p>
    </div>
    
    <div class="stat-card attendance">
        <i class="fas fa-calendar-check"></i>
        <h3>{{ $todayAttendance }}</h3>
        <p>Today's Attendance</p>
    </div>
    
    <div class="stat-card present">
        <i class="fas fa-user-check"></i>
        <h3>{{ $checkedInToday }}</h3>
        <p>Currently Present</p>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-clock me-2"></i>
                    Recent Attendance Records
                </h5>
            </div>
            <div class="card-body">
                @if($recentAttendance->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Date</th>
                                    <th>Time In</th>
                                    <th>Time Out</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentAttendance as $attendance)
                                <tr>
                                    <td>
                                        <strong>{{ $attendance->emp_name }}</strong>
                                    </td>
                                    <td>{{ $attendance->date->format('M d, Y') }}</td>
                                    <td>
                                        <span class="badge bg-success">
                                            {{ $attendance->time_in ? \Carbon\Carbon::parse($attendance->time_in)->format('H:i') : '--' }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($attendance->time_out)
                                            <span class="badge bg-danger">
                                                {{ \Carbon\Carbon::parse($attendance->time_out)->format('H:i') }}
                                            </span>
                                        @else
                                            <span class="badge bg-warning">--</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($attendance->status == 0)
                                            <span class="badge bg-success">
                                                <i class="fas fa-sign-in-alt me-1"></i>
                                                Checked In
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">
                                                <i class="fas fa-sign-out-alt me-1"></i>
                                                Checked Out
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No attendance records found</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-line me-2"></i>
                    Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-3">
                    <a href="{{ route('admin.employees.create') }}" class="btn btn-primary">
                        <i class="fas fa-user-plus me-2"></i>
                        Add New Employee
                    </a>
                    
                    <a href="{{ route('admin.attendance.scanner') }}" class="btn btn-success">
                        <i class="fas fa-qrcode me-2"></i>
                        Open QR Scanner
                    </a>
                    
                    <a href="{{ route('admin.attendance.report') }}" class="btn btn-info">
                        <i class="fas fa-file-alt me-2"></i>
                        View Reports
                    </a>
                    
                    <a href="{{ route('admin.attendance.daily') }}" class="btn btn-warning">
                        <i class="fas fa-calendar-day me-2"></i>
                        Today's Report
                    </a>
                </div>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    System Info
                </h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <h4 class="text-primary">{{ date('d') }}</h4>
                        <small class="text-muted">Day</small>
                    </div>
                    <div class="col-6">
                        <h4 class="text-success">{{ date('M') }}</h4>
                        <small class="text-muted">Month</small>
                    </div>
                </div>
                <hr>
                <div class="text-center">
                    <h5 class="text-info">{{ date('Y') }}</h5>
                    <small class="text-muted">Current Year</small>
                </div>
            </div>
        </div>
    </div>
</div>

@if($monthlyStats->count() > 0)
<div class="card mt-4">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-chart-bar me-2"></i>
            Monthly Attendance Overview
        </h5>
    </div>
    <div class="card-body">
        <div class="row">
            @foreach($monthlyStats as $stat)
            <div class="col-md-2 col-sm-4 col-6 mb-3">
                <div class="text-center p-3 bg-light rounded">
                    <h6 class="text-primary">{{ $stat->count }}</h6>
                    <small class="text-muted">{{ \Carbon\Carbon::parse($stat->date)->format('M d') }}</small>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
    // Auto-refresh dashboard every 30 seconds
    setInterval(function() {
        // Only refresh if user is still on the page
        if (document.visibilityState === 'visible') {
            location.reload();
        }
    }, 30000);
    
    // Add some interactive effects
    document.addEventListener('DOMContentLoaded', function() {
        // Animate stat cards on load
        const statCards = document.querySelectorAll('.stat-card');
        statCards.forEach((card, index) => {
            setTimeout(() => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                card.style.transition = 'all 0.5s ease';
                
                setTimeout(() => {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, 100);
            }, index * 100);
        });
    });
</script>
@endpush
