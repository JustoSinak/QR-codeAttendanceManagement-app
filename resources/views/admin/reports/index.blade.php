@extends('layouts.admin')

@section('title', 'Reports & Analytics')
@section('page-title', 'Reports & Analytics Dashboard')

@push('styles')
<style>
    .report-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 2rem;
        margin-bottom: 3rem;
    }

    .report-card {
        background: white;
        padding: 2rem;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        text-align: center;
        transition: all 0.3s ease;
        border-left: 5px solid transparent;
    }

    .report-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    }

    .report-card.daily {
        border-left-color: #28a745;
    }

    .report-card.weekly {
        border-left-color: #007bff;
    }

    .report-card.monthly {
        border-left-color: #ffc107;
    }

    .report-card.overtime {
        border-left-color: #dc3545;
    }

    .report-card.analytics {
        border-left-color: #6f42c1;
    }

    .report-card.audit {
        border-left-color: #17a2b8;
    }

    .report-icon {
        font-size: 3rem;
        margin-bottom: 1rem;
    }

    .report-card.daily .report-icon { color: #28a745; }
    .report-card.weekly .report-icon { color: #007bff; }
    .report-card.monthly .report-icon { color: #ffc107; }
    .report-card.overtime .report-icon { color: #dc3545; }
    .report-card.analytics .report-icon { color: #6f42c1; }
    .report-card.audit .report-icon { color: #17a2b8; }

    .report-title {
        font-size: 1.5rem;
        font-weight: 600;
        margin-bottom: 1rem;
        color: #333;
    }

    .report-description {
        color: #666;
        margin-bottom: 2rem;
        line-height: 1.6;
    }

    .report-actions {
        display: flex;
        gap: 1rem;
        justify-content: center;
        flex-wrap: wrap;
    }

    .btn-report {
        padding: 0.75rem 1.5rem;
        border: none;
        border-radius: 8px;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .btn-view {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .btn-view:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        color: white;
    }

    .btn-export {
        background: #f8f9fa;
        color: #333;
        border: 2px solid #dee2e6;
    }

    .btn-export:hover {
        background: #e9ecef;
        border-color: #adb5bd;
        color: #333;
    }

    .quick-stats {
        background: white;
        padding: 2rem;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        margin-bottom: 3rem;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 2rem;
        margin-top: 2rem;
    }

    .stat-item {
        text-align: center;
        padding: 1.5rem;
        background: #f8f9fa;
        border-radius: 10px;
    }

    .stat-number {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .stat-label {
        color: #666;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .filters-section {
        background: white;
        padding: 2rem;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        margin-bottom: 3rem;
    }

    .filter-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        align-items: end;
    }

    .form-group {
        margin-bottom: 1rem;
    }

    .form-label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
        color: #333;
    }

    .form-control {
        width: 100%;
        padding: 0.75rem;
        border: 2px solid #e1e5e9;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
</style>
@endpush

@section('content')
<div class="quick-stats">
    <h4 class="mb-3">
        <i class="fas fa-chart-line me-2"></i>
        Quick Statistics
    </h4>
    <div class="stats-grid">
        <div class="stat-item">
            <div class="stat-number text-success">{{ \App\Models\Attendance::whereDate('date', today())->count() }}</div>
            <div class="stat-label">Today's Attendance</div>
        </div>
        <div class="stat-item">
            <div class="stat-number text-primary">{{ \App\Models\Employee::where('employee_status', 'active')->count() }}</div>
            <div class="stat-label">Active Employees</div>
        </div>
        <div class="stat-item">
            <div class="stat-number text-warning">{{ \App\Models\Attendance::whereMonth('date', now()->month)->sum('overtime_hours') }}</div>
            <div class="stat-label">Monthly Overtime (hrs)</div>
        </div>
        <div class="stat-item">
            <div class="stat-number text-info">{{ round(\App\Models\Attendance::whereMonth('date', now()->month)->where('time_in', '<=', '09:00:00')->count() / max(1, \App\Models\Attendance::whereMonth('date', now()->month)->count()) * 100, 1) }}%</div>
            <div class="stat-label">Punctuality Rate</div>
        </div>
    </div>
</div>

<div class="filters-section">
    <h4 class="mb-3">
        <i class="fas fa-filter me-2"></i>
        Report Filters
    </h4>
    <form id="reportFilters">
        <div class="filter-row">
            <div class="form-group">
                <label for="start_date" class="form-label">Start Date</label>
                <input type="date" id="start_date" name="start_date" class="form-control" value="{{ now()->startOfMonth()->format('Y-m-d') }}">
            </div>
            <div class="form-group">
                <label for="end_date" class="form-label">End Date</label>
                <input type="date" id="end_date" name="end_date" class="form-control" value="{{ now()->format('Y-m-d') }}">
            </div>
            <div class="form-group">
                <label for="department" class="form-label">Department</label>
                <select id="department" name="department" class="form-control">
                    <option value="">All Departments</option>
                    @foreach(\App\Models\Employee::distinct()->pluck('department') as $dept)
                        <option value="{{ $dept }}">{{ $dept }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <button type="button" class="btn btn-primary" onclick="applyFilters()">
                    <i class="fas fa-search me-2"></i>
                    Apply Filters
                </button>
            </div>
        </div>
    </form>
</div>

<div class="report-grid">
    <div class="report-card daily">
        <div class="report-icon">
            <i class="fas fa-calendar-day"></i>
        </div>
        <div class="report-title">Daily Reports</div>
        <div class="report-description">
            View detailed daily attendance records, including check-in/out times, late arrivals, and overtime hours.
        </div>
        <div class="report-actions">
            <a href="{{ route('admin.attendance.daily') }}" class="btn-report btn-view">
                <i class="fas fa-eye me-2"></i>
                View Report
            </a>
            <button class="btn-report btn-export" onclick="exportReport('daily', 'pdf')">
                <i class="fas fa-download me-2"></i>
                Export PDF
            </button>
        </div>
    </div>

    <div class="report-card weekly">
        <div class="report-icon">
            <i class="fas fa-calendar-week"></i>
        </div>
        <div class="report-title">Weekly Reports</div>
        <div class="report-description">
            Analyze weekly attendance patterns, employee performance, and identify trends across the week.
        </div>
        <div class="report-actions">
            <a href="#" class="btn-report btn-view" onclick="viewWeeklyReport()">
                <i class="fas fa-eye me-2"></i>
                View Report
            </a>
            <button class="btn-report btn-export" onclick="exportReport('weekly', 'excel')">
                <i class="fas fa-download me-2"></i>
                Export Excel
            </button>
        </div>
    </div>

    <div class="report-card monthly">
        <div class="report-icon">
            <i class="fas fa-calendar-alt"></i>
        </div>
        <div class="report-title">Monthly Reports</div>
        <div class="report-description">
            Comprehensive monthly analysis including attendance rates, punctuality metrics, and payroll data.
        </div>
        <div class="report-actions">
            <a href="#" class="btn-report btn-view" onclick="viewMonthlyReport()">
                <i class="fas fa-eye me-2"></i>
                View Report
            </a>
            <button class="btn-report btn-export" onclick="exportReport('monthly', 'pdf')">
                <i class="fas fa-download me-2"></i>
                Export PDF
            </button>
        </div>
    </div>

    <div class="report-card overtime">
        <div class="report-icon">
            <i class="fas fa-clock"></i>
        </div>
        <div class="report-title">Overtime Reports</div>
        <div class="report-description">
            Track overtime hours, calculate overtime costs, and identify employees with excessive overtime.
        </div>
        <div class="report-actions">
            <a href="#" class="btn-report btn-view" onclick="viewOvertimeReport()">
                <i class="fas fa-eye me-2"></i>
                View Report
            </a>
            <button class="btn-report btn-export" onclick="exportReport('overtime', 'csv')">
                <i class="fas fa-download me-2"></i>
                Export CSV
            </button>
        </div>
    </div>

    <div class="report-card analytics">
        <div class="report-icon">
            <i class="fas fa-chart-bar"></i>
        </div>
        <div class="report-title">Performance Analytics</div>
        <div class="report-description">
            Advanced analytics with trends, patterns, department comparisons, and predictive insights.
        </div>
        <div class="report-actions">
            <a href="#" class="btn-report btn-view" onclick="viewAnalytics()">
                <i class="fas fa-eye me-2"></i>
                View Analytics
            </a>
            <button class="btn-report btn-export" onclick="exportReport('analytics', 'pdf')">
                <i class="fas fa-download me-2"></i>
                Export PDF
            </button>
        </div>
    </div>

    <div class="report-card audit">
        <div class="report-icon">
            <i class="fas fa-shield-alt"></i>
        </div>
        <div class="report-title">Audit Logs</div>
        <div class="report-description">
            Security and compliance reports showing all system activities, user actions, and data changes.
        </div>
        <div class="report-actions">
            <a href="#" class="btn-report btn-view" onclick="viewAuditLogs()">
                <i class="fas fa-eye me-2"></i>
                View Logs
            </a>
            <button class="btn-report btn-export" onclick="exportReport('audit', 'csv')">
                <i class="fas fa-download me-2"></i>
                Export CSV
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function applyFilters() {
    const startDate = $('#start_date').val();
    const endDate = $('#end_date').val();
    const department = $('#department').val();
    
    // Store filters in session storage for use across reports
    sessionStorage.setItem('reportFilters', JSON.stringify({
        start_date: startDate,
        end_date: endDate,
        department: department
    }));
    
    // Show success message
    alert('Filters applied successfully! They will be used in all report views.');
}

function viewWeeklyReport() {
    const filters = getStoredFilters();
    window.location.href = `/admin/reports/weekly?${new URLSearchParams(filters).toString()}`;
}

function viewMonthlyReport() {
    const filters = getStoredFilters();
    window.location.href = `/admin/reports/monthly?${new URLSearchParams(filters).toString()}`;
}

function viewOvertimeReport() {
    const filters = getStoredFilters();
    window.location.href = `/admin/reports/overtime?${new URLSearchParams(filters).toString()}`;
}

function viewAnalytics() {
    const filters = getStoredFilters();
    window.location.href = `/admin/reports/analytics?${new URLSearchParams(filters).toString()}`;
}

function viewAuditLogs() {
    const filters = getStoredFilters();
    window.location.href = `/admin/reports/audit?${new URLSearchParams(filters).toString()}`;
}

function exportReport(type, format) {
    const filters = getStoredFilters();
    
    // Show loading state
    const button = event.target.closest('button');
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Exporting...';
    button.disabled = true;
    
    $.ajax({
        url: '/admin/reports/export',
        method: 'GET',
        data: {
            type: type,
            format: format,
            ...filters
        },
        success: function(response) {
            if (response.success) {
                alert('Report export initiated. Download will start shortly.');
            } else {
                alert('Export failed: ' + response.message);
            }
        },
        error: function() {
            alert('Export failed. Please try again.');
        },
        complete: function() {
            // Restore button
            button.innerHTML = originalText;
            button.disabled = false;
        }
    });
}

function getStoredFilters() {
    const stored = sessionStorage.getItem('reportFilters');
    return stored ? JSON.parse(stored) : {
        start_date: $('#start_date').val(),
        end_date: $('#end_date').val(),
        department: $('#department').val()
    };
}

// Load stored filters on page load
$(document).ready(function() {
    const filters = getStoredFilters();
    if (filters.start_date) $('#start_date').val(filters.start_date);
    if (filters.end_date) $('#end_date').val(filters.end_date);
    if (filters.department) $('#department').val(filters.department);
});
</script>
@endpush
