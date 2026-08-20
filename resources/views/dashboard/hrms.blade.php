<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'HRMS') }} — Dashboard</title>

    {{-- Bootstrap 5 + icons via CDN (kept separate from the app's Tailwind build) --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background-color: #f5f6fa; }
        .stat-card { border: none; border-radius: .75rem; transition: transform .15s ease; }
        .stat-card:hover { transform: translateY(-3px); }
        .stat-icon { width: 56px; height: 56px; display: flex; align-items: center; justify-content: center; border-radius: .75rem; font-size: 1.5rem; }
    </style>
</head>
<body>

    {{-- Top navbar --}}
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container-fluid px-4">
            <a class="navbar-brand fw-bold" href="{{ route('hrms.dashboard') }}">
                <i class="bi bi-buildings me-1"></i>{{ config('app.name', 'HRMS') }}
            </a>
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('employees.index') }}" class="text-light text-decoration-none small">
                    <i class="bi bi-people me-1"></i>Employees
                </a>
                <a href="{{ route('dashboard') }}" class="text-light text-decoration-none small">
                    <i class="bi bi-grid me-1"></i>App Dashboard
                </a>
                <span class="text-secondary small">|</span>
                <span class="text-light small">{{ Auth::user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-light">Log out</button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container-fluid px-4 py-4">

        <h4 class="mb-4 fw-semibold text-dark">HRMS Overview</h4>

        {{-- Stat cards --}}
        <div class="row g-4 mb-4">

            {{-- Employees --}}
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card stat-card shadow-sm h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon bg-primary-subtle text-primary me-3"><i class="bi bi-people-fill"></i></div>
                        <div>
                            <div class="text-muted small text-uppercase">Employees</div>
                            <div class="fs-3 fw-bold">{{ $stats['employees'] }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Pending Leaves --}}
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card stat-card shadow-sm h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon bg-warning-subtle text-warning me-3"><i class="bi bi-hourglass-split"></i></div>
                        <div>
                            <div class="text-muted small text-uppercase">Pending Leaves</div>
                            <div class="fs-3 fw-bold">{{ $stats['pendingLeaves'] }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Approved Leaves --}}
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card stat-card shadow-sm h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon bg-success-subtle text-success me-3"><i class="bi bi-check2-circle"></i></div>
                        <div>
                            <div class="text-muted small text-uppercase">Approved Leaves</div>
                            <div class="fs-3 fw-bold">{{ $stats['approvedLeaves'] }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Departments --}}
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card stat-card shadow-sm h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon bg-info-subtle text-info me-3"><i class="bi bi-diagram-3-fill"></i></div>
                        <div>
                            <div class="text-muted small text-uppercase">Departments</div>
                            <div class="fs-3 fw-bold">{{ $stats['departments'] }}</div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="row g-4">

            {{-- Department breakdown --}}
            <div class="col-12 col-lg-5">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white fw-semibold"><i class="bi bi-diagram-3 me-1"></i>Headcount by Department</div>
                    <div class="card-body p-0">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr><th>Department</th><th class="text-end">Employees</th></tr>
                            </thead>
                            <tbody>
                                @forelse ($departmentBreakdown as $row)
                                    <tr>
                                        <td>{{ $row->department }}</td>
                                        <td class="text-end"><span class="badge bg-secondary">{{ $row->total }}</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="2" class="text-center text-muted py-4">No departments yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Recent leaves --}}
            <div class="col-12 col-lg-7">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white fw-semibold"><i class="bi bi-calendar-event me-1"></i>Recent Leave Requests</div>
                    <div class="card-body p-0">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr><th>Employee</th><th>Type</th><th>Dates</th><th>Status</th></tr>
                            </thead>
                            <tbody>
                                @forelse ($recentLeaves as $leave)
                                    <tr>
                                        <td>{{ $leave->employee->user->name ?? '—' }}</td>
                                        <td>{{ $leave->typeLabel() }}</td>
                                        <td class="small text-muted">
                                            {{ $leave->start_date->format('d M') }} – {{ $leave->end_date->format('d M Y') }}
                                        </td>
                                        <td>
                                            @php($map = ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger'])
                                            <span class="badge bg-{{ $map[$leave->status] ?? 'secondary' }}-subtle text-{{ $map[$leave->status] ?? 'secondary' }} text-capitalize">
                                                {{ $leave->status }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted py-4">No leave requests yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
