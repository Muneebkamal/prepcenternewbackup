@extends('layouts.app')

@section('title', 'Shipping Plan | Prepcenter')

@section('styles')
<style>
    .product-img-wrapper:hover .edit-image-btn {
    display: inline-block;
}

</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row d-flex align-items-center">
        <div class="col-md-8 ">
            <h1 class="mb-3 d-flex ">
                Shipping Plan - {{ $shippingPlan->custom_id }}
                <span id="shipping-name-wrapper" class="d-flex ms-5 align-items-center gap-2 float-left">
                <!-- Display text -->
                @php
                    $displayName = $shippingPlan->name ?? ''; // Adjust as per your model
                @endphp
                <span id="shipping-name-text"> {{ $displayName ? '- ' . $displayName : '- Name' }}</span>

                <!-- Hidden input -->
                <input type="text" id="shipping-name-input" 
                    class="form-control d-none sync-input" 
                    value="{{ $shippingPlan->name }}" 
                    style="max-width: 250px;">
                <!-- Buttons -->
                <button id="edit-btn-name" class="btn btn-sm btn-primary">Edit</button>
                <button id="save-btn-name" class="btn btn-sm btn-success d-none">Save</button>
                <button id="cancel-btn-name" class="btn btn-sm btn-secondary d-none">Cancel</button>
            </span>
            </h1>

            
        </div>
        <div class="col-md-4 text-end">
            <h6>Created By: {{ $shippingPlan->creator?->name ?? 'N/A' }}</h6>
            <h6>Created At: {{ $shippingPlan->created_at->format('Y-m-d h:i A') }}</h6>
        </div>
    </div>
    <input type="hidden" name="ship_plan_id" id="ship_plan_id" value="{{ $shippingPlan->custom_id }}">
    <h2 class="mb-4">Send to Amazon</h2>
    <div class="card mb-3">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0 text-end">Step 1: Choose inventory to send</h5>
            <span>
                @if(
                (empty($shippingPlan->shipment_fee) || $shippingPlan->shipment_fee == 0) &&
                (empty($shippingPlan->handling_cost) || $shippingPlan->handling_cost == 0)
            )
                <button type="button" class="btn btn-danger" id="deleteShippingPlan" onclick="deleteShippingPlan({{ $shippingPlan->id }})">
                    Delete
                </button>
            @endif
            <button type="button" 
                    class="btn btn-success" 
                    onclick="saveShippingPlanData({{ $shippingPlan->id }}, false)">
                Save
            </button>

            <button type="button" 
                    class="btn btn-primary" 
                    onclick="saveShippingPlanData({{ $shippingPlan->id }}, true)">
                Save & Close
            </button>
        </div>
        <ul class="nav nav-tabs my-3">
          <li class="nav-item">
            <a class="nav-link active" href="#">All FBA SKUs</a>
          </li>
        </ul>
        <div class="row g-3">
            {{-- Row 1: Shipment Info --}}
            <div class="col-md-4">
                <div class="card p-3 h-100">
                    <h5 class="mb-3">Shipment Info</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Shipment Name</label>
                            <input type="text" id="shipment_name" class="form-control auto-save sync-input" name="shipment_name" 
                                value="{{ $shippingPlan->shipment_name ?? '' }}" data-id="{{ $shippingPlan->id }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Amazon ID</label>
                            <input type="text" id="amazon_id" class="form-control auto-save" name="amazon_id" 
                                value="{{ $shippingPlan->amazon_id ?? '' }}" data-id="{{ $shippingPlan->id }}">
                        </div>

                        <div class="col-md-6 mt-3">
                            <label class="form-label">Amazon Reference ID</label>
                            <input type="text" id="amazon_reference_id" class="form-control auto-save" name="amazon_reference_id" 
                                value="{{ $shippingPlan->amazon_reference_id ?? '' }}" data-id="{{ $shippingPlan->id }}">
                        </div>
                        <div class="col-md-6 mt-3">
                            <label class="form-label">Fulfillment capability</label>
                            <select class="form-select" id="fullment_capability" name="fullment_capability" id="fullment_capability">
                                <option value="full_fillment" value="{{ $shippingPlan->full_fillment??'' }}">
                                    Standard Fulfillment by Amazon
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mt-3">
                            <label class="form-label">Marketplace destination</label>
                            <input type="text" class="form-control" value="United States" disabled>
                            <input type="hidden" name="market_place" value="us">
                        </div>
                    
                    </div>
                </div>
            </div>
            {{-- Row 2: Ship From / Ship To --}}
            <div class="col-md-3">
                <div class="card p-3 h-100">
                    <h5 class="mb-3">Shipping Address</h5>
                    <p>
                        <strong>Ship From:</strong> Favorite Commodities Inc, 3320 LAWSON BLVD, OCEANSIDE, NY, 11572, US 
                        <a href="#" class="small">Change</a>
                    </p>
                    <div class="row">
                        <div class="col-md-12">
                            <label class="form-label">Ship To</label>
                            <input type="text" id="ship_to" class="form-control auto-save" name="ship_to" 
                                value="{{ $shippingPlan->ship_to ?? '' }}" data-id="{{ $shippingPlan->id }}">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Method & Carrier</label>
                            <input type="text" id="method_carrier" class="form-control auto-save" name="method_carrier" 
                                value="{{ $shippingPlan->method_carrier ?? '' }}" data-id="{{ $shippingPlan->id }}">
                        </div>
                    </div>
                </div>
            </div>
            {{-- Row 4: Totals Table --}}
            <div class="col-md-5">
                <div class="card p-3 h-100">
                    <h5 class="mb-3">Shipment Totals</h5>
                    <table class="table table-bordered align-middle mb-0">
                        <tbody>
                            <tr>
                                <td><strong>Total Units</strong></td>
                                <td id="footerUnits">0</td>
                            </tr>
                            <tr>
                                <td><strong>Total Boxes</strong></td>
                                <td id="footerBoxes">0</td>
                            </tr>
                            <tr>
                                <td><strong>Total Weight (lb)</strong></td>
                                <td id="footerTotalWeight">0</td>
                            </tr>
                            
                            <tr>
                                <td><strong>Handling Fee</strong></td>
                                <td>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" name="handling_fee" id="handlingFee" 
                                            class="form-control" step="0.01" 
                                            value="{{ number_format($shippingPlan->handling_cost, 2, '.', '') }}">
                                    </div>
                                    <div class="small text-muted">Per Item: <span id="perItemHandling">0.00</span></div>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Shipment Cost</strong></td>
                                <td>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" id="shippingCost" name="shipment_cost" 
                                            class="form-control" step="0.01" 
                                            value="{{ number_format($shippingPlan->shipment_fee, 2, '.', '') }}">
                                    </div>
                                    <div class="small text-muted">Per Item: <span id="perItemShipping">0.00</span></div>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Total Charges</strong></td>
                                <td><strong id="totalCost">0.00</strong></td>
                            </tr>
                            <tr>
                                <td><strong>Cost per Unit</strong></td>
                                <td><strong id="totalPerItem">0.00</strong></td>
                            </tr>
                            <tr>
                                <td><strong>Cost per Lb</strong></td>
                                <td><strong id="costPerLb"><span id="totalPerItem">0.00</span></strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
      </div>
    </div>
    <div class="row mt-2 mb-2">
        <div class="col-md-1"></div>
        <div class="col-md-8">
            <div class="row">
                <div class="col-md-4"></div>
                <div class="col-md-8 text-end">
                    <div class="input-group text-end">
                        <select class="form-select select2" id="skuSearchInput" style="width: 100%;"></select>
                        
                    </div>
                </div>
            </div>
        </div>

    </div>
    <!-- SKU Details Table -->
    <div class="card">
        

        <div class="card-header d-flex justify-content-between align-items-center d-none">
            <div>
                    <strong>Lower your storage fees by up to 84%</strong>
                    <p>
                        Amazon Warehousing and Distribution (AWD) is a low-cost bulk storage solution for your inventory. Get additional savings on AWD storage, processing, and transportation when your shipment is Amazon managed. Re
                    </p>
            </div>
            <div>
                    <a href="#" class="text-primary">Create AWD Shipment</a>
            </div>
        </div>
        <div class="card-body">
        <h5 class="mb-3">SKU Details</h5>
        <div class="table-responsive">
            <table class="table table-bordered align-middle" id="productTable">
            <thead class="table-light">
                <tr>
                <th>#No</th>
                <th>SKU</th>
                <th>Packing Details</th>
                <th>Units per box</th>
                <th>Box dimensions (inch)</th>
                <th>Use Original Box </th>
                <th>Box weight (lb) </th>
                {{-- <th>Total Cost </th> --}}
                <th>Total Weight </th>
                <th>Quantity to Send</th>
                <th>Actions</th>
                </tr>
            </thead>
            <tbody>

            </tbody>
            </table>
        </div>

        <div class="text-end ">
            <h6 class="d-none">Total prep and labeling fees: <strong>$0.00</strong></h6>
            <button class="btn btn-primary d-none" onclick="saveShipingPlan()">Confirm and continue</button>
            <button type="button" class="btn btn-primary btn-sm" onclick="printProductTable()">
                Export Excel
            </button>
            <button type="button" class="btn btn-primary btn-sm" onclick="exportShippingPlanExcel()">
                Manifest Format 
            </button>
        </div>
        </div>
    </div>
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
                <input type="hidden" name="prodcutId" id="prodcutId">

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
                        <input type="number" class="form-control" id="unitsPerBox" focus>
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
                {{-- <div class="row">
                    <div class="col-md-3">
                        <label for="labelingBy" class="form-label">Weight Per Case</label>
                        <input class="form-control" id="weightPerCase" type="number" step="0.01">
                    </div>
                    <div class="col-md-3">
                        <label for="labelingBy" class="form-label">Total Weight</label>
                        <input class="form-control" id="totalWeight" type="number" step="0.01">
                    </div>
                </div> --}}

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
                <input type="hidden" id="productIdEdit">

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
                {{-- <div class="row">
                    <div class="col-md-3">
                        <label for="labelingBy" class="form-label">Weight Per Case</label>
                        <input class="form-control" id="weightPerCase" type="number" step="0.01">
                    </div>
                    <div class="col-md-3">
                        <label for="labelingBy" class="form-label">Total Weight</label>
                        <input class="form-control" id="totalWeight" type="number" step="0.01">
                    </div>
                </div> --}}

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
<!-- Move to Another Shipping Plan Modal -->
<div class="modal fade" id="movePlanModal" tabindex="-1" aria-labelledby="movePlanModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="movePlanModalLabel">Move to Another Shipping Plan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
            <input type="hidden" name="itemIdNew" id="itemIdNew">
            <input type="hidden" name="productIdMove" id="productIdMove">
          <label for="shippingPlanSelect" class="form-label">Select Shipping Plan</label>
          <select class="form-select" id="shippingPlanSelect">
            <option value="">Loading...</option>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="confirmMovePlanBtn">Move</button>
      </div>
    </div>
  </div>
