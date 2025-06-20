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
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Products Record</h5>
                    <div class="add-btn d-flex align-items-center">
                        <div class="me-2">
                            <form id="filterForm" action="{{ route('products.index') }}" method="GET">
                                <input type="checkbox" id="temporaryProductFilter" name="temporary" class="me-2"> Temporary Products
                            </form>
                        </div>
                        <div class="d-none">
                            <a href="{{ route('import.products') }}" class="btn btn-primary me-2">Import Products</a>
                            <a href="{{ route('import.table') }}" class="btn btn-primary me-2">Import</a>
                            <a href="{{ route('products.create') }}" class="btn btn-primary">Add Product</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table_prodcut" class="table table-striped align-middle" style="width:100%">
                            <thead>
                                <tr>
                                    <th data-ordering="false" style="width:3%"><small>#</small></th>
                                    <th data-ordering="false" style="width:3%"><small>No</small></th>
                                    <th class="w-100" style="width:58%"><small>Item Name</small></th>
                                    <th style="width:10%"><small>MSKU/SKU</small></th>
                                    <th style="width:10%"><small>ASIN/ITEM.ID</small></th>
                                    <th style="width:10%"><small>FNSKU/GTIN</small></th>
                                    <th style="width:3%"><small>PACK</small></th>
                                    <th style="width:3%"><small>QTY</small></th>
                                </tr>
                            </thead>
                            <tbody>
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
    var table = '';
    $(document).ready(function() {
        $('[data-toggle="tooltip"]').tooltip();
        if ($.fn.DataTable.isDataTable('#table_prodcut')) {
            $('#table_prodcut').DataTable().destroy();
        }
        table = $('#table_prodcut').DataTable({
            processing: true,
            serverSide: true,
            pageLength: userPerPageLength,
            ajax: {
                url: "{{ route('products.data.merge') }}",
                type: 'GET',
                data: function(d) {
                    d.temporary = $('#temporaryProductFilter').is(':checked') ? 'on' : 'off';
                }
            },
            columns: [
                { data: 'checkbox', name: 'checkbox', orderable: false,searchable: false  },
                { data: 'id', name: 'id' },
                { data: 'item', name: 'item', orderable: false },
                { data: 'msku', name: 'msku' },
                { data: 'asin', name: 'asin' },
                { data: 'fnsku', name: 'fnsku' },
                { data: 'pack', name: 'pack' },
                { data: 'qty', name: 'qty', searchable: false }
            ],
            drawCallback: function() {
                // Reapply custom button aligned with the pagination controls after each redraw
                if (!$('#customButton').length) {
                    // $('#table_prodcut_paginate ul').css('display', 'inline-block');
                    $('#table_prodcut_paginate').css('text-align', 'right')
                        .prepend('<a href="#" class="btn btn-success mt-1 mb-2 d-none" onclick="mergeProducts()" id="merge-btn">Merge Product</a>');
                }
            }
            // order: [],
        });
        $('#temporaryProductFilter').on('change', function() {
            table.ajax.reload(); // Reload the DataTable with the new filter
        });
        $('#merge-btn1').on('click', function() {
            var checkedValues = $('input[name="select_products"]:checked').map(function() {
                return $(this).val();
            }).get();
            // console.log(checkedValues);

            $.ajax({
                url: '{{ route("temp.products.merge") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    select_products: checkedValues
                },
                success: function(response) {
                    // alert('Products merged successfully!');
                    var successAlert = $('#success-alertj');
                    $('#success-alertj').removeClass('d-none');
                    successAlert.text('Products merged successfully!');
                    successAlert.fadeIn('slow');
                    localStorage.setItem('tab_id', 3);
                    table.ajax.reload();
                },
                error: function(xhr) {
                    alert('An error occurred while merging products.');
                }
            });
        });
    });
    function checkBox(id){
        var checkedCount = $('input[name="select_products"]:checked').length;
        // Show or hide the button based on the count
        if (checkedCount === 2) {
            $('#merge-btn').removeClass('d-none');
        } else {
            $('#merge-btn').addClass('d-none');
        }
    }
    function mergeProducts(){
        var checkedValues = $('input[name="select_products"]:checked').map(function() {
                return $(this).val();
            }).get();
            // console.log(checkedValues);

            $.ajax({
                url: '{{ route("temp.products.merge") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    select_products: checkedValues
                },
                success: function(response) {
                    // alert('Products merged successfully!');
                    var successAlert = $('#success-alertj');
                    $('#success-alertj').removeClass('d-none');
                    successAlert.text('Products merged successfully!');
                    successAlert.fadeIn('slow');
                    localStorage.setItem('tab_id', 3);
                    table.ajax.reload();
                },
                error: function(xhr) {
                    alert('An error occurred while merging products.');
                }
            });
    }
   
</script>
@endsection