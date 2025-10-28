@extends('layouts.app')

@section('title', 'Analytics Dashboard')

@section('content')
<div class="card shadow-sm p-4">
    <h4>Analytics Overview</h4>
    <p class="text-muted">Track visitor interactions and page views.</p>

    <div class="row text-center mb-4">
        <div class="col-md-4">
            <div class="border rounded p-3">
                <h5>Total Views</h5>
                <h3>1,254</h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="border rounded p-3">
                <h5>Unique Visitors</h5>
                <h3>890</h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="border rounded p-3">
                <h5>Returning Visitors</h5>
                <h3>364</h3>
            </div>
        </div>
    </div>

    <canvas id="viewsChart" height="120"></canvas>
    <div class="mt-4">
        <a href="#" class="btn btn-outline-primary">Export as CSV</a>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('viewsChart');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'],
            datasets: [{
                label: 'Page Views',
                data: [120, 190, 300, 500, 200, 300, 400],
                borderWidth: 2
            }]
        },
    });
</script>
@endpush
@endsection
