@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm p-4">
            <h4 class="text-center mb-4">Client Login</h4>
            <form>
                <div class="mb-3">
                    <label>Email</label>
                    <input type="email" class="form-control" placeholder="Enter email">
                </div>
                <div class="mb-3">
                    <label>Password</label>
                    <input type="password" class="form-control" placeholder="Enter password">
                </div>
                <button type="submit" class="btn btn-primary w-100">Login</button>
                <p class="text-center mt-3">
                    Don’t have an account? <a href="/register">Register</a>
                </p>
            </form>
        </div>
    </div>
</div>
@endsection
