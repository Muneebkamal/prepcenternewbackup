@extends('layouts.app')

@section('title', 'Edit Prep Work Order | Prepcenter')

@section('styles')
<style>
    /* .truncate { */
        /* max-width: 500px; */
        /* white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    } */
    .badge-active {
        background-color: #28a745;
        color: white;
    }
    .badge-warning {
        background-color: rgb(214, 186, 28);
        color: white;
    }
    
    .large-screen {
        display: table-cell;
    }

    .mobile-screen {
        display: none;
    }

    @media (max-width: 767.98px) {
        .large-screen {
            display: none;
        }

        .mobile-screen {
            display: table-cell;
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row d-flex align-items-center">
        <div class="col-md-5">
            <div class="d-flex">
                <h1 class="mb-3">Edit Prep Work Order - {{ $order_id }}</h1>
                <div class="ms-3">
                    @if($daily_input->status == 1)
                        <span class="badge badge-active">Completed</span>
                    @else
                        <span class="badge badge-warning">Pending</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <h2 class="float-left">Total Products: <span id="productCount">0</span></h2>
        </div>
        <div class="col-md-4 text-end">
            <h6>Created By: {{ $daily_input->createdBy->name }}</h6>
            <h6>Created At: {{ $daily_input->createdBy->created_at }}</h6>
        </div>
    </div>

    <!-- Form for selecting product and employee -->
    <form id="editPrepOrderForm">
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="productSelect" class="form-label">Search Products</label>
                <select id="productSelect" name="product_id" class="form-select" multiple style="width: 100%;"></select>
            </div>

            <div class="col-md-4">
                <label for="qty" class="form-label">QTY</label>
                <input type="number" id="qty" name="qty" class="form-control" placeholder="Qty">
            </div>

            <div class="col-md-4">
                <label for="employee_id" class="form-label">Assign Employee</label>
                <select id="employee_id" name="employee_id" class="form-select" style="width: 100%;">
                    <option value="">-- Select Employee --</option>
                    @foreach ($employees as  $employee)
                        <option value="{{ $employee->id }}" {{ $daily_input && $daily_input->employee && $daily_input->employee->id == $employee->id ? 'selected' : '' }}>{{ $employee->name }}</option>
                    @endforeach
                    <!-- Add dynamic options here -->
                </select>
            </div>
        </div>

        <button type="submit" class="btn btn-primary mb-2">Add to Work Order</button>
        <button id="printPrepOrder" type="button" class="btn btn-primary mb-2">
            <i class="ri-printer-line"></i> Print Prep Order
        </button>
    </form>

    <!-- Table to display prep orders -->
    <div class="card mt-2">
        <div class="card-body">
            <div class="table-responsive mt-4">
                <table class="table table-bordered table-striped" id="editOrderTable">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Item Code</th>
                            <th>Product Item Name</th>
                            <th>Product Packing</th>
                            <th>Carton Packing</th>
                            <th>Labels</th>
                            <th>Pack</th>
                            <th>QTY to Pack</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="editOrderTableBody">
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 text-end">
            <button id="edit-order" class="d-none btn btn-success">Edit Order</button>
        </div>
    </div>
</div>
<div class="modal fade" id="moveCopyModal" tabindex="-1" aria-labelledby="moveCopyModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="moveCopyModalLabel">Move Item(s) to PrepOrder</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="moveCopyForm">
                    <div class="mb-3">
                        <label for="selectBuylist" class="form-label">Select PrepOrder</label>
                        <select class="form-select" id="selectBuylist" required>
                            <option value="" disabled selected>Select a PrepOrder</option>
                            @foreach ($prepOrderAll as  $prepOrderItem)
                                <option value="{{ $prepOrderItem->id }}">{{ $prepOrderItem->custom_id }}</option>                                
                            @endforeach
                            <!-- Dynamically populate options here -->
                        </select>
                        <input type="hidden" name="selectedProductId" id="selectedProductId">
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="copyLeadCheckbox">
                        <label class="form-check-label" for="copyLeadCheckbox">
                            Copy Item(s)
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="submitMoveCopy">save</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="updateQtyModal" tabindex="-1" aria-labelledby="updateQtyModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Quantity</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>This product is already in the order. Current Quantity: <strong id="existingQty"></strong></p>
                <label for="newQty">New Quantity:</label>
                <input type="number" id="newQty" name="newQty" class="form-control" min="1">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
                <button type="button" id="confirmUpdate" class="btn btn-primary">Update</button>
            </div>
        </div>
    </div>
</div>
<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
        
            <div class="modal-header">
                <h5 class="modal-title" id="addProductLabel">Add New Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body">
                <div class="card-body">
                    <form id="saveNewPRoduct">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mt-2">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-barcode"></i> FNSKU/GTIN
                                    </span>
                                    <input class="form-control" type="text" id="fnsku-input" name="fnsku" required>
                                </div>
                            </div>
                        
                            <div class="col-md-6 mt-2">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-box-seam"></i> Item Name
                                    </span>
                                    <input type="text" id="item" name="item" class="form-control">
                                </div>
                            </div>

                            <div class="col-md-6 mt-2">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-box"></i> Pack
                                    </span>
                                    <input type="number" id="pack" name="pack" class="form-control">
                                </div>
                            </div>
                        
                            <div class="col-md-6 mt-2">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-tag"></i> MSKU / SKU
                                    </span>
                                    <input type="text" id="msku" name="msku" class="form-control">
                                </div>
                            </div>
                        
                            <div class="col-md-6 mt-2">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-upc-scan"></i> ASIN / ITEM ID
                                    </span>
                                    <input type="text" id="asin" name="asin" class="form-control">
                                </div>
                            </div>
                        
                            <div class="col-md-12">
                                <div class="d-flex justify-content-end mt-3">
                                    <button class="btn btn-danger" type="button" id="resetButton">RESET</button>
                                    <button type="submit" class="btn btn-primary ms-2">+ Add Record</button>
                                </div>
                            </div>
                        </div>                        
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
<!-- Modal -->
<div class="modal fade" id="dailyInputModal" tabindex="-1" aria-labelledby="dailyInputModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="dailyInputModalLabel">Select Daily Input</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <input type="hidden" name="productId" id="productId">
            <input type="hidden" name="productQty" id="productQty">
            <input type="hidden" name="detailId" id="detailId">
          <select id="dailyInputSelect" class="form-select" style="width: 100%;"></select>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="button" class="btn btn-success" onclick="addToDailySheet();">Save</button>

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
    function initializeTooltips() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
</script>

<script>
    var order_id = {!! json_encode($order_id) !!};
    function loadPrepOrderTable(order_id) {
        $.ajax({
            url: '{{ route("prep-orders.data") }}',
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: { order_id: order_id },
            success: function(response) {
                var tableBody = $('#editOrderTableBody');
                tableBody.empty();
                var count = 1;
                var totalQty = 0;
                $.each(response.products, function(index, product) {
                    // console.log(product);
                    if(product.product == null){
                        return true; // Skip this iteration if product is null
                    }
                    $('#edit-order').removeClass('d-none');
                    totalQty += product.qty;
                    const firstChar = product.product.fnsku ? product.product.fnsku[0] : null;
                    let link = '#';
                    // Generate the link based on the first character
                    if (firstChar === 'X') {
                        link = `https://www.amazon.com/dp/${product.product.asin}`;
                    } else if (firstChar === '0' || firstChar === '1') {
                        link = `https://www.walmart.com/ip/${product.product.asin}`;
                    } else{
                        link = `https://www.amazon.com/dp/${product.product.asin}`;
                    }

                    var polyBag = '';
                    if(product.product.poly_bag == 1){
                        polyBag = 'Poly Bag';
                    }
                    var shrinkWrap = '';
                    if(product.product.shrink_wrap == 1){
                        shrinkWrap = 'Shrink Wrap';
                    }
                    var weight = '';
                    if(product.product.weight != null){
                        weight = product.product.weight + ' lbs';
                    }
                    var use_orignal_box = '';
                    if(product.product.use_orignal_box == 1){
                        use_orignal_box = 'Use Original Box';
                    }
                    var urlLable = `{{ url('/print-label/${product.fnsku}') }}`
                    var  urlLable = `<a class="text-white" href="${urlLable}" target="_blank" style="font-size:20px;"><i class=" ri-printer-line"></i></a>`;
                    let icon = '';

                    // Generate the link and icon based on the first character
                    if (firstChar === 'X') {
                        icon = `<img src="{{ asset('assets/prep-order-imgs/fba-logo.png') }}" alt="FBA Logo" width="40">`;
                    } else if(firstChar == '0' || firstChar == '1'){
                        icon =`<img src="{{ asset('assets/prep-order-imgs/wfs-logo.png') }}" alt="WFS Logo" width="40">`;
                    } else{
                         icon = `<img src="{{ asset('assets/prep-order-imgs/fba-logo.png') }}" alt="FBA Logo" width="40">`;
                    }

                    var row = `<tr data-id="${product.id}">
                        <td>${count}
                    
                        </td>
                        <td>
                            FNSKU / GTIN: <a href="${link}" target="_blank">${product.product.fnsku} <i class="ri-external-link-line text-primary fs-4"></i></a> <br>
                            MSKU / SKU: ${product.product.msku} <br>
                            ASIN / ITEM ID: ${product.product.asin} <i class="ri-file-copy-line ms-2" style="cursor: pointer;" onclick="copyToClipboard('${product.product.fnsku}')" title="Copy ASIN"></i> <br>
                        </td>
                        <td style="width: 400px">
                            <div class="truncate"><a data-bs-toggle="tooltip" style="display:inline" data-bs-html="true" title="${product.product.item}" href="/products/${product.product.fnsku}/edit" target="_blank">${product.product.item}</a></div>
                        </td>
                        <td>
                            ${
                                polyBag || product.product.poly_bag_size || shrinkWrap || product.product.shrink_wrap
                                ? `${polyBag} <br>
                                ${product.product.poly_bag_size || ''} <br>
                                ${shrinkWrap} <br>
                                ${product.product.shrink_wrap_size || ''}` 
                                : '--'
                            }
                        </td>
                        <td>
                            ${
                                product.product.no_of_pcs_in_carton || 
                                product.product.carton_size || 
                                product.product.weight ||
                                product.product.use_orignal_box
                                ? `${product.product.no_of_pcs_in_carton ? product.product.     no_of_pcs_in_carton + ' pcs' : ''} <br>
                                    ${product.product.carton_size || ''} <br>
                                    ${use_orignal_box} <br>
                                    ${weight}`
                                : '--'
                            }
                        </td>
                        <td>
                            ${
                                product.product.label_1 || 
                                product.product.label_2 || 
                                product.product.label_3 
                                ? `${product.product.label_1 || ''} <br> 
                                ${product.product.label_2 || ''} <br> 
                                ${product.product.label_3 || ''}`
                                : '--'
                            }
                        </td>
                        <td>${product.product.pack || ''}</td>
                        <td class="qty-cell" data-id="${product.id}">
                            <span class="qty-text qty-text-${product.id}">${product.qty}</span>
                            <div class="d-flex">
                                <input type="number" class="form-control qty-input qty-input-${product.id} d-none w-25" value="${product.qty}">
                                <button class="btn btn-sm btn-primary qty-update-btn qty-update-btn-${product.id} m-1 d-none" data-id="${product.id}">Save</button>
                               
                            </div>
                        </td>
                        <td>
                            <div class="">
                                ${product.status == 1 
                                    ? '<span class="badge badge-active me-2 mb-2">Completed</span><br>' 
                                    : '<span class="badge badge-warning me-2 mb-2">Pending</span><br>'
                                }
                                ${icon}
                            </div>
                        </td>

                        <td class="text-center large-screen">
                            <!-- First Row: Edit, Delete, Add to Time Sheet (Same Line) -->
                            <div class="d-flex align-items-center gap-2 justify-content-center mb-2">
                                <button class="btn btn-sm btn-success edit-qty" data-id="${product.id}">Edit</button>
                                <button class="btn btn-sm btn-danger delete-product" data-id="${product.id}">Delete</button>
                                <button class="btn btn-sm btn-primary daily-input-done" data-detail-id="${product.id}" data-id="${product.product.id}" data-qty="${product.qty}">Add to Time Sheet</button>
                            </div>

                            <!-- Second Row: Print and Move/Copy Buttons -->
                            <div class="d-flex align-items-center gap-2 justify-content-center">
                                <button class="btn btn-sm btn-info">${urlLable}</button>
                                <button class="btn btn-sm btn-warning text-white moveCopy" data-id="${product.id}">
                                    <i class="ri-share-forward-fill"></i> Move/Copy to Work Order...
                                </button>
                            </div>
                        </td>

                        <td class="text-center mobile-screen">
                            <!-- First Row: Edit, Delete, Add to Time Sheet (Same Line) -->
                            <div class="d-flex align-items-center gap-2 justify-content-center mb-2">
                                <button class="btn btn-sm btn-success edit-qty" data-id="${product.id}">Edit</button>
                                <button class="btn btn-sm btn-danger delete-product" data-id="${product.id}">Delete</button>
                                <button class="btn btn-sm btn-info">${urlLable}</button>
                            </div>
                            <div class="mt-2">
                                <button class="btn btn-sm btn-primary daily-input-done" data-detail-id="${product.id}" data-id="${product.product.id}" data-qty="${product.qty}">Add to Time Sheet</button>
                            </div>

                            <!-- Second Row: Print and Move/Copy Buttons -->
                            
                            <div class="mt-2">
                                <button class="btn btn-sm btn-warning text-white moveCopy" data-id="${product.id}">
                                    <i class="ri-share-forward-fill"></i> Move/Copy to Work Order...
                                </button>    
                            </div>
                        </td>
                    </tr>`;
                    tableBody.append(row);
                    initializeTooltips();
                    count++;
                });
                $('#productCount').text(totalQty);
            },
            error: function(xhr) {
                console.error("Failed to load prep orders:", xhr.responseText);
            }
        });
    }

    document.addEventListener("DOMContentLoaded", function() {
        initializeTooltips();
    });

    $(document).on("click", ".edit-qty", function () {
        var productId = $(this).data("id"); // Get product ID from button
        $(".qty-text-" + productId).addClass("d-none");
        $(".qty-input-" + productId).removeClass("d-none").focus();
        $(".qty-update-btn-" + productId).removeClass("d-none");
    });

    // Handle input change with AJAX
    $(document).on("click", ".qty-update-btn", function () {
        var productId = $(this).data("id"); // Get product ID from button
        var input = $(".qty-input-" + productId); // Get input field by product ID
        var newQty = input.val(); // Get new quantity value

        if (!productId || newQty === "") {
            alert("Invalid product ID or quantity");
            return;
        }

        $.ajax({
            url: "/update-qty",
            type: "POST",
            data: {
                _token: $('meta[name="csrf-token"]').attr("content"),
                id: productId,
                qty: newQty
            },
            success: function (response) {
                loadPrepOrderTable(order_id); // Refresh table after update
            },
            error: function () {
                alert("Failed to update quantity");
                input.addClass("d-none");
                $(".qty-text-" + productId).removeClass("d-none");
            }
        });
    });

    $(document).on("click", ".delete-product", function() {
        let productId = $(this).data("id");
        let row = $(this).closest("tr");

        // if (!confirm("Are you sure you want to delete this product?")) {
        //     return;
        // }

        $.ajax({
            url: `/prep-detail/${productId}`,
            type: "DELETE",
            data: {
                _token: "{{ csrf_token() }}"
            },
            success: function(response) {
                if (response.success) {
                    // row.remove(); 
                    loadPrepOrderTable(order_id);
                    // alert(response.message);
                } else {
                    alert("Error: " + response.message);
                }
            },
            error: function(xhr) {
                alert("Something went wrong!");
            }
        });
    });

    // $(document).on("click", ".daily-input-done", function () {
    //     let productId = $(this).data("id");
    //     let detailId = $(this).data("detail-id");
    //     // console.log(productId);
    //     let qty = $('.qty-' + productId).text();
    //     console.log(qty);

    //     var employeeId = $('#employee_id').val();
    //     if (!employeeId) {
    //         alert('Please select an employee.');
    //         return;
    //     }

    //     $.ajax({
    //         url: "/done-daily-input", 
    //         type: "POST",
    //         data: {
    //             _token: $('meta[name="csrf-token"]').attr("content"),
    //             id: productId,
    //             qty: qty,
    //             employee: employeeId,
    //             detailId: detailId
    //         },
    //         success: function (response) {
    //             loadPrepOrderTable(order_id);
    //             // console.log("Daily input updated successfully");
    //             // Uncomment if needed: loadPrepOrderTable(order_id);
    //         },
    //         error: function () {
    //             // alert("Failed to update daily input");
    //         }
    //     });
    // });
    $(document).on("click", ".daily-input-done", function () {
        $('#dailyInputModal').modal('show');
        let productId = $(this).data("id");
        let productQty = $(this).data("qty");
        let detailId = $(this).data("detail-id");
        console.log(productQty);
        $('#productId').val(productId); // Store product ID in the modal
        $('#productQty').val(productQty); // Store product ID in the modal
        $('#detailId').val(detailId); // Store product ID in the modal
        $.ajax({
            url: '/daily-inputs-current-month', // adjust if needed
            type: 'GET',
            success: function (data) {
                let $select = $('#dailyInputSelect');
                $select.empty(); // Clear existing

                if (data.length > 0) {
                    $select.append('<option value="">Select a day</option>');
                    data.forEach(item => {
                        let displayValue = item.value ? item.value : item.user.name;
                        $select.append(`<option value="${item.id}">${item.date} - ${displayValue}</option>`);
                    });
                } else {
                    $select.append('<option value="">No data found</option>');
                }
            },
            error: function () {
                $('#dailyInputSelect').html('<option>Error loading inputs</option>');
            }
        });
    });





    $(document).ready(function() {
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "timeOut": "15000",
            "extendedTimeOut": "5000",
            "positionClass": "toast-top-right"
        };
        loadPrepOrderTable(order_id);
        $('#edit-order').on('click', function() {
            var employeeId = $('#employee_id').val();

            // if (!employeeId) {
            //     alert('Please select an employee.');
            //     return;
            // }

            $.ajax({
                url: '{{ route("prep-orders.edit.order") }}', 
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    employee_id: employeeId,
                    order_id: order_id
                },
                success: function(response) {
                    if (response.order_id) {
                        window.location.href = "/prep-orders/edit/" + response.order_id;
                    } else {
                        alert("Something went wrong!");
                    }
                    // loadPrepOrderTable(order_id);
                    // alert(response.message);
                    // $('#employee_id').val('');
                },
                error: function(xhr) {
                    console.error("Error:", xhr.responseText);
                    alert('Failed to create order.');
                }
            });
        });

        // Initialize the product search select box using Select2
        $('#productSelect').select2({
            placeholder: 'Select Detail',
            multiple: false,
            ajax: {
                url: '/fetch-items',
                type: 'GET',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        search: params.term 
                    };
                },
                processResults: function (data) {
                    // Set the flag based on whether results are found
                    hasResults = data.length > 0;

                    if (hasResults) {
                        // Results found, return them and disable tag creation
                        return {
                            results: $.map(data, function (item) {
                                return {
                                    id: item.id, // The value you want to return
                                    text: `${item.item} | ${item.asin} | ${item.msku} | ${item.fnsku} | ${item.pack}` // The text that appears in the dropdown
                                };
                            })
                        };
                    } else {
                        // No results found, enable tag creation
                        return {
                            results: [],
                            createTag: true // Allow tag creation
                        };
                    }
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
        });

        // Edit button handler (demo purpose only)
        $(document).on('click', '.edit-button', function() {
            alert('Edit button clicked! Implement edit functionality here.');
        });
         // Handle form submission
        $('#editPrepOrderForm').on('submit', function(e) {
            e.preventDefault();
            let formData = new FormData(this);

            formData.append('order_id', order_id);
            $.ajax({
                url: '{{ route("prep-orders.store") }}',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: formData,
                contentType: false,
                processData: false, 
                success: function(response) {
                    // alert(response.message);
                    // loadPrepOrderTable(order_id);
                    // $('#productSelect').val(null).trigger('change');
                    // $('#qty').val('');
                    if (response.exists) {
                        // Product already exists, show confirmation modal
                        $('#updateQtyModal').modal('show');
                        $('#existingQty').text(response.current_qty);
                        $('#newQty').val(response.current_qty);
                        
                    }else if(response.notExist){
                        generateTempFnskuAndShowModal()
                        // $('#addProductModal').modal('show');
                    }else {
                        // Success: Load table and reset form
                        loadPrepOrderTable(order_id);
                        $('#productSelect').val(null).trigger('change');
                        $('#employee_id').val('');
                        $('#qty').val('');
                    }
                },
                error: function(xhr) {
                    alert('Failed to add prep order');
                }
            });
        });
        // Initialize Yajra DataTable
        $('#saveNewPRoduct').on('submit', function (e) {
            e.preventDefault();

            let form = $(this);
            let formData = form.serialize();

            $.ajax({
                url: `{{ url('save-new-product') }}`, // 🔁 Change this to your actual route
                method: 'POST',
                data: formData,
                beforeSend: function () {
                    // Optional: disable button, show loader, etc.
                },
                success: function (response) {
                    if(response.success && response.product) {
                        // Step 1: Close the modal
                        $('#addProductModal').modal('hide');
                        // Step 2: Reset form
                        form[0].reset();
                        // Step 3: Create a new option and select it in #daily-input-detail
                        const product = response.product;
                        const newOption = new Option(
                            `${product.item} | ${product.asin} | ${product.msku} | ${product.fnsku} | ${product.pack}`,
                            product.id,
                            true, // selected
                            true  // selected
                        );

                        $('#productSelect').append(newOption).trigger('change');
                    } else {
                        alert(response.message || 'Something went wrong.');
                    }
                },
                error: function (xhr) {
                    // Handle validation or server errors
                    let errors = xhr.responseJSON?.errors;
                    if (errors) {
                        let firstError = Object.values(errors)[0][0];
                        alert(firstError);
                    } else {
                        alert('An error occurred. Please try again.');
                    }
                }
            });
        });

        // RESET button functionality
        $('#resetButton').on('click', function () {
            $('#saveNewPRoduct')[0].reset();
        });
        
    });
    function copyToClipboard(text) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text)
                .then(() => {
                    alert(`Copied: ${text}`);
                })
                .catch(err => {
                    console.error('Failed to copy using clipboard API:', err);
                });
        } else {
            // Fallback for older browsers
            const input = document.createElement('input');
            input.value = text;
            document.body.appendChild(input);
            input.select();
            input.setSelectionRange(0, 99999); // For mobile devices
            try {
                document.execCommand('copy');
                alert(`Copied: ${text}`); 
            } catch (err) {
                alert('Failed to copy using execCommand:', err);
            }
            document.body.removeChild(input);
        }
    }
    $(document).on("click", ".moveCopy", function() {
        var productId = $(this).data("id");
        $("#selectedProductId").val(productId); // Store product ID in modal
        $("#moveCopyModal").modal("show"); // Open modal
    });
    $("#submitMoveCopy").on("click", function () {
        var itemId = $("#selectedProductId").val();
        var prepOrderId = $("#selectBuylist").val();
        var isCopy = $("#copyLeadCheckbox").prop("checked");

        if (!prepOrderId) {
            alert("Please select a Buylist.");
            return;
        }

        $.ajax({
            url: "{{ url('move-copy-item') }}", // Change to your backend route
            type: "POST",
            data: {
                itemId: itemId,
                prepOrderId: prepOrderId,
                is_copy: isCopy ? 1 : 0, // Send 1 if checked, else 0
                _token: $('meta[name="csrf-token"]').attr("content"), // CSRF Token for Laravel
            },
            success: function (response) {
                alert(response.message);
                loadPrepOrderTable(order_id);
                $("#moveCopyModal").modal("hide");
            },
            error: function () {
                alert("Something went wrong!");
            },
        });
    });
    // Function to update product quantity
    function updateProductQty() {
        var  product_id = $('#productSelect').val();
        var  newQty = $('#newQty').val();

        $.ajax({
            url: '{{ route("prep-orders.update-qty") }}', // Create a new route for updating qty
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                order_id : order_id,
                product_id:product_id,
                newQty : newQty
            },
            success: function (response) {
                $('#updateQtyModal').modal('hide');
                loadPrepOrderTable(order_id);
                $('#qty').val('');
            },
            error: function () {
                alert('Failed to update quantity');
            }
        });
    }
    $('#confirmUpdate').off('click').on('click', function () {
        updateProductQty()
    });
    
    $('#printPrepOrder').on('click', function() {
        let workOrderId = order_id; // Assuming the Work Order ID is stored in an element with ID #workOrderId
        let totalProducts = $('#productCount').text(); // Total products count
        let createdBy = "{{ $daily_input->createdBy->name }}"; // Fetch Created By
        let createdAt = "{{ $daily_input->createdBy->created_at }}"; // Fetch Created At

        let printContent = `
            <body>
                <style>
                    body { font-family: Arial, sans-serif; }
                    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                    th, td { border: 1px solid black; padding: 8px; text-align: left; }
                    th { background-color: #f2f2f2; }
                    .text-center { text-align: center; }
                </style>
                <div style="display: flex;">
                    <h2 style="margin-right: 50px;">Prep Order Details</h2>
                    <h2 style="margin-right: 50px;">Work Order ID: ${workOrderId}</h2>
                    <h2>Total Products: ${totalProducts}</h2>
                </div>
                    
                <div style="text-align: left;">
                    <h6 style="margin: 0;">Created By: ${createdBy}</h6>
                    <h6 style="margin: 0; margin-top: 15px;">Created At: ${createdAt}</h6>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Product Details</th>
                            <th>Item</th>
                            <th>Packaging</th>
                            <th>Carton & Weight</th>
                            <th>Labels</th>
                            <th>Pack</th>
                            <th>Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${$('#editOrderTableBody tr').map(function(index) {
                            let cols = $(this).find('td');
                            return `<tr>
                                <td>${index + 1}</td> <!-- Serial Number -->
                                <td>${cols.eq(1).html()}</td> <!-- Product Details -->
                                <td>${cols.eq(2).html()}</td> <!-- Item -->
                                <td>${cols.eq(3).html()}</td> <!-- Packaging -->
                                <td>${cols.eq(4).html()}</td> <!-- Carton & Weight -->
                                <td>${cols.eq(5).html()}</td> <!-- Labels -->
                                <td>${cols.eq(6).html()}</td> <!-- Pack -->
                                <td>${cols.eq(7).find('.qty-text').text()}</td> <!-- Corrected Qty Display -->
                            </tr>`;
                        }).get().join('')}
                    </tbody>
                </table>
            </body>
        `;

        let printWindow = window.open('', '_blank');
        printWindow.document.open();
        printWindow.document.write(printContent);
        printWindow.document.close();
        printWindow.print();
    });
    function addToDailySheet(){
        var productId = $('#productId').val();
        var dailyInputSelect = $('#dailyInputSelect').val();
        let qty = $('#productQty').val();
        let detailId = $('#detailId').val();
        $.ajax({
            url: "/done-daily-input", 
            type: "POST",
            data: {
                _token: $('meta[name="csrf-token"]').attr("content"),
                id: productId,
                daily_input_id: dailyInputSelect,
                qty:qty,
                detailId:detailId,
            },
            success: function (response) {
                loadPrepOrderTable(order_id);
                toastr.success(response.message || '');
                $('#dailyInputModal').modal('hide');
                // console.log("Daily input updated successfully");
                // Uncomment if needed: loadPrepOrderTable(order_id);
            },
            error: function () {
                // alert("Failed to update daily input");
            }
        });
    }
    function generateTempFnskuAndShowModal() {
        let base = "temp";
        let number = 1;

        function tryNextTempFnsku() {
            let tempFnsku = base + String(number).padStart(3, '0');

            $.ajax({
                url: '/check-temp-fnsku',  // ✅ your endpoint to check if FNSKU exists
                type: 'POST',
                data: {
                    fnsku: tempFnsku,
                    _token: $('meta[name="csrf-token"]').attr('content') // if you're using Laravel
                },
                success: function(response) {
                    if (response.exists) {
                        number++; // Try next one
                        tryNextTempFnsku();
                    } else {
                        // Use the generated FNSKU and show the modal
                        $('#fnsku-input').val(tempFnsku); // assign to input field
                        $('#addProductModal').modal('show');
                    }
                }
            });
        }

        tryNextTempFnsku();
    }


    
</script>
@endsection
