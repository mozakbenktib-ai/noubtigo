<?php

namespace App\Modules\Reports\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Display a summary report.
     */
    public function summary()
    {
        return response()->json([
            'status' => 'success',
            'data' => [
                'total_revenue' => 12450.00,
                'total_appointments' => 342,
                'avg_wait_time' => 15, // minutes
            ]
        ]);
    }
}
