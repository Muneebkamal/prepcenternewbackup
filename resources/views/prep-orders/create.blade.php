@extends('layouts.app')

@section('title', 'Prep Work Order | Prepcenter')

@section('styles')
<!-- You can add custom styles here if needed -->
<style>
    /* .truncate {
        max-width: 300px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    } */
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row d-flex align-items-center">
        <div class="col-md-6">
            <h1 class="mb-3">Prep Work Order - 00{{ $order_id }}</h1>
        </div>
        <div class="col-md-6 text-end">
            <h6>Created By: {{ $created_by }}</h6>
            <h6>Created At: {{ now() }}</h6>
        </div>
    </div>

    <!-- Form for selecting product and employee -->
    <form id="prepOrderForm">
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
                        <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                    @endforeach
                    <!-- Add dynamic options here -->
                </select>
            </div>
        </div>

        <button type="submit" class="btn btn-primary mb-2">Add to Work Order</button>
    </form>

    <!-- Table to display prep orders -->
    <div class="card mt-2">
        <div class="card-body">
            <div class="table-responsive mt-4">
                <table class="table table-bordered table-striped" id="prepOrderTable">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Item Code</th>
                            {{-- <th>FNSKU/GTIN</th> --}}
                            <th>Product Item Name</th>
                            <th>Product Packing</th>
                            <th>Carton Packing</th>
                            <th>Labels</th>
                            <th>Pack</th>
                            <th>QTY to Pack</th>
                            <th>Action</th>
                            {{-- <th>Carton Size</th>
                            <th>Weight/Pounds</th>
                            <th>Labels</th> --}}
                        </tr>
                    </thead>
                    <tbody id="prepOrderTableBody">
                        <!-- Dynamically filled rows will go here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 text-end">
            <button id="create-order" class="d-none btn btn-success">Create Order</button>
        </div>
    </div>
    <div class="modal fade" id="updateQtyModal" tabindex="-1" aria-labelledby="updateQtyModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Quantity</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>This product is already in the order. Current Quantity: <strong id="existingQty"></strong></p>
                    <label for="newQty">New Quantity:</label>
                    <input type="number" id="newQty" name="newQty" class="form-control" min="1">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" id="confirmUpdate" class="btn btn-primary">Update</button>
                </div>
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
<script>
    function initializeTooltips() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
</script>

