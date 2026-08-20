<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\BuildsOrgDashboard;
use Illuminate\View\View;

class HrController extends Controller
{
    use BuildsOrgDashboard;

    /**
     * The HR dashboard — same organisation overview as the admin dashboard.
     */
    public function index(): View
    {
        return view('hr.index', ['dash' => $this->orgDashboard()]);
    }
}
