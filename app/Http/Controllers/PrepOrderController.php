<?php

namespace App\Http\Controllers;

use App\Models\DailyInputDetail;
use App\Models\DailyInputs;
use App\Models\PrepOrder;
use App\Models\PrepOrderDetail;
use App\Models\Products;
use App\Models\SystemSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PHPUnit\Event\Telemetry\System;
use PHPUnit\Event\Test\Prepared;
use Yajra\DataTables\Facades\DataTables;

class PrepOrderController extends Controller
{
    public function index()
    {
        $prep_orders = PrepOrder::with(['employee', 'createdBy', 'details'])->get();
        return view('prep-orders.index', compact('prep_orders'));
    }

    public function createOrder()
    {   
        $created_by = auth()->user()->name;
        $employees = User::where('status', '0')->get(); // Fetch employees for dropdown
        // $order_id = PrepOrder::latest()->first()->id + 1;
        // $order_id = DB::select("SHOW TABLE STATUS LIKE 'prep_orders'")[0]->Auto_increment;
        $order_id = DB::table('prep_orders')->max('custom_id') + 1;
        // dd($order_id);
        return view('prep-orders.create', compact('employees', 'order_id', 'created_by'));
    }
    public function destroy($id)
    {
        $prepOrder = PrepOrder::findOrFail($id);
        $prepOrder->delete();
        return response()->json(['success' => true, 'message' => 'Prep order deleted successfully']);
    }

    public function store(Request $request)
    {
        $product_id = $request->product_id;
        $order_id = $request->order_id;

        // Fetch product details
        $product = Products::where('id', $product_id)->first();
        if (!$product) {
            return response()->json([
                'notExist' => true,
                'message' => 'This product Not Found!',
            ]);
        }

        $fnsku = $product->fnsku;
        $pack = $product->pack;

        // Check if the product already exists in the order
        $existingOrderDetail = PrepOrderDetail::where('prep_order_id', $order_id)
            ->where('product_id', $product_id)
            ->first();
        if ($existingOrderDetail) {
            return response()->json([
                'exists' => true,
                'message' => 'This product is already in the order. Do you want to update the quantity?',
                'current_qty' => $existingOrderDetail->qty
            ]);
        }

        // Insert new order detail
        $order_detail = new PrepOrderDetail;
        $order_detail->prep_order_id = $order_id;
        $order_detail->product_id = $product_id;
        $order_detail->fnsku = $fnsku;
        $order_detail->qty = $request->qty ?? 1;
        $order_detail->pack = $pack;
        $order_detail->save();

        return response()->json(['message' => 'Prep order added successfully']);
    }
    public function updateQtyNew(Request $request)
    {
        $order_id = $request->order_id;
        $product_id = $request->product_id;
        $new_qty = $request->newQty;
        $order_detail = PrepOrderDetail::where('prep_order_id', $order_id)
            ->where('product_id', $product_id)
            ->first();

        if ($order_detail) {
            $order_detail->qty = $new_qty;
            $order_detail->save();

            return response()->json(['message' => 'Quantity updated successfully']);
        }

        return response()->json(['error' => 'Product not found'], 404);
    }
    
    public function getData(Request $request)
    {   $id = $request->order_id;
        $products = PrepOrderDetail::where('prep_order_id', $id)->with('product')->get(); 

        return response()->json([
            'message' => 'Table Loaded',
            'products' => $products
        ]);
    }

    public function createOrderStore(Request $request)
    {
        $order = new PrepOrder;
        $order->employee_id = $request->employee_id;
        $order->created_by = auth()->user()->id;
        $order->save();

        return response()->json([
            'message' => 'Order created successfully!',
            'order_id' => $order->custom_id
        ]);
    }

    public function editOrderStore(Request $request)
    {
        $order_id = $request->order_id;
        $order = PrepOrder::where('custom_id', $order_id)->first();
        $order->employee_id = $request->employee_id;
        $order->created_by = auth()->user()->id;
        $order->save();

        return response()->json([
            'message' => 'Order Updated successfully!',
            'order_id' => $order->custom_id
        ]);
    }

