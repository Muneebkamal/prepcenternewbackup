@extends('layouts.app')

@section('title', 'Expenses | Prepcenter')

@section('content')
<div class="container-fluid">
    <style>
        .select2-container {
            z-index: 9999 !important;
        }

    </style>

    <!-- Flash Messages -->
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <!-- Categories Table -->
    <div class="row">
        <div class="col-lg-12 p-0">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <h5 class="card-title mb-0">Expeneses</h5>
                    <!-- Add Category Button -->
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addExpenseModal" id="addExpenseButton">
                        Add Expense
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="expnseTable" class="table table-striped align-middle" style="width:100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Category</th>
                                    <th>Start Date</th>
                                    <th>Amount</th>
                                    <th>Description</th>
                                    <th>Repeat</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Category Modal -->
    <div class="modal fade" id="addExpenseModal" tabindex="-1" aria-labelledby="addExpenseModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addExpenseModalLabel">Add Expense</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="expenseForm" method="POST">
                    <div class="modal-body">
                        <!-- Hidden input for ID -->
                        <input type="hidden" name="id" id="id">
                
                        <div class="row">
                            <!-- Category -->
                                <div class="col-md-6 mb-3 position-relative">
                                    <label for="category" class="form-label d-flex justify-content-between align-items-center">
                                        Category
                                        <button type="button" id="addCategoryButton" class="btn btn-sm btn-outline-primary" style="font-size: 0.85rem;">
                                            <i class="ri-add-fill"></i> Add
                                        </button>
                                    </label>
                                     <!-- Inline form to add new category -->
                                     <div id="addCategoryForm" class="mt-2" style="display: none;">
                                        <div class="input-group">
                                            <input type="text" id="newCategoryName" class="form-control" placeholder="Enter new category name" />
                                            <button type="button" id="saveCategoryButton" class="btn btn-success">
                                                Save
                                            </button>
                                        </div>
                                    </div>
                                    <select name="category_id" id="category" class="form-select select2" required>
                                        <!-- Select2 will dynamically populate options -->
                                    </select>
                                   
                                </div>
                                

                
                            <!-- Name -->
                            <div class="col-md-6 mb-3">
                                <label for="amount" class="form-label">Amount</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" name="amount" id="amount" class="form-control" placeholder="Enter amount" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <!-- Starting Date -->
                            <div class="col-md-6 mb-3">
                                <label for="starting_date" class="form-label">Starting Date</label>
                                <input type="date" name="starting_date" id="starting_date" class="form-control" required>
                            </div>
                
                            <!-- Type -->
                            <div class="col-md-6 mb-3">
                                <label for="type" class="form-label">Repeat</label>
                                <select name="type" id="type" class="form-select" required>
                                    <option value="daily">Daily</option>
                                    <option value="weekly">Weekly</option>
                                    <option value="monthly">Monthly</option>
                                    <option value="yearly">Yearly</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <!-- Description -->
                            <div class="col-md-12 mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea name="description" id="description" class="form-control" rows="3" placeholder="Enter description"></textarea>
                            </div>
                        </div>
                    </div>
                
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Expense</button>
                    </div>
                </form>
                
                
            </div>
        </div>
    </div>
    <!-- End Add Category Modal -->

</div>
@endsection

