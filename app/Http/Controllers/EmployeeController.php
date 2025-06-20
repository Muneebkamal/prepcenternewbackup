<?php

namespace App\Http\Controllers;

use App\Models\DailyInputDetail;
use App\Models\DailyInputs;
use App\Models\Department;
use App\Models\Products;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $employees = User::with('departments')->get();
        // dd($employees[0]->departments);
        return view('employee.index',compact('employees'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $departments = Department::all();
        return view('employee.add-employee', compact('departments'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the request data
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8', // Ensure password is required and has a minimum length
            'phone_no' => 'nullable|string|max:20',
            'role' => 'required|string|max:255',
            'department' => 'nullable|string|max:255',
            'status' => 'required|string|max:50',
            'rate' => 'nullable|numeric'
        ]);

        // Concatenate first and last names
        $name = $request->first_name . ' ' . $request->last_name;

        // Create a new User instance and set its properties
        $employee = new User;
        $employee->name = $name;
        $employee->email = $request->email;
        $employee->password = Hash::make($request->password); // Hash the provided password
        $employee->phone_no = $request->phone_no;
        $employee->role = $request->role;
        $employee->department = $request->department;
        $employee->status = $request->status;
        $employee->rate = $request->rate;
        if($request->permissions != null){
            $employee->permission = implode(",",$request->permissions);
        }

        // Save the employee to the database
        $employee->save();

        return response()->json(['success'=>'employee added Successful!']);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $employee = User::where('id', $id)->first();
        if (empty($employee)) {
            return '<p>Employee not found!</p>
                    <a href="' . url()->previous() . '" style="display: inline-block; margin-top: 10px; padding: 8px 12px; background-color: #007bff; color: white; text-decoration: none; border-radius: 4px;">Back</a>';
        }
        $departments = Department::all();
        return view('employee.edit-employee', compact('employee', 'departments'));
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
        // Validate the request data
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            // 'last_name' => 'required|string|max:255',
            'phone_no' => 'nullable|string|max:20',
            'role' => 'required|string|max:255',
            // 'rate' => 'nullable|numeric'
        ]);

        // Concatenate first and last names
        $name = $request->first_name . ' ' . $request->last_name;

        // Create a new User instance and set its properties
        $employee = User::where('id',$id)->first();
        $employee->name = $name;
        $employee->first_name = $request->first_name;
        $employee->last_name = $request->last_name;
        if ($request->filled('password')) {
            $employee->password = Hash::make($request->password); // Hash the new password
        }
        // $employee->password = Hash::make($request->password);
        $employee->phone_no = $request->phone_no;
        $employee->role = $request->role;
        $employee->department = $request->department;
        $employee->status = $request->status;
        
        if($request->permissions != null){
            $employee->permission = implode(",",$request->permissions);
        }
        // $employee->rate = $request->rate;

        // if ($request->filled('date')) {
        //     $requestDate = Carbon::parse($request->date);
        //     $today = Carbon::today();
        //     $dailyInputs = DailyInputs::where('date', '<=', $today)->where('date', '>=', $requestDate)->get();
        //     foreach ($dailyInputs as $dailyInput) {
        //         $dailyInput->rate = $request->rate;
        //         $dailyInput->save(); // Save each record individually
        //     }
        // }

        // Save the employee to the database
        $employee->save();

        return response()->json(['success'=>'employee updated Successful!']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $employee = User::find($id);
        if (!$employee) {
            return response()->json(['message' => 'Employee not found.'], 404);
        }
        $employee->delete();
        return redirect()->route('employees.index')->with('success', 'Employee Deleted successfully.');
        // return response()->json(['message' => 'Employee deleted successfully.'], 200);
    }

    // user and employee
    public function employeeMerge()
    {
        $results = DB::table('old_employee')
        ->where('old_employee.email', '<>', '') 
        ->get();
            // dd($results);
        foreach ($results as $result) {
            $email = $result->email; 
            $existingUser = DB::table('old_users')
                ->where('email', $email)
                ->first();
                if($result->employee_status == 11){
                    $status = 0;
                }
                elseif($result->employee_status == 12)
                {
                    $status = 1;
                }else{
                    $status = $result->employee_status;
                }
            if ($existingUser) {
                User::create([
                    'id' => $result->id,
                    'name' => $existingUser->username,
                    'first_name' => $result->first_name,
                    'last_name' => $result->last_name,
                    'email' => $result->email,
                    'password' => Hash::make($existingUser->password),
                    'phone_no' => $result->phone,
                    'role' => $result->role_id,
                    'department' => $result->id_department,
                    'status' => $status,
                    'rate' => $result->rate,
                ]);
            }
        }

        $results1 = DB::table('old_employee')
        ->where('old_employee.email', '') 
        ->get();
        foreach ($results1 as $result1) {
                $email = $result1->first_name. '@temp_mail.com';
                if($result1->employee_status == 11){
                    $status1 = 0;
                }
                elseif($result1->employee_status == 12)
                {
                    $status1 = 1;
                }else{
                    $status1 = $result1->employee_status;
                }
                User::create([
                    'id' => $result1->id,
                    'name' => $result1->first_name . $result1->last_name,
                    'first_name' => $result1->first_name,
                    'last_name' => $result1->last_name,
                    'email' => $email,
                    'password' => Hash::make('password'),
                    'phone_no' => $result1->phone,
                    'role' => $result1->role_id,
                    'department' => $result1->id_department,
                    'status' => $status1,
                    'rate' => $result1->rate,
                ]);
        }

        return 'Data merged successfully!';
    }

    // product merge
    public function productsData()
    {
        set_time_limit(150);

        $results = DB::table('import_result') 
        ->get();
            // dd($results);
        foreach ($results as $result) {

            $product = new Products;
            $product->id = $result->id;
            $product->item = $result->title;
            $product->msku = $result->msku;
            $product->asin = $result->asin;
            $product->fnsku = $result->fnsku;
            if ($result->pcs <= 0 || $result->pcs == '') {
                $product->pack = 1;
            } else {
                $product->pack = $result->pcs;
            }
            $product->save();
        }

        return 'Products merged successfully!';
    }

    //daily import
    public function dailyInputMerge()
    {
        set_time_limit(120);
        $results = DB::table('old_daily_input') 
        ->get();
        // dd($results);
        foreach ($results as $result) {

            $dailyInput = new DailyInputs;
            $dailyInput->id = $result->id;
            $dailyInput->employee_id = $result->id_employee;
            $dailyInput->date = $result->date;
            $dailyInput->start_time = $result->start_time;
            $dailyInput->end_time = $result->endtime;
            $dailyInput->total_time_in_sec = $result->total_time_in_sec;
            $dailyInput->total_paid = $result->total_paid;
            $dailyInput->total_packing_cost = $result->total_packing_cost;
            $dailyInput->total_item_hour = $result->total_item_hour;
            $dailyInput->rate = $result->rate;
            $dailyInput->save();
        }

        return 'Daily Input merged successfully!';
    }

    //daily import
    public function dailyInputDetailMerge()
    {
        set_time_limit(120);
        $results = DB::table('old_daily_input_detail') 
        ->get();
        // dd($results);
        foreach ($results as $result) {

            $dailyInputDetail = new DailyInputDetail;
            $dailyInputDetail->id = $result->id;
            $dailyInputDetail->daily_input_id = $result->id_daily_input;
            $dailyInputDetail->fnsku = $result->fnsku;

            if ($result->pcs == 0 || $result->pcs == '') {
                $dailyInputDetail->pack = 1;
            } else {
                $dailyInputDetail->pack = $result->pcs;
            }

            if ($result->qty == 0 || $result->qty == '') {
                $dailyInputDetail->qty = 1;
            } else {
                $dailyInputDetail->qty = $result->qty;
            }

            $dailyInputDetail->save();
        }

        return 'Daily Input merged successfully!';
    }
    public function emplpyeeRate(Request $request){
        $user = User::where('id',$request->emoloye_id)->first();
        if($user ){
            // Extract rate_date and new_rate from the request
            $rateDate = Carbon::parse($request->rate_date);
            $newRate = $request->rate;
            // Determine the current date
            $currentDate = Carbon::now();
            DailyInputs::where('employee_id', $user->id)
            ->whereBetween('date', [$rateDate, $currentDate])
            ->update(['rate' => $newRate]);
            $user->rate = $request->rate; 
            $user->save(); 
             // Redirect back with a success message
            return redirect()->back()->with('success', 'Rate updated successfully!');
        }else{
            return redirect()->back()->with('error', 'User Not Founded!');
        }
    }
}