</div>
<!-- 🔹 Image Upload Modal -->
<div class="modal fade" id="editImageModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Product Image</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center">
        <img id="imagePreview" src="" class="img-fluid mb-3" style="max-height:200px; object-fit:contain;">
        <input type="file" id="imageInput" accept="image/*" class="form-control">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="saveImageBtn">Save</button>
      </div>
    </div>
  </div>
</div>

@endsection
@section('script')
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="{{ asset('js/jquery.min.js') }}"></script>
<script src="{{ asset('js/datatables.min.js') }}"></script>
<script src="{{ asset('js/dataTables.bootstrap5.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script>
    var totalWEightGb = 0.0;
    const assetBaseUrl = "{{ asset('') }}/";
    const ship_plan_id_g = $(`#ship_plan_id`).val();
    $(document).ready(function () {
       
        getOldITems(ship_plan_id_g);
        setInterval(() => {
            calculate();
        }, 1000);
        $('#skuSearchInput').select2({
            placeholder: 'Search SKU, ASIN, or Name...',
            allowClear: true,
            width: '100%',
            multiple: true,
            ajax: {
                url: '{{ url("/fetch-items") }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return { search: params.term };
                },
                processResults: function (data) {
                    hasResults = data.length > 0;
                    return {
                        results: data.map(function (item) {
                            return {
                                id: item.id,
                                text: `${item.item} | ${item.asin} | ${item.msku} | ${item.fnsku} | ${item.pack}`,
                                full: item // pass all fields for later use
                            };
                        })
                    };
                },
                cache: true
            },
            minimumInputLength: 1,
            tags: true, // Enable tag creation
            createTag: function (params) {
                var term = $.trim(params.term);

                // If the term is empty, do not create a tag
                if (term === '') {
                    return null;
                }

                // If results were found, prevent tag creation
                if (hasResults) {
                    return null; // Do not allow tag creation if data is found
                }

                // If no results found, allow tag creation
                return {
                    id: term, // New value when item is not found
                    text: `Add new item: ${term}`, // Text that shows "Add new item"
                    newTag: true // Mark it as a new tag
                };
            },
            insertTag: function (data, tag) {
                if (tag.newTag) {
                    // Insert the new tag at the beginning of the results list only if it's a new tag
                    data.unshift(tag);
                }
            }
        }).on('select2:select', function (e) {
            const item = e.params.data.full;
            // Check if already added
            if ($('#product-row-' + item.id).length) {
                alert("Product already added.");
                return;
            }
            let rowNumber = $('#productTable tbody tr').length + 1;
            let product_id = null;
            let template_name = '';
            let template_type = '';
            let units_per_box = '';
            let box_length = '';
            let box_width = '';
            let box_height = '';
            let box_weight = '';
            let labeling_by = '';
            let original_pack = '';
            let box_dimensions = '';
            // Append to table
            $('#productTable tbody').append(`
                <tr id="product-row-${item.id}">
                    <td>
                        <span class="row-no">${rowNumber}</span>
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <img src="${item.image 
                                ? `${assetBaseUrl}${item.image}` 
                                : 'https://www.shutterstock.com/shutterstock/photos/2311073121/display_1500/stock-vector-no-photo-thumbnail-graphic-element-no-found-or-available-image-in-the-gallery-or-album-flat-2311073121.jpg'}" 
                                alt="${item.item}" 
                                class="img-thumbnail" 
                                style="width: 50px; height: 50px; object-fit: contain;margin-bottom:auto;">
                            <div>
                                <strong>${item.item}</strong><br>
                                SKU: ${item.msku}<br>
                                ASIN: ${item.asin}<br>
                                FNSKU: ${item.fnsku}<br>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="mb-3">
                            <label for="templateType" class="form-label">Template type</label>
                            <select class="form-select" id="templateType${item.id}" name="templateType${item.id}" onchange="createNewTemp(${item.id})">
                            <option value="case_pack">Case pack</option>
                            <option value="individual_units">Individual units</option>
                            </select>
                            <button id="editPacking${item.id}" onclick="editTemplate(${item.id})" type="button" class="btn btn-outline-secondary" title="Edit Packing Template">
                                Edit
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                        </div>    
                    </td>
                    <td>
                       <span id="perPiece${item.id}"></span>
                    </td>
                    <td>
                        <span id="dimensions${item.id}"></span>
                    </td>
                    <td>
                        <span id="original_pack${item.id}"></span>
                    </td>
                    <td>
                        <span id="box_weight${item.id}"></span>
                    </td>
                    
                    <td>
                        <span id="totalWeight${item.id}"></span>
                        
                    </td>
                    
                    
                    <td>
                        <div class="row mb-2">
                            <div class="col-6">
                                <label class="form-label boxlable${item.id}">Boxes</label>
                                <input type="number" class="form-control form-control-sm boxlable${item.id}" min="0" name="boxes[${item.id}]" data-id="${item.id}" onchange="saveProductData(${item.id})">
                            </div>
                            <div class="col-6">
                                <label class="form-label">Units</label>
                                <input type="number" class="form-control form-control-sm" min="0" name="units[${item.id}]" readonly>
                            </div>
                        </div>
                        <div class="d-none">
                            <label class="form-label">Expiration</label>
                            <input type="date" class="form-control" name="expiration[${item.id}]">
                        </div>
                    </td>
                    <td>
                        <button type="button" class="btn btn-success btn-sm" onclick="saveProductData(${item.id})">
                            Save
                        </button>
                    </td>

                </tr>
            `);
            loadPackingTemplates(item.id)
            // Optional: clear selection after append
            $('#skuSearchInput').val(null).trigger('change');
            setTimeout(() => {
                saveProductData(item.id); // Save immediately after adding
            }, 1000);
        });
    });
    function searchSku() {
        const selectedId = $('#skuSearchInput').val();
        const selectedText = $('#skuSearchInput').select2('data')[0]?.text;
        
        if (!selectedId) {
            alert("Please select a product to search.");
            return;
        }

        console.log("Selected ID:", selectedId);
        console.log("Selected Text:", selectedText);

        // 👉 TODO: perform your action here (filter table, redirect, etc.)
    }
    function loadPackingTemplates(productId,selectedVal=0) {
        // console.log(selectedVal);
        $.ajax({
            url: `/packing-templates/${productId}`,
            type: 'GET',
            success: function(templates) {
                const $select = $('#templateType' + productId);
                $select.empty();
                templates.forEach(function(template) {
                    // Add template to select dropdown
                    $select.append(
                        $('<option>', {
                            value: template.id,
                            text: template.template_name,
                            selected: selectedVal != 0 && selectedVal == template.id // select if matches selectedVal
                        })
                    );
                });
                // Always add individual_units
                $select.append(`<option value="individual_units" ${selectedVal==0?'selected':''}>Individual units</option>`);
                // Show 'Create new packing template' if less than 3
                if (templates.length < 3) {
                    $select.append(`<option value="new_template">Create new packing template</option>`);
                }
                // If at least one template exists, apply its data immediately
                if(selectedVal == 0){
                    if (templates.length > 0) {
                        // const firstTemplate = templates[0];
                        // applyTemplateDataToRow(firstTemplate, productId); // 👈 Call here
                    }
                }else{
                    if (templates.length > 0) {
                        const firstTemplate = templates.find(t => t.id == selectedVal) || templates[0];
                        applyTemplateDataToRow(firstTemplate, productId); // 👈 Call here
                    }
                }

            },
            error: function(err) {
                console.error('Error loading templates:', err);
            }
        });
    }
    function editTemplate(productId) {
        let selectedVal = $('#templateType'+productId).val();
        $('#productIdEdit').val(productId);
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
    }
    function createNewTemp(productId) {
        let selectedVal = $('#templateType' + productId).val();
        if (selectedVal === 'new_template') {
            $('#prodcutId').val(productId);
            $('#newTemplateModal')
            .off('shown.bs.modal') // prevent multiple bindings
            .on('shown.bs.modal', function () {
                $('#unitsPerBox').trigger('focus');
            })
            .modal('show');
        }else if (selectedVal === 'individual_units') {
            $(`input[name="units[${productId}]"]`).prop("readonly", false);
            $(`#perPiece${productId}`).text('');
            $(`#dimensions${productId}`).text('');
            $(`#original_pack${productId}`).text('--');
            $(`#box_weight${productId}`).text('');

            $(`#totalWeight${productId}`).text('0.0');
            $('.boxlable'+productId).addClass('d-none');
            $(`input[name="units[${productId}]"]`).val(0);
            setTimeout(() => {
               saveProductData(productId)
            }, 1000); // Delay to ensure select2 has updated
        }else{
            loadPackingTemplates(productId,selectedVal);
            $(`input[name="units[${productId}]"]`).prop("readonly", true);
            $('.boxlable'+productId).removeClass('d-none');
            setTimeout(() => {
               saveProductData(productId)
            }, 1000); // Delay to ensure select2 has updated
        }
    }
    $('#packingTemplateModal').on('shown.bs.modal', function () {
        $('#unitsPerBox').trigger('focus');
    });
    $('#saveTemplate').on('click', function () {
        var productId = $('#prodcutId').val();
        $.ajax({
            url: '{{ route("packing-template.store") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                product_id: $('#prodcutId').val(), // Make sure this is set in a hidden input
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
    // Save/update template
    $('#updateTemplate').on('click', function () {
        var  p_id = $('#productIdEdit').val();
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
                loadPackingTemplates(p_id)
                // Optional: Refresh select options
            },
            error: function () {
                alert('Error updating template.');
            }
        });
    });
    // Delete template
    $('#deleteTemplate').on('click', function () {
        var  p_id = $('#productIdEdit').val();
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
                loadPackingTemplates(p_id)
            },
            error: function () {
                alert('Failed to delete template.');
            }
        });
    });
    function applyTemplateDataToRow(template, itemId) {
        const dimensions = `${template.box_length +'"' || ''} x ${template.box_width+'"' || ''} x ${template.box_height+'"' || ''}`;
        
        $(`#perPiece${itemId}`).text(template.units_per_box || '');
        $(`#dimensions${itemId}`).text(dimensions.trim() !== 'x x' ? dimensions : '');
        $(`#original_pack${itemId}`).text(template.original_pack == 1 ? 'Use original pack' : '--');
        $(`#box_weight${itemId}`).text(template.box_weight || '');

        let totalCost = 0;
        if (template.box_weight && template.units_per_box) {
            totalCost = parseFloat(template.box_weight) * parseInt(template.units_per_box);
        }
        // $(`#costTotal${itemId}`).text(totalCost ? totalCost.toFixed(2) : '');
        const boxes = $(`input[name="boxes[${itemId}]"]`).val() || 0;
        const totalWeight = boxes * (template.box_weight || 0);
        totalWEightGb += totalWeight || 0; // Ensure totalWeight is a number
       $('#footerTotalWeight').text(totalWEightGb.toFixed(2));
        $(`#totalWeight${itemId}`).text(totalWeight.toFixed(2));
        boxcunt = $(`input[name="boxes[${itemId}]"]`).val();
        unitsnew = boxcunt * template.units_per_box;
        $(`input[name="units[${itemId}]"]`).val(unitsnew);
    }
    $(document).on('input', 'input[name^="boxes["]', function () {
        const boxInput = $(this);
        const itemId = boxInput.attr('name').match(/\[(\d+)\]/)[1]; // extract item.id
        const boxCount = parseInt(boxInput.val()) || 0;

        // Get units per box from the #perPiece element
        const unitsPerBoxText = $(`#perPiece${itemId}`).text().trim();
        const unitsPerBox = parseInt(unitsPerBoxText) || 0;

        const unitInput = $(`input[name="units[${itemId}]"]`);
        unitInput.val(boxCount * unitsPerBox);
        var box_weight = parseFloat($(`#box_weight${itemId}`).text()) || 0;
        var totalWeight = box_weight * boxCount;
        $(`#totalWeight${itemId}`).text(totalWeight.toFixed(2));
        
    });
    function saveProductData( productId) {
        const boxes = $(`input[name="boxes[${productId}]"]`).val();
        const units = $(`input[name="units[${productId}]"]`).val();
        const expiration = $(`input[name="expiration[${productId}]"]`).val();
        const templateType = $(`#templateType${productId}`).val();
        templateType == 'individual_units'?0:templateType;
        const costTotal = 0.00;
        const ship_plan_id = $(`#ship_plan_id`).val();

        const data = {
            product_id: productId,
            item_id: productId,
            boxes: boxes,
            units: units,
            expiration: expiration,
            template_type: templateType,
            cost_total: costTotal,
            ship_plan_id:ship_plan_id
        };

        // Optional: Send to server via AJAX
        $.ajax({
            url: `{{ url('save-shipping-item') }}`,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            method: 'POST',
            data: data,
            success: function(response) {
                // toastr.success("Saved successfully!");
                getOldITems(ship_plan_id);
            },
            error: function(err) {
                toastr.error("Failed to save.");
            }
        });
    }
    function saveShipingPlan() {
        const sku_method = $('#sku_method').val();
        const fullment_capability = $('#fullment_capability').val();
        const market_place = $('#market_place').val();
        const show_filter = $('#show_filter').is(":checked")?1:0;
        const ship_plan_id = $(`#ship_plan_id`).val();
        const handling_cost = $(`#handlingFee`).val();
        const shipment_fee = $(`#shippingCost`).val();
        const data = {
            custom_id:ship_plan_id,
            sku_method: sku_method,
            fullment_capability: fullment_capability,
            market_place: market_place,
            show_filter: show_filter,
            handling_cost:handling_cost,
            shipment_fee:shipment_fee,
        };

        // Optional: Send to server via AJAX
        $.ajax({
            url: `{{ route('shipping-plans.store') }}`,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            method: 'POST',
            data: data,
            success: function(response) {
                toastr.success("Saved successfully!");
                if (response.data) {
                    window.location.href = `/shipping-plans/${response.data.custom_id}/edit`;
                }
            },
            error: function(err) {
                toastr.error("Failed to save.");
            }
        });
    }
    function appendShippingPlanItem(item) {
       
    }
    function getOldITems(custom_id){
            totalWEightGb = 0;
        var totalBoxes = 0;
        var totalUnits = 0;
        let shippingCost = parseFloat($("#shippingCost").val()) || 0;
        let handlingFee = parseFloat($("#handlingFee").val()) || 0;
        var isHidden = 'd-none';
        if (shippingCost === 0 && handlingFee === 0) {
            isHidden = '';
        }
        $('#productTable tbody').empty();
        $.ajax({
            url: `{{ url('/get-shipping-plan-items/${custom_id} ') }}`, // Your backend route
            method: 'GET',
            async:false,
            success: function(response) {
                totalWeight = 0;
                totalBoxes = 0;
                totalUnits = 0;
                response.forEach(function(item) {
                    // appendShippingPlanItem(item);
                    totalBoxes += item.boxes || 0;
                    totalUnits += item.units || 0;
                    // Prevent duplicate rows
                    if ($('#product-row-' + item.product.id).length) return;
                    var productUrl = `{{ url('products') }}/${item.product.id}/edit`;
                    $('#productTable tbody').append(`
                        <tr id="product-row-${item.product.id}">
                            <td>
                                <span class="row-no">${$('#productTable tbody tr').length + 1}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="position-relative product-img-wrapper" style="width: 80px; height: 80px;">
                                            <img src="${item.product.image 
                                                ? `${assetBaseUrl}${item.product.image}` 
                                                : 'https://www.shutterstock.com/shutterstock/photos/2311073121/display_1500/stock-vector-no-photo-thumbnail-graphic-element-no-found-or-available-image-in-the-gallery-or-album-flat-2311073121.jpg'}" 
                                                alt="Product Image" 
                                                class="img-thumbnail product-img" 
                                                style="width: 80px; height: 80px; object-fit: contain; margin-bottom:auto;"
                                                data-product-id="${item.product?.id}">
                                            
                                            <button type="button" 
                                                class="btn btn-xs btn-primary edit-image-btn"
                                                data-product-id="${item.product?.id}"
                                                style="position: absolute; top: 2px; right: 2px; padding: 2px 6px; font-size: 10px; line-height: 1;">
                                                Edit
                                            </button>
                                        </div>

                                    <div>
                                        <a target="_blank" href="${productUrl}"><strong>${item.product?.item || item.item}</strong><br></a>
                                        SKU: ${item.product?.msku || item.msku}<br>
                                        ASIN: ${item.product?.asin || item.asin}<br>
                                        FNSKU: ${item.product?.fnsku || item.fnsku}<br>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="mb-3">
                                    <label for="templateType" class="form-label">Template type
                                    <button id="editPacking${item.product.id}" onclick="editTemplate(${item.product.id})" type="button" class="btn btn-sm btn-outline-secondary" title="Edit Packing Template">
                                        Edit
                                        <i class="fas fa-pencil-alt"></i>
                                    </button>    
                                    </label>
                                    <select class="form-select" id="templateType${item.product.id}" name="templateType${item.product.id}" onchange="createNewTemp(${item.product.id})">
                                    <option value="case_pack">Case pack</option>
                                    <option value="individual_units">Individual units</option>
                                    </select>
                                    
                                </div>    
                            </td>
                            <td><span id="perPiece${item.product.id}"></span></td>
                            <td><span id="dimensions${item.product.id}"></span></td>
                            <td><span id="original_pack${item.product.id}"></span></td>
                            <td><span id="box_weight${item.product.id}"></span></td>
                            
                            <td><span id="totalWeight${item.product.id}"></span></td>
                        
                            <td>
                                <div class="row mb-2">
                                    <div class="col-6">
                                        <label class="form-label boxlable${item.product.id}">Boxes</label>
                                        <input type="number" class="form-control form-control-sm boxlable${item.product.id}" min="0" name="boxes[${item.product.id}]" value="${item.boxes || ''}" data-id="${item.product.id}" onchange="saveProductData(${item.product.id})">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label">Units</label>
                                        <input type="number" class="form-control form-control-sm" min="0" name="units[${item.product.id}]" value="${item.units || ''}" readonly>
                                    </div>
                                </div>
                                <div class="d-none">
                                    <label class="form-label">Expiration</label>
                                    <input type="date" class="form-control" name="expiration[${item.product.id}]" value="${item.expiration || ''}">
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column gap-1">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button type="button" class="btn btn-success" onclick="saveProductData(${item.product.id})">
                                            Save
                                        </button>
                                        <button type="button" class="btn btn-danger" onclick="deleteProcut(${item.id})">
                                            Delete
                                        </button>
                                    </div>
                                    <button type="button" class="btn btn-primary btn-sm" onclick="openMovePlanModal(${item.id},${item.product.id})">
                                        Move to Another Shipping Plan
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `);
                    // Load templates for that product
                    loadPackingTemplates(item.product.id,item.template_id);
                    console.log(item.template_id);
                    if(item.template_id == 0){
                        $(`input[name="units[${item.product.id}]"]`).prop("readonly", false);
                        $(`#perPiece${item.product.id}`).text('');
                        $(`#dimensions${item.product.id}`).text('');
                        $(`#original_pack${item.product.id}`).text('--');
                        $(`#box_weight${item.product.id}`).text('');
                        $(`#totalWeight${item.product.id}`).text('0.0');
                        $('.boxlable'+item.product.id).addClass('d-none');
                    }else{
                        $(`input[name="units[${item.product.id}]"]`).prop("readonly", true);
                        $('.boxlable'+item.product.id).removeClass('d-none');
                    }
                });
            }
        });
        updateTableFooterTotals(totalUnits,totalWeight,totalBoxes)
        
    }
    function deleteShippingPlan(id){
        if (!confirm('Are you sure you want to delete this shipping plan?')) {
            return;
        }
        $.ajax({
            url: '/shipping-plans/' + id,
            type: 'DELETE',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function (response) {
                toastr.success('Shipping plan deleted successfully');
                window.location.href = '/shipping-plans';
            },
            error: function (xhr) {
                toastr.error('Error deleting shipping plan');
            }
        });
    }
    function deleteProcut(product_id){
        if (!confirm('Are you sure you want to delete this product from the shipping plan?')) {
            return;
        }
        const ship_plan_id = $(`#ship_plan_id`).val();
        $.ajax({
            url: `{{ url('/shipping-plans/${product_id}/delete-product') }}`,
            type: 'DELETE',
            data: {
                _token: '{{ csrf_token() }}',
                product_id: product_id,
                ship_plan_id:ship_plan_id,
            },
            success: function (response) {
                toastr.success('Product deleted successfully');
                getOldITems(ship_plan_id);
            },
            error: function (xhr) {
                toastr.error('Error deleting product');
            }
        });
    }
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
    function updateTableFooterTotals(totalUnits = 0, totalWeight = 0, totalBoxes = 0) {

    $('#footerTotalWeight').text(totalWeight.toFixed(2));
    $('#footerBoxes').text(totalBoxes);
    $('#footerUnits').text(totalUnits);
}

    let currentItemId = null;
    function openMovePlanModal(itemId,prodcutId) {
        currentItemId = itemId;
        $("#itemIdNew").val(itemId);
        $("#productIdMove").val(prodcutId);
        let $select = $('#shippingPlanSelect');
        const ship_plan_id_g_2 = $(`#ship_plan_id`).val();
        // Reset dropdown
        $select.html('<option value="">Loading...</option>');

        // Load shipping plans via AJAX
        $.ajax({
            url: `{{ url('shipping-plans-all') }}` , // Change this to your real endpoint
            method: 'GET',
            dataType: 'json',
            success: function(plans) {
                $select.empty();
                if (plans.length === 0) {
                    $select.append('<option value="">No plans available</option>');
                } else {
                    plans.forEach(plan => {
                        let date = new Date(plan.created_at);

                        let year = date.getFullYear();
                        let month = String(date.getMonth() + 1).padStart(2, '0');
                        let day = String(date.getDate()).padStart(2, '0');

                        let hours = date.getHours();
                        let minutes = String(date.getMinutes()).padStart(2, '0');
                        let ampm = hours >= 12 ? 'PM' : 'AM';
                        hours = hours % 12;
                        hours = hours ? hours : 12; // 0 becomes 12

                        let formattedDate = `${year}-${month}-${day} ${String(hours).padStart(2, '0')}:${minutes} ${ampm}`;
                        if(ship_plan_id_g_2 !== plan.custom_id){
                            $select.append(
                                `<option value="${plan.id}">${plan.custom_id} — ${formattedDate}</option>`
                            );
                        }
                    
                    });
                }
            },
            error: function() {
                $select.html('<option value="">Error loading plans</option>');
            }
        });

        // Show modal
        $('#movePlanModal').modal('show');
    }
    $("#confirmMovePlanBtn").on("click", function() {
        let targetPlanId = $("#shippingPlanSelect").val();
        let productIdMove = $("#productIdMove").val();
        let itemIdNew = $("#itemIdNew").val();

        if (!targetPlanId) {
            alert("Please select a shipping plan.");
            return;
        }

        $.ajax({
            url: `{{ url('shipping-plans/move-item') }}`,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            data: {
                item_id: itemIdNew,
                target_plan_id: targetPlanId
            },
            success: function(response) {
                $("#movePlanModal").modal("hide");
                $("#product-row-" + productIdMove).remove(); // Remove from current table
                alert("Item moved successfully!");
            },
            error: function() {
                alert("Failed to move item.");
            }
        });
    });
    function calculate() {
        var totalUnits = parseInt($("#footerUnits").text()) || 0;
        var footerTotalWeight = parseInt($("#footerTotalWeight").text()) || 0;

        var handlingFee = parseFloat($("#handlingFee").val()) || 0;
        var shippingCost = parseFloat($("#shippingCost").val()) || 0;

        var perItemHandling = (totalUnits > 0) ? (handlingFee / totalUnits) : 0;
        var perItemShipping = (totalUnits > 0) ? (shippingCost / totalUnits) : 0;
        

        $("#perItemHandling").text("$" + perItemHandling.toFixed(2));
        $("#perItemShipping").text("$" + perItemShipping.toFixed(2));

        var totalCost = handlingFee + shippingCost;
        $("#totalCost").html("<strong>$" + totalCost.toFixed(2) + "</strong>");
        var costPerLb = (totalUnits > 0) ? (totalCost/  footerTotalWeight) : 0;

        $("#totalPerItem").html("<strong>$" + (perItemHandling + perItemShipping).toFixed(2) + "</strong>");
        let safeCost = (!isFinite(costPerLb) || isNaN(costPerLb)) ? 0 : costPerLb;
        $("#costPerLb").html("<strong>$" + safeCost.toFixed(2) + "</strong>");
    }


    $("#handlingFee, #shippingCost").on("input", calculate);
    $(document).on("blur", "#handlingFee, #shippingCost", function () {
        let val = parseFloat($(this).val()) || 0;
        $(this).val(val.toFixed(2));
    });
    function printProductTable(downloadExcel = true) {
        $.ajax({
            url: `/get-shipping-plan-items/${ship_plan_id_g}`,
            method: 'GET',
            success: function(response) {
                let totalWeight = 0;
                let totalUnits = 0;

                let table = `
                    <table border="1" style="border-collapse: collapse; width: 100%; font-family: Arial; font-size: 14px; text-align: center;">
                        <thead style="background-color: #f2f2f2;">
                            <tr>
                                <th>MSKU</th>
                                <th>Title</th>
                                <th>FNSKU</th>
                                <th>ASIN</th>
                                <th>Qty</th>
                                <th>Qty/Case</th>
                                <th>Carton</th>
                                <th>Weight</th>
                                <th>Total Weight</th>
                            </tr>
                        </thead>
                        <tbody>
                `;

                response.forEach(function(item) {
                    const msku   = item.product.msku ?? '';
                    const asin   = item.product.asin ?? '';
                    const title  = item.product.item ?? '';
                    const fnsku  = item.product.fnsku ?? '';
                    const qty    = item.units ?? 0;
                    const boxes  = item.boxes ?? 0;
                    const weight = item.packing_template ? (item.packing_template.box_weight ?? 0) : 0;
                    const caseQty = item.packing_template ? (item.packing_template.units_per_box ?? 0) : 0;
                    const total  = (boxes * weight).toFixed(2);

                    totalUnits  += parseInt(qty);
                    totalWeight += parseFloat(total);

                    table += `
                        <tr>
                            <td >${msku}</td>
                            <td text-align:left;">${title}</td>
                            <td >${fnsku}</td>
                            <td >${asin}</td>
                            <td >${qty}</td>
                            <td >${caseQty}</td>
                            <td >${boxes}</td>
                            <td >${weight}</td>
                            <td >${total}</td>
                        </tr>
                    `;
                });

                table += `
                        </tbody>
                        <tfoot style="font-weight: bold; background-color: #ddd;">
                            <tr>
                                <td colspan="4" style="padding: 6px;">Totals</td>
                                <td style="padding: 6px;">${totalUnits}</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td style="padding: 6px;">${totalWeight.toFixed(2)}</td>
                            </tr>
                        </tfoot>
                    </table>
                `;
                const now = new Date();
                const timestamp = now.getFullYear() + "-" +
                String(now.getMonth() + 1).padStart(2, '0') + "-" +
                String(now.getDate()).padStart(2, '0') + "-" +
                String(now.getHours()).padStart(2, '0') +
                String(now.getMinutes()).padStart(2, '0') +
                String(now.getSeconds()).padStart(2, '0');

                const filename = `ShippingPlan-${ship_plan_id_g}.xls`;
                if (downloadExcel) {
                    // Convert to Excel and download
                    let blob = new Blob([table], { type: "application/vnd.ms-excel" });
                    let url = window.URL.createObjectURL(blob);
                    let a = document.createElement("a");
                    a.href = url;
                    a.download = filename;
                    a.click();
                    window.URL.revokeObjectURL(url);
                } else {
                    // Print
                    let printWindow = window.open('', '', 'width=900,height=700');
                    printWindow.document.write(table);
                    printWindow.document.close();
                    printWindow.print();
                }
            },
            error: function(xhr) {
                console.error('Error fetching items:', xhr.responseText);
            }
        });
    }


