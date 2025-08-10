<?php

namespace App\Http\Controllers;

use App\Models\DailyInputDetail;
use App\Models\DailyInputs;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }
    public function monthlyProductReport(Request $request){
        $query = DailyInputs::with('user')
        ->leftJoinSub(
            DB::table('daily_input_details')
                ->select('daily_input_id', DB::raw('COALESCE(SUM(qty), 0) as total_qty'))
                ->whereNull('deleted_at')
                ->groupBy('daily_input_id'),
            'details_sum',
            'daily_inputs.id',
            'details_sum.daily_input_id'
        )
        ->select('daily_inputs.*', 'details_sum.total_qty');

        $filterMonth = $request->input('filter_month', null);
        $filterYear = $request->input('filter_year', null);
        // Get the current month and year if they are null
        $currentMonth = date('m'); // Current month in two digits
        $currentYear = date('Y');  // Current year in four digits

        // Use current month/year if the respective input is null
        $filterMonth = $filterMonth ?? $currentMonth;
        $filterYear = $filterYear ?? $currentYear;
        if ($filterMonth !== null) {
            $monthYear = \DateTime::createFromFormat('Y-m', $filterYear.'-'.$filterMonth);
            if ($monthYear) {
                $month = $monthYear->format('m');
                $year = $monthYear->format('Y');

                $query->whereMonth('date', $filterMonth)
                    ->whereYear('date', $filterYear);
            } else {
                $query->whereRaw('1 = 0');
            }
        } else {
            $query->whereRaw('1 = 0');
        }

        $dailyInputIds = $query->pluck('id')->toArray();
        $report_by_times = $query->get();
        $details = DailyInputDetail::whereIn('daily_input_id', $dailyInputIds)
        ->whereNull('deleted_at')
        ->selectRaw('product_id, MAX(pack) as pack, SUM(qty) as qty')
        ->groupBy('product_id')
        ->with('product') // if you have a product() relationship in the model
        ->get();
        return view('reports.monthly-product-report', get_defined_vars());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