    public function editData($id){
        $employees = User::where('status', '0')->get();
        $order_id = $id;
        // $created_by = auth()->user()->name;
        $daily_input = PrepOrder::where('custom_id',$id)->with(['employee', 'createdBy'])->first();
        $daily_input_details  = PrepOrderDetail::where('prep_order_id',$id)->with('product')->get();
        $prepOrderAll = PrepOrder::where('id','!=',$daily_input->id)->where('status',0)->get();
        // dd($daily_input_details);
        return view('prep-orders.edit',get_defined_vars());
    }

    public function prepDetailDel($id)
    {
        $order = PrepOrderDetail::find($id);
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Product not found!'], 404);
        }
        $order->delete();
        return response()->json(['success' => true, 'message' => 'Product deleted successfully!']);
    }

    public function updateQty(Request $request)
    {

        $product = PrepOrderDetail::findOrFail($request->id);
        $product->qty = $request->qty;
        $product->save();

        return response()->json(['success' => true, 'message' => 'Quantity updated successfully']);
    }

    public function dailyInputDone(Request $request)
    {
        $product_id = $request->id;
        $product = Products::where('id', $product_id)->first();
        $daily_input = DailyInputs::where('id', $request->daily_input_id)->first();
        // dd($product);
        $today = Carbon::now()->format('Y-m-d');
        $system_settings = SystemSetting::first();
        $start_time = $system_settings->start_time;
        $end_time = $system_settings->end_time;
        $employee = $request->employee;
        $user = User::where('id', $daily_input->employee_id)->first();
        // dd($request->employee);
        $rate = $user->rate;
        $total_qty = $request->qty;

        $start = Carbon::parse($start_time);
        $end = Carbon::parse($end_time);
        $total_seconds = $end->diffInSeconds($start);
        $total_hours = $total_seconds / 3600;
        $total_hours = number_format($total_hours, 2);
        $total_paid = $rate * $total_hours;

        $total_packing_cost_per_item = $total_paid / $total_qty;
        $total_item_hour = $total_qty/ number_format($total_hours, 2) ;

        
        if (!$daily_input) {
            $new_daily_input = new DailyInputs;
            $new_daily_input->employee_id = $employee;
            $new_daily_input->date = $today;
            $new_daily_input->start_time = $start_time;
            $new_daily_input->end_time = $end_time;
            $new_daily_input->total_time_in_sec = $total_seconds;
            $new_daily_input->total_paid = $total_paid;
            $new_daily_input->total_packing_cost = $total_packing_cost_per_item;
            $new_daily_input->total_item_hour = $total_item_hour;
            $new_daily_input->rate = $rate;
            $new_daily_input->save();
            
            $new_daily_input_detail = new DailyInputDetail;
            $new_daily_input_detail->daily_input_id = $new_daily_input->id;
            $new_daily_input_detail->fnsku = $product->fnsku;
            $new_daily_input_detail->product_id = $product->id;
            $new_daily_input_detail->qty = $total_qty;
            $new_daily_input_detail->pack = $product->pack;
            $new_daily_input_detail->save();
        }else{
            $product_exist = DailyInputDetail::where('daily_input_id', $daily_input->id)->where('fnsku', $product->fnsku)->first();
            if ($product_exist) {
                $product_exist->qty += $total_qty;
                $product_exist->save();
            }else{
                $new_daily_input_detail = new DailyInputDetail;
                $new_daily_input_detail->daily_input_id = $daily_input->id;
                $new_daily_input_detail->fnsku = $product->fnsku;
                $new_daily_input_detail->product_id = $product->id;
                $new_daily_input_detail->qty = $total_qty;
                $new_daily_input_detail->pack = $product->pack;
                $new_daily_input_detail->save();
            }
        }
        $prep_detail = PrepOrderDetail::where('id', $request->detailId)->first();
        if ($prep_detail) {
            $prep_detail->status = 1;
            $prep_detail->save();
        
            $prep_detail_id = $prep_detail->prep_order_id;
            $total = PrepOrderDetail::where('prep_order_id', $prep_detail_id)->count();
            $completed = PrepOrderDetail::where('prep_order_id', $prep_detail_id)->where('status', 1)->count();
            // dd($prep_detail_id);
            if ($total == $completed) {
                $order = PrepOrder::where('custom_id', $prep_detail_id)->first();
                // dd($order);
                $order->status = 1;
                $order->save();
            }
            $prep_detail->delete();
        }
        return response()->json(['success' => true, 'message' => 'Daily input added successfully']);
    }
    public function copyMoveItem(Request $request)
    {
        $prepOrderDetail = PrepOrderDetail::find($request->itemId);
        $prepOrder = PrepOrder::find($request->prepOrderId);

        if (!$prepOrderDetail || !$prepOrder) {
            return response()->json(['message' => 'Invalid product or buylist.'], 400);
        }

        if ($request->is_copy) {
            // Copy Product Logic
            $newProduct = $prepOrderDetail->replicate();
            $newProduct->prep_order_id = $prepOrder->custom_id;
            $newProduct->save();
            return response()->json(['message' => 'Item copied successfully.']);
        } else {
            // Move Product Logic
            $prepOrderDetail->prep_order_id = $prepOrder->custom_id;
            $prepOrderDetail->save();
            return response()->json(['message' => 'Item moved successfully.']);
        }
    }
    public function saveNewProduct(Request $request)
    {
        $request->validate([
            'fnsku' => 'required|string',
            'item' => 'nullable|string',
            'pack' => 'nullable|integer',
            'msku' => 'nullable|string',
            'asin' => 'nullable|string',
        ]);
    
        $product = Products::create($request->only(['fnsku', 'item', 'pack', 'msku', 'asin']));

        return response()->json([
            'success' => true,
            'product' => $product
        ]);

    }
    public function getPrepOrders(){
        $prepOrders = PrepOrder::with(['employee', 'createdBy'])->get();
        return response()->json( $prepOrders);
    }
    public function assignProductToWorkOrder(Request $request)
    {
        $all       = $request->all();
        $asin      = $all['asin'];
        $msku      = $all['msku'];
        $product = Products::where('asin', $asin)->first();
        
        
        if (!$product) {
            $product = Products::create([
                'item' => $all['name'] ?? 'Temporary Product Name',
                'asin' => $all['asin'] ?? 'SKU-' . time(),
                'fnsku' => $all['asin'] ?? '',
                'msku' => $all['msku'] ?? null,
            ]);
        }
    
        $order_detail = PrepOrderDetail::where('prep_order_id', $all['work_order_id'])
            ->where('product_id', $product->id)
            ->first();

        if ($order_detail) {
            $order_detail->qty = $order_detail->qty +$all['quantity'];
            $order_detail->save();
            // return response()->json(['message' => 'Quantity updated successfully']);
        }else{
            $newdetail = new PrepOrderDetail;
            $newdetail->qty = $all['quantity'] ?? 1;
            $newdetail->fnsku = $product->fnsku;
            $newdetail->pack =  $product->pack;
            $newdetail->prep_order_id = $all['work_order_id'];
            $newdetail->product_id = $product->id;
            $newdetail->save();
            // return response()->json(['message' => 'Quantity updated successfully']);
        }

        return response()->json([
            'status' => true,
            'message' => 'Product Added to Prep Work Order',
        ]);
    }
    public function updateName(Request $request){
        $findPreOrder = PrepOrder::where('custom_id',$request->order_id)->first();
        if($findPreOrder){
           $findPreOrder->name = $request->name;
            $findPreOrder->save();
            return response()->json(['status' => 'success', 'message' => 'Name updated successfully']);
        }
    }
}
