@extends('layouts.app')

@section('title', 'Edit Product | Prepcenter')

@section('content')

<div class="container-fluid">
                        
    <!-- start page title -->
    {{-- <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Products</h4>

                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Forms</a></li>
                        <li class="breadcrumb-item active">Basic Elements</li>
                    </ol>
                </div>

            </div>
        </div>
    </div> --}}

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <form id="productEdit">
                    @csrf
                    @method('PUT')
                <div class="card-header d-flex justify-content-between align-items-center d-flex">
                    <h4 class="card-title mb-0 flex-grow-1">Edit Product</h4>
                    <div class="d-flex justify-content-end mt-3">
                        <button class="btn btn-danger ms-2" onclick="history.back()">Cancel</button>
                        <button type="submit" class="btn btn-primary ms-2" id="save-button">SAVE</button>
                        <button type="submit" class="btn btn-secondary ms-2" id="save-close-button">SAVE AND CLOSE</button>
                    </div>
                </div><!-- end card header -->
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card" style="width: 100%;">
                                <img 
                                    id="productImage"
                                    src="{{ $product->image ? asset($product->image) : 'https://www.shutterstock.com/shutterstock/photos/2311073121/display_1500/stock-vector-no-photo-thumbnail-graphic-element-no-found-or-available-image-in-the-gallery-or-album-flat-2311073121.jpg'}}" 
                                    class="card-img-top img-thumbnail img-fluid" 
                                    alt="Product Image"
                                    style="max-height: 200px; width: 100%; object-fit: contain;">
                                
                                <div class="card-body text-center">
                                    <input type="file" id="imageUpload" class="form-control d-none" accept="image/*">
                                    
                                    <!-- Button to trigger file selection -->
                                    <button class="btn btn-primary mt-2" onclick="document.getElementById('imageUpload').click();">Choose Image</button>
                                    
                                    <!-- Button to upload image via AJAX -->
                                    <button class="btn btn-success mt-2" id="uploadImageBtn" disabled>Upload Image</button>
                                </div>
                            </div>
                        </div>
                    </div>                   
                    
                        <input type="hidden" name="product_id" id="product_id"  value="{{ $product->id }}">
                        <div class="row">
                            <div class="col-md-12 mt-2">
                                <input type="hidden" name="id" value="{{ $product->id }}">
                                <label for="">ITEM NAME</label>
                                <textarea  id="item" cols="10"  name="item" class="form-control"  rows="2" placeholder="Item / Title Product">{{ $product->item }}</textarea>
                               
                                @error('item')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                           
                            <div class="col-md-4 mt-2">
                                <label for="" class="d-flex justify-content-between align-items-center">
                                    <span>ASIN / ITEM ID</span>
                                    @php
                                        $firstChar = $product->fnsku[0];
                                        if ($firstChar === 'X') {
                                            $link = "https://www.amazon.com/dp/{$product->asin}";
                                        } elseif ($firstChar === '0' || $firstChar === '1') {
                                            $link = "https://www.walmart.com/ip/{$product->asin}";
                                        } else {
                                            $link = "https://www.amazon.com/dp/{$product->asin}";
                                        }
                                    @endphp
                                    <a id="asin-link" href="{{ $link }}" target="_blank" class="text-primary" style="font-size: 0.9rem;">{{ $link }}</a>
                                </label>
                                @php
                                    $editableAsin = is_null($product->asin) || preg_match('/0{3,}/', $product->asin);
                                @endphp
                                @if ($editableAsin)
                                    <input type="text" id="asin" name="asin" value="{{ $product->asin }}" class="form-control" placeholder="ASIN">
                                @else
                                    <input type="hidden" name="asin" value="{{ $product->asin }}">
                                    <input type="text" value="{{ $product->asin }}" class="form-control" placeholder="ASIN" disabled>
                                @endif

                                @error('asin')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                             <div class="col-md-3 mt-2">
                                <label for="">MSKU / SKU</label>
                                <input type="text" name="msku" value="{{ $product->msku }}" class="form-control" placeholder="MSKU">
                                @error('msku')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="col-md-3 mt-2">
                                <label for="">FNSKU / GTIN</label>
                                <input type="text" name="fnsku" value="{{ $product->fnsku }}" class="form-control" placeholder="FNSKU" readonly >
                                @error('fnsku')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="col-md-2 mt-2">
                                <label for="">PACK</label>
                                <input type="number" name="pack" value="{{ $product->pack }}" class="form-control" placeholder="Pack">
                                @error('pack')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>                           
                        </div>
                        <fieldset class="border p-3 mt-5 rounded">
                            <legend class="fw-bold">WFS / FBA Packing Info</legend>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="card" style="border: 1px solid var(--vz-border-color);">
                                        <div class="card-header">
                                            <h5>Packing</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                               
                                                @php
                                                    $polyBagSizeOptions = $poly_bag_size->options->sortBy(function ($option) {
                                                        preg_match_all('/\d+/', $option->value, $matches);
                                                        $numbers = $matches[0] ?? [0]; // Extract numeric values
                                                        return array_map('intval', $numbers); // Convert to integers
                                                    });
                                                @endphp
                                                <!-- Poly Bag Size -->
                                                <div class="col-md-4 mb-3">
                                                    <div class="d-flex justify-content-between">
                                                        <label for="poly_bag_size" class="form-label">Poly Bag Size</label>
                                                        <label for="poly_bag" class="form-label">
                                                        <input type="checkbox" id="poly_bag" name="poly_bag" class="form-check-input"
                                                            {{ $product->poly_bag ? 'checked' : '' }}>
                                                        Poly Bag
                                                        </label>
                                                        <button class="btn btn-sm btn-primary mb-2" type="button" id="addOptionBtn{{ $poly_bag_size->id }}" data-label-type="{{ $poly_bag_size->type }}" data-label-id="{{ $poly_bag_size->id }}">Add New</button>
                                                        
                                                        <div></div>
                                                    </div>

                                                    <select id="poly_bag_size" name="poly_bag_size" class="form-select">
                                                        <option value="">Select Size</option>
                                                    
                                                        @foreach ($polyBagSizeOptions as $option)
                                                            <option value="{{ $option->value }}" {{ $product->poly_bag_size == $option->value ? 'selected' : '' }}>
                                                                {{ $option->value }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                
                                                @php
                                                    $shrinkWrapSizeOptions = $shrink_wrap_size->options->sortBy(function ($option) {
                                                        preg_match_all('/\d+/', $option->value, $matches);
                                                        $numbers = $matches[0] ?? [0]; // Extract numeric values
                                                        return array_map('intval', $numbers); // Convert to integers
                                                    });
                                                @endphp
                                                <!-- Shrink Wrap Size -->
                                                <div class="col-md-4 mb-3">
                                                    <div class="d-flex justify-content-between">
                                                        <label for="shrink_wrap_size" class="form-label">Shrink Wrap Size</label>
                                                        <label for="shrink_wrap" class="form-label">
                                                            <input type="checkbox" id="shrink_wrap" name="shrink_wrap" class="form-check-input"
                                                                {{ $product->shrink_wrap ? 'checked' : '' }}>
                                                            Shrink Wrap
                                                        </label>
                                                        <button class="btn btn-sm btn-primary mb-2" type="button" id="addOptionBtn{{ $shrink_wrap_size->id }}" data-label-type="{{ $shrink_wrap_size->type }}" data-label-id="{{ $shrink_wrap_size->id }}">Add New</button>
                                                       
                                                    </div>

                                                    <select id="shrink_wrap_size" name="shrink_wrap_size" class="form-select">
                                                        <option value="">Select Size</option>
                                                        @foreach ($shrinkWrapSizeOptions as $option)
                                                            <option value="{{ $option->value }}" {{ $product->shrink_wrap_size == $option->value ? 'selected' : '' }}>
                                                                {{ $option->value }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="col-md-4">
                                                   <div class="d-flex justify-content-between">
                                                        <label for="bubble_wrap" class="form-label">Bubble wrap </label>
                                                    <button class="btn btn-sm btn-primary mb-2" type="button" id="addOptionBtn{{ $bubble_wrap->id }}" data-label-type="{{ $bubble_wrap->type }}" data-label-id="{{ $bubble_wrap->id }}">Add New</button>
                                                   </div>
                                                    @php
                                                    $bubleWrapsOptions = $bubble_wrap->options->sortBy(function ($option) {
                                                            preg_match_all('/\d+/', $option->value, $matches);
                                                            $numbers = $matches[0] ?? [0]; // Extract numeric values
                                                            return array_map('intval', $numbers); // Convert to integers
                                                        });
                                                    @endphp
                                                    <select id="bubble_wrap" name="bubble_wrap" class="form-select">
                                                    <option value="">Select Size</option>
                                                    @foreach ($bubleWrapsOptions as $option)
                                                        <option value="{{ $option->value }}" {{ $product->bubble_wrap == $option->value ? 'selected' : '' }}>
                                                            {{ $option->value }}
                                                        </option>
                                                    @endforeach
                                                    </select>

                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card" style="border: 1px solid var(--vz-border-color);">
                                        <div class="card-header">
                                            <h5>Labels</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <!-- Label 1 -->
                                                <div class="col-md-4 mb-3">
                                                    <label for="label_1" class="form-label">Label 1</label>
                                                    @php
                                                        $selectedLabel = $product->label_1 ?? ($label_1->options[0]->value ?? null);
                                                    @endphp

                                                    <select id="label_1" name="label_1" class="form-select">
                                                        <option value="">Select Label</option>
                                                        @foreach ($label_1->options as $option)
                                                            <option value="{{ $option->value }}" {{ $selectedLabel == $option->value ? 'selected' : '' }}>
                                                                {{ $option->value }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <!-- Label 2 -->
                                                <div class="col-md-4 mb-3">
                                                    <label for="label_2" class="form-label">Label 2</label>
                                                    @php
                                                        // If pack is 2 or more and no label_2 selected, default to second option (index 1)
                                                        $autoSelectLabel = null;
                                                        if ($product->pack >= 2 && empty($product->label_2) && isset($label_2->options[1])) {
                                                            $autoSelectLabel = $label_2->options[1]->value;
                                                        } else {
                                                            $autoSelectLabel = $product->label_2;
                                                        }
                                                    @endphp
                                                    <select id="label_2" name="label_2" class="form-select">
                                                        <option value="">Select Label</option>
                                                        @foreach ($label_2->options as $option)
                                                            <option value="{{ $option->value }}" {{ $autoSelectLabel == $option->value ? 'selected' : '' }}>
                                                                {{ $option->value }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <!-- Label 3 -->
                                                <div class="col-md-4 mb-3">
                                                    <label for="label_3" class="form-label">Label 3</label>
                                                    <select id="label_3" name="label_3" class="form-select">
                                                        <option value="">Select Label</option>

                                                        @foreach ($label_3->options as $option)
                                                            <option value="{{ $option->value }}" {{ $product->label_3 == $option->value ? 'selected' : '' }}>
                                                                {{ $option->value }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                            </div>
                                        </div>
                                    </div>

                                    <div class="card" style="border: 1px solid var(--vz-border-color);">
                                        <div class="card-body">
                                            <div class="row">
                                                <!-- Number of Pieces in a Carton -->
                                                <div class="col-md-6 mb-3 d-none">
                                                    {{-- <label for="no_of_pcs_in_carton" class="form-label">No of Pcs In a Carton</label> --}}
                                                    <div class="d-flex justify-content-between">
                                                        <label for="no_of_pcs_in_carton" class="form-label">No of Pcs In a Carton</label>
                                                        <label for="use_orignal_box" class="form-label">
                                                            <input type="checkbox" id="use_orignal_box" name="use_orignal_box" class="form-check-input" {{ $product->use_orignal_box ? 'checked' : '' }}>
                                                            Use Orignal Box
                                                        </label>
                                                        <div></div>
                                                    </div>
                                                    <input type="number" id="no_of_pcs_in_carton" name="no_of_pcs_in_carton" min="1" class="form-control"
                                                        value="{{ $product->no_of_pcs_in_carton }}">
                                                </div>
                                                @php
                                                    $cartonSizeOptions = $carton_size->options->sortBy(function ($option) {
                                                        preg_match_all('/\d+/', $option->value, $matches);
                                                        $numbers = $matches[0] ?? [0]; // Extract numeric values
                                                        return array_map('intval', $numbers); // Convert to integers
                                                    });
                                                @endphp
                                                <!-- Carton Size -->
                                                <div class="col-md-12 mb-3">
                                                    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
                                                    <div class="d-flex justify-content-between">
                                                         <label>Packing Details</label>
                                                    </div>
                                                    <div class="d-flex align-items-center">
                                                        <select id="packingSelect" class="form-select me-2">
                                                            <option value="individual_units">Individual units</option>
                                                            <option value="new_template">Create new packing template</option>
                                                        </select>
                                                        <button id="editPacking" type="button" class="btn btn-outline-secondary" title="Edit Packing Template">
                                                            <i class="fas fa-pencil-alt"></i>
                                                        </button>
                                                    </div>
                                                    <div id="templateDetailsContainer">

                                                    </div>
                                                    
                                                    <!-- Modal structure (initially hidden) -->
                                                    <div class="modal fade" id="newTemplateModal" tabindex="-1" aria-labelledby="newTemplateLabel" aria-hidden="true">
                                                        <div class="modal-dialog modal-lg">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                            <h5 class="modal-title" id="newTemplateLabel">Create New Packing Template</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                    
                                                            <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label for="templateName" class="form-label">Packing template name</label>
                                                                <input type="text" class="form-control" id="templateName">
                                                            </div>
                                                    
                                                            <div class="mb-3">
                                                                <label for="templateType" class="form-label">Template type</label>
                                                                <select class="form-select" id="templateType">
                                                                <option value="case_pack">Case pack</option>
                                                                <option value="individual_units">Individual units</option>
                                                                </select>
                                                            </div>
                                                    
                                                            <div class="row mb-3">
                                                                <div class="col-md-3">
                                                                <label for="unitsPerBox" class="form-label">Units per box</label>
                                                                <input type="number" class="form-control" id="unitsPerBox">
                                                                </div>
                                                                <div class="col-md-3">
                                                                <label class="form-label">Box dimensions (inch)</label>
                                                                <div class="d-flex gap-1">
                                                                    <input type="number" class="form-control" id="boxLength" placeholder="L">
                                                                    <input type="number" class="form-control" id="boxWidth" placeholder="W">
                                                                    <input type="number" class="form-control" id="boxHeight" placeholder="H">
                                                                </div>
                                                                </div>
                                                                <div class="col-md-3">
                                                                <label for="boxWeight" class="form-label">Box weight (lb)</label>
                                                                <input type="number" class="form-control" id="boxWeight" step="0.1">
                                                                </div>
                                                                <div class="col-md-3 d-flex align-items-center mt-4">
                                                                    <input type="checkbox" id="modal_use_orignal_box"
                                                                    name="modal_use_orignal_box" class="form-check-input me-2">
                                                                    <label for="modal_use_orignal_box" class="form-label mb-0">Use Original Box</label>
                                                                </div>
                                                            </div>
                                                    
                                                            <div class="row mb-3">
                                                                <div class="col-md-6">
                                                                <label class="form-label">Prep category</label><br>
                                                                <span class="text-success">No prep needed</span><br>
                                                                <small class="text-muted">Unit labels required</small>
                                                                </div>
                                                                <div class="col-md-6">
                                                                <label for="labelingBy" class="form-label">Who labels units?</label>
                                                                <select class="form-select" id="labelingBy">
                                                                    <option value="seller">By seller</option>
                                                                    <option value="amazon">By Amazon</option>
                                                                </select>
                                                                </div>
                                                            </div>
                                                    
                                                            </div>
                                                    
                                                            <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="button" class="btn btn-primary" id="saveTemplate">Save Template</button>
                                                            </div>
                                                        </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal fade" id="editTemplateModal" tabindex="-1" aria-labelledby="editTemplateLabel" aria-hidden="true">
                                                        <div class="modal-dialog modal-lg">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                            <h5 class="modal-title" id="editTemplateLabel">Edit Packing Template</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                    
                                                            <div class="modal-body">
                                                            <input type="hidden" id="editTemplateId">
                                                    
                                                            <div class="mb-3">
                                                                <label for="editTemplateName" class="form-label">Packing template name</label>
                                                                <input type="text" class="form-control" id="editTemplateName">
                                                            </div>
                                                    
                                                            <div class="mb-3">
                                                                <label for="editTemplateType" class="form-label">Template type</label>
                                                                <select class="form-select" id="editTemplateType">
                                                                <option value="case_pack">Case pack</option>
                                                                <option value="individual_units">Individual units</option>
                                                                </select>
                                                            </div>
                                                    
                                                            <div class="row mb-3">
                                                                <div class="col-md-3">
                                                                <label for="editUnitsPerBox" class="form-label">Units per box</label>
                                                                <input type="number" class="form-control" id="editUnitsPerBox">
                                                                </div>
                                                                <div class="col-md-3">
                                                                <label class="form-label">Box dimensions (inch)</label>
                                                                <div class="d-flex gap-1">
                                                                    <input type="number" class="form-control" id="editBoxLength" placeholder="L">
                                                                    <input type="number" class="form-control" id="editBoxWidth" placeholder="W">
                                                                    <input type="number" class="form-control" id="editBoxHeight" placeholder="H">
                                                                </div>
                                                                </div>
                                                                <div class="col-md-3">
                                                                <label for="editBoxWeight" class="form-label">Box weight (lb)</label>
                                                                <input type="number" class="form-control" id="editBoxWeight" step="0.1">
                                                                </div>
                                                                 <div class="col-md-3 d-flex align-items-center mt-4">
                                                                    <input type="checkbox" id="modal_use_orignal_box_edit"
                                                                    name="modal_use_orignal_box_edit" class="form-check-input me-2">
                                                                    <label for="modal_use_orignal_box" class="form-label mb-0">Use Original Box</label>
                                                                </div>
                                                            </div>
                                                    
                                                            <div class="row mb-3">
                                                                <div class="col-md-6">
                                                                <label class="form-label">Prep category</label><br>
                                                                <span class="text-success">No prep needed</span><br>
                                                                <small class="text-muted">Unit labels required</small>
                                                                </div>
                                                                <div class="col-md-6">
                                                                <label for="editLabelingBy" class="form-label">Who labels units?</label>
                                                                <select class="form-select" id="editLabelingBy">
                                                                    <option value="seller">By seller</option>
                                                                    <option value="amazon">By Amazon</option>
                                                                </select>
                                                                </div>
                                                            </div>
                                                    
                                                            </div>
                                                    
                                                            <div class="modal-footer">
                                                            <button type="button" class="btn btn-danger" id="deleteTemplate">Delete</button>
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="button" class="btn btn-primary" id="updateTemplate">Update Template</button>
                                                            </div>
                                                        </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-3 d-none">
                                                    <div class="d-flex justify-content-between">
                                                        <label for="carton_size" class="form-label">Carton Size</label>
                                                        <button class="btn btn-sm btn-primary mb-2" type="button" id="addOptionBtn{{ $carton_size->id }}" data-label-type="{{ $carton_size->type }}" data-label-id="{{ $carton_size->id }}">Add New</button>
                                                        <div></div>
                                                    </div>
                                                    {{-- <label for="carton_size" class="form-label">Carton Size</label> --}}
                                                    <select id="carton_size" name="carton_size" class="form-select">
                                                        <option value="">Select Size</option>

                                                        @foreach ($cartonSizeOptions as $option)
                                                            <option value="{{ $option->value }}" {{ $product->carton_size == $option->value ? 'selected' : '' }}>
                                                                {{ $option->value }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <!-- Packing Link -->
                                                <div class="col-md-6 d-none">
                                                    <label for="weight" class="form-label">Weight/Pounds</label>
                                                    <input type="text" class="form-control" name="weight" id="weight" value="{{ $product->weight ?: '' }}">
                                                </div>
                                                <!-- Packing Link -->
                                                <div class="col-md-6">
                                                    <label for="packing_link" class="form-label">Packing Link</label>
                                                    <input type="text" name="packing_link" id="packing_link" class="form-control"
                                                        value="{{ $product->packing_link ?: '' }}">
                                                </div>
                                                
                                                <div class="col-md-6">
                                                    <label for="packing_note" class="form-label">Packing Note</label>
                                                    <textarea name="packing_note" id="packing_note" class="form-control">{{ $product->packing_note }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                        <fieldset class="border p-3 mt-5 rounded">
                            <legend class="fw-bold">
                                SELLER Fulfilled / FBM Packing Info                            
                            </legend>
                            <div class="row">
                                <div class="col-md-6">
                                    @php
                                        $cartonSizeOptions = $carton_size->options->sortBy(function ($option) {
                                            preg_match_all('/\d+/', $option->value, $matches);
                                            $numbers = $matches[0] ?? [0]; // Extract numeric values
                                            return array_map('intval', $numbers); // Convert to integers
                                        });
                                    @endphp

                                    <!-- Cotton Size Dropdown -->
                                    <div class="d-flex justify-content-between">
                                        <label for="carton_size" class="form-label">Carton Size</label>
                                    <button class="btn btn-sm btn-primary mb-2" type="button" id="addOptionBtn{{ $carton_size->id }}" data-label-type="{{ $carton_size->type }}" data-label-id="{{ $carton_size->id }}">Add New</button>
                                    </div>
                                    <div></div>
                                    <select id="cotton_size_sales" name="cotton_size_sales"  class="form-select">
                                        <option value="">Select Carton Size</option>
                                        @foreach ($cartonSizeOptions as $option)
                                            <option value="{{ $option->value }}" {{ $product->cotton_size_sales == $option->value ? 'selected' : '' }}>{{ $option->value }}
                                                {{ $option->price_of_cottom ?? 0 }} 
                                                {{ $option->no_of_pcs_in_cotton ?? 0 }} 

                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Weight</label>
                                        <div class="input-group">
                                            <input type="number" id="weight_lb" name="weight_lb" class="form-control" placeholder="Pounds" value="{{ $product->weight_lb ?: '' }}">
                                            <div class="input-group-append input-group-prepend">
                                                <span class="input-group-text">×</span>
                                            </div>
                                            <input type="number" step="0.1" name="weight_oz" id="weight_oz" class="form-control" value="{{ $product->weight_oz ?: '' }}" placeholder="Ounces">
                                        </div>
                                    </div>
                                </div>
                            </div>                            

                        </fieldset>
                        <div class="row mt-1">
                                                    
                                <!-- Save Button -->
                              
                        </div>
                        <input type="hidden" name="action" id="form-action" value="save">
                    </form>
                </div>
            </div>
            <div class="card">
                <div class="card-header align-items-center d-flex">
                    <h4 class="card-title mb-0 flex-grow-1">Products Detail Record</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="example3" class="table table-bordered table-striped align-middle" style="width:100%">
                            <thead>
                                <tr>
                                    <th data-ordering="false">Working Date</th>
                                    <th>Employee Name</th>
                                    <th>Pack</th>
                                    <th>Qty</th>
                                    {{-- <th>Action</th> --}}
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($product->dailyInputDetails as $item)
                                    <tr>
                                        <td><a href="{{ url('edit-daily-input-qty',$item->id) }}">{{$item->dailyInput->date}}</a> </td>
                                         <td> {{ $item->dailyInput && $item->dailyInput->user ? $item->dailyInput->user->name : 'N/A' }}
                                        </td>
                                          <td>{{$item->pack}}</td>
                                           <td>{{$item->qty}}</td>
                                           {{-- <td>
                                            <a data-id="{{ $item->id }}" data-pack="{{ $item->pack }}" data-qty="{{ $item->qty }}" data-name="{{ $product->name }}" data-fnsku="{{ $item->fnsku }}" data-bs-toggle="modal" data-bs-target="#editmodal" class="d-flex btn btn-primary edit-item-btn me-1"><i class="ri-pencil-fill align-bottom me-2"></i> Edit</a>
                                           </td> --}}
                                    </tr>
                                @endforeach
                                
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- end page title -->

</div>

<div id="editmodal" class="modal fade" tabindex="-1" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 overflow-hidden">
            <div class="modal-header p-3">
                <h4 class="card-title mb-0">Update QTY Daily Input FNSKU/GTIN = (<span id="fnsku"></span>)</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="detailEdit">
                    @csrf
                    <div class="row">
                        <div class="col-md-12 mt-2">
                            <p class="fw-bold">
                                Title : <span id="name" class="ms-3"></span>
                            </p>
                        </div>
                        <div class="col-md-12 mt-2">
                            <input type="hidden" id="detail_id" name="detail_id">
                            <label for="">QTY</label>
                            <input type="number" id="edit_qty" name="edit_qty" class="form-control" required>
                        </div>
                        <div class="col-md-12 mt-2">
                            <label for="">Pack</label>
                            <input type="number" id="edit_pack" name="edit_pack" class="form-control" required>
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary mt-3">Update</button>
                        </div>
                    </div>
                </form>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>

<!-- Modal for Adding Option -->
<div class="modal fade" id="addOptionModal" tabindex="-1" aria-labelledby="addOptionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addOptionModalLabel">Add Option</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="addOptionModalBody">
                
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveOption">Save</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
<script>
     $(document).ready(function() {
        // Set form action based on button click
        $('#save-button').on('click', function() {
            $('#form-action').val('save');
        });

        $('#save-close-button').on('click', function() {
            $('#form-action').val('save_and_close');
        });
        $('.edit-item-btn').on('click', function() {
            var pack = $(this).data('pack');
            var qty = $(this).data('qty');
            var name = $(this).data('name');
            var fnsku = $(this).data('fnsku');
            var detail_id = $(this).data('id');

            $('#edit_pack').val(pack);
            $('#edit_qty').val(qty);
            $('#name').text(name);
            $('#fnsku').text(fnsku);
            $('#detail_id').val(detail_id);
        });

        $('#example3').DataTable();
        // $('#resetButton').click(function() {
        //     $('#productEdit')[0].reset(); // [0] is used to access the DOM element
        // });

        $('#productEdit').on('submit', function(e) {
            e.preventDefault(); // Prevent the default form submission

            // Create FormData object
            let formData = new FormData(this);
            let id = $('input[name="id"]').val();
            // Send AJAX request
            $.ajax({
                url: "{{ route('products.update', 'id') }}".replace('id', id),
                type: 'POST',
                data: formData,
                contentType: false, // Tell jQuery not to set content type
                processData: false, // Tell jQuery not to process data
                success: function(response) {
                    if (response.success) {
                        if ($('#form-action').val() === 'save_and_close') {
                            window.location.href = "{{ route('products.index') }}"; // Redirect to index
                        } else {
                            alert('Product saved successfully!'); // Stay on the page
                        }
                    } else {
                        alert('An error occurred.');
                    }
                    console.log(data.success);
                },
                error: function(xhr) {
                    // Handle error response
                    let errors = xhr.responseJSON.errors;
                    
                }
            });
        });
    });
    document.getElementById('weight_oz').addEventListener('blur', function() {
        let value = parseFloat(this.value);
        if (!isNaN(value)) {
            this.value = value.toFixed(1); // Ensures one decimal place
        }
    });

    function appendInputs(labelType, id) {
        const targetContainer = $(`#addOptionModalBody`);
        // Clear previous content (if any)
        targetContainer.empty();
        let inputFields = ``;
        if (labelType === 'poly_bag_size') {
            $(`#addOptionModalLabel`).text('Add Poly Bag Size Option');
            // Poly Bag Size
            inputFields += `
                <div class="d-flex align-items-center gap-2">
                    <input type="number" id="heightoption${id}" class="form-control" style="width: 50px;" placeholder="8">
                    <span class="mx-1">X</span>
                    <input type="number" id="widthoption${id}" class="form-control" style="width: 50px;" placeholder="10">
                </div>
            `;
        } else if (labelType === 'carton_size') {
            $(`#addOptionModalLabel`).text('Add Carton Size Option');
            // Carton Size
            inputFields += `
                <div class="row d-flex align-items-center gap-2">
                    <div class="col-md-6 d-flex align-items-center gap-2">
                        <input type="number" id="heightoption${id}" class="form-control mt-4" style="width: 50px;" placeholder="8" value="">
                        <span class="mx-1 mt-4">X</span>
                        <input type="number" id="widthoption${id}" class="form-control mt-4" style="width: 50px;" placeholder="10" value="">
                        <span class="mx-1 mt-4">X</span>
                        <input type="number" id="weightoption${id}" class="form-control mt-4" style="width: 50px;" placeholder="5" value="">
                    </div>
                    <div class="col-md-12 d-flex align-items-center gap-2">
                        <div class="ms-2">
                            <label for="">No of Carton in Bundle</label>
                            <input type="number" id="no_of_pcs${id}" class="form-control" style="width: 120px;" placeholder="" value="">
                        </div>
                        <div class="ms-2">
                            <label for="">Cost per bundle</label>
                            <input type="number" id="price_of_cotton${id}" class="form-control" style="width: 120px;" placeholder="" value="">
                        </div>
                    </div>
                </div>
            `;
        } else if (labelType === 'shrink_wrap_size') {
            $(`#addOptionModalLabel`).text('Add Shrink Wrap Size Option');
            // Shrink Wrap Size
            inputFields += `
                <div class="d-flex align-items-center gap-2">
                    <input type="number" id="heightoption${id}" class="form-control" style="width: 50px;" placeholder="8">
                    <span class="mx-1">X</span>
                    <input type="number" id="widthoption${id}" class="form-control" style="width: 50px;" placeholder="10">
                </div>
            `;
        } else if (labelType === 'bubble_wrap') {
            $(`#addOptionModalLabel`).text('Add Bubble Wrap Size Option');
            // Shrink Wrap Size
            inputFields += `
                <div class="d-flex align-items-center gap-2">
                    <input type="number" id="heightoption${id}" class="form-control" style="width: 50px;" placeholder="8">
                    <span class="mx-1">X</span>
                    <input type="number" id="widthoption${id}" class="form-control" style="width: 50px;" placeholder="10">
                </div>
            `;
        }  else {
            // Default Input
            inputFields += `
                <input type="text" id="defaultOption${id}" class="form-control mt-2" placeholder="Enter">
            `;
        }
        targetContainer.append(inputFields);
    }

    $(document).on('click', '[id^="addOptionBtn"]', function () {
        const labelId = $(this).data('label-id');
        const labelType = $(this).data('label-type');
        $('#saveOption').data('label-id', labelId).data('label-type', labelType);
        appendInputs(labelType, labelId);
        $('#addOptionModal').modal('show');
    });

    $(document).on('click', '[id^="saveOption"]', function () {
        const labelId = $(this).data('label-id');
        const labelType = $(this).data('label-type');
        // console.log(labelId, labelType);
        // const newOption = $(`#newOption${labelId}`).val().trim();
        let value = '';
        let no_of_pcs_in_cotton = '';
        let price_of_cotton = '';
        if (labelType === 'poly_bag_size') {
            const height = document.getElementById(`heightoption${labelId}`).value;
            const width = document.getElementById(`widthoption${labelId}`).value;
            value = `${height}" X ${width}"`;
        } else if (labelType === 'carton_size') {
            const height = document.getElementById(`heightoption${labelId}`).value;
            const width = document.getElementById(`widthoption${labelId}`).value;
            const weight = document.getElementById(`weightoption${labelId}`).value;
            no_of_pcs_in_cotton = document.getElementById(`no_of_pcs${labelId}`).value;
            price_of_cotton = document.getElementById(`price_of_cotton${labelId}`).value;
            value = `${height}" X ${width}" X ${weight}"`;
        } else if (labelType === 'shrink_wrap_size') {
            const height = document.getElementById(`heightoption${labelId}`).value;
            const width = document.getElementById(`widthoption${labelId}`).value;
            value = `${height}" X ${width}"`;
        }else if (labelType === 'bubble_wrap') {
            const height = document.getElementById(`heightoption${labelId}`).value;
            const width = document.getElementById(`widthoption${labelId}`).value;
            value = `${height}" X ${width}"`;
        }  else {
            value = document.getElementById(`defaultOption${labelId}`).value;
        }
        // Validate input
        if (!value || value.includes('undefined')) {
            alert('Please fill in all fields.');
            return;
        }
        newOption  = value;

        if (newOption === '') {
            alert('Option cannot be empty.');
            return;
        }
        // Send AJAX request to add the new option
        $.ajax({
            url: `{{ url('/options/add') }}`,
            method: 'POST',
            data: {
                label_id: labelId,
                new_option: newOption,
                no_of_pcs_in_cotton: no_of_pcs_in_cotton,
                price_of_cotton: price_of_cotton,
                _token: $('meta[name="csrf-token"]').attr('content'),
            },
            success: function (response) {
                if (response.success) {
                    const newOption = `<option value="${response.option.value}" selected>${response.option.value}</option>`;

                    if (labelType === 'poly_bag_size') {
                        $('#poly_bag_size').append(newOption);
                        $(`#height${labelId}`).val('');
                        $(`#width${labelId}`).val('');
                    } else if (labelType === 'shrink_wrap_size') {
                        $('#shrink_wrap_size').append(newOption);
                        $(`#height${labelId}`).val('');
                        $(`#width${labelId}`).val('');
                    }
                    else if (labelType === 'carton_size') {
                        newOption_a = newOption + ` - ${no_of_pcs_in_cotton} pcs - $${price_of_cotton}`;
                        $('#carton_size').append(newOption_a);
                        $('#cotton_size_sales').append(newOption_a);
                        $(`#height${labelId}`).val('');
                        $(`#width${labelId}`).val('');
                        $(`#weight${labelId}`).val('');
                        $(`#no_of_pcs${labelId}`).val('');
                        $(`#price_of_cotton${labelId}`).val('');
                    } else {
                        $(`#defaultOption${labelId}`).val('');
                    }

                    $('#addOptionModal').modal('hide');
                } else {
                    alert('Failed to add option.');
                }
            },
            error: function (xhr) {
                alert('An error occurred while adding the option.');
                console.error(xhr.responseText);
            },
        });
    });
    var productId = $('#product_id').val(); // Assuming you have a hidden input with the product ID
    $(document).ready(function () {
        
        loadPackingTemplates(productId)
        let selectedFile = null;
        // Live preview when selecting an image
        $('#imageUpload').on('change', function(event) {
            selectedFile = event.target.files[0]; // Store selected file
                alert(selectedFile);
            if (selectedFile) {
                let reader = new FileReader();
                reader.onload = function(e) {
                    $('#productImage').attr('src', e.target.result); // Show live preview
                };
                reader.readAsDataURL(selectedFile);

                $('#uploadImageBtn').prop('disabled', false); // Enable upload button
            }
        });

        // AJAX Image Upload
        $('#uploadImageBtn').on('click', function () {
            if (!selectedFile) {
                alert("Please select an image first.");
                return;
            }

            let formData = new FormData();
            formData.append('image', selectedFile);
            formData.append('_token', '{{ csrf_token() }}'); // Laravel CSRF token

            $.ajax({
                url: '{{ route("product.update.image", ["id" => $product->id]) }}', // Update route
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    alert("Image updated successfully!");
                    $('#uploadImageBtn').prop('disabled', true); // Disable after upload
                },
                error: function (xhr) {
                    alert("Image upload failed! Please try again.");
                }
            });
        });
        $(document).ready(function() {
        $('#packingSelect').on('change', function() {
            if ($(this).val() === 'new_template') {
                var modal = new bootstrap.Modal(document.getElementById('newTemplateModal'));
                modal.show();
            }
        });

        $('#saveTemplate').on('click', function() {
                const name = $('#templateName').val();
                const units = $('#unitsPerBox').val();

                // Here you can do an AJAX call to save the new template
                console.log('Saving template:', name, units);

                // Close modal after saving
                $('#newTemplateModal').modal('hide');
            });
        });
        
    });
    $('#saveTemplate').on('click', function () {
        $.ajax({
            url: '{{ route("packing-template.store") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                product_id: $('#product_id').val(), // Make sure this is set in a hidden input
                template_name: $('#templateName').val(),
                template_type: $('#templateType').val(),
                units_per_box: $('#unitsPerBox').val(),
                box_length: $('#boxLength').val(),
                box_width: $('#boxWidth').val(),
                box_height: $('#boxHeight').val(),
                box_weight: $('#boxWeight').val(),
                labeling_by: $('#labelingBy').val(),
                original_pack: $('#modal_use_orignal_box').is(':checked') ? 1 : 0
            },
            success: function (response) {
                alert(response.message);
                $('#newTemplateModal').modal('hide');
                loadPackingTemplates(productId)
                // Reload selectbox or template list if needed
            },
            error: function (xhr) {
                alert('Error saving template');
                console.log(xhr.responseText);
            }
        });
    });
    function loadPackingTemplates(productId) {
        $.ajax({
            url: `/packing-templates/${productId}`,
            type: 'GET',
            success: function(templates) {
                const $select = $('#packingSelect');
                $select.empty();
                const $container = $('#templateDetailsContainer');
                $container.empty();

                // Create table structure
                let table = `
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Template</th>
                                <th>Dimensions (L×W×H)</th>
                                <th>Weight</th>
                                <th>Use Original Pack</th>
                            </tr>
                        </thead>
                        <tbody>
                `;

                templates.forEach(function(template) {
                    table += `
                        <tr>
                            <td>${template.template_name}</td>
                            <td>${template.box_length} × ${template.box_width} × ${template.box_height ?? 'N/A'}</td>
                            <td>${template.box_weight}</td>
                            <td>${template.original_pack ? 'Yes' : 'No'}</td>
                        </tr>
                    `;

                    // Add template to select dropdown
                    $select.append(
                        $('<option>', {
                            value: template.id,
                            text: template.template_name
                        })
                    );
                });

                table += `</tbody></table>`;
                $container.append(table);

                // Always add individual_units
                $select.append(`<option value="individual_units">Individual units</option>`);

                // Show 'Create new packing template' if less than 3
                if (templates.length < 3) {
                    $select.append(`<option value="new_template">Create new packing template</option>`);
                }
            },
            error: function(err) {
                console.error('Error loading templates:', err);
            }
        });
    }


    $(document).ready(function () {
        // Click on edit button
        $('#editPacking').on('click', function () {
            let selectedVal = $('#packingSelect').val();
            // Skip if it's a fixed value
            if (selectedVal === 'new_template' || selectedVal === 'individual_units') {
                return;
            }
            // Fetch template data via AJAX
            $.ajax({
                url: '/packing-template/' + selectedVal, // GET /packing-template/{id}
                type: 'GET',
                success: function (data) {
                    // Fill modal fields
                    $('#editTemplateId').val(data.id);
                    $('#editTemplateName').val(data.template_name);
                    $('#editTemplateType').val(data.template_type);
                    $('#editUnitsPerBox').val(data.units_per_box);
                    $('#editBoxLength').val(data.box_length);
                    $('#editBoxWidth').val(data.box_width);
                    $('#editBoxHeight').val(data.box_height);
                    $('#editBoxWeight').val(data.box_weight);
                    $('#editLabelingBy').val(data.labeling_by);
                    if (data.original_pack == 1) {
                        $('#modal_use_orignal_box_edit').prop('checked', true);
                    } else {
                        $('#modal_use_orignal_box_edit').prop('checked', false);
                    }

                    // Show edit modal
                    $('#editTemplateModal').modal('show');
                },
                error: function () {
                    alert('Failed to load packing template.');
                }
            });
        });
        // Save/update template
        $('#updateTemplate').on('click', function () {
            let id = $('#editTemplateId').val();
            let payload = {
                template_name: $('#editTemplateName').val(),
                template_type: $('#editTemplateType').val(),
                units_per_box: $('#editUnitsPerBox').val(),
                box_length: $('#editBoxLength').val(),
                box_width: $('#editBoxWidth').val(),
                box_height: $('#editBoxHeight').val(),
                box_weight: $('#editBoxWeight').val(),
                labeling_by: $('#editLabelingBy').val(),
                original_pack: $('#modal_use_orignal_box_edit').is(':checked') ? 1 : 0,
                _token: '{{ csrf_token() }}',
            };

            $.ajax({
                url: '/packing-template/' + id,
                type: 'PUT',
                data: payload,
                success: function (response) {
                    alert('Template updated successfully!');
                    $('#editTemplateModal').modal('hide');
                    loadPackingTemplates(productId)
                    // Optional: Refresh select options
                },
                error: function () {
                    alert('Error updating template.');
                }
            });
        });
        // Delete template
        $('#deleteTemplate').on('click', function () {
            if (!confirm("Are you sure you want to delete this template?")) return;

            let id = $('#editTemplateId').val();

            $.ajax({
                url: '/packing-template/' + id,
                type: 'DELETE',
                data:{
                    _token: '{{ csrf_token() }}',
                },
                success: function () {
                    alert('Template deleted!');
                    $('#editTemplateModal').modal('hide');
                    // Optional: remove from select dropdown
                    $('#packingSelect option[value="' + id + '"]').remove();
                    loadPackingTemplates(productId)
                },
                error: function () {
                    alert('Failed to delete template.');
                }
            });
        });
    });
    function updateTemplateName() {
        const units = document.getElementById('unitsPerBox').value;
        const type = document.getElementById('templateType').value;
        const nameField = document.getElementById('templateName');

        if (!units || units <= 0) {
            nameField.value = '';
            return;
        }

        if (type === 'case_pack') {
            nameField.value = `${units} case pack`;
        } else if (type === 'individual_units') {
            nameField.value = `${units} per case`;
        }
    }

    document.getElementById('unitsPerBox').addEventListener('input', updateTemplateName);
    document.getElementById('templateType').addEventListener('change', updateTemplateName);
    function updateTemplateNameEdit() {
        const units = document.getElementById('editUnitsPerBox').value;
        const type = document.getElementById('editTemplateType').value;
        const nameField = document.getElementById('editTemplateName');

        if (!units || units <= 0) {
            nameField.value = '';
            return;
        }

        if (type === 'case_pack') {
            nameField.value = `${units} case pack`;
        } else if (type === 'individual_units') {
            nameField.value = `${units} per case`;
        }
    }

    document.getElementById('editUnitsPerBox').addEventListener('input', updateTemplateNameEdit);
    document.getElementById('editTemplateType').addEventListener('change', updateTemplateNameEdit);

</script>
@endsection