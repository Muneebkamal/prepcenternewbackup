@extends('layouts.app')

@section('title', 'Daily Inputs | Prepcenter')

@section('styles')
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

<style>
    .applyBtn {
        --vz-btn-bg: var(--vz-success);
        --vz-btn-border-color: var(--vz-success);
        --vz-btn-hover-bg: var(--vz-success-text-emphasis);
        --vz-btn-hover-border-color: var(--vz-success-text-emphasis);
        --vz-btn-focus-shadow-rgb: var(--vz-success-rgb);
        --vz-btn-active-bg: var(--vz-success-text-emphasis);
        --vz-btn-active-border-color: var(--vz-success-text-emphasis);
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
        <div class="alert alert-success" id="success-alert">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger" id="error-alert">
            {{ session('error') }}
        </div>
    @endif
    
     <div class="row mb-3">
        <div class="col-md-8 d-flex justify-content-between align-items-center">
            <form id="search_form" action="{{ route('daily-input.index') }}" method="GET" class="">
                @csrf
                    <div class="row d-flex align-items-end">
                        <div class="col-md-8 d-flex align-items-center">
                            <div class="me-2">
                                <label for="date-input">
                                    Select Date Range:
                                </label>
                                <div id="reportrange" class="reportrange p-2" style="background-color: white; border: var(--vz-border-width) solid var(--vz-input-border-custom); border-radius: var(--vz-border-radius);">
                                    <span></span>
                                    <b class="caret"></b>
                                </div>
                                <input type="hidden" id="date_range" name="date_range" />  
                            </div>
                            <div class="col-md-4">
                                <div class="mt-4 d-flex">
                                    <button type="submit" id="sub_btn" class="btn btn-primary me-2">Search</button>
                                    <button type="button" class="btn btn-danger" id="resetButton">Clear</button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mt-3 ms-2">
                                <input type="checkbox" id="productShowCheck" class="form-control-check" checked>
                                <label for="productShowCheck">Show Product Record</label>
                            </div>
                        </div>
                    </div>
            </form>
        </div>
        <div class="col-md-4 text-end">
            <div class="add-btn mt-4">
                <a href="{{ route('daily-input.create') }}" class="btn btn-primary">Add Daily Input</a>
            </div>
        </div>
    </div>
    @php
    use Carbon\Carbon;
    // Initialize variables
    $totalHours = 0;
    $totalMinutes = 0;
    $totalPaid = 0;
    $totalQty = 0;
    $totalPackingCost = 0;
    $totalItemHour = 0;
@endphp
<div class="card mb-4">
    <div class="card-body">
        <h5 class="card-title">Totals Summary</h5>
        <div class="row">
            <div class="col-md-2">
                <p><strong>Total Time:</strong> <span id="totalHours">0 H 0 m</span></p>
            </div>
            @if(Auth()->user()->role == 1)
            <div class="col-md-2">
                <p><strong>Total Paid:</strong> <span id="totalPaid">$0.00</span></p>
            </div>
            @endif
            <div class="col-md-2">
                <p><strong>Total Quantity:</strong> <span id="totalQty">0</span></p>
            </div>
            @if(Auth()->user()->role == 1)
            <div class="col-md-2">
                <p><strong>Packing Cost per Item:</strong> <span id="totalPackingCost">$0.000</span></p>
            </div>
            @endif
            <div class="col-md-2">
                <p><strong>Items per Hour:</strong> <span id="totalItemHour">0.00</span></p>
            </div>
        </div>
    </div>
</div>


    <div class="row">
        <div class="col-md-12">
            <div class="card">
               
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="daily-input" class="table table-striped align-middle" style="width:100%">
                            <thead>
                                 <tr>
                                    <th><small>Working Date</small></th>
                                    <th><small>Employee Name</small></th>
                                    <th><small>Start Time</small></th>
                                    
                                    <th><small>End Time</small></th>
                                    @if(Auth()->user()->role == 1)
                                    <th><small>Rate /Hour</small></th>
                                    @endif
                                    <th><small class="text-center d-block">Total Time</small></th>
                                    @if(Auth()->user()->role == 1)
                                    <th class="w-10" ><small class="text-center d-block">Total Paid</small></th>
                                    @endif
                                    <th class="w-5"><small>QTY</small></th>
                                    @if(Auth()->user()->role == 1)
                                    <th><small>PC /Item</small></th>
                                    <th ><small>Item /Hour</small></th>
                                    @endif
                                    <th><small>Action</small></th>
                                </tr>
                            </thead>
                            <tbody>
                                @isset($daily_inputss)
                                @foreach($daily_inputs as $daily_input)
                                <tr>
                                    <td>
                                        <small>{{ Carbon::parse($daily_input->date)->format('D, M j, Y') }}</small>
                                    </td>
                                    <td>
                                        <small>{{ $daily_input->user->name ?? 'N/A' }}</small>
                                    </td>
                                    <td>
                                        <small>{{ \Carbon\Carbon::parse($daily_input->start_time)->format('H:i') }}
                                            
                                        </small>
                                    </td>
                                    <td>
                                        <small>{{ \Carbon\Carbon::parse($daily_input->end_time)->format('H:i') }}</small>
                                    </td>
                                    <td>
                                        <small>${{ $daily_input->rate }}</small>
                                    </td>
                                    @php
                                        $totalTimeInSeconds = $daily_input->total_time_in_sec;
                                        $hours = intdiv($totalTimeInSeconds, 3600); // Total hours
                                        $minutes = intdiv($totalTimeInSeconds % 3600, 60); // Remaining minutes
                                    @endphp
                                    <td>
                                        <small>{{ $hours }} H {{ $minutes }} m</small>
                                    </td>
                                    <td>
                                        <small>${{ $daily_input->total_paid }}</small>
                                    </td>
                                    <td>
                                        <small>{{ $daily_input->total_qty ?? 1}}</small>
                                    </td>
                                    <td>
                                        <small>{{ number_format($daily_input->total_packing_cost, 3) }}</small>
                                    </td>
                                    <td>
                                        <small>{{ number_format($daily_input->total_item_hour, 2) }}</small>
                                    </td>
                                    <td class="d-flex">
                                        <a href="{{ route('daily-input.show', $daily_input->id) }}" class="btn btn-primary p-1 m-0 me-1">
                                            <i class="ri-eye-fill align-bottom me-1"></i> View
                                        </a>
                                        @if(Auth()->user()->role == 1)
                                            <form method="POST" action="{{ route('daily-input.destroy', $daily_input->id) }}" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="d-flex btn btn-danger remove-item-btn  p-1 m-0">
                                                    <i class="ri-delete-bin-fill align-bottom me-1"></i> Delete
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                                @endisset
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div><!--end col-->
        <div class="col-md-12 mt-2" id="productRecordDev">
            <div class="card">
                <div class="card-body">
                    <div class="mt-4">
                        <h4>Product Packing  <span id="filterLable">This Month</span> </h4>
                        <div class="table-responsive">
                            <table id="product-record-table" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th data-ordering="false">No</th>
                                        <th>Asin/Item ID</th>
                                        <th>FNSKU/GTIN</th>
                                        <th>Msku/Sku</th>
                                        <th>Product Item Name</th>
                                        <th>Pack</th>
                                        <th>QTY</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Product records will be dynamically added here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div><!--end row-->
    
    <!-- end page title -->

</div>

@endsection

@section('script')
{{-- <script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script> --}}
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="{{ asset('js/jquery.min.js') }}"></script>
<script src="{{ asset('js/datatables.min.js') }}"></script>
<script src="{{ asset('js/dataTables.bootstrap5.min.js') }}"></script>
<script>
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

        // $('#reportrange').daterangepicker({
        //     startDate: start,
        //     endDate: end,
        //     ranges: {
        //         'Last 7 Days': [moment().subtract(6, 'days'), moment()],
        //         'This Week': [startOfWeek, endOfWeek],
        //         'Last Week': [startOfLastWeek, endOfLastWeek],
        //         'Last 30 Days': [moment().subtract(29, 'days'), moment()],
        //         'This Month': [moment().startOf('month'), moment().endOf('month')],
        //         'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
        //         'Last 3 Months': [moment().subtract(2, 'month').startOf('month'), moment().endOf('month')],
        //         'Last 6 Months': [moment().subtract(5, 'month').startOf('month'), moment().endOf('month')],
        //         'This Year': [moment().startOf('year'), moment().endOf('year')],
        //         'Last Year': [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')]
        //     }
        // }, cb);
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
        }, function(start, end, label) {
            // Directly assign the label to the date_range input field
            // $('#date_range').val(label);
            $('#filterLable').text(label)
            // Call your callback function (cb) if needed
            cb(start, end);
        });

        cb(start, end);
    });
    var userRole = "{{ auth()->user()->role }}"; 
    $(document).ready(function() {
        // $('#daily-input').DataTable({
        //     "ordering": false
        // });
        // $('#sub_btn').click();
        if ($.fn.DataTable.isDataTable('#daily-input')) {
            $('#daily-input').DataTable().destroy();
        }
        var applyDateFilter = true; // Flag to control date range filter application
        

        // Add these columns only if the user is an admin (role = 1)
        if (userRole == 1) {
            var columns = [
                { data: 'date', name: 'date' },
                { data: 'employee_id', name: 'employee_id' },
                { data: 'start_time', name: 'start_time' },
                { data: 'end_time', name: 'end_time' },
                { data: 'rate', name: 'rate' },
                { data: 'total_time_in_sec', name: 'total_time_in_sec' },
                
                { data: 'total_paid', name: 'total_paid' },
                { data: 'qty', name: 'qty', orderable: false, searchable: false },
                { data: 'total_packing_cost', name: 'total_packing_cost' },
                { data: 'total_item_hour', name: 'total_item_hour' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
       
        ];
        }else{
            var columns = [
                { data: 'date', name: 'date' },
                { data: 'employee_id', name: 'employee_id' },
                { data: 'start_time', name: 'start_time' },
                { data: 'end_time', name: 'end_time' },
                { data: 'total_time_in_sec', name: 'total_time_in_sec' },
                { data: 'qty', name: 'qty', orderable: false, searchable: false },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ];
            
        }

        

        var subscription_table =  $('#daily-input').DataTable({
            processing: true,
            serverSide: true,
            pageLength: userPerPageLength, // Default page length
            ajax: {
                url: "{{url('get-daily-input-data') }}",
                type: 'GET',
                dataSrc: function(json) {
                    // Access the totals from the response and display them
                    $('#totalHours').text(Math.floor(json.totals.totalMinutes / 60) + ' H ' + (json.totals.totalMinutes % 60) + ' m');
                    $('#totalPaid').text('$' + json.totals.totalPaid);
                    $('#totalQty').text(json.totals.totalQty);
                    $('#totalPackingCost').text('$' + json.totals.totalPackingCost);
                    $('#totalItemHour').text(json.totals.totalItemHour);
                    // Populate the product record table
                    const productDetails = json.details;
                    let productRows = '';
                    productDetails.forEach((detail, index) => {
                        // Determine the first character of the FNSKU
                        const firstChar = detail.fnsku ? detail.fnsku[0] : null;
                        let link = '#';
                        // Generate the link based on the first character
                        if (firstChar === 'X') {
                            link = `https://www.amazon.com/dp/${detail.product.asin}`;
                        } else if (firstChar === '0' || firstChar === '1') {
                            link = `https://www.walmart.com/ip/${detail.product.asin}`;
                        }else{
                            link = `https://www.amazon.com/dp/${detail.product.asin}`;
                        }
                        // Generate the table row
                        productRows += `
                            <tr>
                                <td>${index + 1}</td>
                                <td>
                                    ${link !== '#' ? `<a href="${link}" target="_blank">${detail.product.asin} <i class="ri-external-link-line text-primary fs-4"></i></a>` : (detail.product.asin || '--')}
                                    <i class="ri-file-copy-line ms-2" style="cursor: pointer;" onclick="copyToClipboard('${detail.product.asin}')" title="Copy ASIN"></i>
                                </td>
                                <td>
                                    ${detail.product.fnsku}
                                    <i class="ri-file-copy-line ms-2" style="cursor: pointer;" onclick="copyToClipboard('${detail.product.fnsku}')" title="Copy Fnsku"></i>
                                </td>
                                <td>
                                    ${detail.product.msku}
                                    <i class="ri-file-copy-line ms-2" style="cursor: pointer;" onclick="copyToClipboard('${detail.product.msku}')" title="Copy Msku"></i>
                                </td>
                                <td>
                                    ${detail.product ? `<div class="truncate"> <a data-toggle="tooltip" title="${detail.product.item}" href="/products/show/${detail.product.id}/edit" target="_blank">${detail.product.item}</a></div` : '--'}
                                </td>
                                <td>${detail.pack || '--'}</td>
                                <td>${detail.qty || '0'}</td>
                            </tr>
                        `;
                    });
                    $('#product-record-table tbody').html(productRows);
                    return json.data; // Return the data for DataTables to use
                },
                data: function(d) {
                    d.date_range = $('#date_range').val();
                },
            },
            columns:columns,
            "order": [],
        });
        // Optional: Reset the filter when the table is loaded to avoid using it for subsequent reloads
        subscription_table.on('xhr', function() {
            applyDateFilter = false; // Reset filter after each reload
        });
        // Detect length change event
        $('#daily-input').on('length.dt', function(e, settings, len) {
            applyDateFilter = false; // Disable date range filter when length is changed
        });
        $('#search_form').on('submit', function(e) {
            e.preventDefault();
            applyDateFilter = true;
            subscription_table.draw();
            applyDateFilter = false;
        });
        // Reset the filters
        $('#resetButton').on('click', function() {
            $('#reportrange span').html('');
            $('#date_range').val('');
            subscription_table.draw();
            var today = moment().format('MMMM D, YYYY'); // Format date as needed
            $('#reportrange span').html(today + ' - ' + today);
            $('#date_range').val(today + ' - ' + today);
        });

        $('input[name="daterange"]').daterangepicker({
            opens: 'right',
            autoUpdateInput: false,
            locale: {
                cancelLabel: 'Clear'
            }
        }, function(start, end) {
            $('input[name="daterange"]').val(start.format('MM/DD/YYYY') + ' - ' + end.format('MM/DD/YYYY'));

            $('input[name="start_date"]').remove();
            $('input[name="end_date"]').remove();

            $('<input>').attr({
                type: 'hidden',
                name: 'start_date',
                value: start.format('YYYY-MM-DD')
            }).appendTo('#search_form');

            $('<input>').attr({
                type: 'hidden',
                name: 'end_date',
                value: end.format('YYYY-MM-DD')
            }).appendTo('#search_form');
        });

        $('#resetButton').click(function() {
            $('#daterange').val('');
            $('input[name="start_date"]').remove();
            $('input[name="end_date"]').remove();
            window.location.href = "{{ route('daily-input.index') }}";
        });

        $('#success-alert').each(function() {
            setTimeout(() => $(this).fadeOut('slow'), 2000); // 3000 milliseconds = 3 seconds
        });

        // Set a timeout for the error alert
        $('#error-alert').each(function() {
            setTimeout(() => $(this).fadeOut('slow'), 2000); // 3000 milliseconds = 3 seconds
        });
    });
   
    function getDailyInputs() {
        if ( $('#daily-input').DataTable()) {
            $('#daily-input').DataTable().destroy();
        }
       
    }
    $('#productShowCheck').on('change',function(){
        if($(this).is(':checked')){
            $('#productRecordDev').removeClass('d-none');
        }else{
            $('#productRecordDev').addClass('d-none');
        }
    })
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