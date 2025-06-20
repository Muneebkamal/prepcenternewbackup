<?php

namespace App\Http\Controllers;

use App\Models\DailyInputDetail;
use App\Models\PackingTemplate;
use App\Models\Products;
use App\Models\SystemOption;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use League\Csv\Reader;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Http;

class ProductsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // $sumQty = DailyInputDetail::whereHas('product')
        // ->whereColumn('daily_input_details.fnsku', 'products.fnsku')
        // ->sum('qty');
        // $query = Products::query();
        // $query->with(['dailyInputDetails' => function($query) {
        //     $query->select('fnsku')
        //     ->selectRaw('SUM(qty) as total_qty')
        //     ->groupBy('fnsku');
        // }]);

        // // Check if the 'temporary' parameter is set
        // if ($request->has('temporary') && $request->temporary == 'on') {
        //     $query->where('item', 'LIKE', '%Temporary Product Name%'); // Adjust this condition to match your temporary product naming
        // }
        // $products =$query->get();
        return view('products.index' );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $poly_bag_size = SystemOption::where('type','poly_bag_size')->first();
        $carton_size = SystemOption::where('type','carton_size')->first();
        $shrink_wrap_size = SystemOption::where('type','shrink_wrap_size')->first();
        $label_1 = SystemOption::where('type','label_1')->first();
        $label_2 = SystemOption::where('type','label_1')->first();
        $label_3 = SystemOption::where('type','label_1')->first();
        $weight = SystemOption::where('type','weight')->first();
        return view('products.add-product',get_defined_vars());
    }

    public function importProducts()
    {
        return view('products.import-product');
    }

    public function uploadCSV(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt|max:2048',
        ]);
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filePath = $file->getRealPath();

            if (($handle = fopen($filePath, 'r')) !== false) {
                $header = fgetcsv($handle);

                while (($row = fgetcsv($handle)) !== false) {
                    $data = array_combine($header, $row);
                    $product = Products::where('fnsku',$data['FNSKU'])->first();
                    if($product){
                       
                        // dd($row['SKU']);
                        $product->msku = $product->msku == null? addslashes($data['MSKU']):$product->msku ;
                        $product->item = $product->item == null? addslashes($data['Title']):$product->item ;
                        $product->asin = $product->asin == null? addslashes($data['ASIN']):$product->asin ;
                        $product->save();
                    }else{
                        Products::updateOrCreate(
                            ['fnsku' => $data['FNSKU'] ?? null],
                            [
                                'msku' => $data['MSKU'] ?? null,
                                'item' => $data['Title'] ?? null,
                                'asin' => $data['ASIN'] ?? null,
                                'pack' => 0,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]
                        );
                    }
                    
                }

                fclose($handle);

                return redirect()->back()->with('success', 'Products have been uploaded successfully!');
            } else {
                return redirect()->back()->with('error', 'Unable to open the file.');
            }
        }

        return redirect()->back()->with('error', 'Please select a valid CSV file.');
    }

    public function uploadWalmart(Request $request)
    {
        $request->validate([
            'walmartFile' => 'required|mimes:csv,txt|max:2048',
        ]);

        if ($request->hasFile('walmartFile')) {
            $file = $request->file('walmartFile');
            $filePath = $file->getRealPath();

            if (($handle = fopen($filePath, 'r')) !== false) {
                $header = fgetcsv($handle);

                while (($row = fgetcsv($handle)) !== false) {
                    $data = array_combine($header, $row);
                    $product = Products::where('fnsku',$data['GTIN'])->first();
                    if($product){
                        // dd($row['SKU']);
                        $product->msku = $product->msku == null? addslashes($row['SKU']):$product->msku ;
                        $product->item = $product->item == null? addslashes($row['Item name']):$product->item ;
                        $product->asin = $product->asin == null? addslashes($row['Item ID']):$product->asin ;
                        $product->save();
                    }else{
                        Products::updateOrCreate(
                            ['fnsku' => $data['GTIN'] ?? null],
                            [
                                'msku' => $data['SKU'] ?? null,
                                'item' => $data['Item name'] ?? null,
                                'asin' => $data['Item ID'] ?? null,
                                'pack' => 0,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]
                        );
                    }
                    
                }

                fclose($handle);

                return redirect()->back()->with('success', 'Products have been uploaded successfully!');
            } else {
                return redirect()->back()->with('error', 'Unable to open the file.');
            }
        }

        return redirect()->back()->with('error', 'Please select a valid CSV file.');
    }

    public function importTable()
    {
        return view('products.import-table');
    }

    public function deleteDuplicate()
    {
        $duplicateFnskus = Products::select('fnsku')
        ->groupBy('fnsku')
        ->havingRaw('COUNT(fnsku) > 1')
        ->pluck('fnsku');
        // dd($duplicateFnskus);
        $latestIds = Products::select(DB::raw('MAX(id) as id'))
        ->whereIn('fnsku', $duplicateFnskus)
        ->groupBy('fnsku')
        ->pluck('id');

        $idsToDelete = Products::whereIn('fnsku', $duplicateFnskus)
        ->whereNotIn('id', $latestIds)
        ->pluck('id');
        // dd($idsToDelete);
        Products::whereIn('id', $idsToDelete)->delete();
        return true;
    }

    public function tempProductMerge(Request $request)
    {
        $productIds = $request->input('select_products', []);
        if(sizeof($productIds)>2){
            return response()->json(['error' => 'cannot merge more than two Products'], 404);
        }
        $temp = null;
        $orignal = null;
        foreach($productIds as $id){
            $product1 = Products::where('id',$id)->first();
            
            if($product1->item === 'Temporary Product Name'){
                $temp = $product1;
            }else{
                $orignal = $product1;
            }
        }
        if($temp){
            if($orignal){
                $dailyInputs = DailyInputDetail::where('fnsku', $temp->fnsku)->update([
                    'fnsku'=>$orignal->fnsku
                ]);
                $temp->delete();
                return response()->json(['success' => 'Product merged','tab_id'=>3]);
            }else{
                return response()->json(['error' => 'Temporary Product not found','tab_id'=>3], 404);
            }
        }else{
            return response()->json(['error' => 'Temporary Product not found','tab_id'=>3], 404);
        }

        // $product1 = Products::where('id',$productIds[0])->first();
        // $product2 = Products::where('id',$productIds[1])->first();

        // // dd($product2->dailyInputDetails->pluck('total_qty'));

        // if($product1->item == 'Temporary Product Name'){
        //     if($product2->item != 'Temporary Product Name'){

        //         $dailyInputs = DailyInputDetail::where('fnsku', $product1->fnsku)->get();
        //         foreach($dailyInputs as $dailyInput){
        //             $dailyInput->fnsku = $product2->fnsku;
        //             $dailyInput->save();
        //         }
        //         $product1->delete();
        //         return response()->json(['success' => 'Product merged']);
        //     }else{
        //         return response()->json(['error' => 'Temporary Product not found'], 404);
        //     }
        // }elseif($product1->item != 'Temporary Product Name'){
        //     if($product2->item == 'Temporary Product Name'){
        //         $dailyInputs = DailyInputDetail::where('fnsku', $product2->fnsku)->get();
        //         foreach($dailyInputs as $dailyInput){
        //             $dailyInput->fnsku = $product1->fnsku;
        //             $dailyInput->save();
        //         }
        //         $product2->delete();
        //         return response()->json(['success' => 'Product merged']);
        //     }else{
        //         return response()->json(['error' => 'Temporary Product not found'], 404);
        //     }
        // }else{
        //     return response()->json(['error' => 'Temporary Product not found'], 404);
        // }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $product = new Products;
        $product->item = $request->item;
        $product->msku = $request->msku;
        $product->asin = $request->asin;
        $product->fnsku = $request->fnsku;
        $product->pack = $request->pack;

        // Add new columns
        $product->poly_bag = isset($request->poly_bag)?1:0; // Default to 0 if not provided
        $product->poly_bag_size = $request->poly_bag_size;
        $product->shrink_wrap = isset($request->shrink_wrap )?1:0; // Default to 0 if not provided
        $product->shrink_wrap_size = $request->shrink_wrap_size;
        $product->no_of_pcs_in_carton = $request->no_of_pcs_in_carton;
        $product->carton_size = $request->carton_size;
        $product->label_1 = $request->label_1;
        $product->label_2 = $request->label_2;
        $product->label_3 = $request->label_3;
        $product->packing_link = $request->packing_link;
        $product->ti_in_item_page = $request->ti_in_item_page; 
        $product->weight = $request->weight;       
        $product->bubble_wrap = $request->bubble_wrap;       
        $product->weight_oz = $request->weight_oz;     
        $product->weight_lb = $request->weight_lb;     
        $product->cotton_size_sales = $request->cotton_size_sales; 
        $product->use_orignal_box =  isset($request->use_orignal_box)?1:0;
        $product->packing_note = $request->packing_note;
        $product->save();

        return response()->json(['success'=>'product added Successful!']);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $product = Products::where('id', $id)->orWhere('fnsku',$id)->with('dailyInputDetails')->first(); 
        if ($product && is_null($product->label_1)) {
            $labelOption = SystemOption::where('type', 'label_1')->with('options')->first();
            if ($labelOption && $labelOption->options) {
                $product->label_1 = $labelOption->options[0]->value;
                $product->save(); // ✅ Save the updated label
            }
        } 
        if ($product && is_null($product->label_2) && $product->pack > 1) {
            $labelOption = SystemOption::where('type', 'label_1')->with('options')->first();
            if ($labelOption && $labelOption->options) {
                $product->label_2 = $labelOption->options[1]->value;
                $product->save(); // ✅ Save the updated label
            }
        } 
        $poly_bag_size = SystemOption::where('type','poly_bag_size')->first();
        $carton_size = SystemOption::where('type','carton_size')->first();
        $shrink_wrap_size = SystemOption::where('type','shrink_wrap_size')->first();
        $label_1 = SystemOption::where('type','label_1')->first();
        $label_2 = SystemOption::where('type','label_1')->first();
        $label_3 = SystemOption::where('type','label_1')->first();
        $packingTemplates = PackingTemplate::where('product_id',$product->id)->get();
        if($product->image == null){
            if (empty($product->fnsku)) {
                return view('products.show-product', get_defined_vars());
            }

            $firstChar = $product->fnsku[0];
            $image = false;

            if ($firstChar === 'X') {
                $image = $this->getAmazonImage($product->asin);
            } elseif ($firstChar === '0') {
                $image = $this->getWalmartImage($product->asin);
            }

            if ($image) {
                // dd($image);
                $this->saveProductImage($product, $image);
            }
        }
        // $weight = SystemOption::where('type','weight')->first();
        return view('products.show-product', get_defined_vars());
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $product = Products::where('id', $id)->orWhere('fnsku',$id)->with('dailyInputDetails')->first(); 
        $poly_bag_size = SystemOption::where('type','poly_bag_size')->first();
        $carton_size = SystemOption::where('type','carton_size')->first();
        $shrink_wrap_size = SystemOption::where('type','shrink_wrap_size')->first();
         $bubble_wrap = SystemOption::where('type','bubble_wrap')->first();
        $label_1 = SystemOption::where('type','label_1')->first();
        $label_2 = SystemOption::where('type','label_1')->first();
        $label_3 = SystemOption::where('type','label_1')->first();
        $weight = SystemOption::where('type','weight')->first();
        return view('products.edit-product', get_defined_vars());
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $product = Products::where('id',$id)->first();
        $product->item = $request->item;
        $product->msku = $request->msku;
        $product->asin = $request->asin;
        // $product->fnsku = $request->fnsku;
        $product->pack = $request->pack;  
        // Add new columns
        $product->poly_bag = isset($request->poly_bag)?1:0; // Default to 0 if not provided // Default to 0 if not provided
        $product->poly_bag_size = $request->poly_bag_size;
        $product->shrink_wrap = isset($request->shrink_wrap)?1:0;// Default to 0 if not provided
        $product->shrink_wrap_size = $request->shrink_wrap_size;
        $product->no_of_pcs_in_carton = $request->no_of_pcs_in_carton;
        $product->carton_size = $request->carton_size;
        $product->label_1 = $request->label_1;
        $product->label_2 = $request->label_2;
        $product->label_3 = $request->label_3;
        $product->packing_link = $request->packing_link;
        $product->ti_in_item_page = $request->ti_in_item_page;     
        $product->weight = $request->weight;     
        $product->bubble_wrap = $request->bubble_wrap;   
        $product->weight_oz = $request->weight_oz;     
        $product->weight_lb = $request->weight_lb;     
        $product->cotton_size_sales = $request->cotton_size_sales;  
        $product->use_orignal_box =  isset($request->use_orignal_box)?1:0;
        $product->packing_note = $request->packing_note;
        $product->save();
        // update daily input detail
        DailyInputDetail::where('fnsku',$product->fnsku)->update([
            'pack'=> $product->pack
        ]);
        return response()->json(['success'=>'product updated Successful!']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Products::find($id);
        if (!$product) {
            return response()->json(['error' => 'Product not found.'], 404);
        }

        try {
            $product->delete();
            return response()->json(['success' => 'Product deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Something went wrong.'], 500);
        }
    }
    public function upload(Request $request)
    {
        // Validate the uploaded file
        $request->validate([
            'csv_file' => 'required|mimes:csv,txt|max:2048',
        ]);
    
        $file = $request->file('csv_file');
    
        // Open the file for reading
        $handle = fopen($file->getRealPath(), 'r');
        if (!$handle) {
            return response()->json([
                'error' => 'Unable to open file.'
            ], 400);
        }
    
        // Initialize an array to hold the data
        $data = [];
        
        // Read the CSV file
        while (($row = fgetcsv($handle, 1000, ',')) !== false) {
            // Check if the row is already in UTF-8 encoding, else convert it
            $data[] = array_map(function($field) {
                // Check if the string is valid UTF-8
                if (!mb_check_encoding($field, 'UTF-8')) {
                    // Convert it to UTF-8 from ISO-8859-1 (Latin-1) or Windows-1252 as fallback
                    return mb_convert_encoding($field, 'UTF-8', 'ISO-8859-1');
                }
                return $field;
            }, $row);
        }
        fclose($handle);
    
        // Get the column headers
        $columns = isset($data[0]) ? $data[0] : [];
        $rows = array_slice($data, 1);
    
        // Define required columns for each file type
        $requiredColumnsAmazon = ['MSKU','Title', 'FNSKU','ASIN'];
        $requiredColumnsWalmart = ['Item name', 'GTIN', 'Item ID', 'SKU'];
    
        // Determine the type of file
        $isAmazonFile = !empty(array_intersect($columns, $requiredColumnsAmazon));
        $isWalmartFile = !empty(array_intersect($columns, $requiredColumnsWalmart));
    
        if (!$isAmazonFile && !$isWalmartFile) {
            return response()->json([
                'error' => 'CSV must contain columns for either Amazon (Title, MSKU, ASIN, FNSKU) or Walmart (Item name, GTIN, Item ID, SKU).'
            ], 400);
        }
    
        // Extract required columns based on file type
        $requiredColumns = $isAmazonFile ? $requiredColumnsAmazon : $requiredColumnsWalmart;
    
        // Get column indices
        $columnIndices = array_flip($columns);
        $filteredColumns = array_intersect($columns, $requiredColumns);
    
        // Filter and transform rows
        $filteredRows = array_map(function($row) use ($columnIndices, $requiredColumns) {
            return array_intersect_key($row, array_flip(array_intersect_key($columnIndices, array_flip($requiredColumns))));
        }, $rows);
    
        return response()->json([
            'columns' => $filteredColumns,
            'rows' => $filteredRows,
        ]);
    }
    public function saveColumns(Request $request)
    {
        $request->validate([
            'column_mapping' => 'required|array',
            'rows' => 'required|array',
        ]);
      
        $mapping = $request->input('column_mapping');
        $rows = $request->input('rows');
        // Determine the file type based on the column mapping
        $isAmazonFile = isset($mapping['MSKU']) && isset($mapping['Title']) && isset($mapping['FNSKU']) && isset($mapping['ASIN']);
        $isWalmartFile = isset($mapping['Item name']) && isset($mapping['Item ID']) && isset($mapping['SKU']) && isset($mapping['GTIN']);
    


        // Validate that we have a valid file type
        if (!$isAmazonFile && !$isWalmartFile) {
            return response()->json(['status' => 'error', 'message' => 'Unsupported file format'], 400);
        }


        foreach ($rows as $row) {
            if ($isAmazonFile) {
                $fnsku = $row['FNSKU'];
                $product = Products::where('fnsku',$fnsku)->first();
                if($product){
                    $product->msku = $product->msku == null? addslashes($row['MSKU']):$product->msku ;
                    $product->item = $product->item == null? addslashes($row['Title']):$product->item ;
                    $product->asin = $product->asin == null? addslashes($row['ASIN']):$product->asin ;
                    $product->save();
                }else{
                    DB::table('products')->updateOrInsert(
                        ['fnsku' => $fnsku], // The condition to check if the record exists
                        [
                            'msku' => $row['MSKU'],
                            'item' => $row['Title'],
                            'asin' => $row['ASIN'],
                            // Update the fields only if their current value is NULL
                        ]
                    );
                }
            } elseif ($isWalmartFile) {
                $fnsku = $row['GTIN'];
                $product = Products::where('fnsku',$fnsku)->first();
                if($product){
                    // dd($row['SKU']);
                    $product->msku = $product->msku == null? addslashes($row['SKU']):$product->msku ;
                    $product->item = $product->item == null? addslashes($row['Item name']):$product->item ;
                    $product->asin = $product->asin == null? addslashes($row['Item ID']):$product->asin ;
                    $product->save();
                }else{
                    DB::table('products')->updateOrInsert(
                        ['fnsku' => $fnsku], // The condition to check if the record exists
                        [
                            'item' => addslashes($row['Item name']) ,
                            'msku' => addslashes($row['SKU']),
                            'asin' => addslashes($row['Item ID']) ,
                            // Update the fields only if their current value is NULL
                        ]
                    );
                }
            }
        }

        return response()->json(['status' => 'success']);
    }
    public function getProducts(Request $request){
        // $products = Products::with('dailyInputDetails')->select('products.*');
        $products = Products::query();
        $products->with(['dailyInputDetails' => function($query) {
            $query->select('fnsku')
            ->selectRaw('SUM(qty) as total_qty')
            ->groupBy('fnsku');
        }]);
        
        // Apply the temporary filter if the checkbox is checked
        if ($request->has('temporary') && $request->get('temporary') == 'on') {
            $products->where('item', 'like', '%Temporary Product Name%');
        }
        return DataTables::of($products)
        ->addColumn('id', function($product) {
            static $counter = 1; // Start from 1 and increment for each row
            return $counter++;
        })
        ->editColumn('item', function($product) {
            $printLable = '<a href="'.url('/print-label/' . $product->fnsku).'" target="_blank"><i class=" ri-printer-line"></i></a>';
            // Add tooltip and link to edit product
            return '<div class="truncate d-flex justify-content-between"><a href="' . route('products.show', $product->id) . '" data-toggle="tooltip" title="' . $product->item . '"><small>' . $product->item . '</small></a></div> ';
           
        })
        ->editColumn('msku', function($product) {
            return '<small>' . $product->msku . '</small>';
        })
        ->editColumn('asin', function($product) {
            $firstChar = $product->fnsku[0];
            if ($firstChar === 'X') {
                $link = "https://www.amazon.com/dp/{$product->asin}";
            } elseif ($firstChar === '0' || $firstChar === '1') {
                $link = "https://www.walmart.com/ip/{$product->asin}";
            } else {
                $link = "https://www.amazon.com/dp/{$product->asin}";
            }
            return '<small> <a href="'.$link.'" target="_blank"> ' . $product->asin . '<i class="ri-external-link-line text-primary fs-4"></i></a></small>';
        })
        ->editColumn('fnsku', function($product) {
            $firstChar = $product->fnsku[0];
            if ($firstChar === 'X') {
                $link = "https://www.amazon.com/dp/{$product->asin}";
            } elseif ($firstChar === '0' || $firstChar === '1') {
                $link = "https://www.walmart.com/ip/{$product->asin}";
            } else {
                $link = "https://www.amazon.com/dp/{$product->asin}";
            }
            return '<small> <a href="'.$link.'" target="_blank"> ' . $product->fnsku . '</a></small>';
            return '<small>' . $product->fnsku . '</small>';
        })
        ->editColumn('pack', function($product) {
            // Ensure non-empty pack values are handled
            return '<small>' . ($product->pack <= 0 || $product->pack == '' ? '1' : $product->pack) . '</small>';
        })
        ->editColumn('qty', function($product) {
            // Return total_qty or default to 1
            return '<small>' . ($product->dailyInputDetails->first()->total_qty ?? 1) . '</small>';
        })
        ->editColumn('actions', function($product) {
            $deleteBtn = '<a href="javascript:void(0);" class="text-white btn me-1 btn-danger btn-sm  delete-product" data-id="' . $product->id . '" title="Delete Product">Delete <i class="ri-delete-bin-6-line"></i></a>';
            $printLabel = '<a class="text-white" href="'.url('/print-label/' . $product->fnsku).'" target="_blank"><i class="ri-printer-line"></i></a>';
            $viewButton = '<a href="' . route('products.show', $product->id) . '" class="btn me-1 btn-info btn-sm text-white"><i class="ri-eye-line"></i> View</a>';
            $edit = ' <a href="' . route('products.edit', $product->id) . '" class="text-white me-1 btn btn-info btn-sm"><small>Edit</small></a>';
            return '<div class="d-flex justify-content-between">
                '.$edit.'
                <button class="btn btn-primary text-white btn-sm me-1">' . $printLabel . '</button>
                ' . $viewButton . '
                '. $deleteBtn .'
               
            </div>';
        })
        ->rawColumns(['id','item', 'msku', 'asin', 'fnsku', 'pack', 'qty','actions'])
        ->make(true);
    }
    public function getProductsMerge(Request $request){
        // $products = Products::with('dailyInputDetails')->select('products.*');
        $products = Products::query();
        $products->with(['dailyInputDetails' => function($query) {
            $query->select('fnsku')
            ->selectRaw('SUM(qty) as total_qty')
            ->groupBy('fnsku');
        }]);
        // Apply the temporary filter if the checkbox is checked
       // Check if there is no search input and the temporary checkbox is checked
        if ($request->has('temporary') && $request->get('temporary') == 'on' &&  !isset($request->get('search')['value'])) {
            $products->where('item', 'like', '%Temporary Product Name%');
        }
        return DataTables::of($products)
        ->editColumn('checkbox', function($product) {
            return '<input type="checkbox" class="dt-checkboxes form-check-input" name="select_products" id="product'.$product->id.'" value="'.$product->id.'" onclick="checkBox('.$product->id.')">';
        })
        ->addColumn('id', function($product) {
            static $counter = 1; // Start from 1 and increment for each row
            return $counter++;
        })
        ->editColumn('item', function($product) {
            // Add tooltip and link to edit product
            return '<div class="truncate"><a href="' . route('products.show', $product->id) . '" data-toggle="tooltip" title="' . $product->item . '"><small>' . $product->item . '</small></a></div>';
        })
        ->editColumn('msku', function($product) {
            return '<small>' . $product->msku . '</small>';
        })
        ->editColumn('asin', function($product) {
            $firstChar = $product->fnsku[0];
            if ($firstChar === 'X') {
                $link = "https://www.amazon.com/dp/{$product->asin}";
            } elseif ($firstChar === '0' || $firstChar === '1') {
                $link = "https://www.walmart.com/ip/{$product->asin}";
            } else {
                $link = "https://www.amazon.com/dp/{$product->asin}";
            }
            return '<small> <a href="'.$link.'" target="_blank"> ' . $product->asin . '</a></small>';
            return '<small>' . $product->asin . '</small>';
        })
        ->editColumn('fnsku', function($product) {
            $firstChar = $product->fnsku[0];
            if ($firstChar === 'X') {
                $link = "https://www.amazon.com/dp/{$product->asin}";
            } elseif ($firstChar === '0' || $firstChar === '1') {
                $link = "https://www.walmart.com/ip/{$product->asin}";
            } else {
                $link = "https://www.amazon.com/dp/{$product->asin}";
            }
            return '<small> <a href="'.$link.'" target="_blank"> ' . $product->fnsku . ' <i class="ri-external-link-line text-primary fs-4"></i></a></small>';
            return '<small>' . $product->fnsku . '</small>';
        })
        ->editColumn('pack', function($product) {
            // Ensure non-empty pack values are handled
            return '<small>' . ($product->pack <= 0 || $product->pack == '' ? '1' : $product->pack) . '</small>';
        })
        ->editColumn('qty', function($product) {
            // Return total_qty or default to 1
            return '<small>' . ($product->dailyInputDetails->first()->total_qty ?? 1) . '</small>';
        })
        ->editColumn('actions', function($product) {
            $deleteBtn = '<a href="javascript:void(0);" class="text-white btn btn-danger btn-sm  delete-product" data-id="' . $product->id . '" title="Delete Product">Delete <i class="ri-delete-bin-6-line"></i></a>';
            $printLabel = '<a class="text-white" href="'.url('/print-label/' . $product->fnsku).'" target="_blank"><i class="ri-printer-line"></i></a>';
            $viewButton = '<a href="' . route('products.show', $product->id) . '" class="btn btn-info btn-sm text-white"><i class="ri-eye-line"></i> View</a>';
            return '<div class="d-flex justify-content-between">
                <button class="btn btn-primary text-white btn-sm">' . $printLabel . '</button>
                ' . $viewButton . '
                '.$deleteBtn.'
            </div>';
        })    
        ->rawColumns(['checkbox','id','item', 'msku', 'asin', 'fnsku', 'pack', 'qty','actions'])
        ->make(true);
    }
    public function mergeMenu(Request $request){
        return view('system-setting.merge-products');
    }
    // public function getAmazonImage($asin = null)
    // {
    //     $url = "https://www.amazon.com/dp/{$asin}";
    //     // Add headers to mimic a browser
    //     $options = [
    //         "http" => [
    //             "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:100.0) Gecko/20100101 Firefox/100.0\r\n" .
    //                         "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8\r\n" .
    //                         "Accept-Language: en-US,en;q=0.5\r\n" .
    //                         "Accept-Encoding: gzip, deflate, br\r\n" .
    //                         "Connection: keep-alive\r\n" .
    //                         "Upgrade-Insecure-Requests: 1\r\n"
    //         ]
    //     ];

    //     $context = stream_context_create($options);
    //     // Fetch the page content
    //     $response = @file_get_contents($url, false, $context);
    //     if ($response === false) {
    //         return false;
    //     }
    //     // Check if the content is gzip-compressed and decompress if necessary
    //     if (substr($response, 0, 2) == "\x1f\x8b") { // Gzip signature
    //         $response = gzdecode($response);
    //     } elseif (strpos($http_response_header[0], 'Content-Encoding: br') !== false) { // Brotli encoding
    //         if (function_exists('brotli_uncompress')) {
    //             $response = brotli_uncompress($response);
    //         }
    //     }
    //     // Check for captcha or error page
    //     if (strpos($response, 'Enter the characters you see below') !== false) {
    //         return false;
    //     }
    //     // Try to match the Amazon image URL (more comprehensive regex for multiple formats)
    //     preg_match_all('/https:\/\/m\.media-amazon\.com\/images\/I\/[^"]+\.jpg/', $response, $matches);
    //     if (!empty($matches[0])) {
    //         return $matches[0][0];
    //     }
    //     return false;
    // }
    // public function getWalmartImage($asin)
    // {
    //     $url = "https://www.walmart.com/ip/{$asin}";
    //     // Set context options to add headers
    //     $options = [
    //         "http" => [
    //             "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:100.0) Gecko/20100101 Firefox/100.0\r\n"
    //         ]
    //     ];
    //     $context = stream_context_create($options);
    //     // Fetch the page content with headers
    //     $html = @file_get_contents($url, false, $context);
    //     if ($html === false) {
    //         return false;
    //     }
    //   // Regex to capture Walmart image URLs
    //     preg_match('/https:\/\/i5\.walmartimages\.com\/asr\/[^"]+\.jpeg\?[^"]*/', $html, $matches);
    //     if (isset($matches[0])) {
    //         return $matches[0];
    //     }
    //     return  false;
    // }
 
    // public function getImageProductImages(Request $request)
    // {
    //     $page = $request->input('page', 1); // Default to page 1

    //     // Retrieve 10 products for the current page
    //     $products = Products::whereNull('image')
    //         ->skip(($page - 1) * 5)
    //         ->take(5)
    //         ->get();

    //     // If no products are found, return a "done" message
    //     if ($products->isEmpty()) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'No more products found.',
    //         ]);
    //     }

    //     foreach ($products as $product) {
    //         if (empty($product->fnsku)) {
    //             continue;
    //         }

    //         $firstChar = $product->fnsku[0];
    //         $image = '#';

    //         if ($firstChar === 'X') {
    //             $image = $this->getAmazonImage($product->asin);
    //         } elseif ($firstChar === '0') {
    //             $image = $this->getWalmartImage($product->asin);
    //         }

    //         if ($image !== '#' && $this->isValidImage($image) && $image !=false) {
    //             $uniqueName = $this->generateUniqueName($product->asin, $image);
    //             $filePath = "Products/{$product->id}/{$uniqueName}.jpg";
    //             $directory = public_path("Products/{$product->id}");

    //             if (!file_exists($directory)) {
    //                 mkdir($directory, 0755, true);
    //             }

    //             try {
    //                 file_put_contents(public_path($filePath), file_get_contents($image));
    //                 $product->image = $filePath;
    //                 $product->save();
    //             } catch (\Exception $e) {
    //                 \Log::error("Failed to save image for Product ID {$product->id}: " . $e->getMessage());
    //             }
    //         }
    //     }

    //     return response()->json([
    //         'success' => true,
    //         'message' => "Processed 10 products on page {$page}.",
    //     ]);
    // }
    // // Function to generate a unique image name
    public function generateUniqueName($asin, $imageUrl)
    {
        // Step 1: Get the current timestamp
        $currentTime = time();
    
        // Step 2: Combine ASIN and timestamp
        $uniqueName = $asin . '-' . $currentTime;

        return $uniqueName;
    }
    // // Helper function to validate if the image URL is valid
    // public function isValidImage($url)
    // {
    //     // Ensure the URL is not empty
    //     if (empty($url)) {
    //         return false;
    //     }

    //     // Validate that the URL is properly formed
    //     if (!filter_var($url, FILTER_VALIDATE_URL)) {
    //         return false;
    //     }

    //     // Get headers for the given URL
    //     $headers = @get_headers($url, 1);

    //     // Check if headers were retrieved and the status is 200 (OK)
    //     if ($headers && isset($headers[0]) && strpos($headers[0], '200') !== false) {
    //         // Check if the Content-Type is an image
    //         if (isset($headers['Content-Type']) && strpos($headers['Content-Type'], 'image') !== false) {
    //             return true;
    //         }
    //     }

    //     return false;
    // }

    public function getAmazonImage($asin)
    {
        $url = "https://www.amazon.com/dp/{$asin}";
        return $this->fetchImage($url, '/https:\/\/m\.media-amazon\.com\/images\/I\/[^"]+\.jpg/');
    }

    public function getWalmartImage($asin)
    {
        $url = "https://www.walmart.com/ip/{$asin}";
        return $this->fetchImage($url, '/https:\/\/i5\.walmartimages\.com\/asr\/[^"]+\.jpeg\?[^"]*/');
    }

    // private function fetchImage($url, $regex)
    // {
    //     $maxRetries = 3;
    //     $attempt = 0;

    //     while ($attempt < $maxRetries) {
    //         try {
    //           $response = Http::withHeaders([
    //         'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:100.0) Gecko/20100101 Firefox/100.0',
    //         'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
    //         'Accept-Language' => 'en-US,en;q=0.5',
    //         'Referer' => 'https://www.google.com/',
    //     ])->timeout(10)->get($url);
    
    //             if ($response->successful()) {
    //                 if (preg_match($regex, $response->body(), $matches)) {
    //                     return $matches[0];
    //                 }
    //             }
    //         } catch (\Exception $e) {
    //             \Log::error("Failed to fetch image from {$url} on attempt {$attempt}: " . $e->getMessage());
    //         }

    //         $attempt++;
    //         sleep(1); // Pause before retrying
    //     }

    //     return false;
    // }
    private function fetchImage($url, $regex, $attempt = 0, $maxRetries = 3)
{
    try {
        $response = Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:100.0) Gecko/20100101 Firefox/100.0',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
            'Accept-Language' => 'en-US,en;q=0.5',
            'Referer' => 'https://www.google.com/',
        ])->timeout(10)->get($url);

        if ($response->successful()) {
            $body = $response->body();
            \Log::info("Response Body: " . substr($body, 0, 500)); // Log first 500 chars for debugging

            // Check for CAPTCHA
            if (str_contains($body, 'Robot Check') || str_contains($body, 'Enter the characters you see below')) {
                \Log::warning("Amazon CAPTCHA detected. Aborting.");
                return null;
            }

            // Check if image URL matches
            if (preg_match($regex, $body, $matches)) {
                \Log::info("Matched Image URL: " . $matches[0]);
                return $matches[0];
            }
        }
    } catch (\Exception $e) {
        \Log::error("Failed to fetch image from {$url} on attempt {$attempt}: " . $e->getMessage());
    }

    // Retry logic
    if ($attempt < $maxRetries) {
        sleep(1); // Pause before retrying
        return $this->fetchImage($url, $regex, $attempt + 1, $maxRetries);
    }

    \Log::error("Failed to fetch image after {$maxRetries} attempts.");
    return null;
}


    public function getImageProductImages(Request $request)
    {
        $page = $request->input('page', 1);
        $limit = 5; // Process 5 products at a time

        $products = Products::whereNull('image')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        if ($products->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No more products found.',
            ]);
        }

        foreach ($products as $product) {
            if (empty($product->fnsku)) {
                continue;
            }

            $firstChar = $product->fnsku[0];
            $image = false;

            if ($firstChar === 'X') {
                $image = $this->getAmazonImage($product->asin);
            } elseif ($firstChar === '0') {
                $image = $this->getWalmartImage($product->asin);
            }

            if ($image) {
                // dd($image);
                $this->saveProductImage($product, $image);
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Processed {$limit} products on page {$page}.",
        ]);
    }

    private function saveProductImage($product, $imageUrl)
    {
        $uniqueName = $this->generateUniqueName($product->asin, $imageUrl);
        $filePath = "Products/{$product->id}/{$uniqueName}.jpg";
        $directory = public_path("Products/{$product->id}");

        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        try {
            file_put_contents(public_path($filePath), file_get_contents($imageUrl));
            $product->image = $filePath;
            $product->save();
        } catch (\Exception $e) {
            Log::error("Failed to save image for Product ID {$product->id}: " . $e->getMessage());
        }
    }


    public function printLabel($order_sku, $download = true)
    {
        $product = Products::where('fnsku', $order_sku)->first();

        $barcodeImage = $this->generateBarcode($order_sku); // base64 image
        $productName = $product ? $product->item : 'Unknown Product';
        $fnsku = $order_sku;


        return view('printLable', compact('barcodeImage', 'fnsku', 'productName'));
        
    }
    public function downloadLabel($order_sku){
        $product = Products::where('fnsku', $order_sku)->first();
        $barcodeImage = $this->generateBarcode($order_sku); // returns base64
        $productName = $product ? $product->item : 'Unknown Product';
        $fnsku = $order_sku;
        $pdf = PDF::loadView('printLable', compact('barcodeImage', 'fnsku', 'productName'))
        ->setPaper([0, 0, 300, 150], 'portrait');

        return $pdf->download("label-$fnsku.pdf");
    }
    public function generateBarcode($fnsku)
    {
        $barcodeImage = 'barcodes/' . md5($fnsku) . '.png';
        $barcodePath = public_path($barcodeImage);
        if (!file_exists(public_path('barcodes'))) {
            mkdir(public_path('barcodes'), 0777, true);
        }

        $url = "https://barcode.tec-it.com/barcode.ashx?data=$fnsku&code=Code128&translate-esc=true&dpi=300";
        file_put_contents(public_path($barcodeImage), file_get_contents($url));

        // Download the image and save it locally
        $url = "https://barcode.tec-it.com/barcode.ashx?data=$fnsku&code=Code128&translate-esc=true&dpi=300";
        file_put_contents($barcodePath, file_get_contents($url));

        // Return as base64 so DomPDF can embed it
        $base64Image = 'data:image/png;base64,' . base64_encode(file_get_contents($barcodePath));
        return $base64Image;
    }
    public function getItemsWps(Request $request)
    {
        $page = 1;
        $limit = 50; // Adjust this limit as needed
        $totalProcessed = 0;
        do {
            // API URL with pagination
            $apiUrl = "https://dev.wfsmanage.com/newImageApi.php?page={$page}&limit={$limit}";

            // Fetch data
            $response = Http::get($apiUrl);
            $data = $response->json();

            if (!isset($data['data']) || empty($data['data'])) {
                break; // Stop if no more data
            }

            foreach ($data['data'] as $item) {
                $findProduct = Products::where('fnsku', $item['sku'])
                    ->orWhere('asin', $item['sku'])
                    ->orWhere('msku', $item['sku'])
                    ->first();

                if ($findProduct && !empty($item['image'])) {
                    if(empty($findProduct->image)){
                        $uniqueName = $this->generateUniqueName($findProduct->asin, $item['image']);
                        $filePath = "Products/{$findProduct->id}/{$uniqueName}.jpg";
                        $directory = public_path("Products/{$findProduct->id}");
            
                        if (!file_exists($directory)) {
                            mkdir($directory, 0755, true);
                        }
                        try {
                            file_put_contents(public_path($filePath), file_get_contents($item['image']));
                            $findProduct->image = $filePath;
                            $findProduct->save();
                        } catch (\Exception $e) {
                            \Log::error("Failed to save image for Product ID {$findProduct->id}: " . $e->getMessage());
                        }
                    }
                    
                }
                $totalProcessed++;
            }

            $page++; // Move to next page

        } while (count($data['data']) == $limit); // Continue if full page is returned
        return response()->json(['message' => "Processed $totalProcessed products"]);
    }
    public function savePackingTemplate(Request $request){
        $data = $request->validate([
            'product_id'     => 'required|exists:products,id',
            'template_name'  => 'required|string|max:255',
            'template_type'  => 'required|string',
            'units_per_box'  => 'nullable|integer',
            'box_length'     => 'nullable|numeric',
            'box_width'      => 'nullable|numeric',
            'box_height'     => 'nullable|numeric',
            'box_weight'     => 'nullable|numeric',
            'labeling_by'    => 'nullable|string',
            'original_pack'    => 'nullable|string',
        ]);

        $template = PackingTemplate::create($data);

        return response()->json(['success' => true, 'message' => 'Template saved.', 'template' => $template]);
    }
    public function getTemplates($productId)
    {
        // Fetch all templates for this product (or global ones if needed)
        $templates = PackingTemplate::where('product_id', $productId)->get();

        return response()->json($templates);
    }
    // Show template
    public function showTemplate($id)
    {
        $template = PackingTemplate::findOrFail($id);
        return response()->json($template);
    }

    // Update template
    public function updateTemplate(Request $request, $id)
    {
        $template = PackingTemplate::findOrFail($id);

        $template->update($request->only([
            'template_name', 'template_type', 'units_per_box',
            'box_length', 'box_width', 'box_height',
            'box_weight', 'labeling_by','original_pack'
        ]));

        return response()->json(['message' => 'Updated successfully']);
    }

    // Delete template
    public function destroyTemplate($id)
    {
        $template = PackingTemplate::findOrFail($id);
        $template->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }
    public function checkTempFnsku(Request $request)
    {
        $fnsku = $request->fnsku;
        $exists = Products::where('fnsku', $fnsku)->exists(); // adapt table/column if needed

        return response()->json(['exists' => $exists]);
    }
}
