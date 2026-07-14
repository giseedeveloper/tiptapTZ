<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Services\TableOccupancyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TableOccupancyController extends Controller
{
    public function __construct(private TableOccupancyService $occupancy)
    {
    }

    public function index(): View
    {
        $restaurantId = (int) Auth::user()->restaurant_id;
        $snapshot = $this->occupancy->snapshot($restaurantId);

        return view('manager.tables.occupancy', [
            'snapshot' => $snapshot,
            'restaurantName' => Auth::user()->restaurant?->name ?? 'Restaurant',
        ]);
    }

    public function feed(): JsonResponse
    {
        $restaurantId = (int) Auth::user()->restaurant_id;

        return response()->json([
            'success' => true,
            'data' => $this->occupancy->snapshot($restaurantId),
        ]);
    }
}
