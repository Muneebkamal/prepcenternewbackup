<?php

namespace App\Http\Controllers;

use App\Models\DailyInputDetail;
use App\Models\DailyInputs;
use App\Models\Department;
use App\Models\Products;
use App\Models\SystemOption;
use App\Models\SystemSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session as FacadesSession;
use Illuminate\Validation\Rules\Exists;
use Yajra\DataTables\Facades\DataTables;

class DailyInputController extends Controller
{
    public function updatePRoductId(){
        $dailyInputDetails = DailyInputDetail::where('product_id', null)->withTrashed()->get();
        foreach ($dailyInputDetails as $detail) {
            $product = Products::where('fnsku', $detail->fnsku)->first();
            if ($product) {
                $detail->product_id = $product->id;
                $detail->save();
            }
        }
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $userIds = DB::table('users')->pluck('id');
        $dateRange = $request->input('date_range');
        $dates = explode('_', $dateRange);
         $weekStart = SystemSetting::pluck('week_started_day')->first();
        $weekStart =  $weekStart??6;
        if(count($dates) == 2) {
            $startDate = $dates[0];
            $endDate = $dates[1];
        }
        if(count($dates) == 2) {
            $startDate = $dates[0];
            $endDate = $dates[1];
        }else{
            $today = Carbon::now();
            $startOfWeek = $today->startOfWeek()->addDays($weekStart - 1);
            $endOfWeek = $startOfWeek->copy()->addDays(6);
            
            $startDate = $startOfWeek->format('Y-m-d');
            $endDate = $endOfWeek->format('Y-m-d');
            // $startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
            // $endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
        }
       
        return view('daily_input.index', get_defined_vars());
    }

    public function reportByEmployee(Request $request)
    {
        // Retrieve all employees for the dropdown
        $employees = User::where('status', '0')->get();
        $dateRange = $request->input('date_range');
        $dates = explode('_', $dateRange);
        
        if(count($dates) == 2) {
            $startDate = $dates[0];
            $endDate = $dates[1];
        } else {
            // Default to today's date if date range is null or not properly set
            $startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
            $endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
        }
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
        // Query the user table
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
        $query->whereBetween('date', [$startDate, $endDate]);

        if ($request->has('employee_id') && $request->input('employee_id') !== 'all') {
            $employeeId = $request->input('employee_id');
            $query->where('daily_inputs.employee_id', $employeeId);
        }
        $weekStart = SystemSetting::pluck('week_started_day')->first();
        $weekStart =  $weekStart??6;
        $report_by_employees = $query->orderBy('date', 'DESC')->get();
        return view('report_by_employee.index', get_defined_vars());
    }

    public function dashboard()
    {
        $weekStartDay = SystemSetting::first()->week_started_day ?? 6;

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
           // Get the custom start day from the settings, default to 6 (Saturday) if not set
            $weekStart = SystemSetting::pluck('week_started_day')->first();
            $weekStart = $weekStart ?? 6; // Default to Saturday if not set

            // Get the current day of the week (1 to 7, where 1 is Monday and 7 is Sunday)
            $currentDayOfWeek = Carbon::now()->dayOfWeekIso;

            // Calculate the difference between the current day and the custom start day
            $dayDifference = $weekStart - $currentDayOfWeek;

            // Calculate the start of the week
            $startOfWeek = Carbon::now()->startOfDay()->addDays($dayDifference);
            if ($dayDifference > 0) {
            $startOfWeek->subWeek();
            }

            // Calculate the end of the week
            $endOfWeek = $startOfWeek->copy()->addDays(6)->endOfDay();

            // Format the dates for the frontend
            $startOfWeekFormatted = $startOfWeek->format('Y-m-d');
            $endOfWeekFormatted = $endOfWeek->format('Y-m-d');

            // $currentDayOfWeek = now()->dayOfWeekIso;
            // $startOfWeek = now()->startOfWeek()->subDays(($currentDayOfWeek - $weekStartDay + 7) % 7)->startOfDay();
            // $endOfWeek = $startOfWeek->copy()->addDays(6)->endOfDay();
            $query->whereBetween('daily_inputs.date', [$startOfWeekFormatted, $endOfWeekFormatted]);

            $report_by_times = $query->orderBy('date', 'DESC')->get();
            // dd($report_by_times);
            $report_ids = $report_by_times->pluck('id')->toArray();
            
            $details = DailyInputDetail::whereIn('daily_input_id', $report_ids)->whereNull('deleted_at')->get();
            $details = DailyInputDetail::whereIn('daily_input_id', $report_ids)
            ->whereNull('deleted_at')
            ->selectRaw('product_id, pack, SUM(qty) as qty')
            ->groupBy('product_id', 'pack')
            ->with('product')
            ->get();
            // dd($details);
        return view('dashboard', compact('report_by_times', 'details'));
    }


