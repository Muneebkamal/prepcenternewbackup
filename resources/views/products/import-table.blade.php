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
                    <h4 class="card-title mb-0">Import CSV File</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <div class="row" id="formrow">
                        <div class="d-flex justify-content-center align-items-center">
                            <div class="col-md-5">
                                <form action="#" id="formdata" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <label for="file">Select CSV File:</label>
                                    <input class="form-control" id="csv_file" name="csv_file" type="file" accept=".csv">
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-5 d-none" id="tablerow">
                        <div class="col-md-12">
                            <table id="csv-table" class="table table-bordered" border="1" >
                                <thead>
                                    <!-- Columns will be populated by AJAX -->
                                </thead>
                                <tbody>
                                    <!-- Rows will be populated by AJAX -->
                                </tbody>
                            </table>
                            <div class="d-flex justify-content-right mt-4">
                                <button type="button" id="importbtn" class="btn btn-success me-2 float-left" disabled="disbaled">Import</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div> <!-- end col -->
    </div>
    
    <!-- end page title -->

</div>

@endsection

@section('script')
<script>
    // $(document).ready(function() {
    //     $('#success-alert').each(function() {
    //         setTimeout(() => $(this).fadeOut('slow'), 3000); // 3000 milliseconds = 3 seconds
    //     });

    //     // Set a timeout for the error alert
    //     $('#error-alert').each(function() {
    //         setTimeout(() => $(this).fadeOut('slow'), 3000); // 3000 milliseconds = 3 seconds
    //     });
    // });

    // $(document).ready(function() {
    //     $('#show_file').on('change', function(event) {
    //         var file = event.target.files[0];
    //         if (file && file.type === 'text/csv') {
    //             var reader = new FileReader();
    //             reader.onload = function(e) {
    //                 var text = e.target.result;
    //                 var rows = text.split('\n').map(function(row) {
    //                     return row.split(',');
    //                 });
    //                 var headers = rows[0];
    //                 var $tableHeaders = $('#tableHeaders');
    //                 var $tableBody = $('#tableBody');
    //                 var $csvTable = $('.csvTable');

    //                 // Show the table
    //                 $csvTable.show();

    //                 // Clear previous content
    //                 $tableHeaders.empty();
    //                 $tableBody.empty();

    //                 // Create table headers
    //                 headers.forEach(function(header) {
    //                     $tableHeaders.append('<th>' + header + '</th>');
    //                 });

    //                 // Create table rows
    //                 rows.slice(1).forEach(function(row) {
    //                     var $tr = $('<tr>');
    //                     row.forEach(function(cell) {
    //                         $tr.append('<td>' + cell + '</td>');
    //                     });
    //                     $tableBody.append($tr);
    //                 });
    //             };
    //             reader.readAsText(file);
    //         } else {
    //             alert('Please upload a CSV file.');
    //             $('.csvTable').hide(); // Hide the table if the file is not a CSV
    //         }
    //     });
    // });
