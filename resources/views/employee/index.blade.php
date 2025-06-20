@extends('layouts.app')

@section('title', 'Employees | Prepcenter')

@section('content')
<style>
    .badge-inactive {
        background-color: #6c757d; /* Bootstrap's "bg-secondary" gray */
        color: white;
    }
    .badge-active {
        background-color: #28a745; /* Bootstrap's "bg-success" green */
        color: white;
    }
</style>
<div class="container-fluid">
                        
    <!-- start page title -->
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
        <div class="col-lg-12 p-0">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <h5 class="card-title mb-0">Employees</h5>
                    <div class="d-flex">
                        <button id="toggleInactive" class="btn btn-sm btn-secondary me-2">Show Inactive Employees</button>
                        <div class="add-btn">
                            <a href="{{ route('employees.create') }}" class="btn btn-sm btn-primary">Add Employee</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="example1" class="table table-striped align-middle" style="width:100%">
                            <thead>
                                <tr>
                                    <th><small>Full Name</small></th>
                                    <th><small>Department</small></th>
                                    <th><small>Privilege</small></th>
                                    <th><small>Status</small></th>
                                    <th><small>Action</small></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($employees as $employee)
                                    <tr class="{{ $employee->status == '1' ? 'inactive-employee' : '' }}">
                                        <td>
                                            <small class="fw-bold m-0"><a class="text-dark text-decoration-none" href="{{ route('employees.show', $employee->id) }}">{{ $employee->name }}</a></small><br>
                                            <small class="m-0"><a href="{{ route('employees.show', $employee->id) }}">{{ $employee->email }}</a></small>
                                        </td>
                                        <td>
                                            <small class="m-0">
                                                @if($employee->departments)
                                                    {{ $employee->departments->dep_name ?? '--' }}
                                                @else
                                                    No Department
                                                @endif
                                            </small>
                                        <td>
                                            @if($employee->role == 1)
                                                <small class="m-0">Manager</small>
                                            @elseif($employee->role == 2)
                                                <small class="m-0">User</small>
                                            @endif
                                        </td>
                                        <td>                                        
                                            @if($employee->status == '0')
                                            <span class="badge  badge-active">Active</span>
                                            @elseif($employee->status == '1')
                                            <span class="badge badge-inactive">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex">
                                                <a href="{{ route('employees.show', $employee->id) }}" class="btn btn-sm btn-success edit-item-btn me-1"><i class="ri-pencil-fill align-bottom"></i> Edit</a>
                                                <form method="POST" action="{{ route('employees.destroy', $employee->id) }}" style="display:inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger remove-item-btn">
                                                        <i class="ri-delete-bin-fill align-bottom"></i> Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
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
<script>
    $(document).ready(function() {
        $('#example1').DataTable({
            "ordering": false,
            "pageLength": userPerPageLength
        });

        // Show/Hide Inactive Employees
        $('#toggleInactive').click(function() {
            var buttonText = $(this).text();
            if (buttonText === 'Show Inactive Employees') {
                $('.inactive-employee').show();
                $(this).text('Hide Inactive Employees');
            } else {
                $('.inactive-employee').hide();
                $(this).text('Show Inactive Employees');
            }
        });

        // Initially hide inactive employees
        $('.inactive-employee').hide();
    });
</script>
@endsection
