@extends('layouts.app')

@section('title', 'Dashboard | Prepcenter')

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
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Dashboard</h4>
            </div>
        </div>
    </div>

    {{-- <div class="row mb-3">
        <form id="search_form" action="{{ route('time.search') }}" method="POST" class="d-flex align-items-center">
            @csrf
            <div class="col-md-3 p-3">
                <label for="filter-select">
                    Filter By:
                </label>
                <select class="form-select" id="filter-select" name="filter_by">
                    <option value="today" {{ request('filter_by', 'today') === 'today' ? 'selected' : '' }} >Today</option>
                    <option value="custom" {{ request('filter_by') === 'custom' ? 'selected' : '' }} >Custom Date</option>
                    <option value="this_week" {{ request('filter_by') === 'this_week' ? 'selected' : '' }} >This Week</option>
                    <option value="last_week" {{ request('filter_by') === 'last_week' ? 'selected' : '' }} >Last Week</option>
                    <option value="last_month" {{ request('filter_by') === 'last_month' ? 'selected' : '' }} >Last Month</option>
                    <option value="last_year" {{ request('filter_by') === 'last_year' ? 'selected' : '' }} >Last Year</option>
                </select>
            </div>
            <div class="col-md-3 p-3 d-none" id="date-div">
                <label for="date-input">
                    Select Date:
                </label>
                <input type="date" class="form-control" id="date-input" name="filter_date">
            </div>
            <div class="col-md-3 d-flex pt-4 px-3">
                <button type="submit" class="btn btn-primary me-2">Search</button>
                <button type="button" class="btn btn-danger" id="resetButton">Clear</button>
            </div>
        </form>
    </div> --}}
    
    
    <div class="row @if(Auth()->user()->role != 1) d-none @endif">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Information For This Week</h5>
                    <div class="add-btn">
                        <a href="{{ route('daily-input.create') }}" class="btn btn-primary">Add Daily Input</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="example1" class="table nowrap align-middle" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th data-ordering="false">Working Date</th>
                                <th>Employee Name</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Rate / Hour</th>
                                <th>Total Time</th>
                                <th>Total Paid</th>
                                <th>QTY</th>
                                <th>PC / Item</th>
                                <th>Item /Hour</th>
                            </tr>
                        </thead>
                            @php
                                use Carbon\Carbon;

                                $totalHours = 0;
                                $totalMinutes = 0;
                                $totalPaid = 0;
                                $totalQty = 0;
                                $totalPackingCost = 0;
                                $totalItemHour = 0;
                            @endphp
                        <tbody>
                            @foreach($report_by_times as $time)
                            <tr>
                                <td>
                                    <a href="{{ url('daily-input', $time->id) }}" target="_blank"> {{ Carbon::parse($time->date)->format('D, F j, Y') }} </a></td>
                                <td>{{ $time->user->name ?? 'N/A' }}</td>
                                <td> <small>{{ \Carbon\Carbon::parse($time->start_time)->format('H:i') }}
                                        
                                </small></td>
                                <td>{{ \Carbon\Carbon::parse($time->end_time)->format('H:i') }}</td>
                                <td>${{ $time->rate }}</td>
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
                                <td>{{ $hours }} H {{ $minutes }} m</td>
                                <td>${{ $time->total_paid }}</td>
                                <td>{{ $time->total_qty ?? 0}}</td>
                                <td>{{ number_format($time->total_packing_cost, 3) }}</td>
                                <td>{{ number_format($time->total_item_hour, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="5">Total</td>
                                <td>
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
                                    {{ $totalHours }} H {{ $totalMinutes }} m
                                </td>
                                <td>${{ number_format($totalPaid, 2) }}</td>
                                <td>{{ number_format($totalQty, 0) }}</td>
                                <td>${{ number_format($totalPackingCost, 3) }}</td>
                                <td>{{ number_format($totalItemHour, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                    </div>
                </div>
            </div>
        </div><!--end col-->
    </div>
    
    <div class="row @if(Auth()->user()->role != 1) d-none @endif">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Product Packing This Week</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="example2" class="table nowrap align-middle" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th data-ordering="false">No</th>
                                <th>Asin/Item Id</th>
                                <th>FNSKU/GTIN</th>
                                <th>Msku/Sku</th>
                                <th>Product Item Name</th>
                                <th>Msku/Sku</th>
                                <th>Pack</th>
                                <th>QTY</th>
                            </tr>
                        </thead>
                           
                        <tbody>
                            @php 
                                $quantity = 0;
                            @endphp
                             @foreach($details as $index => $detail)
                             <tr>
                                 <td>{{ $index +1 }}</td>
                                <td> 
                                    @php 
                                        $firstChar = $detail->product->fnsku[0];
                                        if ($firstChar === 'X') {
                                            $link = "https://www.amazon.com/dp/{$detail->product->asin}";
                                        } elseif ($firstChar === '0' || $firstChar === '1') {
                                            $link = "https://www.walmart.com/ip/{$detail->product->asin}";
                                        } else {
                                            $link = "https://www.amazon.com/dp/{$detail->product->asin}";
                                        }
                                    @endphp
                                    
                                    <a href="{{ $link }}" target="_blank">{{ $detail->product->asin }} <i class="ri-external-link-line text-primary fs-4"></i>
                                    </a>
                                    <i class="ri-file-copy-line ms-2" style="cursor: pointer;" onclick="copyToClipboard('{{ $detail->product->asin }}')" title="Copy ASIN"></i> 
                                    
                                </td>
                                <td>
                                  {{ $detail->product->fnsku }} <i class="ri-external-link-line text-primary fs-4">
                                    <i class="ri-file-copy-line ms-2" style="cursor: pointer;" onclick="copyToClipboard('{{ $detail->product->fnsku }}')" title="Copy Fnsku"></i> 
                                </td>
                                <td>
                                    {{ $detail->product->msku }} <i class="ri-external-link-line text-primary fs-4">
                                    <i class="ri-file-copy-line ms-2" style="cursor: pointer;" onclick="copyToClipboard('{{ $detail->product->msku }}')" title="Copy Msku"></i> 
                                </td>
                                <td><div class="truncate"> <a href="{{ route('products.show',$detail->product->id) }}"target="_blank" data-toggle="tooltip" title="{{ $detail->product->item }}">{{ $detail->product != null?$detail->product->item:'--' }} </a> </div></td>
                                 <td>{{ $detail->product->msku }}</td>
                                <td>{{ $detail->pack }}</td>
                                <td>{{ $detail->qty }}</td>
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
        </div><!--end col-->
    </div>
    
    
    <!--end row-->
    
    <!-- end page title -->

</div>

@endsection


@section('script')
<script>
    $(document).ready(function() {
        $('#example1').DataTable({
            "ordering": false,
            "pageLength": userPerPageLength,
        });
        $('#example2').DataTable({
            "ordering": false,
            "pageLength": userPerPageLength,
        });
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
</script>
@endsection