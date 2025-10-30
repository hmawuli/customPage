@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="card shadow-sm p-4">
    <h3 class="mb-3">Welcome, {{ Auth::user()->name ?? 'Client' }}</h3>
    <p>Here you can manage your web pages and view analytics.</p>

    <div class="row">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <h5>Page Customization</h5>
                    <p>Edit text, images, and color themes for your website.</p>
                    <a href="{{ url('/dashboard/edit-page') }}" class="btn btn-primary">Go to Page Editor</a>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <h5>View Analytics</h5>
                    <p>Check visitor statistics and engagement on your website.</p>
                    <a href="{{ url('/dashboard/analytics') }}" class="btn btn-success">View Analytics</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
