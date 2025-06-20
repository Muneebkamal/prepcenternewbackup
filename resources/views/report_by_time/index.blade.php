@extends('layouts.app')

@section('title', 'Report By Time | Prepcenter')

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

    .applyBtn {
        --vz-btn-bg: var(--vz-success);
        --vz-btn-border-color: var(--vz-success);
        --vz-btn-hover-bg: var(--vz-success-text-emphasis);
        --vz-btn-hover-border-color: var(--vz-success-text-emphasis);
        --vz-btn-focus-shadow-rgb: var(--vz-success-rgb);
        --vz-btn-active-bg: var(--vz-success-text-emphasis);
        --vz-btn-active-border-color: var(--vz-success-text-emphasis);
    }
</style>
@endsection

@section('content')

<div class="container-fluid">
                        
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Report By Time</h4>

                {{-- <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Forms</a></li>
                        <li class="breadcrumb-item active">Basic Elements</li>
                    </ol>
                </div> --}}

            </div>
        </div>
    </div>

    <div class="mb-3">
        <form id="search_form" action="{{ route('time.search') }}" method="POST" class="">
            @csrf
            <div class="row d-flex align-items-center">
                <div class="col-md-4">
                    <label for="filter-select">
                        Filter By:
                    </label>
                    {{-- <select class="form-select" id="filter-select" name="filter_by">
                        <option value="today" {{ request('filter_by', 'today') === 'today' ? 'selected' : '' }} >Today</option>
                        <option value="custom" {{ request('filter_by') === 'custom' ? 'selected' : '' }} >Custom Date</option>
                        <option value="this_week" {{ request('filter_by') === 'this_week' ? 'selected' : '' }} >This Week</option>
                        <option value="last_week" {{ request('filter_by') === 'last_week' ? 'selected' : '' }} >Last Week</option>
                        <option value="last_month" {{ request('filter_by') === 'last_month' ? 'selected' : '' }} >Last Month</option>
                        <option value="last_year" {{ request('filter_by') === 'last_year' ? 'selected' : '' }} >Last Year</option>
                    </select> --}}
                    <div id="reportrange" class="reportrange p-2" style="background-color: white; border: var(--vz-border-width) solid var(--vz-input-border-custom); border-radius: var(--vz-border-radius);">
                        <span></span>
                        <b class="caret"></b>
                    </div>
                    <input type="hidden" id="date_range" name="date_range" />  
                </div>
                <div class="col-md-4 d-none" id="date-div">
                    <label for="date-input">
                        Select Date:
                    </label>
                    <input type="date" class="form-control" id="date-input" name="filter_date">
                </div>
                {{-- <div class="col-md-3">
                    <label for="employee-select">
                        Select Employee:
                    </label>
                    <select class="form-select" id="employee-select" name="employee_id">
                        <option value="all" {{ request('employee_id') === 'all' ? 'selected' : '' }} >All Employees</option>
                        @foreach($employees as $employee)
                        <option value="{{ $employee->id }}"  {{ request('employee_id') == $employee->id ? 'selected' : '' }} >{{ $employee->name }}</option>
                        @endforeach
                        <!-- Add employee options here -->
                    </select>
                </div> --}}
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
                    <h5 class="card-title mb-0">Please Select Date Range first.</h5>
                </div>
                <div class="card-body">
                   <div class="table-responsive">
                        <table id="employeeTable" class="table align-middle" style="width:100%">
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
    
                                    <td> <span class="d-none">{{ $time->date }}</span>
                                        <a href="{{ url('daily-input', $time->id) }}" target="_blank">
                                            {{ Carbon::parse($time->date)->format('D, F j, Y') }}
                                        </a>
                                        
                                    </td>
                                    <td>{{ $time->user->name ?? 'N/A' }}</td>
                                    <td>{{ $time->start_time }}</td>
                                    <td>{{ $time->end_time }}</td>
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
                                        // $totalPackingCost += $time->total_packing_cost;
                                        // $totalItemHour += $time->total_item_hour;
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
                                    <td>{{ number_format($totalPackingCost, 3) }}</td>
                                    <td>{{ number_format($totalItemHour, 2) }}</td>
                                </tr>
                            </tfoot>
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
<script>
    $(document).ready(function() {
       
        $('#employeeTable').dataTable({
            "order": [],
            "pageLength": userPerPageLength
        })
        $('#resetButton').click(function() {
            $('#search_form')[0].reset();
            
            $('#filter-select').val('today');
            $('#date-div').addClass('d-none');
            $('#search_form').submit();
        });


        const $filterSelect = $('#filter-select');
        const $dateDiv = $('#date-div');

        function toggleDateDiv() {
            if ($filterSelect.val() === 'custom') {
                $dateDiv.removeClass('d-none');
            } else {
                $dateDiv.addClass('d-none');
            }
        }

        $filterSelect.on('change', toggleDateDiv);
        toggleDateDiv();
    });
    $(function() {
        var start = {!! json_encode( $startDate) !!};
        var end = {!! json_encode( $endDate) !!};
        var start = moment(start, 'YYYY-MM-DD');
        var end = moment(end, 'YYYY-MM-DD');
        var dynamicStartDayNumber = {!! json_encode( $weekStart) !!};

        var today = moment();
        var startOfWeek = today.clone().startOf('week').add(dynamicStartDayNumber, 'days');
        if (startOfWeek.isAfter(today)) {
            startOfWeek.subtract(7, 'days');
        }
        var endOfWeek = startOfWeek.clone().add(6, 'days');
        var startOfLastWeek = startOfWeek.clone().subtract(7, 'days');
        var endOfLastWeek = endOfWeek.clone().subtract(7, 'days');

        function cb(start, end) {
            $('#reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
            $("#date_range").val(start.format('YYYY-M-D') + '_' + end.format('YYYY-M-D'));
        }

        $('#reportrange').daterangepicker({
            startDate: start,
            endDate: end,
            ranges: {
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'This Week': [startOfWeek, endOfWeek],
                'Last Week': [startOfLastWeek, endOfLastWeek],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                'Last 3 Months': [moment().subtract(2, 'month').startOf('month'), moment().endOf('month')],
                'Last 6 Months': [moment().subtract(5, 'month').startOf('month'), moment().endOf('month')],
                'This Year': [moment().startOf('year'), moment().endOf('year')],
                'Last Year': [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')]
            }
        }, cb);

        cb(start, end);
    });
</script>
@endsection