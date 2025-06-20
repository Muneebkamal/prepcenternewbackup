@extends('layouts.app')

@section('title', 'Import Products | Prepcenter')

@section('styles')
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
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Amazon Product Import - Export from Inventory</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <div class="row">
                        <div class="d-flex justify-content-center align-items-center">
                            <div class="col-md-5">
                                <form action="{{ route('import.csv') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <label for="file">Select CSV File:</label>
                                    <input class="form-control" id="file" name="file" type="file" accept=".csv">
                                    <div class="d-flex justify-content-center mt-4">
                                        <button type="submit" class="btn btn-primary me-2">Upload</button>
                                        <a class="btn btn-danger">Cancel / Back</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Walmart Product Import - Export from Walmart Inventory</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <div class="row">
                        <div class="d-flex justify-content-center align-items-center">
                            <div class="col-md-5">
                                <form action="{{ route('import.walmart') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <label for="">Select CSV File:</label>
                                    <input class="form-control" id="walmartFile" name="walmartFile" type="file" accept=".csv">
                                    <div class="d-flex justify-content-center mt-4">
                                        <button type="submit" class="btn btn-primary me-2">Upload</button>
                                        <button class="btn btn-danger">Cancel / back</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end card -->
        </div> <!-- end col -->
    </div>
    
    <!-- end page title -->

</div>

@endsection

@section('script')
<script>
    $(document).ready(function() {
        $('#success-alert').each(function() {
            setTimeout(() => $(this).fadeOut('slow'), 3000); // 3000 milliseconds = 3 seconds
        });

        // Set a timeout for the error alert
        $('#error-alert').each(function() {
            setTimeout(() => $(this).fadeOut('slow'), 3000); // 3000 milliseconds = 3 seconds
        });
    });
</script>
@endsection