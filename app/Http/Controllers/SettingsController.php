<?php

namespace App\Http\Controllers;

use App\Models\Option;
use App\Models\SystemOption;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function addOption(Request $request)
    {
        $request->validate([
            'label_id' => 'required|exists:system_options,id',
            'new_option' => 'required|string',
        ]);
    
        $option = Option::create([
            'system_option_id' => $request->label_id,
            'value' => $request->new_option,
            'no_of_pcs_in_cotton' => $request->no_of_pcs_in_cotton ?? 0,
            'price_of_cotton' => $request->price_of_cotton ?? 0
        ]);
    
        return response()->json(['success' => true, 'option' => $option]);
    }
    public function editOption(Request $request)
    {
        $request->validate([
            'label_id' => 'required|exists:system_options,id',
            'option_id' => 'required|exists:options,id',
            'new_value' => 'required|string',
        ]);

        $option = Option::find($request->option_id);
        $option->value = $request->new_value;
        $option->save();

        return response()->json(['success' => true]);
    }
    public function deleteOption(Request $request)
    {
        $request->validate([
            'label_id' => 'required|exists:system_options,id',
            'option_id' => 'required|exists:options,id',
        ]);

        Option::destroy($request->option_id);

        return response()->json(['success' => true]);
    }
    public function updateOption(Request $request)
    {
        $request->validate([
            'label_id' => 'required|exists:system_options,id',
            'option_id' => 'required|exists:options,id',
            'updated_option' => 'required|string|max:255',
        ]);

        // Find and update the option
        $option = Option::where('id', $request->option_id)
            ->where('system_option_id', $request->label_id)
            ->first();

        if (!$option) {
            return response()->json(['success' => false, 'message' => 'Option not found.'], 404);
        }

        $option->value = $request->updated_option;
        $option->no_of_pcs_in_cotton = $request->no_of_pcs_in_cotton;
        $option->price_of_cotton = $request->price_of_cotton;
        $option->save();

        return response()->json(['success' => true, 'message' => 'Option updated successfully.']);
    }
    public function fetchOptions(Request $request) {
        $labelId = $request->input('label_id');
        $options = Option::where('system_option_id', $labelId)->get();
        return response()->json(['options' => $options]);
    }
    public function getLables() {
        $options = SystemOption::all();
        return response()->json($options);
    }
}