</script>
<!-- Add SheetJS library -->
<script src="https://cdn.jsdelivr.net/npm/xlsx/dist/xlsx.full.min.js"></script>

<script>
function exportShippingPlanExcel() {
    $.ajax({
        url: `/get-shipping-plan-items/${ship_plan_id_g}`,
        method: 'GET',
        success: function(response) {
            // 🔹 Start Excel data
            const workflow = [
                ["Default prep owner", "Amazon"],
                ["Default labeling owner", "Seller"],
                [], // empty row
                ["", "Optional", "", "", "Optional: Use only for case-packed SKUs"], // info row
                [], // empty row
                [
                    "Merchant SKU",
                    "Quantity",
                    "Prep owner",
                    "Labeling owner",
                    "Expiration date (MM/DD/YYYY)",
                    "Manufacturing lot code",
                    "Units per box",
                    "Number of boxes",
                    "Box length (in)",
                    "Box width (in)",
                    "Box height (in)",
                    "Box weight (lb)"
                ]
            ];

            // 🔹 Fill rows with data from API
            response.forEach(function(item) {
                workflow.push([
                    item.product?.msku ?? '',
                    item.units ?? '',
                    "", // Prep owner (can fill if you store it)
                    "", // Labeling owner (can fill if you store it)
                    item.expiration_date ?? '',
                    item.lot_code ?? '',
                    item.packing_template?.units_per_box ?? '',
                    item.boxes ?? '',
                    item.packing_template?.box_length ?? '',
                    item.packing_template?.box_width ?? '',
                    item.packing_template?.box_height ?? '',
                    item.packing_template?.box_weight ?? ''
                ]);
            });

            // 🔹 Build Workbook (one sheet only)
            const wb = XLSX.utils.book_new();
            const ws = XLSX.utils.aoa_to_sheet(workflow);
            XLSX.utils.book_append_sheet(wb, ws, "Workflow Template");

            // 🔹 Export File
            // 🔹 Create unique filename with current date-time
            const now = new Date();
            const timestamp = now.getFullYear() + "-" +
            String(now.getMonth() + 1).padStart(2, '0') + "-" +
            String(now.getDate()).padStart(2, '0') + "-" +
            String(now.getHours()).padStart(2, '0') +
            String(now.getMinutes()).padStart(2, '0') +
            String(now.getSeconds()).padStart(2, '0');
            const filename = `Manifest-Format-${timestamp}.xlsx`;
            XLSX.writeFile(wb, filename);
        },
        error: function(xhr) {
            console.error('Error fetching items:', xhr.responseText);
        }
    });
}

    // Function to format decimal value (2 decimals always)
    function formatDecimal(value) {
        let num = parseFloat(value);
        if (isNaN(num)) {
            return "0.00";
        }
        return num.toFixed(2); // always 2 decimal places
    }
    // Save cost function
    function saveCost(field, value) {
        $.ajax({
            url: "{{ route('shippingplan.updateCost', $shippingPlan->id) }}", // your update route
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                field: field,
                value: value
            },
            success: function (response) {
                console.log("Saved:", response);
            },
            error: function (xhr) {
                console.error("Error saving cost:", xhr.responseText);
            }
        });
    }

    // On change Shipping Cost
    $("#shippingCost").on("change", function () {
        let formatted = formatDecimal($(this).val());
        $(this).val(formatted); // update input
        saveCost("shipment_fee", formatted);
        toggleDeleteButton();
    });

    // On change Handling Fee
    $("#handlingFee").on("change", function () {
        let formatted = formatDecimal($(this).val());
        $(this).val(formatted); // update input
        saveCost("handling_cost", formatted);
        toggleDeleteButton();
    });
    function toggleDeleteButton() {
        let shippingCost = parseFloat($("#shippingCost").val()) || 0;
        let handlingFee = parseFloat($("#handlingFee").val()) || 0;

        if (shippingCost === 0 && handlingFee === 0) {
            $("#deleteShippingPlan").show();
        } else {
            $("#deleteShippingPlan").hide();
        }
    }
    // $(document).on('change', '.auto-save', function() {
    //     let id = $(this).data('id');
    //     let field = $(this).attr('name');
    //     let value = $(this).val();

    //     $.ajax({
    //         url: '/shipping-plan/' + id + '/update-field',
    //         type: 'POST',
    //         data: {
    //             _token: '{{ csrf_token() }}',
    //             field: field,
    //             value: value
    //         },
    //         success: function(res) {
    //             console.log('Updated:', field);
    //         },
    //         error: function(err) {
    //             console.error('Error:', err);
    //         }
    //     });
    // });
    let selectedProductId = null;
    let newImageFile = null;

    // Open modal on Edit button click
    $(document).on("click", ".edit-image-btn", function() {
        selectedProductId = $(this).data("product-id");
        const currentImg = $(this).siblings("img").attr("src");
        $("#imagePreview").attr("src", currentImg);
        $("#imageInput").val("");
        newImageFile = null;

        $("#editImageModal").modal("show");
    });

    // Live preview when selecting new image
    $("#imageInput").on("change", function(event) {
        const file = event.target.files[0];
        if (file) {
            newImageFile = file;
            const reader = new FileReader();
            reader.onload = function(e) {
                $("#imagePreview").attr("src", e.target.result);
            };
            reader.readAsDataURL(file);
        }
    });

    // Save new image
    $("#saveImageBtn").on("click", function() {
        if (!newImageFile || !selectedProductId) {
            alert("Please select an image.");
            return;
        }

        let formData = new FormData();
        formData.append("image", newImageFile);
        formData.append("product_id", selectedProductId);

        $.ajax({
            url: `{{ route('product.upload.image') }}`, // your backend route
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                // update product image live
                $(`img[data-product-id="${selectedProductId}"]`).attr("src", response.new_image_url);

                $("#editImageModal").modal("hide");
            },
            error: function(xhr) {
                alert("Failed to upload image");
            }
        });
    });
    function saveShippingPlanData(id, closeAfterSave = false) {
        // Collect values by ID
        let shipmentName     = $('#shipment_name').val();
        let amazonId         = $('#amazon_id').val();
        let amazonReference  = $('#amazon_reference_id').val();
        let shipTo           = $('#ship_to').val();
        let method_carrier          = $('#method_carrier').val();

        $.ajax({
            url: `{{ url('save-shipping-plan-data') }}`,   // 👈 your route here
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'), // CSRF
                shipment_name: shipmentName,
                amazon_id: amazonId,
                amazon_reference_id: amazonReference,
                ship_to: shipTo,
                method_carrier: method_carrier,
                id: id,
            },
            success: function(response) {
                toastr.success("Shipping plan saved successfully!");
                console.log(response);
                if (closeAfterSave) {
                    // redirect to index
                    window.location.href = "{{ url('/shipping-plans') }}";
                } else {
                    // just show a success message
                }
            },
            error: function(xhr) {
                console.error(xhr.responseText);
                alert("Something went wrong!");
            }
        });
    }

    // Edit button
    $("#edit-btn-name").click(function () {
        $("#shipping-name-text").addClass("d-none");
        $("#shipping-name-input").removeClass("d-none");
        $("#edit-btn-name").addClass("d-none");
        $("#save-btn-name, #cancel-btn-name").removeClass("d-none");
    });

    // Cancel button
    $("#cancel-btn-name").click(function () {
        $("#shipping-name-input").addClass("d-none");
        $("#shipping-name-text").removeClass("d-none");
        $("#edit-btn-name").removeClass("d-none");
        $("#save-btn-name, #cancel-btn-name").addClass("d-none");
    });

    // Save button
    $("#save-btn-name").click(function () {
        let newName = $("#shipping-name-input").val();

        $.ajax({
            url: "{{ route('shipping.update.name', $shippingPlan->id) }}", // your update route
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                name: newName
            },
            success: function (response) {
                $("#shipping-name-text").text(response.name);
                $("#cancel-btn-name").click(); // reset UI
            }
        });
    });
    $(document).on('input', '.sync-input', function () {
        let value = $(this).val();
        let id = $(this).data('id');

        // Sync both inputs
        $('#shipping-name-input').val(value);
        $('#shipment_name').val(value);

        // Update text span
        $('#shipping-name-text').text(value ? '- ' + value : '- Name');

        // Auto-save to DB (debounced so it won’t spam server)
        clearTimeout(window.saveTimer);
        window.saveTimer = setTimeout(function () {
            $.ajax({
                url: "{{ url('/shipping-plans/update-name') }}/" + id,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    name: value,
                    shipment_name: value
                },
                success: function (res) {
                    console.log('Saved!', res);
                },
                error: function (err) {
                    console.error('Save failed', err);
                }
            });
        }, 500); // wait 0.5s after typing stops
    });

</script>

@endsection