<?php

namespace App\Http\Controllers;

use App\Dashboard\DashboardData;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Show the dashboard for the authenticated user's role.
     */
    public function __invoke(Request $request, DashboardData $dashboard): View
    {
        return view('dashboard', [
            'data' => $dashboard->for($request->user()),
        ]);
    }
}
