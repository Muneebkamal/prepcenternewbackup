@extends('layouts.app')

@section('title', 'Edit Daily Input | Prepcenter')

@section('content')

<div class="container-fluid">
                        
    <!-- start page title -->
    {{-- <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Daily Input</h4>

                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Forms</a></li>
                        <li class="breadcrumb-item active">Basic Elements</li>
                    </ol>
                </div>

            </div>
        </div>
    </div> --}}

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header align-items-center d-flex">
                    <h4 class="card-title mb-0 flex-grow-1">Edit Daily Input</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <form id="dailyInputEdit">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <input type="hidden" name="id" value="{{ $daily_input->id }}">
                            <div class="col-md-6 mt-2">
                                <label for="">Employee</label>
                                <select name="employee_id" class="form-select" disabled>
                                    @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}" {{ $daily_input->employee_id == $employee->id ? 'selected' : '' }} >{{ $employee->name }}</option>
                                    @endforeach
                                </select>
                                @error('employee')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="col-md-6 mt-2">
                                <label for="">Date</label>
                                <input type="date" name="date" class="form-control" value="{{ $daily_input->date }}" readonly>
                                @error('date')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="col-md-6 mt-2">
                                <label for="">Start Time</label>
                                <input type="time" name="start_time" value="{{ $daily_input->start_time }}" class="form-control" required>
                                @error('start_time')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="col-md-6 mt-2">
                                <label for="">End Time</label>
                                <input type="time" name="end_time" value="{{ $daily_input->end_time }}" class="form-control" required>
                                @error('end_time')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            
                            <div class="col-md-12">
                                <div class="d-flex justify-content-end mt-3">
                                    <button class="btn btn-danger" id="resetButton">RESET</button>
                                    <button type="submit" class="btn btn-primary ms-2">SAVE</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- end page title -->

</div>

@endsection

@section('script')
<script>
     $(document).ready(function() {
        $('#resetButton').click(function() {
            $('#dailyInputEdit')[0].reset(); // [0] is used to access the DOM element
        });

        $('#dailyInputEdit').on('submit', function(e) {
            e.preventDefault();

            // Create FormData object
            let formData = new FormData(this);
            let id = $('input[name="id"]').val();

            $.ajax({
                url: "{{ route('daily-input.update', 'id') }}".replace('id', id),
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    if (response.success) {
                        var id = response.id;
                        var url = "{{ route('daily-input.show', ':id') }}".replace(':id', id);
                        window.location.href = url;
                    } else {
                        alert('An error occurred.');
                    }
                    console.log(response.success);
                },
                error: function(xhr) {
                    // Handle error response
                    let errors = xhr.responseJSON.errors;
                    
                }
            });
        });
    });
</script>
@endsection