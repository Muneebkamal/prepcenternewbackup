<?php

namespace App\Http\Controllers;

use App\Models\PackingTemplate;
use App\Models\ShipPlan;
use App\Models\ShipPlanDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ShippingPlanController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = ShipPlan::latest();
            return DataTables::of($query)
                ->addColumn('date', function ($row) {
                    return $row->created_at->format('m/d/yy');
                })
                ->addColumn('amazon_id', function ($row) {
                    return $row->amazon_id;
                })
                ->addColumn('no_of_sku', function ($row) {
                    return $row->shippingDetails->count() ?? 0;
                })
                ->addColumn('total_units', function ($row) {
                    return $row->shippingDetails->sum('units') ?? 0;
                })
                ->addColumn('total_boxes', function ($row) {
                    return $row->shippingDetails->sum('boxes') ?? 0;
                })
                ->addColumn('total_weight', function ($row) {
                    $totalWeight = 0;
                    foreach($row->shippingDetails as $item){
                        if($item->template_id){
                            $findTemplate = PackingTemplate::where('id',$item->template_id)->first();
                            if($findTemplate){
                                $itemboxes = $item->boxes;
                                $itemWeight = $itemboxes * $findTemplate->box_weight;
                                $totalWeight += $itemWeight;
                            }
                        }
                    }
                    return $totalWeight;
                })
                ->addColumn('handling_cost', function ($row) {
                    return '$'.number_format($row->handling_cost, 2);
                })
                ->addColumn('shipping_cost', function ($row) {
                    return '$'.number_format($row->shipment_fee, 2);
                })
                ->addColumn('total_charge', function ($row) {
                    $total = $row->handling_cost + $row->shipment_fee; // sum of both
                    return '$' . number_format($total, 2);
                })
                ->addColumn('cost_per_unit', function ($row) {
                    $total = $row->handling_cost + $row->shipment_fee; // recalc total
                    $totalUnits = $row->shippingDetails->sum('units') ?? 0;
                    return $totalUnits > 0 
                        ? '$' . number_format($total / $totalUnits, 2) 
                        : '0.00';
                })
                ->addColumn('cost_per_lb', function ($row) {
                    $total = $row->handling_cost + $row->shipment_fee;

                    // Recalculate weight here
                    $totalWeight = 0;
                    foreach ($row->shippingDetails as $item) {
                        if ($item->template_id) {
                            $findTemplate = PackingTemplate::where('id', $item->template_id)->first();
                            if ($findTemplate) {
                                $itemboxes = $item->boxes;
                                $itemWeight = $itemboxes * $findTemplate->box_weight;
                                $totalWeight += $itemWeight;
                            }
                        }
                    }

                    return $totalWeight > 0
                        ? number_format($total / $totalWeight, 2)
                        : '0.00';
                })
                ->addColumn('action', function ($row) {
                    $editUrl = url('shipping-plans/' . $row->custom_id . '/edit');
                    return '<a href="' . $editUrl . '" class="btn btn-sm btn-warning">Edit</a>';
                })
                ->rawColumns(['date', 'amazon_id', 'action'])
                ->make(true);
        }
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
        $created_by = auth()->user()->id ?? 'System';
        $shipPlan = ShipPlan::updateOrCreate(
            ['custom_id' =>  $nextId], // 🔄 Match on custom_id
            [ // 🔁 Update with:
                'sku_method' => $data['sku_method'] ?? '',
                'fullment_capability' => 'full_fillment',
                'market_place' => 'us',
                'show_filter' => $data['show_filter'] ?? 0,
                'handling_cost' => $data['handling_cost'] ?? 0,
                'is_pending' => 1,
                'shipment_fee' => $data['shipment_fee'] ?? 0,
                'created_by' => $created_by,
            ]
        );
        $shippingPlan = ShipPlan::with('creator')->findOrFail( $shipPlan->id);
        return redirect()->route('shipping-plans.edit', $shipPlan->custom_id);
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
        $shippingPlan = ShipPlan::with('creator')->where('custom_id',$id)->first();
        return view('shipping-plan.edit',get_defined_vars());
    }
    public function destroy($id)
    {
        $plan = ShipPlan::findOrFail($id);
        $plan->delete();

        return response()->json(['success' => true]);
    }
    public function saveItem(Request $request){
        $shipping_plan = ShipPlan::where('custom_id',$request->ship_plan_id)->first();
        if( $shipping_plan ){
            $item = ShipPlanDetail::where('ship_plan_id',$shipping_plan->id)->where('product_id',$request->product_id)->first();
            if( $item){
                $item->product_id = $request->product_id;
                $item->ship_plan_id = $shipping_plan->id;
                $item->template_id = is_numeric($request->template_type) ? $request->template_type : 0;
                $item->units = $request->units;
                $item->boxes = $request->boxes;
                $item->expiration = $request->expiration;
                $item->save();
            }else{
                $newItem =  new ShipPlanDetail;
                $newItem->product_id = $request->product_id;
                $newItem->ship_plan_id = $shipping_plan->id;
                $newItem->template_id = is_numeric($request->template_type) ? $request->template_type : 0;
                $newItem->units = $request->units;
                $newItem->boxes = $request->boxes;
                $newItem->expiration = $request->expiration;
                $newItem->save();
            }
             $shipping_plan->is_pending = 0;
             $shipping_plan->save();
        }
        return response()->json(['success' => true]);

    }
    public function getShippingItems($custom_id){
        $shipping_plan = ShipPlan::where('custom_id',$custom_id)->where('is_pending',0)->first();
        if( $shipping_plan ){
            $items = ShipPlanDetail::where('ship_plan_id',$shipping_plan->id) ->with(['product', 'packingTemplate'])->get();
        }else{
            $items = [];
        }
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
        $shipPlans = ShipPlan::with('creator')->Where('is_pending',0)->get();
        return response()->json($shipPlans);
    }
    public function moveITem(Request $request){
        $item = ShipPlanDetail::findOrFail($request->item_id);
        $item->ship_plan_id = $request->target_plan_id;
        $item->save();

        return response()->json(['success' => true, 'message' => 'Item moved successfully']);
    }
    public function updateCost(Request $request, $id)
    {
        $plan = ShipPlan::findOrFail($id);

        if ($request->field == 'shipment_fee') {
            $plan->shipment_fee = $request->value;
        } elseif ($request->field == 'handling_cost') {
            $plan->handling_cost = $request->value;
        }
        $plan->is_pending = 0;     
        $plan->save();

        return response()->json(['success' => true, 'message' => 'Updated successfully']);
    }
    public function updateField(Request $request, $id)
    {
        $plan = ShipPlan::findOrFail($id);
        $plan->{$request->field} = $request->value;
        $plan->is_pending = 0;
        $plan->save();
        return response()->json(['success' => true]);
    }
    public function saveShippingPlanData(Request $request)
    {
        $plan = ShipPlan::findOrFail($request->id);
        $plan->shipment_name = $request->shipment_name;
        $plan->amazon_id = $request->amazon_id;
        $plan->amazon_reference_id = $request->amazon_reference_id;
        $plan->ship_to = $request->ship_to;
        $plan->method_carrier = $request->method_carrier;
        $plan->is_pending = 0;
        $plan->save();
        return response()->json(['success' => true]);
    }
    public function updatePlanName($id,Request $request)
    {
        $plan = ShipPlan::findOrFail($id);
        $plan->name = $request->name;
        $plan->save();
        return response()->json(['success' => true, 'name' => $plan->name]);
    }
    public function updateName(Request $request, $id)
    {
        $plan = ShipPlan::findOrFail($id);
        $plan->name = $request->name;
        $plan->shipment_name = $request->shipment_name;
        $plan->save();

        return response()->json(['status' => true, 'message' => 'Updated successfully']);
    }

}
