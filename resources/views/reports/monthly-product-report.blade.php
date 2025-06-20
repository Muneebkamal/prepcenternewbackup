@extends('layouts.app')

@section('title', 'Monthly Summary | Prepcenter')

@section('styles')
<style>
    .dataTables_wrapper .dataTables_scrollBody {
        overflow-y: hidden !important;
        overflow-x: auto;
        height: auto;
    }

    #scroll-horizontal {
        height: auto;
    }
</style>
@endsection

@section('content')

<div class="container-fluid">
                        
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Monthly Product Report</h4>
            </div>
        </div>
    </div>
    @php
                    use Carbon\Carbon;
                    $totalHours = 0;
                    $totalMinutes = 0;
                    $totalPaid = 0;
                    $totalQty = 0;
                    $totalPackingCost = 0;
                    $totalItemHour = 0;
                @endphp
                @foreach($report_by_times as $time)
                @php
                        $totalTimeInSeconds = $time->total_time_in_sec;
                        $hours = intdiv($totalTimeInSeconds, 3600); // Total hours
                        $minutes = intdiv($totalTimeInSeconds % 3600, 60); // Remaining minutes

                        // Accumulate totals
                        $totalHours += $hours;
                        $totalMinutes += $minutes;
                        $totalPaid += $time->total_paid;
                        $totalQty += $time->total_qty ?? 0;
                        $totalPackingCost += $time->total_packing_cost;
                        $totalItemHour += $time->total_item_hour;
                    @endphp

                @endforeach
                @php
                // Convert total minutes to hours and minutes
                $totalHours += intdiv($totalMinutes, 60);
                $totalMinutes = $totalMinutes % 60;

                if($totalQty > 0){
                    $totalPackingCost = $totalPaid / $totalQty;
                    $totalMinutesInHours = $totalMinutes / 60;
                    $totalItemHour = $totalQty / ($totalHours + $totalMinutesInHours);
                }else{
                    $totalPackingCost = 0;
                    $totalItemHour = 0;
                }
            @endphp
    <div class="mb-3">
        <form id="search_form" action="{{ route('monthly.product.report.search') }}" method="POST" class="">
            @csrf
            <div class="row d-flex align-items-center">
                <div class="col-md-2">
                    <label for="month-select">
                        Select Month:
                    </label>
                    <select class="form-control" id="month-select" name="filter_month">
                        @for ($month = 1; $month <= 12; $month++)
                            <option value="{{ $month }}" {{ $filterMonth == $month ? 'selected' : '' }}>
                                {{ DateTime::createFromFormat('!m', $month)->format('F') }}
                            </option>
                        @endfor
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label for="year-select">
                        Select Year:
                    </label>
                    <select class="form-control" id="year-select" name="filter_year">
                        @php
                            $currentYear = date('Y');
                        @endphp
    
                        @for ($year = 2023; $year <= $currentYear; $year++)
                            <option value="{{ $year }}" {{ $filterYear == $year ? 'selected' : '' }}>
                                {{ $year }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-3 d-flex pt-4 px-3">
                    <button type="submit" class="btn btn-primary me-2">Search</button>
                    <button type="button" class="btn btn-danger" id="resetButton">Clear</button>
                </div>
            </div>
        </form>        
    </div>
    
    
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    {{-- <h5 class="card-title mb-0">Please Select  Month  and Year First.</h5> --}}
                </div>
                <div class="card-body">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="products-table" class="table table-bordered nowrap align-middle" style="width:100%">
                            <thead class="table-light">
                                <tr>
                                    <th data-ordering="false">No</th>
                                    <th>FNSKU/GTIN</th>
                                    <th>Product Item Name</th>
                                    <th class="text-center ">Pack</th>
                                    <th class="text-center ">QTY</th>
                                </tr>
                            </thead>
                            <tbody class="">
                                @php 
                                    $quantity = 0;
                                @endphp
                                @foreach($details as $index => $detail)
                                <tr>
                                    <td>{{ $index +1 }}</td>
                                   <td>
                                        @php 
                                        $firstChar = $detail->fnsku[0];
                                        if ($firstChar === 'X') {
                                            $link = "https://www.amazon.com/dp/{$detail->product->asin}";
                                        } elseif ($firstChar === '0') {
                                            $link = "https://www.walmart.com/ip/{$detail->product->asin}";
                                        } else {
                                            $link = '#';
                                        }
                                    @endphp
                                    
                                    <a href="{{ $link }}" target="_blank">{{ $detail->fnsku }} <i class="ri-external-link-line text-primary fs-4"></i></a> 
                                    </td>
                                    <td><a href="{{ route('products.show',$detail->fnsku) }}"target="_blank">{{ $detail->product != null?$detail->product->item:'--' }} </a></td>
                                    <td class="text-center ">{{ $detail->pack }}</td>
                                    <td class="text-center ">{{ $detail->qty }}</td>
                                </tr>
                                    @php
                                        $quantity += $detail->qty;
                                    @endphp
                                @endforeach
                            </tbody>
                        </table>
                        </div>
                        @if(Auth::user()->role == 1)
                            @if($details->isNotEmpty())
                                <div class="row mt-4">
                                    <div class="col-md-12 d-flex justify-content-end">
                                        <div class="content me-4">
                                            <p class="fw-bold me-5">Total QTY: <span class="ms-3">{{ $quantity }}</span></p>
                                            <p class="fw-bold me-5">Total packing Cost per Item: <span class="ms-3">${{ number_format($totalPackingCost, 3) }}</span></p>
                                            <p class="fw-bold me-5">Total Item / Hour: <span class="ms-3">{{ number_format($totalItemHour, 2) }}</span></p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div><!--end col-->
    </div><!--end row-->
    
    <!-- end page title -->
</div>

@endsection


@section('script')
<script>
    $(document).ready(function() {
        $('#products-table').dataTable({
            "order": [],
            "pageLength": userPerPageLength
        })
        $('#resetButton').click(function() {
            $('#search_form')[0].reset();
            $('#employee-select').val('');
            $('#month-input').val('');
            window.location.href = "{{ route('monthly.summary') }}";
            $('#search_form').submit();
        });
    });
</script>
@endsection