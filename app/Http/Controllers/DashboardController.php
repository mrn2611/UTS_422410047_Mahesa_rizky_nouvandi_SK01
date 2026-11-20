<?php

namespace App\Http\Controllers;

use App\Services\AccessionStatisticsService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private AccessionStatisticsService $stats)
    {
    }

    public function __invoke(): View
    {
        return view('dashboard.index', [
            'summary' => $this->stats->summary(),
            'charts'  => $this->stats->charts(),
            'latest'  => $this->stats->latest(),
        ]);
    }
}
