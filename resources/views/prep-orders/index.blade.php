@extends('layouts.app')

@section('title', 'Prep Orders List | Prepcenter')

@section('styles')
<style>
    .badge-active {
        background-color: #28a745;
        color: white;
    }
    .badge-warning {
        background-color: rgb(214, 186, 28);
        color: white;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mt-4">Prep Work Orders List</h4>
            <button id="toggleCompletedBtn" class="btn btn-sm btn-info mt-3">Show Completed</button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Assigned to</th>
                            <th>Status</th>
                            <th>Created By</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($prep_orders as $order)
                            <tr class="{{ $order->status == 1 ? 'completed-order-row d-none' : '' }}">
                                <td>
                                  <div class="d-flex justify-content-between">
                                      <a href="{{ route('prep-orders.editData', $order->custom_id) }}">
                                        {{ $order->custom_id }} -  {{ $order->name }}
                                    </a>

                                    <a href="{{ route('prep-orders.editData', $order->custom_id) }}" class="btn btn-sm btn-primary ms-2">
                                        View
                                    </a>
                                  </div>
                                </td>
                                <td>{{ optional($order->employee)->name ?? 'unassigned' }}</td>
                                <td>
                                    @if($order->status == 1)
                                        <span class="badge badge-active">Completed</span>
                                    @else
                                        <span class="badge badge-warning">Pending</span>
                                    @endif
                                </td>                                
                                <td>{{ optional($order->createdBy)->name ?? 'N/A' }}</td>
                                <td>{{ optional($order->createdBy)->created_at ?? 'N/A' }}</td>
                                <td>
                                    @if($order->details->count() == 0)
                                        <button class="btn btn-danger btn-sm deleteOrder" data-id="{{ $order->id }}">
                                            Delete
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
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
<script>
    $(document).on('click', '.deleteOrder', function () {
        let orderId = $(this).data('id');
        
        if (confirm("Are you sure you want to delete this order?")) {
            $.ajax({
                url: `/prep-orders/${orderId}`,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function (response) {
                    alert('Order deleted successfully.');
                    location.reload(); // or use DataTable().ajax.reload() if using DataTables
                },
                error: function (xhr) {
                    alert('Something went wrong. Could not delete the order.');
                }
            });
        }
    });
    let isVisible = false;
    $('#toggleCompletedBtn').click(function () {
        isVisible = !isVisible;
        $('.completed-order-row').toggleClass('d-none', !isVisible);
        $(this).text(isVisible ? 'Hide Completed' : 'Show Completed');
    });

    
</script>
@endsection
