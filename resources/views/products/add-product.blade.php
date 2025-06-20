@extends('layouts.app')

@section('title', 'Add Product | Prepcenter')

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
                <div class="card-header align-items-center d-flex">
                    <h4 class="card-title mb-0 flex-grow-1">Add Product</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <form id="productForm">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mt-2">
                                <div class="input-group">
                                    <span class="input-group-text">Item</span>
                                    <input type="text" class="form-control" name="item" placeholder="Item / Title Product" required>
                                </div>
                                @error('item')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="col-md-6 mt-2">
                                <div class="input-group">
                                    <span class="input-group-text">MSKU</span>
                                    <input type="text" class="form-control" name="msku" placeholder="MSKU / SKU" required>
                                </div>
                                @error('msku')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="col-md-6 mt-2">
                                <div class="input-group">
                                    <span class="input-group-text">ASIN / ITEM ID</span>
                                    <input type="text" class="form-control" name="asin" placeholder="ASIN / ITEM ID" required>
                                </div>
                                @error('asin')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="col-md-6 mt-2">
                                <div class="input-group">
                                    <span class="input-group-text">FNSKU</span>
                                    <input type="text" class="form-control" name="fnsku" placeholder="FNSKU / GTIN" required>
                                </div>
                                @error('fnsku')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="col-md-6 mt-2">
                                <div class="input-group">
                                    <span class="input-group-text">Pack</span>
                                    <input type="text" class="form-control" name="pack" placeholder="Pack" required>
                                </div>
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
                                <!-- Poly Bag -->
                                <div class="col-md-6 mb-3">
                                    <label for="poly_bag" class="form-label">
                                        <input type="checkbox" id="poly_bag" name="poly_bag" class="form-check-input">
                                        Poly Bag
                                    </label>
                                </div>

                            
                                <!-- Shrink Wrap -->
                                <div class="col-md-6 mb-3">
                                    <label for="shrink_wrap" class="form-label">
                                        <input type="checkbox" id="shrink_wrap" name="shrink_wrap" class="form-check-input">
                                        Shrink Wrap
                                    </label>
                                </div>
                                
                                @php
                                    $polyBagSizeOptions = $poly_bag_size->options->sortBy(function ($option) {
                                        preg_match_all('/\d+/', $option->value, $matches);
                                        $numbers = $matches[0] ?? [0]; // Extract numeric values
                                        return array_map('intval', $numbers); // Convert to integers
                                    });
                                @endphp
                                <!-- Poly Bag Size -->
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex justify-content-between">
                                        <label for="poly_bag_size" class="form-label">Poly Bag Size</label>
                                        <button class="btn btn-sm btn-primary mb-2" type="button" id="addOptionBtn{{ $poly_bag_size->id }}" data-label-type="{{ $poly_bag_size->type }}" data-label-id="{{ $poly_bag_size->id }}">Add New</button>
                                        <div></div>
                                    </div>
                                    
                                    <select id="poly_bag_size" name="poly_bag_size" class="form-select">
                                        <option value="">Select Size</option>
                                        @foreach ($polyBagSizeOptions as $option)
                                            <option value="{{ $option->value }}">{{ $option->value }}</option>
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
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex justify-content-between">
                                        <label for="shrink_wrap_size" class="form-label">Shrink Wrap Size</label>
                                        <button class="btn btn-sm btn-primary mb-2" type="button" id="addOptionBtn{{ $shrink_wrap_size->id }}" data-label-type="{{ $shrink_wrap_size->type }}" data-label-id="{{ $shrink_wrap_size->id }}">Add New</button>
                                        <div></div>
                                    </div>
                                    
                                    <select id="shrink_wrap_size" name="shrink_wrap_size" class="form-select">
                                        <option value="">Select Size</option>
                                        @foreach ($shrinkWrapSizeOptions as $option)
                                            <option value="{{ $option->value }}">{{ $option->value }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <!-- Label 1 -->
                                <div class="col-md-6 mb-3">
                                    <label for="label_1" class="form-label">Label 1</label>
                                    <select id="label_1" name="label_1" class="form-select">
                                        <option value="">Select Label</option>
                                        @foreach ($label_1->options as $option)
                                            <option value="{{ $option->value }}">{{ $option->value }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            
                                <!-- Number of Pieces in a Carton -->
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex justify-content-between">
                                        <label for="no_of_pcs_in_carton" class="form-label">No of Pcs In a Carton</label>
                                        <label for="use_orignal_box" class="form-label">
                                            <input type="checkbox" id="use_orignal_box" name="use_orignal_box" class="form-check-input">
                                            Use Orignal Box
                                        </label>
                                        <div></div>
                                    </div>
                                    
                                    <input type="number" id="no_of_pcs_in_carton" name="no_of_pcs_in_carton" min="1" class="form-control">
                                </div>
                                <!-- Label 2 -->
                                <div class="col-md-6 mb-3">
                                    <label for="label_2" class="form-label mb-3">Label 2</label>
                                    <select id="label_2" name="label_2" class="form-select">
                                        <option value="">Select Label</option>
                                       
                                        @foreach ($label_2->options as $option)
                                            <option value="{{ $option->value }}">{{ $option->value }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                @php
                                    $cartonSizeOptions = $carton_size->options->sortBy(function ($option) {
                                        preg_match_all('/\d+/', $option->value, $matches);
                                        $numbers = $matches[0] ?? [0]; // Extract numeric values
                                        return array_map('intval', $numbers); // Convert to integers
                                    });
                                @endphp
                                <!-- Carton Size -->
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex justify-content-between">
                                        <label for="carton_size" class="form-label">Carton Size</label>
                                        <button class="btn btn-sm btn-primary mb-2" type="button" id="addOptionBtn{{ $carton_size->id }}" data-label-type="{{ $carton_size->type }}" data-label-id="{{ $carton_size->id }}">Add New</button>
                                        <div></div>
                                    </div>
                                    <select id="carton_size" name="carton_size" class="form-select">
                                        <option value="">Select Size</option>

                                        @foreach ($cartonSizeOptions as $option)
                                            <option value="{{ $option->value }}">{{ $option->value }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <!-- Label 3 -->
                                <div class="col-md-6 mb-3">
                                    <label for="label_3" class="form-label">Label 3</label>
                                    <select id="label_3" name="label_3" class="form-select">
                                        <option value="">Select Label</option>

                                        @foreach ($label_3->options as $option)
                                            <option value="{{ $option->value }}">{{ $option->value }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="weight" class="form-label">Weight/Pounds</label>
                                    <input type="text" class="form-control" name="weight" id="weight">
                                </div>
                                <div class="col-md-6">
                                    <label for="packing_link" class="form-label">Packing Link</label>
                                    <input type="text" name="packing_link" id="packing_link" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label for="bubble_wrap" class="form-label">Bubble wrap </label>
                                    <input type="text" name="bubble_wrap" id="bubble_wrap" class="form-control">
                                </div>
                                
                                <div class="col-md-12 mt-3">
                                    <label for="packing_note" class="form-label">Packing Note</label>
                                    <textarea name="packing_note" id="packing_note" class="form-control"></textarea>
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
                                    {{-- <div class="d-flex justify-content-between">
                                        <label for="cotton_size_sales" class="form-label">Carton Size</label>
                                        <button class="btn btn-sm btn-primary mb-2" id="addOptionBtn{{ $carton_size->id }}" data-label-type="{{ $carton_size->type }}" data-label-id="{{ $carton_size->id }}">Add New</button>
                                        <div></div>
                                    </div> --}}
                                    <label for="cotton_size_sales" class="form-label">Carton Size</label>
                                    <select id="cotton_size_sales" name="cotton_size_sales" class="form-select">
                                        <option value="">Select Cotton Size</option>
                                        @foreach ($cartonSizeOptions as $option)
                                            <option value="{{ $option->value }}">{{ $option->value }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Weight</label>
                                        <div class="input-group">
                                            <input type="number" id="weight_lb" name="weight_lb" class="form-control" placeholder="Pounds">
                                            <div class="input-group-append input-group-prepend">
                                                <span class="input-group-text">×</span>
                                            </div>
                                            <input type="number" name="weight_oz" id="weight_oz" class="form-control" placeholder="Ounces">
                                        </div>
                                    </div>
                                </div>
                            </div>                            

                        </fieldset>
                        <div class="row mt-1">
                            <div class="col-md-12">
                                <div class="d-flex justify-content-end mt-3">
                                    <button type="button" class="btn btn-danger" id="resetButton">RESET</button>
                                    <button type="submit" class="btn btn-primary ms-2">SAVE</button>
                                </div>
                            </div>
                        </div>                   
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- end page title -->

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
        $('#resetButton').click(function() {
            $('#productForm')[0].reset(); // [0] is used to access the DOM element
        });

        $('#productForm').on('submit', function(e) {
            e.preventDefault(); // Prevent the default form submission

            // Create FormData object
            let formData = new FormData(this);

            // Send AJAX request
            $.ajax({
                url: "{{ route('products.store') }}",
                type: 'POST',
                data: formData,
                contentType: false, // Tell jQuery not to set content type
                processData: false, // Tell jQuery not to process data
                success: function(response) {
                    if (response.success) {
                        window.location.href = "{{ route('products.index') }}"; // Redirect to the URL
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
                <div class="d-flex align-items-center gap-2">
                    <input type="number" id="heightoption${id}" class="form-control" style="width: 50px;" placeholder="8">
                    <span class="mx-1">X</span>
                    <input type="number" id="widthoption${id}" class="form-control" style="width: 50px;" placeholder="10">
                    <span class="mx-1">X</span>
                    <input type="number" id="weightoption${id}" class="form-control" style="width: 50px;" placeholder="5">
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
        } else {
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

        if (labelType === 'poly_bag_size') {
            const height = document.getElementById(`heightoption${labelId}`).value;
            const width = document.getElementById(`widthoption${labelId}`).value;
            value = `${height}" X ${width}"`;
        } else if (labelType === 'carton_size') {
            const height = document.getElementById(`heightoption${labelId}`).value;
            const width = document.getElementById(`widthoption${labelId}`).value;
            const weight = document.getElementById(`weightoption${labelId}`).value;
            value = `${height}" X ${width}" X ${weight}"`;
        } else if (labelType === 'shrink_wrap_size') {
            const height = document.getElementById(`heightoption${labelId}`).value;
            const width = document.getElementById(`widthoption${labelId}`).value;
            value = `${height}" X ${width}"`;
        } else {
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
                        $('#carton_size').append(newOption);
                        $(`#height${labelId}`).val('');
                        $(`#width${labelId}`).val('');
                        $(`#weight${labelId}`).val('');
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
</script>
@endsection