@extends('layouts.app')

@section('title', 'Categories | Prepcenter')

@section('content')
<div class="container-fluid">

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
                    <h5 class="card-title mb-0">Categories</h5>
                    <!-- Add Category Button -->
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addCategoryModal" id="addCategoryButton">
                        Add Category
                    </button>
                </div>
                <div class="card-body">
                    <table id="categoriesTable" class="table dt-responsive nowrap table-striped align-middle" style="width:100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Category Modal -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCategoryModalLabel">Add Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form  id="categoryForm" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="id">
                        <div class="mb-3">
                            <label for="categoryName" class="form-label">Category Name</label>
                            <input type="text" name="name" id="categoryName" class="form-control" placeholder="Enter category name" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- End Add Category Modal -->

</div>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
     var categoriesTable ='';
    $(document).ready(function() {
        categoriesTable = $('#categoriesTable').DataTable({
            processing: true,
            serverSide: true,
            pageLength: userPerPageLength, // Default page length
            ajax: '{{ route("categories.index") }}',
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
                { data: 'actions', name: 'actions', orderable: false, searchable: false }
            ]
        });
        // Save Category with AJAX
        $('#categoryForm').on('submit', function (e) {
            e.preventDefault(); // Prevent default form submission
            // Get form data
            const formData = $(this).serialize(); // Serialize all form data (includes CSRF and all fields)
            $.ajax({
                url: '{{ route("categories.store") }}', // URL to send data to
                method: 'POST', // HTTP method
                data: formData, // Pass serialized form data
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') // Fetch token from meta tag
                },
                success: function (response) {
                    // Handle success response
                    $('#addCategoryModal').modal('hide'); // Close modal
                    $('#categoryForm')[0].reset(); // Reset form fields
                    $('#form-errors').addClass('d-none').html(''); // Clear errors
                    // Reload DataTable
                    categoriesTable.ajax.reload();
                    // Show success message
                    alert(response.message || 'Category added successfully!');
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
        $('#addCategoryButton').on('click', function () {
            // Clear all inputs inside the modal, including hidden inputs
            $('#addCategoryModal').find('input').val('');
            $('#form-errors').addClass('d-none').html('');
        });
    });
    function editCategory(id,name){
        $("#id").val(id),
        $("#categoryName").val(name),
        $('#addCategoryModal').modal('show');
    }
    function deleteCategory(id) {
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
                    url: `{{ url('categories/destroy/${id}') }}`, // Adjust the route as per your backend
                    method: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}' // CSRF token for Laravel
                    },
                    success: function(response) {
                        if (response.success) {
                            // Reload the DataTable or update the UI
                            categoriesTable.ajax.reload();
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

</script>
@endsection