    public function reportByTime(Request $request)
    {
        // Initialize the query
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
        $dateRange = $request->input('date_range');
        $dates = explode('_', $dateRange);
        
        if(count($dates) == 2) {
            $startDate = $dates[0];
            $endDate = $dates[1];
        } else {
            // Default to today's date if date range is null or not properly set
            $startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
            $endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
        }
        // Execute the query and get results
        $query->whereBetween('date', [$startDate, $endDate]);
        $report_by_times = $query->orderBy('date', 'DESC')->get();
        $weekStart = SystemSetting::pluck('week_started_day')->first();
        $weekStart =  $weekStart??6;
        // Return the view with the data
        return view('report_by_time.index', get_defined_vars());
    }

    public function monthlySummary(Request $request)
    {
        $employees = User::where('status', '0')->get();
        $query = DailyInputs::with('user');

        $filterMonth = $request->input('filter_month', null);
        $filterYear = $request->input('filter_year', null);
        // Get the current month and year if they are null
        $currentMonth = date('m'); // Current month in two digits
        $currentYear = date('Y');  // Current year in four digits

        // Use current month/year if the respective input is null
        $filterMonth = $filterMonth ?? $currentMonth;
        $filterYear = $filterYear ?? $currentYear;
        $employeeId = $request->input('employee_id', 'all');
        if ($filterMonth && $employeeId !== null) {
            $monthYear = \DateTime::createFromFormat('Y-m', $filterYear.'-'.$filterMonth);
            if ($monthYear) {
                $month = $monthYear->format('m');
                $year = $monthYear->format('Y');

                $query->whereMonth('date', $filterMonth)
                    ->whereYear('date', $filterYear);
            } else {
                $query->whereRaw('1 = 0');
            }

            if ($employeeId !== 'all') {
                $query->where('employee_id', $employeeId);
            }
        } else {
            $query->whereRaw('1 = 0');
        }

        $monthly_summary = $query->orderBy('id', 'DESC')->get();
        return view('monthly-summary.index', get_defined_vars());
    }

    public function systemSetting(Request $request)
    {
    $query = Products::query();
    $query->with(['dailyInputDetails' => function($query) {
        $query->select('fnsku')
        ->selectRaw('SUM(qty) as total_qty')
        ->groupBy('fnsku');
    }]);

    // Check if the 'temporary' parameter is set
    if ($request->has('temporary') && $request->temporary == 'on') {
        $query->where('item', 'LIKE', '%Temporary Product Name%'); // Adjust this condition to match your temporary product naming
    }
    $products =$query->get();
    // dd($products);
    // return view('products.index', compact('products'));

    $setting = SystemSetting::first();
    $departments = Department::all();
    $labels = SystemOption::all();
    
    if ($request->isMethod('post')) {
        $request->validate([
            'day' => 'required|integer',
        ]);
        $current_user = User::where('id',auth()->user()->id)->first();
        $current_user->per_page = $request->per_page;
        $current_user->save();

        if ($setting) {
            $setting->week_started_day = $request->day;
            $setting->start_time = $request->start_time;
            $setting->end_time = $request->end_time;
            $setting->save();

            return view('system-setting.index', get_defined_vars())->
            with([
                'success'=> 'Day Updated Successfully',
                'tab_id'=>1
            ]);
        } else {
            $started_day = new SystemSetting;
            $started_day->week_started_day = $request->day;
            $setting->start_time = $request->start_time;
            $setting->end_time = $request->end_time;
            $started_day->save();

            return view('system-setting.index', get_defined_vars())->with([
                'success'=> 'Day Added Successfully',
                'tab_id'=>1
            ]);
        }
    } else {
        return view('system-setting.index', get_defined_vars());
    }
}