</script>
<script>
    $(document).ready(function() {
        $('#csv_file').on('change', function(e) {
            e.preventDefault();
            let formData = new FormData();
            formData.append('csv_file', this.files[0]);
            $.ajax({
                url: "{{ route('csv.upload') }}",
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    // Ensure columns is an array
                if (!Array.isArray(response.columns)) {
                    alert('Unexpected response format.');
                    return;
                }
                let dbColumns = ['item','msku','asin','fnsku'];
                let columns = response.columns;
                let rows = response.rows;
                // Populate table
                let thead = $('#csv-table thead');
                let tbody = $('#csv-table tbody');
                thead.empty();
                tbody.empty();

                // Define column mappings based on the file type
                const amazonColumns = ['Title', 'MSKU', 'ASIN', 'FNSKU'];
                const walmartColumns = ['Item name', 'GTIN', 'Item ID', 'SKU'];

                let requiredColumns;
                let headerRow;
                let dropdownRow;

                // Check if columns include any known values for Amazon or Walmart
                const isAmazonFile = amazonColumns.some(col => columns.includes(col));
                const isWalmartFile = walmartColumns.some(col => columns.includes(col));
                // Create mappings of column names to indices
                let columnIndexMap = {};
                    columns.forEach((column, index) => {
                    columnIndexMap[column] = index;
                });
                if (isAmazonFile) {
                    requiredColumns = ['MSKU','Title', 'FNSKU','ASIN'];
                    headerRow = '<tr>';
                    dropdownRow = '<tr>';

                    requiredColumns.forEach((requiredColumn) => {
                        dropdownRow += `<th><select class="column-mapping form-control select2" data-column="${requiredColumn}">
                            <option value="">Select Column for ${requiredColumn}</option>`;
                        dbColumns.forEach((dbColumn, index) => {
                            dropdownRow += `<option value="${dbColumn}">${dbColumn}</option>`;
                        });
                        dropdownRow += `</select></th>`;
                    });

                    headerRow += '</tr>';
                    dropdownRow += '</tr>';

                    thead.append(headerRow);
                    thead.append(dropdownRow);

                } else if (isWalmartFile) {
                    requiredColumns = ['Item name', 'Item ID', 'SKU', 'GTIN'];
                    headerRow = '<tr>';
                    dropdownRow = '<tr>';

                    requiredColumns.forEach((requiredColumn) => {
                        dropdownRow += `<th><select class="column-mapping form-control select2" data-column="${requiredColumn}">
                            <option value="">Select Column for ${requiredColumn}</option>`;
                        dbColumns.forEach((dbColumn, index) => {
                            dropdownRow += `<option value="${dbColumn}">${dbColumn}</option>`;
                        });
                        dropdownRow += `</select></th>`;
                    });

                    headerRow += '</tr>';
                    dropdownRow += '</tr>';

                    thead.append(headerRow);
                    thead.append(dropdownRow);
                } else {
                    alert('Unsupported file format');
                    return;
                }

                rows.forEach(row => {
                    let rowHtml = '<tr>';
                    row.forEach(cell => rowHtml += `<td>${cell}</td>`);
                    rowHtml += '</tr>';
                    tbody.append(rowHtml);
                });
                // $('#importbtn').attr('disabled',false);
                $('#tablerow').removeClass('d-none');
                $('#formrow').addClass('d-none');
                // Initially disable the import button
                 $('#importbtn').attr('disabled', true);

                // Check the state of dropdowns after populating
                checkAllDropdownsSelected();
                },
                error: function(xhr) {
                    alert('Error uploading file');
                }
            });
        });

        $('#importbtn').on('click', function() {
            let columnMappings = {};
            var columnIndices = [];

            // Collect column mappings and their indices
            $('.column-mapping').each(function() {
                let column = $(this).data('column');
                let value = $(this).val();
                if (value) {
                    columnMappings[column] = value;
                    columnIndices.push(column); // Store the column name by index
                }
            });
            // Prepare rows based on column indices
            let rows = $('#csv-table tbody tr').map(function() {
            let row = {};
            $(this).find('td').each(function(index) {
                console.log($(this).text().trim());
                let columnName = columnIndices[index];
                console.log(`Index: ${index}, Column Name: ${columnName}`); // Debugging
                if (columnName) {
                    row[columnName] = $(this).text().trim(); // Use trim() to remove extra spaces
                }
            });
            // Log each row object for debugging
                return row;
            }).get();
            let chunkSize = 100; // Adjust this value based on your needs
            let rowChunks = [];
            let savedChunksCount = 0; // Track the number of successfully saved chunks

            // Split the rows into chunks
            for (let i = 0; i < rows.length; i += chunkSize) {
                rowChunks.push(rows.slice(i, i + chunkSize));
            }

            rowChunks.forEach(function(chunk, index) {
                $.ajax({
                    url: "{{ route('csv.saveColumns') }}",
                    type: 'POST',
                    data: {
                        column_mapping: columnMappings,
                        rows: chunk,
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        console.log(`Chunk ${index + 1} saved successfully`);
                        savedChunksCount++;

                        // Check if this is the last chunk
                        if (savedChunksCount === rowChunks.length) {
                            // Redirect to the products page after the last chunk is saved
                            window.location.href = "{{ route('products.index') }}";
                        }
                    },
                    error: function(xhr) {
                        alert('Error saving chunk');
                    }
                });
            });


            // $.ajax({
            //     url: "{{ route('csv.saveColumns') }}",
            //     type: 'POST',
            //     data: {
            //         column_mapping: columnMappings,
            //         rows: rows,
            //     },
            //     headers: {
            //         'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            //     },
            //     success: function(response) {
            //         alert('Columns saved successfully');
            //             window.location.href = "{{ route('products.index') }}";;
            //     },
            //     error: function(xhr) {
            //         alert('Error saving columns');
            //     }
            // });
        });
    });
    function checkAllDropdownsSelected() {
        let allSelected = true;
        // Iterate over each required column dropdown
        $('.column-mapping').each(function() {
            if ($(this).val() === '') {
                allSelected = false;
            }
        });
        // Enable or disable the import button based on the selection state
        $('#importbtn').prop('disabled', !allSelected);
    }

// Bind the function to change events of all dropdowns
$(document).on('change', '.column-mapping', function() {
    checkAllDropdownsSelected();
});
</script>
@endsection