@extends('layouts.app')

@section('title', 'Shipping Plan List | Prepcenter')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/datatables.min.css') }}">
<link rel="stylesheet" href="{{ asset('css/dataTables.bootstrap5.min.css') }}">
@endsection

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mt-4">Shipping Plan List</h4>
            <a href="{{ url('shipping-plans/create') }}" class="btn btn-sm btn-info mt-3">Create New</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="shippingPlansTable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Amazon ID</th>
                            <th>No of SKU</th>
                            <th>Total Units</th>
                            <th>Total Boxes</th>
                            <th>Total Weight</th>
                            <th>Handling Charge</th>
                            <th>Shipping Cost</th>
                            <th>Total Charge</th>
                            <th>Cost per Unit</th>
                            <th>Cost per lb</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="{{ asset('js/jquery.min.js') }}"></script>
<script src="{{ asset('js/datatables.min.js') }}"></script>
<script src="{{ asset('js/dataTables.bootstrap5.min.js') }}"></script>
<script>
$(function() {
    $('#shippingPlansTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('shipping-plans.index') }}",
        columns: [
            { data: 'date', name: 'date' },
            { data: 'amazon_id', name: 'amazon_id' },
            { data: 'no_of_sku', name: 'no_of_sku' },
            { data: 'total_units', name: 'total_units' },
            { data: 'total_boxes', name: 'total_boxes' },
            { data: 'total_weight', name: 'total_weight' },
            { data: 'handling_cost', name: 'handling_cost' },
            { data: 'shipping_cost', name: 'shipping_cost' },
            { data: 'total_charge', name: 'total_charge' },
            { data: 'cost_per_unit', name: 'cost_per_unit', orderable: false, searchable: false },
            { data: 'cost_per_lb', name: 'cost_per_lb', orderable: false, searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ]
    });
});
</script>
@endsection
