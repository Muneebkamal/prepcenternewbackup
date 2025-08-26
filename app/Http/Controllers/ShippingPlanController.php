<?php

namespace App\Http\Controllers;

use App\Models\ShipPlan;
use App\Models\ShipPlanDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShippingPlanController extends Controller
{
    public function index()
    {
        $shippingPlans = ShipPlan::get();
        return view('shipping-plan.index',get_defined_vars());
    }
    public function create()
    {
        // Get max custom_id (assuming 3-digit string format)
        $latestId = DB::table('ship_plans')
            ->selectRaw("MAX(CAST(custom_id AS UNSIGNED)) as max_id")
            ->value('max_id');

        // Generate next custom_id with leading zeros
        $nextId = str_pad((int)$latestId + 1, 3, '0', STR_PAD_LEFT);
        $created_by = auth()->user()->name ?? 'System';

        return view('shipping-plan.create',get_defined_vars());
    }
    public function store(Request $request){
       $data = $request->validate([
            'custom_id' => 'required',
            'sku_method' => 'nullable|string',
            'shipment_fee' => 'nullable|numeric',
            'handling_cost' => 'nullable|numeric',
            'fullment_capability' => 'nullable|string',
            'market_place' => 'nullable|string',
            'show_filter' => 'nullable|boolean',
        ]);
        $created_by = null;
        if(auth()->user()){
            $created_by = auth()->user()->id;
        }

        $shipPlan = ShipPlan::updateOrCreate(
            ['custom_id' => $data['custom_id']], // 🔄 Match on custom_id
            [ // 🔁 Update with:
                'sku_method' => $data['sku_method'] ?? '',
                'fullment_capability' => $data['fullment_capability'] ?? '',
                'market_place' => $data['market_place'] ?? '',
                'show_filter' => $data['show_filter'] ?? 0,
                'handling_cost' => $data['handling_cost'] ?? 0,
                'shipment_fee' => $data['shipment_fee'] ?? 0,
                'created_by' => $created_by,
            ]
        );
        return response()->json([
            'message' => 'Ship plan saved successfully',
            'data' => $shipPlan,
        ]);
    }

    public function show($id)
    {
        return view('shipping-plan.create');
    }
    public function Edit($id)
    {
        $shippingPlan = ShipPlan::with('creator')->findOrFail($id);
        return view('shipping-plan.edit',get_defined_vars());
    }
    public function destroy($id)
    {
        $plan = ShipPlan::findOrFail($id);
        $plan->delete();

        return response()->json(['success' => true]);
    }
    public function saveItem(Request $request){
        $item = ShipPlanDetail::where('ship_plan_id',$request->ship_plan_id)->where('product_id',$request->product_id)->first();
        if( $item){
            $item->product_id = $request->product_id;
            $item->ship_plan_id = $request->ship_plan_id;
            $item->template_id = is_numeric($request->template_type) ? $request->template_type : 0;
            $item->units = $request->units;
            $item->boxes = $request->boxes;
            $item->expiration = $request->expiration;
            $item->save();
        }else{
            $newItem =  new ShipPlanDetail;
            $newItem->product_id = $request->product_id;
            $newItem->ship_plan_id = $request->ship_plan_id;
            $newItem->template_id = is_numeric($request->template_type) ? $request->template_type : 0;
            $newItem->units = $request->units;
            $newItem->boxes = $request->boxes;
            $newItem->expiration = $request->expiration;
            $newItem->save();
        }

    }
    public function getShippingItems($custom_id){
        $items = ShipPlanDetail::where('ship_plan_id',$custom_id) ->with(['product', 'packingTemplate'])->get();
        return response()->json($items);
    }
    public function deleteProduct($id, Request $request)
    {
        $productId = $request->product_id;

        $shippingPlan = ShipPlan::where('custom_id',$request->ship_plan_id)->first();
        // Example: shipping_plan_products
        $deleted = ShipPlanDetail::where('id', $id)
        ->delete();

        if ($deleted) {
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 400);
    }
    public function getAllShipPlans(){
        $shipPlans = ShipPlan::with('creator')->get();
        return response()->json($shipPlans);
    }
    public function moveITem(Request $request){
        $item = ShipPlanDetail::findOrFail($request->item_id);
        $item->ship_plan_id = $request->target_plan_id;
        $item->save();

        return response()->json(['success' => true, 'message' => 'Item moved successfully']);
    }
}
