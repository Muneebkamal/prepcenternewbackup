@extends('layouts.app')

@section('title', 'System Setting | Prepcenter')

@section('styles')
    <style>
        #table_prodcut_filter {
            display: flex;
            justify-content: start;
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
        .compact-input {
            width: 50px;
            text-align: center;
        }

    </style>
@endsection

@section('content')

<div class="container-fluid">

    <div class="row">
        <div class="col-md-12">
            <h5 class="mb-3">System Setting</h5>
            <div class="card">
                <div class="card-body">
                    <div class="alert alert-success d-none" id="success-alertj">
                    </div>
                    @php
                    $tab = 1;
                    if(session('tab_id')){
                        $tab = session('tab_id');
                    }
                    @endphp
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
                    <!-- Nav tabs -->
                    <ul class="nav nav-pills animation-nav nav-justified gap-2 mb-3" role="tablist">
                        <li class="nav-item waves-effect waves-light" >
                            <a class="nav-link" data-bs-toggle="tab" id="week_tab" href="#animation-home" role="tab">
                                System Settings
                            </a>
                        </li>
                        <li class="nav-item waves-effect waves-light" >
                            <a class="nav-link" data-bs-toggle="tab" id="dept_tab" href="#animation-profile" role="tab">
                                Departments
                            </a>
                        </li>
                        <li class="nav-item waves-effect waves-light" >
                            <a class="nav-link" data-bs-toggle="tab" id="option_tab" href="#animation-options" role="tab">
                                Options Setting
                            </a>
                        </li>
                        @php
                        $user = Auth()->user();
                        $permissions = $user ? explode(",", $user->permission) : [];
                        @endphp
                        @if($user && in_array('merge_products', $permissions))
                        {{-- <li class="nav-item waves-effect waves-light" >
                            <a class="nav-link" data-bs-toggle="tab"id="product_tab" href="#products-merge" role="tab">
                                Merge Product
                            </a>
                        </li> --}}
                        @endif
                    </ul>
                    <div class="tab-content text-muted">
                        <div class="tab-pane" id="animation-home" role="tabpanel">
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">System Settings</h5>
                                        </div>
                                        <div class="card-body">
                                            <form id="search_form" action="{{ route('system.setting.add') }}" method="POST">
                                                @csrf
                                                <div class="row">
                                                    <div class="col-md-6 p-3">
                                                        <label for="employee-select" class="form-label">
                                                            Select Started Day of Week:
                                                        </label>
                                                        <select class="form-select" id="day" name="day">
                                                            <option value="" selected disabled>Select Day</option>
                                                            <option value="1" {{ isset($setting) && $setting->week_started_day == 1 ? 'selected' : '' }}>Monday</option>
                                                            <option value="2" {{ isset($setting) && $setting->week_started_day == 2 ? 'selected' : '' }}>Tuesday</option>
                                                            <option value="3" {{ isset($setting) && $setting->week_started_day == 3 ? 'selected' : '' }}>Wednesday</option>
                                                            <option value="4" {{ isset($setting) && $setting->week_started_day == 4 ? 'selected' : '' }}>Thursday</option>
                                                            <option value="5" {{ isset($setting) && $setting->week_started_day == 5 ? 'selected' : '' }}>Friday</option>
                                                            <option value="6" {{ isset($setting) && $setting->week_started_day == 6 ? 'selected' : '' }}>Saturday</option>
                                                            <option value="7" {{ isset($setting) && $setting->week_started_day == 7 ? 'selected' : '' }}>Sunday</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6 p-3">
                                                        <label for="start_time" class="form-label">
                                                            Start Time:
                                                        </label>
                                                        <input type="time" id="start_time" name="start_time" class="form-control" 
                                                               value="{{ isset($setting) ? $setting->start_time : '' }}">
                                                    </div>
                                                    <div class="col-md-6 p-3">
                                                        <label for="end_time" class="form-label">
                                                            End Time:
                                                        </label>
                                                        <input type="time" id="end_time" name="end_time" class="form-control" 
                                                               value="{{ isset($setting) ? $setting->end_time : '' }}">
                                                    </div>
                                                    <div class="col-md-6 p-3">
                                                        <label for="end_time" class="form-label">
                                                            Per Page length:
                                                        </label>
                                                       <select class="form-control select2" id="per_page" name="per_page">
                                                        <option value="10" {{ auth()->user()->per_page ==10?'selected':'' }}>10</option>
                                                        <option value="25" {{ auth()->user()->per_page ==25?'selected':'' }}>25</option>
                                                        <option value="50" {{ auth()->user()->per_page ==50?'selected':'' }}>50</option>
                                                        <option value="100" {{ auth()->user()->per_page ==100?'selected':'' }}>100</option>

                                                       </select>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                        <div class="card-footer text-end">
                                            <button type="submit" form="search_form" id="saveweek" class="btn btn-primary">Save</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="tab-pane" id="animation-profile" role="tabpanel">
                            <div class="row mb-3">
                                <form action="{{ route('department.add') }}" method="POST" class="d-flex align-items-center">
                                    @csrf
                                    <div class="col-md-6 p-3">
                                        <label for="employee-select">
                                            Department:
                                        </label>
                                        <input type="text" name="department" class="form-control">
                                    </div>
                                    <div class="col-md-3 d-flex pt-4 px-3">
                                        <button type="submit" class="btn btn-primary me-2" id="savedept">Save</button>
                                    </div>
                                </form>        
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <table id="example7" class="table nowrap align-middle" style="width:100%">
                                        <thead class="table-light">
                                            <tr>
                                                <th data-ordering="false">#</th>
                                                <th>Departments</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($departments as $department)
                                            <tr>
                                                <td>{{ $department->id }}</td>
                                                <td>{{ $department->dep_name }}</td>
                                                <td>
                                                    <a data-id="{{ $department->id }}" data-dep="{{ $department->dep_name }}" data-bs-toggle="modal" data-bs-target="#editmodal" class="btn btn-success edit-item-btn"><i class="ri-pencil-fill align-bottom me-2"></i> Edit</a>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="products-merge" role="tabpanel">
                            <div class="row mb-3">
                                <div class="col-lg-12">
                                    <div class="card">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <h5 class="card-title mb-0">Products Record</h5>
                                            <div class="add-btn d-flex align-items-center">
                                                <div class="me-2">
                                                    <input type="checkbox" id="temporaryProductFilter" name="temporary" class="me-2" 
                                                    > Temporary Products
                                                </div>
                                                <div>
                                                   
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body">
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
                                                        {{-- <th>Action</th> --}}
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php
                                                    $counter = 1;
                                                    @endphp
                                                    @isset($productsss)
                                                    @foreach($products as $product)
                                                    <tr>
                                                        <td class="py-1">
                                                            <input type="checkbox" class="form-check-input" name="select_products" id="product{{ $product->id }}" value="{{ $product->id }}" onclick="checkBox({{ $product->id }})">
                                                        </td>
                                                        <td class="py-1"><small>{{ $counter }}</small></td>
                                                        <td class="py-1 truncate" data-toggle="tooltip" title="{{ $product->item }}">
                                                            <a href="{{ route('products.show', $product->id) }}">
                                                                {{-- <small>{{ Str::limit($product->item, 60, '...') }}</small> --}}
                                                                <small>{{ $product->item }}</small>
                                                            </a>
                                                        </td>
                                                        <td class="py-1 fw-bold"><small>{{ $product->msku }}</small></td>
                                                        <td class="py-1"><small>{{ $product->asin }}</small></td>
                                                        <td class="py-1"><small>{{ $product->fnsku }}</small></td>
                                                        <td class="py-1">
                                                        
                                                            <small>{{ $product->pack }}</small>
                                                            {{-- @endif --}}
                                                        </td>
                                                        <td class="py-1"><small>{{ $product->dailyInputDetails->first()->total_qty ?? 1 }}</small></td>

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
                            </div>
                        </div>
                        <div class="tab-pane" id="animation-options" role="tabpanel">
                            <div class="row">
                                @foreach ($labels as  $label)
                                <div class="accordion lefticon-accordion custom-accordionwithicon accordion-border-box" id="accordionlefticon">
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="accordionlefticonExample{{ $label->id }}">
                                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#accor_lefticonExamplecollapse{{ $label->id }}" aria-expanded="true" aria-controls="accor_lefticonExamplecollapse1">
                                                {{ $label->label }}
                                            </button>
                                        </h2>
                                        <div id="accor_lefticonExamplecollapse{{ $label->id }}" class="accordion-collapse collapse show" aria-labelledby="accordionlefticonExample{{ $label->id }}" data-bs-parent="#accordionlefticon">
                                            <div class="accordion-body">
                                                <!-- Existing options -->
                                                {{-- <ol id="optionsList{{ $label->id }}">
                                                </ol> --}}
                                                <table id="optionsTable{{ $label->id }}" class="table table-bordered table-sm mt-2">
                                                    <thead id="optionsTableHead{{ $label->id }}">
                                                    
                                                        
                                                    </thead>
                                                    <tbody id="optionsTableBody{{ $label->id }}">
                                                        <!-- Dynamic rows will be inserted here -->
                                                    </tbody>
                                                </table>
                                                
                                                <div class="label-container mb-4" id="labelContainer{{ $label->id }}">
                                                    <label class="form-label">{{ $label->name }}</label>
                                                    @if ($label->type == 'poly_bag_size')
                                                        <!-- Compact Inputs for Poly Bag Size -->
                                                        <div class="row d-flex align-items-center gap-2">
                                                            <div class="col-md-3 gap-2 d-flex align-items-center">
                                                                <input type="number" id="height{{ $label->id }}" class="form-control mt-4" style="width: 50px;" placeholder="8">
                                                                <span class="mx-1 mt-4">X</span>
                                                                <input type="number" id="width{{ $label->id }}" class="form-control mt-4" style="width: 50px;" placeholder="10">
                                                            </div>
                                                            <div class="col-md-4 d-flex align-items-center">
                                                                <div class="ms-2">
                                                                    <label for="">Polybags Per Case</label>
                                                                    <input type="number" id="no_of_pcs{{ $label->id }}" class="form-control" style="width: 120px;" placeholder="">
                                                                </div>
                                                                <div class="ms-2">
                                                                    <label for="">Cost per Case</label>
                                                                    <input type="number" id="price_of_cotton{{ $label->id }}" class="form-control" style="width: 120px;" placeholder="">
                                                                </div>
                                                                <div class="ms-2">
                                                                    <label for="">Supplier</label>
                                                                    <input type="text" id="Supplier{{ $label->id }}" class="form-control" style="width: 120px;" placeholder="">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @elseif ($label->type == 'carton_size')
                                                        <!-- Compact Inputs for Cotton -->
                                                        <div class="row d-flex align-items-center gap-2">
                                                            <div class="col-md-3 gap-2 d-flex align-items-center">
                                                                <input type="number" id="height{{ $label->id }}" class="form-control mt-4" style="width: 50px;" placeholder="8">
                                                                <span class="mx-1 mt-4">X</span>
                                                                <input type="number" id="width{{ $label->id }}" class="form-control mt-4" style="width: 50px;" placeholder="10">
                                                                <span class="mx-1 mt-4">X</span>
                                                                <input type="number" id="weight{{ $label->id }}" class="form-control mt-4" style="width: 50px;" placeholder="5">
                                                            </div>
                                                            <div class="col-md-4 d-flex align-items-center">
                                                                <div class="ms-2">
                                                                    <label for="">No of Carton in Bundle</label>
                                                                    <input type="number" id="no_of_pcs{{ $label->id }}" class="form-control" style="width: 120px;" placeholder="">
                                                                </div>
                                                                <div class="ms-2">
                                                                    <label for="">Cost per bundle</label>
                                                                    <input type="number" id="price_of_cotton{{ $label->id }}" class="form-control" style="width: 120px;" placeholder="">
                                                                </div>
                                                                <div class="ms-2">
                                                                    <label for="">Supplier</label>
                                                                    <input type="text" id="Supplier{{ $label->id }}" class="form-control" style="width: 120px;" placeholder="">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @elseif($label->type == 'shrink_wrap_size')
                                                    <div class="row d-flex align-items-center gap-2">
                                                       <div class="col-md-3 gap-2 d-flex align-items-center">
                                                            <input type="number" id="height{{ $label->id }}" class="form-control mt-4" style="width: 50px;" placeholder="8">
                                                            <span class="mx-1 mt-4">X</span>
                                                            <input type="number" id="width{{ $label->id }}" class="form-control mt-4" style="width: 50px;" placeholder="10">
                                                       </div>
                                                       <div class="col-md-4 d-flex align-items-center">
                                                            <div class="ms-2">
                                                                <label for="">Shrinkbag per Case</label>
                                                                <input type="number" id="no_of_pcs{{ $label->id }}" class="form-control" style="width: 120px;" placeholder="">
                                                            </div>
                                                            <div class="ms-2">
                                                                <label for="">Cost per Case</label>
                                                                <input type="number" id="price_of_cotton{{ $label->id }}" class="form-control" style="width: 120px;" placeholder="">
                                                            </div>
                                                            <div class="ms-2">
                                                                <label for="">Supplier</label>
                                                                <input type="text" id="Supplier{{ $label->id }}" class="form-control" style="width: 120px;" placeholder="">
                                                            </div>
                                                       </div>
                                                    </div>
                                                    @elseif($label->type == 'bubble_wrap')
                                                    <div class="row d-flex align-items-center gap-2">
                                                       <div class="col-md-3 gap-2 d-flex align-items-center">
                                                            <input type="number" id="height{{ $label->id }}" class="form-control mt-4" style="width: 50px;" placeholder="8">
                                                            <span class="mx-1 mt-4">X</span>
                                                            <input type="number" id="width{{ $label->id }}" class="form-control mt-4" style="width: 50px;" placeholder="10">
                                                       </div>
                                                       <div class="col-md-4 d-flex align-items-center">
                                                            <div class="ms-2">
                                                                <label for="">Feet per Roll</label>
                                                                <input type="number" id="no_of_pcs{{ $label->id }}" class="form-control" style="width: 120px;" placeholder="">
                                                            </div>
                                                            <div class="ms-2">
                                                                <label for="">Cost for Bubble Wrap Roll</label>
                                                                <input type="number" id="price_of_cotton{{ $label->id }}" class="form-control" style="width: 120px;" placeholder="">
                                                            </div>
                                                            <div class="ms-2">
                                                                <label for="">Supplier</label>
                                                                <input type="text" id="Supplier{{ $label->id }}" class="form-control" style="width: 120px;" placeholder="">
                                                            </div>
                                                       </div>
                                                    </div>
                                                    @else
                                                        <!-- Default Input -->
                                                        
                                                        <div class="col-md-8 d-flex align-items-center">
                                                            <input type="text" id="defaultOption{{ $label->id }}" class="form-control mt-4" placeholder="Enter value">
                                                            <div class="ms-2">
                                                                <label for="">Labels in a Roll</label>
                                                                <input type="number" id="no_of_pcs{{ $label->id }}" class="form-control" style="width: 120px;" placeholder="">
                                                            </div>
                                                            <div class="ms-2">
                                                                <label for="">Cost per Roll</label>
                                                                <input type="number" id="price_of_cotton{{ $label->id }}" class="form-control" style="width: 120px;" placeholder="">
                                                            </div>
                                                            <div class="ms-2">
                                                                <label for="">Supplier</label>
                                                                <input type="text" id="Supplier{{ $label->id }}" class="form-control" style="width: 120px;" placeholder="">
                                                            </div>
                                                       </div>
                                                    @endif
                                                
                                                   
                                                </div>
                                                <button type="button" class="btn btn-success mt-2" id="addOptionBtn{{ $label->id }}" data-label-type="{{ $label->type }}" data-label-id="{{ $label->id }}">Add Option</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                                <!-- Left Icon Accordions -->
                            </div>
                        </div>
                    </div>
                </div><!-- end card-body -->
            </div>
        </div>
        <!--end col-->
    </div>
    
