<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    // Dashboard home
    public function index()
    {
        return view('dashboard.index');
    }

    // Page editor
    public function editPage()
    {
        return view('dashboard.edit-page');
    }

    // Analytics
    public function analytics()
    {
        return view('dashboard.analytics');
    }
}