@section('script')
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
     var expenseTable ='';
    $(document).ready(function() {
      // Show/hide the inline form for adding a new category
        $('#addCategoryButton').on('click', function () {
            $('#addCategoryForm').toggle(); // Toggle visibility
        });
        // Save new category logic
        $('#saveCategoryButton').on('click', function () {
            const newCategoryName = $('#newCategoryName').val().trim();
            if (newCategoryName === '') {
                alert('Please enter a category name.');
                return;
            }
            // Send an AJAX request to save the new category
            $.ajax({
                url: '{{ route("categories.store") }}', // Laravel route for storing categories
                type: 'POST',
                data: {
                    name: newCategoryName,
                    _token: '{{ csrf_token() }}', // CSRF token for security
                },
                success: function (response) {
                    if (response.success) {
                        // Append the new category to the Select2 dropdown
                        const newOption = new Option(response.data.name, response.data.id, true, true);
                        $('#category').append(newOption).trigger('change');

                        // Clear and hide the form
                        $('#newCategoryName').val('');
                        $('#addCategoryForm').hide();
                    } else {
                        alert(response.message || 'Failed to add category.');
                    }
                },
                error: function () {
                    alert('An error occurred while adding the category.');
                },
            });
        });
        expenseTable = $('#expnseTable').DataTable({
            processing: true,
            serverSide: true,
            pageLength: userPerPageLength, // Default page length
            ajax: '{{ route("expenses.index") }}',
            columns: [
                { 
                    data: null, 
                    name: 'index', 
                    orderable: false,  // Prevent sorting on this column
                    searchable: false, // Prevent searching on this column
                    render: function (data, type, row, meta) {
                        return meta.row + 1; // Display the index (row number)
                    } 
                },
                { data: 'name', name: 'name' },
                { data: 'starting_date', name: 'starting_date' },
                { data: 'amount', name: 'amount' },
                { data: 'description', name: 'description' },
                { data: 'type', name: 'type' },
                { data: 'actions', name: 'actions', orderable: false, searchable: false }
            ]
        });
        // Save Category with AJAX
        $('#expenseForm').on('submit', function (e) {
            e.preventDefault(); // Prevent default form submission
            // Get form data
            const formData = $(this).serialize(); // Serialize all form data (includes CSRF and all fields)
            $.ajax({
                url: '{{ route("expneses.store") }}', // URL to send data to
                method: 'POST', // HTTP method
                data: formData, // Pass serialized form data
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') // Fetch token from meta tag
                },
                success: function (response) {
                    // Handle success response
                    $('#addExpenseModal').modal('hide'); // Close modal
                    $('#expenseForm')[0].reset(); // Reset form fields
                    $('#form-errors').addClass('d-none').html(''); // Clear errors
                    // Reload DataTable
                    expenseTable.ajax.reload();
                    // Show success message
                    alert(response.message || 'Expenese added successfully!');
                },
                error: function (xhr) {
                    // Handle errors
                    const errors = xhr.responseJSON.errors;
                    let errorHtml = '<ul>';
                    for (const key in errors) {
                        errorHtml += `<li>${errors[key][0]}</li>`;
                    }
                    errorHtml += '</ul>';
                    $('#form-errors').removeClass('d-none').html(errorHtml);
                }
            });
        });
        $('#addExpenseButton').on('click', function () {
            // Clear all inputs inside the modal, including hidden inputs
            $('#addExpenseModal').find('input').val('');
            $('#form-errors').addClass('d-none').html('');
            $('#category').select2(
                {
                    dropdownParent: $('#addExpenseModal')
                }
            );
            categoryList();
        });
    });
    function editExpense(button){
        categoryList();
        const expenseId = $(button).data('id');
        const categoryId = $(button).data('category-id');
        const name = $(button).data('name');
        const amount = $(button).data('amount');
        const startingDate = $(button).data('starting-date');
        const type = $(button).data('type');
        const description = $(button).data('description');
        // Populate the modal fields
        $('#addExpenseModal input[name="id"]').val(expenseId);
         // Assuming you use Select2
        $('#addExpenseModal input[name="amount"]').val(amount);
        $('#addExpenseModal input[name="starting_date"]').val(startingDate);
        $('#addExpenseModal select[name="type"]').val(type);
        $('#addExpenseModal textarea[name="description"]').val(description);

        // Open the modal
        $('#addExpenseModal').modal('show');
        setTimeout(() => {
            console.log(categoryId)
            $('#addExpenseModal select[name="category_id"]').val(categoryId).trigger('change');
        }, 1000);
    }
    function deleteExpnse(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                // Make an AJAX request to delete the category
                $.ajax({
                    url: `{{ url('expneses/destroy/${id}') }}`, // Adjust the route as per your backend
                    method: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}' // CSRF token for Laravel
                    },
                    success: function(response) {
                        if (response.success) {
                            // Reload the DataTable or update the UI
                            expenseTable.ajax.reload();
                        } else {
                            Swal.fire(
                                'Error!',
                                response.message || 'An error occurred while deleting the category.',
                                'error'
                            );
                        }
                    },
                    error: function(xhr) {
                        Swal.fire(
                            'Error!',
                            'An error occurred while processing your request.',
                            'error'
                        );
                    }
                });
            }
        });
    }
    function categoryList(){
        // Clear previous options
        $('#category').empty();
        // Add placeholder option
        $('#category').append('<option value="" disabled selected>Loading categories...</option>');
        // Make AJAX request to fetch categories
        $.ajax({
            url: '{{ route("categories.list") }}', // Replace with your route
            type: 'GET',
            dataType: 'json',
            aysnc:false,
            success: function (response) {
                // Clear loading text
                $('#category').empty();
                // Add placeholder option
                $('#category').append('<option value="" disabled selected>Select a category</option>');

                // Append fetched options
                response.forEach(function (category) {
                    $('#category').append(`<option value="${category.id}">${category.name}</option>`);
                });
            },
            error: function () {
                // Handle errors
                $('#category').empty();
                $('#category').append('<option value="" disabled>Error loading categories</option>');
            },
        });
    }




</script>
@endsection