</div>


<div id="editmodal" class="modal fade" tabindex="-1" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 overflow-hidden">
            <div class="modal-header p-3">
                <h4 class="card-title mb-0">Edit Department Name</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('department.add') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-12 mt-2">
                            <input type="hidden" id="edit_id" name="edit_id">
                            <label for="">Department</label>
                            <input type="text" id="edit_dep" name="edit_dep" class="form-control">
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary mt-3">Update</button>
                        </div>
                    </div>
                </form>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>
<!-- Modal for Editing Option -->
<div class="modal fade" id="editOptionModal" tabindex="-1" aria-labelledby="editOptionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editOptionModalLabel">Edit Option</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="editOptionModalBody">
                
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveEditedOption">Save changes</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha384-KyZXEAg3QhqLMpG8r+Knujsl5+P36mnbZqNBtKN3zzO9nGO+z9fPz5XYC9J30VZk9" crossorigin="anonymous"></script>
<!-- SweetAlert2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    var tab_id = {!!json_encode($tab)!!}    
    function checkBox(id){
        var checkedCount = $('input[name="select_products"]:checked').length;
        // Show or hide the button based on the count
        if (checkedCount === 2) {
            $('#merge-btn').removeClass('d-none');
        } else {
            $('#merge-btn').addClass('d-none');
        }
    }
    var table = '';
    $(document).ready(function() {
        getLabels();
        $("#example7").DataTable();

        $('#temporaryProductFilter').on('click',function(){
            localStorage.setItem('tab_id', 3);
        })
        $('#saveweek').on('click',function(){
            localStorage.setItem('tab_id', 1);
        })
        $('#savedept').on('click',function(){
            localStorage.setItem('tab_id', 2);
        })
        if(localStorage.getItem('tab_id') != null){
            tab_id = localStorage.getItem('tab_id')
            localStorage.setItem('tab_id',null)
        }else{
            tab_id =1;
        }
        // console.log(tab_id);
        if(tab_id == 1){
            $('#week_tab').tab('show');
        }else if(tab_id == 2){
            $('#dept_tab').tab('show');
        }else if(tab_id == 3){
            $('#product_tab').tab('show');
        }else{
            $('#week_tab').tab('show');
        }
        
        $('.edit-item-btn').on('click', function() {
            var id = $(this).data('id');
            var dep = $(this).data('dep');

            $('#edit_id').val(id);
            $('#edit_dep').val(dep);
        });

        $('#success-alert').each(function() {
            setTimeout(() => $(this).fadeOut('slow'), 2000); // 3000 milliseconds = 3 seconds
        });

        // Set a timeout for the error alert
        $('#error-alert').each(function() {
            setTimeout(() => $(this).fadeOut('slow'), 2000); // 3000 milliseconds = 3 seconds
        });


        $('#example4').DataTable({
            "ordering": false,
            pageLength: 100,
        });
        $('[data-toggle="tooltip"]').tooltip();
        if ($.fn.DataTable.isDataTable('#table_prodcut')) {
            $('#table_prodcut').DataTable().destroy();
        }
        table = $('#table_prodcut').DataTable({
            processing: true,
            serverSide: true,
            pageLength: '100',
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
    })
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
    $(document).on('click', '[id^="addOptionBtn"]', function () {
        const labelId = $(this).data('label-id');
        const labelType = $(this).data('label-type');
        let value = '';

        let height = $(`#height${labelId}`).val()?.trim() || '';
        let width = $(`#width${labelId}`).val()?.trim() || '';
        let weight = $(`#weight${labelId}`).val()?.trim() || '';
        let no_of_pcs_in_cotton = $(`#no_of_pcs${labelId}`).val()?.trim() || '';
        let price_of_cotton = $(`#price_of_cotton${labelId}`).val()?.trim() || '';
        let supplier = $(`#Supplier${labelId}`).val()?.trim() || '';

        // Construct value based on labelType
        if (labelType === 'poly_bag_size') {
            if (!height || !width) {
                alert('Please fill in all required fields.');
                return;
            }
            value = `${height}" X ${width}"`;
        } else if (labelType === 'carton_size') {
            if (!height || !width || !weight) {
                alert('Please fill in all required fields.');
                return;
            }
            value = `${height}" X ${width}" X ${weight}"`;
        } else if (labelType === 'shrink_wrap_size') {
            if (!height || !width) {
                alert('Please fill in all required fields.');
                return;
            }
            value = `${height}" X ${width}"`;
        }else if (labelType === 'bubble_wrap') {
            if (!height || !width) {
                alert('Please fill in all required fields.');
                return;
            }
            value = `${height}" X ${width}"`;
        }  else {
            value = $(`#defaultOption${labelId}`).val()?.trim() || '';
            if (!value) {
                alert('Please enter a valid option.');
                return;
            }
        }

        // Ensure value does not contain "undefined"
        if (!value || value.includes('undefined')) {
            alert('Please fill in all fields correctly.');
            return;
        }

        // AJAX request to add the new option
        $.ajax({
            url: `{{ url('/options/add') }}`,
            method: 'POST',
            data: {
                label_id: labelId,
                new_option: value,
                no_of_pcs_in_cotton: no_of_pcs_in_cotton,
                price_of_cotton: price_of_cotton,
                supplier:supplier,
                _token: $('meta[name="csrf-token"]').attr('content'),
            },
            success: function (response) {
                if (response.success) {
                    getLabels();

                    // Clear input fields
                    $(`#no_of_pcs${labelId}`).val('');
                    $(`#price_of_cotton${labelId}`).val('');
                    if (labelType === 'poly_bag_size' || labelType === 'shrink_wrap_size') {
                        $(`#height${labelId}, #width${labelId}`).val('');
                    } else if (labelType === 'carton_size') {
                        $(`#height${labelId}, #width${labelId}, #weight${labelId}`).val('');
                    } else {
                        $(`#defaultOption${labelId}`).val('');
                    }
                } else {
                    alert('Failed to add option.');
                }
            },
            error: function (xhr) {
                alert('An error occurred while adding the option.');
                console.error(xhr.responseText);
            },
        });
    });

    $(document).on('click', '.editOption', function () {
        const labelId = $(this).data('label-id'); // Get the label ID
        const optionId = $(this).data('option-id'); // Get the option ID
        const currentOption = $(this).data('option'); // Get the current option value
        const no_of_pcs = $(this).data('no-of-pcs');
        const price_of_cotton = $(this).data('price-cotton');
        const labelType = $(this).data('label-type'); // Get the current option value
        const labelsupplier = $(this).data('label-supplier'); // Get the current option value

        // console.log('current option:' + currentOption);
        // Split currentOption if it's a size-related field (height x width)
        let height = '';
        let width = '';
        let weight = '';
        if (typeof currentOption != 'number') {
            if (currentOption.toLowerCase().includes('x')) {
                const parts = currentOption.toLowerCase().split('x').map(part => part.trim());
                height = parts[0] || '';
                width = parts[1] || '';
                weight = parts[2] || '';
            } else {
                height = currentOption; 
            }
        } else {
            height = currentOption; 
        }

        
        // Append the inputs based on the label type and current option values
        appendInputs(labelType, optionId, height, width, weight, no_of_pcs, price_of_cotton,labelsupplier);
        // Store the labelId and optionId in data attributes of the save button
        $('#saveEditedOption').data('label-id', labelId).data('option-id', optionId).data('label-type',labelType);
        // Show the modal
        $('#editOptionModal').modal('show');
    });

    $(document).on('click', '#saveEditedOption', function () {
        const labelId = $(this).data('label-id');
        const labelType = $(this).data('label-type');
        const optionId = $(this).data('option-id');

        let value = '';
        let no_of_pcs_in_cotton = '';
        let price_of_cotton = '';
        var supplierVal = document.getElementById(`supplier${optionId}`).value;

        if (labelType === 'poly_bag_size' || labelType === 'shrink_wrap_size' || labelType === 'bubble_wrap') {
            const height = document.getElementById(`heightoption${optionId}`).value;
            const width = document.getElementById(`widthoption${optionId}`).value;
            no_of_pcs_in_cotton = document.getElementById(`no_of_pcs${optionId}`).value;
            price_of_cotton = document.getElementById(`price_of_cotton${optionId}`).value;
            value = `${height}" X ${width}"`;
        } else if (labelType === 'carton_size') {
            const height = document.getElementById(`heightoption${optionId}`).value;
            const width = document.getElementById(`widthoption${optionId}`).value;
            const weight = document.getElementById(`weightoption${optionId}`).value;
            no_of_pcs_in_cotton = document.getElementById(`no_of_pcs${optionId}`).value;
            price_of_cotton = document.getElementById(`price_of_cotton${optionId}`).value;
            value = `${height}" X ${width}" X ${weight}"`;
        } else {
            no_of_pcs_in_cotton = document.getElementById(`no_of_pcs${optionId}`).value;
            price_of_cotton = document.getElementById(`price_of_cotton${optionId}`).value;
            value = document.getElementById(`defaultOption${optionId}`).value;
        }
        // console.log(no_of_pcs_in_cotton, price_of_cotton);
        // Validate input
        if (!value || value.includes('undefined')) {
            alert('Please fill in all fields.');
            return;
        }

        let updatedOption = value;

        if (updatedOption === '') {
            alert('Option cannot be empty.');
            return;
        }

        // Send AJAX request to update the option
        $.ajax({
            url: `{{ url('/options/update') }}`, // Ensure this matches your backend route
            method: 'POST',
            data: {
                label_id: labelId,
                option_id: optionId,
                updated_option: updatedOption,
                no_of_pcs_in_cotton: no_of_pcs_in_cotton,
                price_of_cotton: price_of_cotton,
                supplierVal:supplierVal,
                _token: $('meta[name="csrf-token"]').attr('content'), // CSRF token
            },
            success: function (response) {
                if (response.success) {
                    getLabels();
                    $('#editOptionModal').modal('hide');
                } else {
                    alert('Failed to update option.');
                }
            },
            error: function (xhr) {
                alert('An error occurred while updating the option.');
                console.error(xhr.responseText);
            },
        });
    });

    $(document).on('click', '.deleteOption', function () {
        const labelId = $(this).data('label-id');
        const optionId = $(this).data('option-id');
        // SweetAlert confirmation dialog
        Swal.fire({
            title: 'Are you sure?',
            text: "This action cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Perform AJAX request to delete the option
                $.ajax({
                    url: `{{ url('/options/delete') }}`,
                    method: 'POST',
                    data: {
                        label_id: labelId,
                        option_id: optionId,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        if (response.success) {
                            // Remove the option from the DOM
                            $('#option' + labelId + '_' + optionId).remove();

                            // Success alert
                            Swal.fire(
                                'Deleted!',
                                'The option has been deleted.',
                                'success'
                            );
                        } else {
                            // Failure alert
                            Swal.fire(
                                'Failed!',
                                'The option could not be deleted.',
                                'error'
                            );
                        }
                    }
                });
            }
        });
    });
    function appendInputs(labelType, id, height, width, weight, no_of_pcs, price_of_cotton,labelsupplier) {
        // console.log(labelType);
        // Target container to append inputs
        const targetContainer = $(`#editOptionModalBody`);
        // Clear previous content (if any)
        targetContainer.empty();
        // Generate inputs based on the label type
        let inputFields = ``;
        if (labelType === 'poly_bag_size') {
            // Poly Bag Size
            inputFields += `
                <div class="row d-flex align-items-center gap-2">
                    <div class="col-md-6 d-flex align-items-center gap-2">
                        <input type="number" id="heightoption${id}" class="form-control mt-4" style="width: 50px;" placeholder="8" value="${height}">
                        <span class="mx-1 mt-4">X</span>
                        <input type="number" id="widthoption${id}" class="form-control mt-4" style="width: 50px;" placeholder="10" value="${width}">
                    </div>
                    <div class="col-md-12 d-flex align-items-center gap-2">
                        <div class="ms-2">
                            <label for="">Polybag in Case</label>
                            <input type="number" id="no_of_pcs${id}" class="form-control" style="width: 120px;" placeholder="" value="${no_of_pcs}">
                        </div>
                        <div class="ms-2">
                            <label for="">Cost Per Case</label>
                            <input type="number" id="price_of_cotton${id}" class="form-control" style="width: 120px;" placeholder="" value="${price_of_cotton}">
                        </div>
                        <div class="ms-2">
                            <label for="">Supplier</label>
                            <input type="text" id="supplier${id}" class="form-control" style="width: 120px;" placeholder="" value="${labelsupplier}">
                        </div>
                    </div>
                </div>
            `;
        } else if (labelType === 'carton_size') {
            // Carton Size
            inputFields += `
                <div class="row d-flex align-items-center gap-2">
                    <div class="col-md-6 d-flex align-items-center gap-2">
                        <input type="number" id="heightoption${id}" class="form-control mt-4" style="width: 50px;" placeholder="8" value="${height}">
                        <span class="mx-1 mt-4">X</span>
                        <input type="number" id="widthoption${id}" class="form-control mt-4" style="width: 50px;" placeholder="10" value="${width}">
                        <span class="mx-1 mt-4">X</span>
                        <input type="number" id="weightoption${id}" class="form-control mt-4" style="width: 50px;" placeholder="5" value="${weight}">
                    </div>
                    <div class="col-md-12 d-flex align-items-center gap-2">
                        <div class="ms-2">
                            <label for="">No of Carton in Bundle</label>
                            <input type="number" id="no_of_pcs${id}" class="form-control" style="width: 120px;" placeholder="" value="${no_of_pcs}">
                        </div>
                        <div class="ms-2">
                            <label for="">Cost per bundle</label>
                            <input type="number" id="price_of_cotton${id}" class="form-control" style="width: 120px;" placeholder="" value="${price_of_cotton}">
                        </div>
                        <div class="ms-2">
                            <label for="">Supplier</label>
                            <input type="text" id="supplier${id}" class="form-control" style="width: 120px;" placeholder="" value="${labelsupplier}">
                        </div>
                    </div>
                </div>
            `;
        } else if (labelType === 'shrink_wrap_size') {
            // Shrink Wrap Size
            inputFields += `
                <div class="row d-flex align-items-center gap-2">
                    <div class="col-md-6 d-flex align-items-center gap-2">
                        <input type="number" id="heightoption${id}" class="form-control mt-4" style="width: 50px;" placeholder="8" value="${height}">
                        <span class="mx-1 mt-4">X</span>
                        <input type="number" id="widthoption${id}" class="form-control mt-4" style="width: 50px;" placeholder="10" value="${width}">
                    </div>
                    <div class="col-md-12 d-flex align-items-center gap-2">
                        <div class="ms-2">
                            <label for="">Shrink per Case</label>
                            <input type="number" id="no_of_pcs${id}" class="form-control" style="width: 120px;" placeholder="" value="${no_of_pcs}">
                        </div>
                        <div class="ms-2">
                            <label for="">Cost per Case</label>
                            <input type="number" id="price_of_cotton${id}" class="form-control" style="width: 120px;" placeholder="" value="${price_of_cotton}">
                        </div>
                        <div class="ms-2">
                            <label for="">Supplier</label>
                            <input type="text" id="supplier${id}" class="form-control" style="width: 120px;" placeholder="" value="${labelsupplier}">
                        </div>
                    </div>
                </div>
            `;
        } else if (labelType === 'bubble_wrap') {
            // Shrink Wrap Size
            inputFields += `
                <div class="row d-flex align-items-center gap-2">
                    <div class="col-md-6 d-flex align-items-center gap-2">
                        <input type="number" id="heightoption${id}" class="form-control mt-4" style="width: 50px;" placeholder="8" value="${height}">
                        <span class="mx-1 mt-4">X</span>
                        <input type="number" id="widthoption${id}" class="form-control mt-4" style="width: 50px;" placeholder="10" value="${width}">
                    </div>
                    <div class="col-md-12 d-flex align-items-center gap-2">
                        <div class="ms-2">
                            <label for="">Feet per Roll</label>
                            <input type="number" id="no_of_pcs${id}" class="form-control" style="width: 120px;" placeholder="" value="${no_of_pcs}">
                        </div>
                        <div class="ms-2">
                            <label for="">Cost for Bubble Wrap Roll</label>
                            <input type="number" id="price_of_cotton${id}" class="form-control" style="width: 120px;" placeholder="" value="${price_of_cotton}">
                        </div>
                        <div class="ms-2">
                            <label for="">Supplier</label>
                            <input type="text" id="supplier${id}" class="form-control" style="width: 120px;" placeholder="" value="${labelsupplier}">
                        </div>
                    </div>
                </div>
            `;
        } else {
            // Default Input
            inputFields += `
                <input type="text" id="defaultOption${id}" class="form-control mt-2" placeholder="Enter"
                value="${height}">
                <div class="col-md-12 d-flex align-items-center gap-2">
                    <div class="ms-2">
                        <label for="">Labels in per Roll</label>
                        <input type="number" id="no_of_pcs${id}" class="form-control" style="width: 120px;" placeholder="" value="${no_of_pcs}">
                    </div>
                    <div class="ms-2">
                        <label for="">Cost Per Roll</label>
                        <input type="number" id="price_of_cotton${id}" class="form-control" style="width: 120px;" placeholder="" value="${price_of_cotton}">
                    </div>
                    <div class="ms-2">
                         <label for="">Supplier</label>
                        <input type="text" id="supplier${id}" class="form-control" style="width: 120px;" placeholder="" value="${labelsupplier}">
                    </div>
                </div>
            `;
        }

        // Append the generated input fields to the container
        targetContainer.append(inputFields);
    }
    function appendOptions(labelId, newOptions) {
        let tableId = `#optionsTable${labelId}`;
        let tableBody = $(`#optionsTableBody${labelId}`);

        // Destroy existing DataTable instance if any
        if ($.fn.DataTable.isDataTable(tableId)) {
            $(tableId).DataTable().destroy();
        }

        tableBody.empty();
        newOptions = sortOptionsByNumericValues(newOptions);
        if(labelId == 1){
            tableheader = `<tr>
                <th>Poly Bag</th>
                <th class="text-center">polybags per Case</th>
                <th class="text-center">Cost per Case</th>
                <th class="text-center">Cost per polybag</th>
                <th class="text-center">Supplier</th>
                <th>Actions</th>
            </tr>`;
           
        }else if(labelId == 2){
            tableheader = `<tr>
                <th>Carton</th>
                <th class="text-center">No of Carton in Bundle</th>
                <th class="text-center">Cost per bundle</th>
                <th class="text-center">Cost per Carton</th>
                <th class="text-center">Supplier</th>
                <th>Actions</th>
            </tr>`;
        }else if(labelId == 6){
            tableheader = `<tr>
                <th>Shrink Wrap</th>
                <th class="text-center">Shrinkbag per Case</th>
                <th class="text-center">Cost per Case</th>
                <th class="text-center">Cost per shrinkbag</th>
                <th class="text-center">Supplier</th>
                <th>Actions</th>
            </tr>`;
        }else if(labelId == 8){
            tableheader = `<tr>
                <th>Bubble  Wrap</th>
                <th class="text-center">Feets per  Roll</th>
                <th class="text-center">Cost of Bubble wrap Roll</th>
                <th class="text-center">Cost per feet</th>
                <th class="text-center">Supplier</th>
                <th>Actions</th>
            </tr>`;
        }else{
            tableheader = `<tr>
                <th>Labels</th>
                <th class="text-center">Label in a Roll</th>
                <th class="text-center">Cost per Roll</th>
                <th class="text-center">Cost per label</th>
                <th class="text-center">Supplier</th>
                <th>Actions</th>
            </tr>`;
        }
        $('#optionsTableHead'+labelId).html(tableheader);
        newOptions.forEach(function (option) {
            let optionsType = '';
            if (labelId == 1) optionsType = 'poly_bag_size';
            else if (labelId == 2) optionsType = 'carton_size';
            else if (labelId == 6) optionsType = 'shrink_wrap_size';
            else if (labelId == 7) optionsType = 'label_1';
            else if (labelId == 8) optionsType = 'bubble_wrap';
            var tableheader ='';
            

            let prce = '';
            if (option.no_of_pcs_in_cotton && option.price_of_cotton) {
                prce = `x ${option.no_of_pcs_in_cotton}" x ${option.price_of_cotton}"`;
            }
            var row='';

            const parts = option.value.split('X').map(v => v.trim());
             var   height = parts[0] || '';
              var  width = parts[1] || '';
              var  weight = parts[2] || '';


            row = `
                <tr id="option${labelId}_${option.id}">
                    <td>
                       <strong>${option.value}</strong>
                    </td>
                    <td class="text-center">${option.no_of_pcs_in_cotton || '0'}</td>
                    <td class="text-center"> <div class=""> <sapn>$</sapn>  ${parseFloat(option.price_of_cotton || 0).toFixed(2)}</div> </td>
                    <td class="text-center">
                        <div class=""> <sapn>$</sapn>
                    ${(option.no_of_pcs_in_cotton && option.price_of_cotton
                        ? (option.price_of_cotton / option.no_of_pcs_in_cotton)
                        : 0).toFixed(2)}</div>
                    </td>
                    <td class="text-center">${option.supplier??'-'}</td>
                    <td>
                        <button type="button" class="btn btn-primary btn-sm editOption"
                            data-label-id="${labelId}"
                            data-label-supplier="${option.supplier}"
                            data-option-id="${option.id}"
                            data-no-of-pcs="${option.no_of_pcs_in_cotton}"
                            data-price-cotton="${option.price_of_cotton}"
                            data-option="${option.value.replace(/"/g, '')}"
                            data-label-type="${optionsType}">
                            <i class="ri-pencil-fill"></i>
                        </button>
                        <button type="button" class="btn btn-danger btn-sm deleteOption"
                            data-label-id="${labelId}"
                            data-option-id="${option.id}">
                            <i class="ri-chat-delete-fill"></i>
                        </button>
                    </td>
                </tr>`;
            tableBody.append(row);
        });

        // Re-initialize DataTable
        $(tableId).DataTable({
            pageLength: 25,
            lengthMenu: [5, 10, 25, 50],
            ordering: false,
            searching: false,
            info: false
        });
    }
    function getLabels(){
        $.ajax({
            url:"{{ url('get-lables') }}",
            type:"GET",
            aync:false,
            success:function(data){
                $.each(data,function(index,value){
                    getOptions(value.id);
                })
            }
        })
    }
    function getOptions(id){
        // Example AJAX request to fetch options (replace the URL and data as needed)
        $.ajax({
            url: "{{ url('fetch-options-url') }}", // Replace with your backend endpoint
            type: 'GET',
            aync:false,
            data: { label_id: id }, // Pass the label ID if needed
            success: function (response) {
                // Assuming response contains an array of options
                appendOptions(id, response.options);
            },
            error: function () {
                alert('Failed to fetch options.');
            }
        });
    }
    function sortOptionsByNumericValues(options) {
        // Function to extract numbers from a string and return them as an array of integers
        function extractNumericValues(str) {
            let matches = str.match(/\d+/g); // Match all numeric parts
            return matches ? matches.map(Number) : [0]; // Convert to integers or default to [0]
        }

        // Sort options by extracted numeric values
        options.sort((a, b) => {
            let numsA = extractNumericValues(a.value);
            let numsB = extractNumericValues(b.value);

            // Compare each number sequentially
            for (let i = 0; i < Math.max(numsA.length, numsB.length); i++) {
                let numA = numsA[i] || 0; // Default to 0 if one array is shorter
                let numB = numsB[i] || 0;

                if (numA !== numB) {
                    return numA - numB; // Sort in ascending order
                }
            }

            return 0; // If all numbers are equal, maintain original order
        });

        return options;
    }
    $(document).on('keypress', 'input', function (e) {
        if (e.which === 13) { // Check if Enter key is pressed
            e.preventDefault(); // Prevent form submission if any
            let labelId = $(this).closest('.label-container').attr('id').replace('labelContainer', '');
            let inputsFilled = true;

            // Check all inputs under the current label
            $(`#labelContainer${labelId} input`).each(function () {
                if ($(this).val().trim() === '') {
                    inputsFilled = false; // Mark as not filled if any input is empty
                }
            });

            if (inputsFilled) {
                // Trigger the click on the corresponding "Add Option" button
                $(`#addOptionBtn${labelId}`).click();
            } else {
                alert('Please fill in all input fields before adding an option.');
            }
        }
    });


</script>
@endsection
