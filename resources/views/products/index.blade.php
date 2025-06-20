@extends('layouts.app')

@section('title', 'Products | Prepcenter')

@section('styles')
<style>
    #table_prodcut_filter {
        display: flex;
        justify-content: center;
    }
    #table_prodcut_filter label input {
        width: 100%;
    }
    .truncate {
        max-width: 500px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
</style>
@endsection

@section('content')

<div class="container-fluid">
                        
    <!-- start page title -->
    {{-- <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Products Information</h4>

                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Forms</a></li>
                        <li class="breadcrumb-item active">Basic Elements</li>
                    </ol>
                </div>

            </div>
        </div>
    </div> --}}
    <button id="fetch-images-btn" class="btn btn-sm btn-primary mb-3">Fetch Missing Product Images</button>
<div id="progress-status"></div>

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

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <div class="row w-100 d-flex align-items-center">
                        <div class="col-md-6">
                            <h5 class="card-title mb-0">Products Record</h5>
                        </div>
                        <div class="col-md-6">
                            <div class="add-btn d-flex justify-content-end">
                                <div class="row w-100 d-flex align-items-center">
                                    <div class="col-md-6 d-flex justify-content-end">
                                        <div class="me-2 mb-2 d-flex align-items-center">
                                            <form id="filterForm" action="{{ route('products.index') }}" method="GET">
                                                <input type="checkbox" id="temporaryProductFilter" name="temporary" class="me-2"> Temporary Products
                                            </form>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex justify-content-end">
                                            <a href="{{ route('import.products') }}" class="btn btn-sm btn-primary me-2">Import Products</a>
                                            <a href="{{ route('import.table') }}" class="btn btn-sm btn-primary me-2">Import</a>
                                            <a href="{{ route('products.create') }}" class="btn btn-sm btn-primary">Add Product</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table_prodcut" class="table table-striped align-middle" style="width:100%">
                            <thead>
                                <tr>
                                    <th style="width:5%"><small>No</small></th>
                                    <th style="width:45%"><small>Item Name</small></th>
                                    <th style="width:10%"><small>MSKU/SKU</small></th>
                                    <th style="width:15%"><small>ASIN/ITEM.ID</small></th>
                                    <th style="width:10%"><small>FNSKU/GTIN</small></th>
                                    <th style="width:5%"><small>PACK</small></th>
                                    <th style="width:2%"><small>QTY</small></th>
                                    <th style="width:8%"><small>Actions</small></th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                $counter = 1;
                                @endphp
                               @isset($productsss)
                               @foreach($products as $product)
                                    <tr>
                                        <td class="py-1"><small>{{ $counter }}</small></td>
                                        <td class="py-1 truncate" data-toggle="tooltip" title="{{ $product->item }}">
                                            <a href="{{ route('show.edit', $product->id) }}">
                                                {{-- <small>{{ Str::limit($product->item, 60, '...') }}</small> --}}
                                                <small>{{ $product->item }}</small>
                                            </a>
                                        </td>
                                        <td class="py-1 fw-bold"><small>{{ $product->msku }}</small></td>
                                        <td class="py-1"><small>{{ $product->asin }}</small></td>
                                        <td class="py-1"><small>{{ $product->fnsku }}</small></td>
                                        <td class="py-1">
                                            {{-- @if($product->pack <= 0 || $product->pack == '')
                                                1
                                            @else --}}
                                            <small>{{ $product->pack }}</small>
                                            {{-- @endif --}}
                                        </td>
                                        <td class="py-1"><small>{{ $product->dailyInputDetails->first()->total_qty ?? 1 }}</small></td>
                                        {{-- <td class="py-1">
                                            <a href="{{ route('products.edit', $product->id) }}" class="edit-item-btn text-muted"><i class="ri-pencil-fill align-bottom me-2"></i></a>
                                        </td> --}}
                                    </tr>
                                    @php
                                    $counter++;
                                    @endphp
                               @endforeach
                               @endisset
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div><!--end col-->
    </div><!--end row-->
    
    <!-- end page title -->

</div>

@endsection

@section('script')
<script src="{{ asset('js/jquery.min.js') }}"></script>
<script src="{{ asset('js/datatables.min.js') }}"></script>
<script src="{{ asset('js/dataTables.bootstrap5.min.js') }}"></script>
<script>
    $(document).ready(function() {
        $('[data-toggle="tooltip"]').tooltip();
        if ($.fn.DataTable.isDataTable('#table_prodcut')) {
            $('#table_prodcut').DataTable().destroy();
        }
        var table = $('#table_prodcut').DataTable({
            processing: true,
            serverSide: true,
            pageLength: userPerPageLength, // Default page length
            // lengthMenu: [10, 25, 50, 100, 250, 500], // Options for the per-page dropdown
            ajax: {
                url: "{{ route('products.data') }}",
                type: 'GET',
                data: function(d) {
                    d.temporary = $('#temporaryProductFilter').is(':checked') ? 'on' : 'off';
                }
            },
            columns: [
                { data: 'id', name: 'id', orderable: false },
                { data: 'item', name: 'item', orderable: false },
                { data: 'msku', name: 'msku' },
                { data: 'asin', name: 'asin' },
                { data: 'fnsku', name: 'fnsku' },
                { data: 'pack', name: 'pack' },
                { data: 'qty',name: 'total_qty', searchable: false },
                { data: 'actions', name: 'actions', searchable: false }
            ],
            order: [],
            dom: '<"row" <"col-sm-12 col-md-12"l> <"col-sm-12 col-md-12"Bf>>' + // Adjust the layout of buttons and lengthMenu
         '<"row" <"col-sm-12"tr>>' + 
         '<"row" <"col-sm-12 col-md-5"i> <"col-sm-12 col-md-7"p>>',
            buttons: [
                {
                    extend: 'csvHtml5',
                    text: 'Export CSV',
                    title: function() {
                        let now = new Date();
                        let month = ('0' + (now.getMonth() + 1)).slice(-2);
                        let day = ('0' + now.getDate()).slice(-2);
                        let year = now.getFullYear();
                        let dateStr = month + '-' + day + '-' + year;

                        let isChecked = $('#temporaryProductFilter').is(':checked');
                        return isChecked ? 'Products_Record_Temporary_Products_' + dateStr : 'Products_Record_' + dateStr;
                    },
                    filename: function() {
                        let now = new Date();
                        let month = ('0' + (now.getMonth() + 1)).slice(-2);
                        let day = ('0' + now.getDate()).slice(-2);
                        let year = now.getFullYear();
                        let dateStr = month + '-' + day + '-' + year;

                        let isChecked = $('#temporaryProductFilter').is(':checked');
                        return isChecked ? 'Products_Record_Temporary_Products_' + dateStr : 'Products_Record_' + dateStr;
                    }
                },
                {
                    extend: 'excelHtml5',
                    text: 'Export Excel',
                    title: function() {
                        let now = new Date();
                        let month = ('0' + (now.getMonth() + 1)).slice(-2);
                        let day = ('0' + now.getDate()).slice(-2);
                        let year = now.getFullYear();
                        let dateStr = month + '-' + day + '-' + year;

                        let isChecked = $('#temporaryProductFilter').is(':checked');
                        return isChecked ? 'Products_Record_Temporary_Products_' + dateStr : 'Products_Record_' + dateStr;
                    },
                    filename: function() {
                        let now = new Date();
                        let month = ('0' + (now.getMonth() + 1)).slice(-2);
                        let day = ('0' + now.getDate()).slice(-2);
                        let year = now.getFullYear();
                        let dateStr = month + '-' + day + '-' + year;

                        let isChecked = $('#temporaryProductFilter').is(':checked');
                        return isChecked ? 'Products_Record_Temporary_Products_' + dateStr : 'Products_Record_' + dateStr;
                    }
                }
            ]
        });
        $('#temporaryProductFilter').on('change', function() {
            table.ajax.reload(); // Reload the DataTable with the new filter
        });
    });
       $(document).on('click', '.delete-product', function () {
        var productId = $(this).data('id');

        if (confirm('Are you sure you want to delete this product?')) {
            $.ajax({
                url: '/products/' + productId, // Adjust if using a different route
                type: 'DELETE',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    // Optionally, refresh DataTable
                    $('#table_prodcut').DataTable().ajax.reload(null, false);
                    alert('Product deleted successfully.');
                },
                error: function (xhr) {
                    alert('Failed to delete product.');
                }
            });
        }
    });
</script>


<script>
    $(document).ready(function () {
        let currentPage = 1; // Start with the first page

        $('#fetch-images-btn').on('click', function () {
            fetchProductImages(currentPage);
        });

        function fetchProductImages(page) {
            $.ajax({
                url: `{{url('getAllProduct')}}`, // Update this to match your route
                method: 'GET',
                data: { page: page }, // Send the current page number
                success: function (response) {
                    if (response.success) {
                        $('#progress-status').text(response.message);
                        currentPage++; // Increment the page number for the next call
                        fetchProductImages(currentPage); // Continue fetching the next chunk
                    } else {
                        $('#progress-status').text('All images have been processed.');
                    }
                },
                error: function () {
                    $('#progress-status').text('An error occurred while fetching product images.');
                }
            });
        }
    });
</script>

@endsection