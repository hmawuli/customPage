@extends('layouts.app')

@section('title', 'Register')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm p-4">
            <h4 class="text-center mb-4">Client Registration</h4>
            <form>
                <div class="mb-3">
                    <label>Full Name</label>
                    <input type="text" class="form-control" placeholder="Enter full name">
                </div>
                <div class="mb-3">
                    <label>Email</label>
                    <input type="email" class="form-control" placeholder="Enter email">
                </div>
                <div class="mb-3">
                    <label>Password</label>
                    <input type="password" class="form-control" placeholder="Enter password">
                </div>
                <div class="mb-3">
                    <label>Confirm Password</label>
                    <input type="password" class="form-control" placeholder="Confirm password">
                </div>
                <button type="submit" class="btn btn-success w-100">Register</button>
                <p class="text-center mt-3">
                    Already have an account? <a href="/login">Login</a>
                </p>
            </form>
        </div>
    </div>
</div>
@endsection