<script>
    
    // var order_id = {!! json_encode($order_id) !!};
    var order_id = {!! json_encode(str_pad($order_id, 3, '0', STR_PAD_LEFT)) !!};
    function loadPrepOrderTable(order_id) {
        $.ajax({
            url: '{{ route("prep-orders.data") }}',
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: { order_id: order_id },
            success: function(response) {
                var tableBody = $('#prepOrderTableBody');
                tableBody.empty();
                var count = 1;
                $.each(response.products, function(index, product) {
                    $('#create-order').removeClass('d-none');

                    const firstChar = product.product.fnsku ? product.product.fnsku[0] : null;
                    let link = '#';
                    // Generate the link based on the first character
                    if (firstChar === 'X') {
                        link = `https://www.amazon.com/dp/${product.product.asin}`;
                    } else if (firstChar === '0') {
                        link = `https://www.walmart.com/ip/${product.product.asin}`;
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

                    var row = `<tr>
                        <td>${count}</td>
                        <td>
                            FNSKU / GTIN: <a href="${link}" target="_blank">${product.product.fnsku} <i class="ri-external-link-line text-primary fs-4"></i></a> <br>
                            MSKU / SKU: ${product.product.msku} <br>
                            ASIN / ITEM ID: ${product.product.asin} <i class="ri-file-copy-line ms-2" style="cursor: pointer;" onclick="copyToClipboard('${product.product.fnsku}')" title="Copy ASIN"></i><br>
                        </td>
                        <td style="width: 450px">
                            <div class="truncate"><a data-bs-toggle="tooltip" data-bs-html="true" title="${product.product.item}" href="/products/${product.product.fnsku}/edit" target="_blank">${product.product.item}</a></div>
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
                        <td class="text-center">
                            <div class="d-flex align-items-center gap-2 justify-content-center mb-2">
                                <button class="btn btn-sm btn-success edit-qty" data-id="${product.id}">Edit</button>
                                <button class="btn btn-sm btn-danger delete-product" data-id="${product.id}">Delete</button>
                                <button class="btn btn-sm btn-info">${urlLable}</button>
                            </div>
                            <br>
                        </td>
                    </tr>`;
                    tableBody.append(row);
                    initializeTooltips();
                    count++;
                });
            },
            error: function(xhr) {
                console.error("Failed to load prep orders:", xhr.responseText);
            }
        });
    }

    document.addEventListener("DOMContentLoaded", function() {
        initializeTooltips();
    });

    // $(document).on("click", ".edit-qty", function () {
    //     var row = $(this).closest("tr");
    //     var qtyCell = row.find(".qty-cell");
    //     qtyCell.find(".qty-text").addClass("d-none");
    //     qtyCell.find(".qty-input").removeClass("d-none").focus();
    //     qtyCell.find(".qty-update-btn").removeClass("d-none");
    // });

    // // Handle input change with AJAX
    // $(document).on("click", ".qty-update-btn", function () {
    //     var input = $('.qty-input');
    //     var newQty = input.val();
    //     var row = input.closest("tr");
    //     var qtyCell = row.find(".qty-cell");
    //     var productId = qtyCell.data("id");

    //     $.ajax({
    //         url: "/update-qty", 
    //         type: "POST",
    //         data: {
    //             _token: $('meta[name="csrf-token"]').attr("content"),
    //             id: productId,
    //             qty: newQty
    //         },
    //         success: function (response) {
    //             // qtyCell.find(".qty-text").text(newQty).removeClass("d-none");
    //             // input.addClass("d-none");
    //             loadPrepOrderTable(order_id);
    //         },
    //         error: function () {
    //             alert("Failed to update quantity");
    //             input.addClass("d-none");
    //             qtyCell.find(".qty-text").removeClass("d-none");
    //         }
    //     });
    // });

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


    $(document).on("click", ".daily-input-done", function () {
        let productId = $(this).data("id");
        let detailId = $(this).data("detail-id");
        // console.log(detailId);
        let qty = $('.qty-' + productId).text();
        console.log(qty);

        var employeeId = $('#employee_id').val();
        if (!employeeId) {
            alert('Please select an employee.');
            return;
        }

        $.ajax({
            url: "/done-daily-input", 
            type: "POST",
            data: {
                _token: $('meta[name="csrf-token"]').attr("content"),
                id: productId,
                qty: qty,
                employee: employeeId
            },
            success: function (response) {
                loadPrepOrderTable(order_id);
                // alert("Updated daily input");
            },
            error: function () {
                alert("Failed to update daily input");
            }
        });
    });

    $(document).on("click", ".delete-product", function() {
        let productId = $(this).data("id");
        let row = $(this).closest("tr");


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

    $(document).ready(function() {
        loadPrepOrderTable(order_id);
        $('#create-order').on('click', function() {
            var employeeId = $('#employee_id').val();

            // if (!employeeId) {
            //     alert('Please select an employee.');
            //     return;
            // }

            $.ajax({
                url: '{{ route("prep-orders.create.order") }}', 
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    employee_id: employeeId
                },
                success: function(response) {
                    // loadPrepOrderTable(order_id);
                    // alert(response.message);
                    // $('#employee_id').val('');
                    if (response.order_id) {
                        window.location.href = "/prep-orders/edit/" + response.order_id;
                    } else {
                        alert("Something went wrong!");
                    }
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
        $('#prepOrderForm').on('submit', function(e) {
            e.preventDefault();
            // var order_id = {!! json_encode($order_id) !!};
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
                    // $('#employee_id').val('');
                    // $('#qty').val('');
                    if (response.exists) {
                        // Product already exists, show confirmation modal
                        $('#updateQtyModal').modal('show');
                        $('#existingQty').text(response.current_qty);
                        $('#newQty').val(response.current_qty);
                    } else {
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
</script>
@endsection
