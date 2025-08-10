@extends('layouts.app')

@section('title', 'Shipping Plan | Prepcenter')

@section('styles')

@endsection

@section('content')
<div class="container-fluid">
    <div class="row d-flex align-items-center">
        <div class="col-md-6">
            <h1 class="mb-3">Shipping Plan - {{ $shippingPlan->custom_id }}</h1>
        </div>
        <div class="col-md-6 text-end">
            <h6>Created By: {{ $shippingPlan->creator?->name ?? 'N/A' }}</h6>
            <h6>Created At: {{ now() }}</h6>
        </div>
    </div>
    <input type="hidden" name="ship_plan_id" id="ship_plan_id" value="{{ $shippingPlan->custom_id }}">
    <h2 class="mb-4">Send to Amazon</h2>
    <div class="card mb-3">
      <div class="card-body">
        <h5>Step 1: Choose inventory to send</h5>
        <ul class="nav nav-tabs my-3">
          <li class="nav-item">
            <a class="nav-link active" href="#">All FBA SKUs</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#">SKUs ready to send (0)</a>
          </li>
        </ul>

        <div class="row g-3">
            <div class="col-md-2">
                <label class="form-label d-block mb-2">SKU Selection Method</label>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="sku_method" value="list" id="skuList" {{ $shippingPlan->sku_method == 'list'?'checked':''  }} >
                    <label class="form-check-label" for="skuList">Select from list</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="sku_method" {{ $shippingPlan->sku_method == 'file'?'checked':''  }} value="file" id="skuFile">
                    <label class="form-check-label" for="skuFile">File upload</label>
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label">Ship From</label>
                    <span>Favorite Commodities Inc, 3320 LAWSON BLVD, OCEANSIDE, NY, 11572, US</span>
                <a href="#" class="small">Ship from another address</a>
            </div>
            <div class="col-md-2">
                <label class="form-label">Marketplace destination</label>
                <select class="form-select" name="market_place" id="market_place">
                <option value="us" {{ $shippingPlan->market_place == 'us'?'selected':''  }}>United States</option>
                <option value="ca" {{ $shippingPlan->market_place == 'ca'?'selected':''  }}>Canada</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Fulfillment capability</label>
                <select class="form-select" name="fullment_capability" id="fullment_capability">
                <option value="full_fillment" value="{{ $shippingPlan->full_fillment??'' }}">Standard Fulfillment by Amazon</option>
                </select>
            </div>
            <div class="col-md-2">
                 <label for="">Filter</label>
                <div class="form-check ">
                   
                    <input class="form-check-input" type="checkbox" id="show_filter" name="show_filter" checked>
                    <label class="form-check-label" for="casePack" {{ $shippingPlan->show_filter == 1?'checked':''  }}>
                        Only show SKUs with case pack template
                    </label>
                </div>
            </div>
        </div>

        <div class="row mt-3 g-3">
        
   
        </div>
      </div>
    </div>
    <div class="row mt-2 mb-2">
        <div class="col-md-6"></div>
        <div class="col-md-6">
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
        

        <div class="card-header d-flex justify-content-between align-items-center">
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
                <th>SKU</th>
                <th>Packing Details</th>
                <th>Units per box</th>
                <th>Box dimensions (inch)</th>
                <th>Use Original Box </th>
                <th>Box weight (lb) </th>
                <th>Total Cost </th>
                <th>Information/Action</th>
                <th>Quantity to Send</th>
                <th>Actions</th>
                </tr>
            </thead>
            <tbody>

            </tbody>
            </table>
        </div>

        <div class="text-end">
            <h6>Total prep and labeling fees: <strong>$0.00</strong></h6>
            <button class="btn btn-primary" onclick="saveShipingPlan()">Confirm and continue</button>
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
                <div class="row">
                    <div class="col-md-3">
                        <label for="labelingBy" class="form-label">Weight Per Case</label>
                        <input class="form-control" id="weightPerCase" type="number" step="0.01">
                    </div>
                    <div class="col-md-3">
                        <label for="labelingBy" class="form-label">Total Weight</label>
                        <input class="form-control" id="totalWeight" type="number" step="0.01">
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
                <div class="row">
                    <div class="col-md-3">
                        <label for="labelingBy" class="form-label">Weight Per Case</label>
                        <input class="form-control" id="weightPerCase" type="number" step="0.01">
                    </div>
                    <div class="col-md-3">
                        <label for="labelingBy" class="form-label">Total Weight</label>
                        <input class="form-control" id="totalWeight" type="number" step="0.01">
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
    $(document).ready(function () {
        const ship_plan_id_g = $(`#ship_plan_id`).val();
        getOldITems(ship_plan_id_g);
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
                        <strong>${item.item}</strong><br>
                        SKU: ${item.msku}<br>
                        ASIN: ${item.asin}<br>
                        FNSKU: ${item.fnsku}<br>
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
                        <span id="costTotal${item.id}"></span>
                    </td>
                    
                    <td>
                        <div>Prep required: ${item.prep || 'None'}</div>
                        <div>Labeling: By seller – <a href="#">Print SKU labels</a></div>
                    </td>
                    <td>
                        <div class="row mb-2">
                            <div class="col-6">
                                <label class="form-label">Boxes</label>
                                <input type="number" class="form-control form-control-sm" min="0" name="boxes[${item.id}]">
                            </div>
                            <div class="col-6">
                                <label class="form-label">Units</label>
                                <input type="number" class="form-control form-control-sm" min="0" name="units[${item.id}]">
                            </div>
                        </div>
                        <div>
                            <label class="form-label">Expiration</label>
                            <input type="date" class="form-control" name="expiration[${item.id}]">
                        </div>
                    </td>
                    <td>
                        <button type="button" class="btn btn-success btn-sm" onclick="saveProductData(${item.id}, ${item.id})">
                            Save
                        </button>
                    </td>

                </tr>
            `);
            loadPackingTemplates(item.id)
            // Optional: clear selection after append
            $('#skuSearchInput').val(null).trigger('change');
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
    function loadPackingTemplates(productId) {
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
                            text: template.template_name
                        })
                    );
                });
                // Always add individual_units
                $select.append(`<option value="individual_units">Individual units</option>`);
                // Show 'Create new packing template' if less than 3
                if (templates.length < 3) {
                    $select.append(`<option value="new_template">Create new packing template</option>`);
                }
                // If at least one template exists, apply its data immediately
                if (templates.length > 0) {
                    const firstTemplate = templates[0];
                    applyTemplateDataToRow(firstTemplate, productId); // 👈 Call here
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
            $('#newTemplateModal').modal('show');
        }
    }
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
        $(`#costTotal${itemId}`).text(totalCost ? totalCost.toFixed(2) : '');
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
    });
    function saveProductData(itemId, productId) {
        const boxes = $(`input[name="boxes[${itemId}]"]`).val();
        const units = $(`input[name="units[${itemId}]"]`).val();
        const expiration = $(`input[name="expiration[${itemId}]"]`).val();
        const templateType = $(`#templateType${itemId}`).val();
        const costTotal = $(`#costTotal${itemId}`).text();
        const ship_plan_id = $(`#ship_plan_id`).val();

        const data = {
            product_id: productId,
            item_id: itemId,
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
                toastr.success("Saved successfully!");
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
        const data = {
            custom_id:ship_plan_id,
            sku_method: sku_method,
            fullment_capability: fullment_capability,
            market_place: market_place,
            show_filter: show_filter,
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
        // Prevent duplicate rows
        if ($('#product-row-' + item.id).length) return;

        $('#productTable tbody').append(`
            <tr id="product-row-${item.id}">
                <td>
                    <strong>${item.product?.item || item.item}</strong><br>
                    SKU: ${item.product?.msku || item.msku}<br>
                    ASIN: ${item.product?.asin || item.asin}<br>
                    FNSKU: ${item.product?.fnsku || item.fnsku}<br>
                </td>
                <td>
                    <div class="mb-3">
                        <label for="templateType" class="form-label">Template type</label>
                        <select class="form-select" id="templateType${item.product_id}" name="templateType${item.product_id}" onchange="createNewTemp(${item.id})">
                        <option value="case_pack">Case pack</option>
                        <option value="individual_units">Individual units</option>
                        </select>
                        <button id="editPacking${item.product_id}" onclick="editTemplate(${item.product_id})" type="button" class="btn btn-outline-secondary" title="Edit Packing Template">
                            Edit
                            <i class="fas fa-pencil-alt"></i>
                        </button>
                    </div>    
                </td>
                <td><span id="perPiece${item.product_id}"></span></td>
                <td><span id="dimensions${item.product_id}"></span></td>
                <td><span id="original_pack${item.product_id}"></span></td>
                <td><span id="box_weight${item.product_id}"></span></td>
                <td><span id="costTotal${item.product_id}"></span></td>
                <td>
                    <div>Prep required: ${item.prep || 'None'}</div>
                    <div>Labeling: By seller – <a href="#">Print SKU labels</a></div>
                </td>
                <td>
                    <div class="row mb-2">
                        <div class="col-6">
                            <label class="form-label">Boxes</label>
                            <input type="number" class="form-control form-control-sm" min="0" name="boxes[${item.product_id}]" value="${item.boxes || ''}">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Units</label>
                            <input type="number" class="form-control form-control-sm" min="0" name="units[${item.product_id}]" value="${item.units || ''}">
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Expiration</label>
                        <input type="date" class="form-control" name="expiration[${item.id}]" value="${item.expiration || ''}">
                    </div>
                </td>
                <td>
                    <button type="button" class="btn btn-success btn-sm" onclick="saveProductData(${item.id}, ${item.product_id || item.id})">
                        Save
                    </button>
                </td>
            </tr>
        `);

        // Load templates for that product
        loadPackingTemplates(item.product.id);
    }
    function getOldITems(custom_id){
        $.ajax({
            url: `{{ url('/get-shipping-plan-items/${custom_id} ') }}`, // Your backend route
            method: 'GET',
            success: function(response) {
                response.forEach(function(item) {
                    appendShippingPlanItem(item);
                });
            }
        });
    }


</script>
@endsection