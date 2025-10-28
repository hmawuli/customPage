<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;

class ClientController extends Controller
{
    // GET /api/clients
    public function index()
    {
        $clients = User::where('role', 'client')->withCount('pages')->get();

        return response()->json([
            'success' => true,
            'message' => 'Clients retrieved successfully',
            'data' => $clients
        ], 200);
    }

    // GET /api/clients/{id}/statistics
    public function statistics($id)
    {
        $client = User::where('role', 'client')->with(['pages'])->find($id);

        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Client not found'
            ], 404);
        }

        $totalPages = $client->pages()->count();
        $totalPublished = $client->pages()->where('status', 'published')->count();
        $totalViews = $client->pages()->withCount('views')->get()->sum('views_count');

        return response()->json([
            'success' => true,
            'data' => [
                'client' => $client->name,
                'total_pages' => $totalPages,
                'total_published' => $totalPublished,
                'total_views' => $totalViews,
            ]
        ], 200);
    }
}
