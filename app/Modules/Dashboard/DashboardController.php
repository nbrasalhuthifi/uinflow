<?php
declare(strict_types=1);

final class DashboardController extends Controller
{
    public static function index(): void
    {
        require_auth();
        $repository = new DashboardRepository();

        (new self())->view('dashboard/index', [
            'stats' => $repository->stats(),
            'timeline' => $repository->timeline(),
        ]);
    }
}
