@extends('layouts.app')

@section('title', 'Shipping Plan List | Prepcenter')

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
            <h4 class="mt-4">Shipping Plan List</h4>
            <a href="{{ url('shipping-plans/create') }}"  class="btn btn-sm btn-info mt-3">Create New</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($shippingPlans as $shippingPlan )
                        <tr>
                            <td>
                                <a class="" href="{{ url('shipping-plans/' . $shippingPlan->custom_id . '/edit') }}">{{ $shippingPlan->custom_id }} {{ $shippingPlan->name ? ' - ' . $shippingPlan->name : '' }}
                                    <span>
                                      {{-- - {{ $shippingPlan->creator?->name ?? 'N/A' }}  --}}
                                      -
                                      {{ $shippingPlan->created_at->format('Y-m-d h:i A')  }}
                                    </span> 
                                
                                </a>
                            </td>
                            <td>
                                <a href="{{ url('shipping-plans/' . $shippingPlan->custom_id . '/edit') }}" class="btn btn-warning">Edit</a>
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
   

    
</script>
@endsection