    public function depAdd(Request $request)
    {   
        $edit_id = $request->edit_id;
        $dep_id = Department::where('id', $edit_id)->first();
        
        if ($dep_id) {
            $dep_id->dep_name = $request->edit_dep;
            $dep_id->save();

            return redirect()->route('system.setting')->with('success', 'Department Update Successfully');
        }else{
            $department = new Department;
            $department->dep_name = $request->department;
            $department->save();

            $departments = Department::all();
            
            return redirect()->route('system.setting')->with([
                'success'=> 'Department Add Successfully',
                'tab_id'=>2
            ]);
        }

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $employees = User::where('status', '0')->get();
        $system_settings = SystemSetting::first();
        return view('daily_input.add-daily-input', compact('employees','system_settings'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $start_time = $request->start_time;
        $end_time = $request->end_time;

        $start = Carbon::parse($start_time);
        $end = Carbon::parse($end_time);

        $total_seconds = $end->diffInSeconds($start);

        $total_hours = $total_seconds / 3600;

        $total_hours = number_format($total_hours, 2);

        // Retrieve the user based on the employee_id
        $user = User::find($request->employee_id);
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $total_paid = $user->rate * $total_hours;
        // Create a new DailyInputs record
        $daily_input = new DailyInputs;
        $daily_input->rate = $user->rate; // Fetch the rate from the user model
        $daily_input->employee_id = $request->employee_id;
        $daily_input->date = $request->date;
        $daily_input->start_time = $request->start_time;
        $daily_input->end_time = $request->end_time;
        $daily_input->total_time_in_sec = $total_seconds;
        $daily_input->total_paid =  $total_paid;

        $daily_input->save();

        $id = $daily_input->id;
        return response()->json([
            'success' => 'Product added Successful!',
            'id' => $id
        ]);
    }

        public function detailStore(Request $request)
    {
        $daily_input_id = $request->daily_input_id;
        $fnsku_value = $request->fnsku;
        $product_id = $request->product_id;
        $detail_update = DailyInputDetail::where('daily_input_id', $daily_input_id)
        
        ->when(!is_null($product_id), function ($query) use ($product_id) {
            $query->where('product_id', $product_id);
        })
        ->first();
        if($detail_update){
            $old_qty = $detail_update->qty;
            $new_qty = $old_qty + $request->qty;
            
            $detail_update->fnsku = $request->fnsku;
            $detail_update->qty = $new_qty;
            $detail_update->pack = $request->pack;
            $product = Products::where('id', $product_id)->first();
            // $product->item = $request->item;
            
            // $product->pack = $request->pack;
            if(empty($product)){
                $new_product = new Products;
                $new_product->fnsku = $request->fnsku;
                if($request->pack != null){
                    $new_product->pack = $request->pack;
                }
                $new_product->item = $request->item == null || $request->item == "" ?"Temporary Product Name":$request->item;
                $new_product->msku =  $request->msku != null?$request->msku:'0000000000';
                $new_product->asin =  $request->asin != null?$request->asin:'0000000000';
                $new_product->save();
                $detail_update->product_id = $new_product->id;
            }else{
                $product->msku =  $request->msku != null?$request->msku:$product->msku;
                $product->asin =  $request->asin != null?$request->asin: $product->asin ;
                $product->item = $request->item;
                $product->pack = $request->pack;
                $detail_update->product_id = $product->id;
                $product->save();
            }
            $detail_update->save();
        }else{
            $detail = new DailyInputDetail;
            $detail->daily_input_id = $daily_input_id;
            $detail->fnsku = $request->fnsku;
            $detail->product_id = $request->product_id;
            if($request->qty != null){
                $detail->qty = $request->qty;
            }
            if($request->pack != null){
                $detail->pack = $request->pack;
            }
            $product = Products::where('id', $product_id)->first();
            // $product->item = $request->item;
            
            // $product->pack = $request->pack;
            if(empty($product)){
                $new_product = new Products;
                $new_product->fnsku = $request->fnsku;
                if($request->pack != null){
                    $new_product->pack = $request->pack;
                }
                $new_product->item = $request->item == null || $request->item == "" ?"Temporary Product Name":$request->item;
                $new_product->msku =  $request->msku != null?$request->msku:'0000000000';
                $new_product->asin =  $request->asin != null?$request->asin:'0000000000';
                $new_product->save();
                $detail->product_id = $new_product->id;
            }else{
                $product->msku =  $request->msku != null?$request->msku:$product->msku;
                $product->asin =  $request->asin != null?$request->asin: $product->asin ;
                $product->item = $request->item;
                $product->pack = $request->pack;
                $detail->product_id = $product->id;
                $product->save();
            }
            

            $detail->save();
            
        }


        $total_qty = DailyInputDetail::where('daily_input_id', $daily_input_id)->sum('qty');
        $dailyInput = DailyInputs::where('id', $daily_input_id)->first();
        if ($dailyInput) {
            $totalPaid = $dailyInput->total_paid;
            $totalTimeInSeconds = $dailyInput->total_time_in_sec;
            $totalTimeHours = $totalTimeInSeconds / 3600;
        }
       
        $total_packing_cost_per_item = $totalPaid / $total_qty;
        $total_item_hour =$total_qty/ number_format($totalTimeHours, 2) ;
        
        $dailyInput->total_item_hour = number_format($total_item_hour, 5);
        $dailyInput->total_packing_cost = $total_packing_cost_per_item;

        $dailyInput->save();

        return response()->json([
            'success' => 'Product added Successful!',
            'id' => $daily_input_id
        ]);
    }

    public function detailEdit(Request $request, string $id)
    {
        
        $edit_detail = DailyInputDetail::where('id', $id)->first();
        $edit_detail->qty = $request->edit_qty;
        $edit_detail->pack = $request->edit_pack;
        
        $edit_detail->save();

        $total_qty = DailyInputDetail::where('daily_input_id', $edit_detail->daily_input_id)->sum('qty');
        $dailyInput = DailyInputs::where('id', $edit_detail->daily_input_id)->first();
        if ($dailyInput) {
            $totalPaid = $dailyInput->total_paid;
            $totalTimeInSeconds = $dailyInput->total_time_in_sec;
            $totalTimeHours = $totalTimeInSeconds / 3600;
        }

        $total_packing_cost_per_item = $totalPaid / $total_qty;
        $total_item_hour = $total_qty / number_format($totalTimeHours, 2) ;
        
        $dailyInput->total_item_hour = number_format($total_item_hour, 5);
        $dailyInput->total_packing_cost = $total_packing_cost_per_item;
        $dailyInput->save();

        return response()->json([
            'success' => 'Product added Successful!',
        ]);
    }

    public function checkFnsku(Request $request)
    {
        $fnsku = $request->fnsku;
        $product =  Products::where('id', $fnsku)->first();
        
        if ($product) {
            return response()->json([
                'success' => true,
                'data' => $product 
            ]);
        } else {
            return response()->json([
                'success' => false,
                'name' => null
            ]);
        }
    }

    public function delete($id)
    {
        // $daily_input_delete = DailyInputDetail::find($id);
        // $daily_input_delete->delete();
        $detail = DailyInputDetail::find($id);

        if (!$detail) {
            return response()->json(['success' => false, 'message' => 'Detail not found.'], 404);
        }

        $dailyInputId = $detail->daily_input_id;

        $detail->delete(); // delete the detail first

        // Now update the related DailyInputs record
        $dailyInput = DailyInputs::find($dailyInputId);

        if ($dailyInput) {
            $total_qty = DailyInputDetail::where('daily_input_id', $dailyInput->id)->sum('qty');

            $total_hours = $dailyInput->total_time_in_sec / 3600;
            $dailyInput->total_paid = $dailyInput->rate * $total_hours;

            if ($total_qty > 0 && $total_hours > 0) {
                $total_item_hour = $total_qty / $total_hours;
                $total_packing_cost_per_item = $dailyInput->total_paid / $total_qty;

                $dailyInput->total_item_hour = number_format($total_item_hour, 5);
                $dailyInput->total_packing_cost = $total_packing_cost_per_item;
            } else {
                $dailyInput->total_item_hour = 0;
                $dailyInput->total_packing_cost = 0;
            }

            $dailyInput->save();
        }

        return redirect()->back()->with('success', 'Record Deleted Successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $daily_input = DailyInputs::where('id', $id)->with('user')->first();
        $employees = User::where('status', '0')->get();
        $daily_input_details = DailyInputDetail::with('product')->where('daily_input_id', $id)->get();
        return view('daily_input.edit-daily-input', compact('daily_input','employees','daily_input_details'));
    }
    public function edit2(string $id)
    {
        $daily_input = DailyInputs::where('id', $id)->with('user')->first();
        $employees = User::where('status', '0')->get();
        $daily_input_details = DailyInputDetail::with('product')->where('daily_input_id', $id)->get();
        return view('daily_input.edit-daily-input-new', compact('daily_input','employees','daily_input_details'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $daily_input = DailyInputs::where('id', $id)->first();
        $employees = User::where('status', '0')->get();
        return view('daily_input.edit-time', compact('daily_input','employees'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $edit_daily_input = DailyInputs::find($id);

        if (!$edit_daily_input) {
            return response()->json(['success' => false, 'message' => 'Record not found.'], 404);
        }

        $edit_daily_input->start_time = $request->input('start_time');
        $edit_daily_input->end_time = $request->input('end_time');
        $start_time = $request->start_time;
        $end_time = $request->end_time;

        $start = Carbon::parse($start_time);
        $end = Carbon::parse($end_time);

        $total_seconds = $end->diffInSeconds($start);
        $total_hours = $total_seconds / 3600;

        $total_hours = number_format($total_hours, 2);
        // dd( $total_hours );

        // Retrieve the user based on the employee_id
        $user = User::find($edit_daily_input->employee_id);
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $total_paid = $edit_daily_input->rate * $total_hours;
        $edit_daily_input->total_time_in_sec = $total_seconds;
        $edit_daily_input->total_paid = $total_paid;
        $total_qty = DailyInputDetail::where('daily_input_id', $edit_daily_input->id)->sum('qty');
        if($total_qty >0){
            $total_packing_cost_per_item =  $edit_daily_input->total_paid  / $total_qty;
            $total_item_hour = $total_qty / number_format( $total_hours , 2) ;
            
            $edit_daily_input->total_item_hour = number_format($total_item_hour, 5);
            $edit_daily_input->total_packing_cost = $total_packing_cost_per_item;
        }
        $edit_daily_input->save();

        return response()->json([
            'success' => true,
            'id' => $edit_daily_input->id,
            'message' => 'Daily Input Time updated successfully!'
        ]);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $daily_input_delete = DailyInputs::find($id);
        $daily_input_delete->delete();

        return redirect()->back()->with('success', 'Record Deleted Successfully!');
    }
        //get dialyinput for datatable
    public function getDailyInputData(Request $request){
        if(auth()->user()->role == 1 || auth()->user()->department == 4){
            $query = DailyInputs::query();
        }else{
            $query = DailyInputs::where('employee_id',auth()->user()->id);
        }
            
            $userIds = DB::table('users')->pluck('id');

            $query->with('user')
            ->leftJoinSub(
                DB::table('daily_input_details')
                    ->select('daily_input_id', DB::raw('COALESCE(SUM(qty), 0) as total_qty'))
                    ->whereNull('deleted_at')
                    ->groupBy('daily_input_id'),
                'details_sum',
                'daily_inputs.id',
                'details_sum.daily_input_id'
            )
            ->select('daily_inputs.*', 'details_sum.total_qty')
            ->whereIn('daily_inputs.employee_id', $userIds)
            ->orderBy('date','desc');
            // Date range filtering logic
            if ($dateRange = request()->get('date_range')) {
                $dates = explode('_', $dateRange); 
                if(count($dates) == 2) {
                    $startDate = $dates[0];
                    $endDate = $dates[1];
                }
                // Apply the date range filter if both dates are provided
                if ($startDate && $endDate) {
                    $query->whereBetween('date', [$startDate, $endDate]);
                }
            }
             // Calculate totals
            $totals = [
                'totalMinutes' => $query->sum('total_time_in_sec') / 60, // Convert to minutes
                'totalPaid' => $query->sum('total_paid'),
                'totalQty' => $query->sum('total_qty'),
            ];

            // Perform calculations for packing cost per item and items per hour
            if ($totals['totalQty'] > 0) {
                $totals['totalPackingCost'] = $totals['totalPaid'] / $totals['totalQty'];
                $totalHours = intdiv($totals['totalMinutes'], 60);
                $totalMinutes = $totals['totalMinutes'] % 60;
                $totals['totalItemHour'] = $totals['totalQty'] / ($totalHours + ($totalMinutes / 60));
            } else {
                $totals['totalPackingCost'] = 0;
                $totals['totalItemHour'] = 0;
            }
            $totals['totalPackingCost'] = number_format( $totals['totalPackingCost'], 3);
            $totals['totalItemHour'] = number_format( $totals['totalItemHour'], 3);
             // Collect filtered report IDs
            $report_ids = $query->pluck('id');

            // Retrieve related product records
            $details = DailyInputDetail::whereIn('daily_input_id', $report_ids)
            ->whereNull('deleted_at')
            ->selectRaw('fnsku, pack, MAX(id) as id, MAX(created_at) as created_at, MAX(product_id) as product_id, SUM(qty) as qty')
            ->groupBy('fnsku', 'pack')
            ->with('product')
            ->get();
            return DataTables::of($query)
            ->editColumn('date', function($daily_input) {
                return ' <small>'.Carbon::parse($daily_input->date)->format("D, M j, Y").'</small>';
            })
            ->editColumn('employee_id', function($daily_input) {
                return ' <small>'.$daily_input->user->name ?? 'N/A' .'</small>';
            })
            ->editColumn('start_date', function($daily_input) {
                return ' <small class="text-center d-block">'.Carbon::parse($daily_input->start_date)->format("H:i").'</small>';
            })
            ->editColumn('end_date', function($daily_input) {
                return ' <small class="text-center d-block">'.Carbon::parse($daily_input->end_date)->format("H:i").'</small>';
            })
            ->editColumn('rate', function($daily_input) {
                return ' <small class="text-center d-block">'.$daily_input->rate.'</small>';
            })
            ->editColumn('total_time_in_sec', function($daily_input) {
                $totalTimeInSeconds = $daily_input->total_time_in_sec;
                $hours = intdiv($totalTimeInSeconds, 3600); // Total hours
                $minutes = intdiv($totalTimeInSeconds % 3600, 60); // Remaining minutes
                $time =  $hours .'H'. $minutes .'m';
                return ' <small class="text-center d-block"> '. $time.'</small>';
            })
            ->editColumn('total_paid', function($daily_input) {
                return ' <small class="text-center d-block">$'.$daily_input->total_paid.'</small>';
            })
            ->editColumn('qty', function($daily_input) {
                $qty =  $daily_input->total_qty ?? 1;
                return ' <small class="text-center d-block">'.$qty .'</small>';
            })
            ->editColumn('total_packing_cost', function($daily_input) {
                $cost = number_format($daily_input->total_packing_cost, 3);
                return ' <small class="text-center d-block">$'.$cost .'</small>';
            })
            ->editColumn('total_item_hour', function($daily_input) {
                $total_item_hour = number_format($daily_input->total_item_hour, 3);
                return ' <small class="text-center d-block">'.$total_item_hour .'</small>';
            })
            ->editColumn('action', function($daily_input) {
                $show = '<div class="d-flex flex-nowrap gap-1 align-items-center text-nowrap">';
            
                // New View button
                $show .= '<a href="' . route('daily-input.edit2', $daily_input->id) . '" class="btn btn-primary btn-sm p-1 text-nowrap">
                            <i class="ri-eye-fill align-bottom me-1"></i>  View
                          </a>';
            
                // View button
                // $show .= '<a href="' . route('daily-input.show', $daily_input->id) . '" class="btn btn-info btn-sm p-1 text-nowrap">
                //             <i class="ri-eye-fill align-bottom me-1"></i> View
                //           </a>';
            
                // Delete form and button (only for admin)
                if (auth()->user()->role == 1) {
                    $show .= '<form method="POST" action="' . route('daily-input.destroy', $daily_input->id) . '" class="m-0 p-0 text-nowrap" style="display:inline;">
                                ' . csrf_field() . '
                                ' . method_field('DELETE') . '
                                <button type="submit" class="btn btn-danger btn-sm p-1 text-nowrap">
                                    <i class="ri-delete-bin-fill align-bottom me-1"></i> Delete
                                </button>
                              </form>';
                }
            
                $show .= '</div>';
            
                return $show;
            })
            ->rawColumns(['date','employee_id', 'start_date', 'end_date', 'rate', 'total_time_in_sec','total_paid', 'qty','total_packing_cost','total_item_hour','action'])
            ->with('totals', $totals) // Pass totals to the frontend
            ->with('details', $details)
            ->make(true);
    }
    public function fetchItems(Request $request)
    {
       $search = trim($request->input('search'));

        if ($search) {
            $items = Products::where(function ($query) use ($search) {
                $query->where('item', 'LIKE', "%{$search}%")
                    ->orWhere('asin', 'LIKE', "%{$search}%")
                    ->orWhere('msku', 'LIKE', "%{$search}%")
                    ->orWhere('fnsku', 'LIKE', "%{$search}%")
                    ->orWhere('pack', 'LIKE', "%{$search}%");
            })->with('templates')->get();
        } else {
            $items = collect(); // empty if no search
        }


        return response()->json($items);
    }
    public function updateDate(Request $request){
        // Validate the input
        $request->validate([
            'id' => 'required|exists:daily_inputs,id',
            'date' => 'required|date',
        ]);

        // Find the daily input record by ID
        $dailyInput = DailyInputs::findOrFail($request->id);

        // Update the date
        $dailyInput->date = $request->date;
        $dailyInput->save();

        // Return a success response
        return response()->json(['message' => 'Date updated successfully']);
    }
    public function editDetailQty($id){
        $inputDetails = DailyInputDetail::where('id',$id)->with('product')->first();
        return view('daily_input.edit-inputdetail-qty',get_defined_vars());
    }
    // Controller
    public function fetchDailyInputs(Request $request)
    {
        $today = Carbon::today();
        $startOfMonth = $today->copy()->startOfMonth();
        $endOfMonth = $today;

        $dailyInputs = DailyInputs::whereBetween('date', [$startOfMonth, $endOfMonth])
        ->orderBy('date', 'desc')
        ->with('user')
        ->get(); // Customize fields as needed

        return response()->json($dailyInputs);
    }

}
