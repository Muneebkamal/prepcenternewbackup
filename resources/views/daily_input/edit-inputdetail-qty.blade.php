@extends('layouts.app')

@section('title', 'Edit Daily Input | Prepcenter')

@section('content')

<div class="container-fluid">
    <div class="container-fluid py-4">
        <h5>Update QTY Daily Input FNSKU = ({{ $inputDetails->fnsku }})</h5>
      
        <button type="button" class="btn btn-info btn-lg" onclick="window.history.back();">Back</button>
      
        <div class="row">
          <div class="col-md-7 mt-4">
            <div class="card">
              <div class="card-header pb-0 px-3">
                <!-- Optional Header Content -->
              </div>
              <div class="card-body pt-4 p-3">
                <ul class="list-group">
                  <li class="list-group-item border-0 d-flex p-4 mb-2 bg-light rounded">
                    <form method="post" action="" id="detailEdit" class="w-100">
                        @csrf
                      <div class="d-flex flex-column">
                        <!-- Hidden Inputs -->
                        <input type="hidden" name="detail_id" id="detail_id" value="{{ $inputDetails->id }}">
                        <input type="hidden" name="daily_input_id" id="daily_input_id" value="{{ $inputDetails->daily_input_id }}">
                        <input type="hidden" name="current_pcs" value="{{ $inputDetails->pack }}">
                        <input type="hidden" name="current_qty" value="{{ $inputDetails->qty }}">
      
                        <!-- Display Fields -->
                        <div class="mb-3">
                          <label class="form-label"><b>Title:</b></label>
                          <span class="ms-2 text-dark">{{ $inputDetails->product?$inputDetails->product->item:'N/A' }}</span>
                        </div>
      
                        <div class="mb-3">
                          <label for="pcs" class="form-label"><b>PCS:</b></label>
                          <input type="number" id="pcs" name="edit_pack" class="form-control" value="{{ $inputDetails->pack }}">
                        </div>
      
                        <div class="mb-3">
                          <label for="qty" class="form-label"><b>QTY:</b></label>
                          <input type="number" id="qty" name="edit_qty" class="form-control" value="{{ $inputDetails->qty }}">
                        </div>
                      </div>
      
                      <div class="d-flex">
                        <button type="submit" class="btn btn-info">Save</button>
                      </div>
                    </form>
                  </li>
                </ul>
                <div class="text-muted mt-3">
                  <i>*When update is <b>Successful</b>, You will automatically return to the daily input detail page.</i>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
</div>
@endsection

@section('script')
<script>
      $('#detailEdit').on('submit', function(e) {
            e.preventDefault(); // Prevent the default form submission

            let formData = new FormData(this);

            // Define the URL for the AJAX request
            let url = "{{ route('daily.input.detail.edit', ':detail_id') }}".replace(':detail_id', $('#detail_id').val());
            var daily_input_id = $('#daily_input_id').val();

            // Send AJAX request
            $.ajax({
                url: url, // URL for the request
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false, 
                success: function(response) {
                    if (response.success) {
                        // location.reload();
                        // alert('Update successful');
                        // Optionally, redirect or update the UI here
                        // var id = response.id;
                        var url = "{{ route('daily-input.show', ':id') }}".replace(':id', daily_input_id);
                        window.location.href = url;
                    } else {
                        alert('An error occurred.');
                    }
                    console.log(response.success);
                },
                error: function(xhr) {
                    let errors = xhr.responseJSON.errors;
                    console.error(errors);
                }
            });
        });
</script>
@endsection