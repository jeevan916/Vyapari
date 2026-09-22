<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Services\LedgerService;

final class DashboardController extends Controller
{
    public function index(): void
    {
        Auth::requireLogin();
        $rows = (new LedgerService())->dashboardTotals();
        $this->view('dashboard/index', ['title' => 'Dashboard', 'rows' => $rows]);
    }
}
