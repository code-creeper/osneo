<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;

class VehicleController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Vehicle::class, 'vehicle');

        $this->middleware('log.activity')->only(['show']);
    }

    public function show(Vehicle $vehicle)
    {
        $vehicle->load([
            'maintenanceHistories' => fn($query) => $query->relevant(),
            'damages' => fn($query) => $query->relevant(),
            'driverHistories'
        ]);

        $data = array();
        $data['vehicle'] = $vehicle;
        $data['condition'] = $vehicle->condition;
        return view('vehicle.show', $data);
    }
}
