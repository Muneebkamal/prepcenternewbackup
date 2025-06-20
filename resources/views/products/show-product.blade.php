@extends('layouts.app')

@section('title', 'Product Details | Prepcenter')

@section('content')

<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header align-items-center d-flex">
                    <h4 class="card-title mb-0 flex-grow-1">Product Detail</h4>
                    <a href="{{ route('products.edit', $product->id) }}" class="btn btn-primary text-end">Edit Product</a>
                </div><!-- end card header -->
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card" style="width: 100%;">
                                <img 
                                    src="{{ $product->image ? asset($product->image) : 'https://www.shutterstock.com/shutterstock/photos/2311073121/display_1500/stock-vector-no-photo-thumbnail-graphic-element-no-found-or-available-image-in-the-gallery-or-album-flat-2311073121.jpg'}}" 
                                    class="card-img-top img-thumbnail img-fluid" 
                                    alt="Product Image"
                                    style="max-height:  200px; width: 100%; object-fit: contain;">
                            </div>
                        </div>

                    </div>
                    <form id="productEdit">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-12 mt-2">
                                <input type="hidden" name="id" value="{{ $product->id }}">
                                <label for="">ITEM NAME</label>
                                <textarea name="" id="" cols="10"  name="item" class="form-control"  rows="2" placeholder="Item / Title Product">{{ $product->item }}</textarea>
                                
                                @error('item')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="col-md-4 mt-2">
                                <label for="" class="d-flex justify-content-between align-items-center">
                                    <span>ASIN / ITEM ID</span>
                                    @php
                                        $firstChar = $product->fnsku[0];
                                        if ($firstChar === 'X') {
                                            $link = "https://www.amazon.com/dp/{$product->asin}";
                                        } elseif ($firstChar === '0' || $firstChar === '1') {
                                            $link = "https://www.walmart.com/ip/{$product->asin}";
                                        } else {
                                            $link = "https://www.amazon.com/dp/{$product->asin}";
                                        }
                                    @endphp
                                    <a id="asin-link" href="{{ $link }}" target="_blank" class="text-primary" style="font-size: 0.9rem;">{{ $link }}</a>
                                </label>
                                <input type="text" name="asin" value="{{ $product->asin }}" class="form-control" placeholder="ASIN">
                                @error('asin')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="col-md-3 mt-2">
                                <label for="">MSKU / SKU</label>
                                <input type="text" name="msku" value="{{ $product->msku }}" class="form-control" placeholder="MSKU">
                                @error('msku')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="col-md-3 mt-2">
                                <label for="">FNSKU / GTIN</label>
                                <input type="text" name="fnsku" value="{{ $product->fnsku }}" class="form-control" placeholder="FNSKU" readonly >
                                @error('fnsku')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="col-md-2 mt-2">
                                <label for="">PACK</label>
                                <input type="number" name="pack" value="{{ $product->pack }}" class="form-control" placeholder="Pack">
                                @error('pack')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>                           
                        </div>
                        <fieldset class="border p-3 mt-5 rounded">
                            <legend class="fw-bold">WFS / FBA Packing Info</legend>
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle">
                                    <tbody>
                                        <tr>
                                            <th colspan="6"><h4>Packing</h4></th>
                                        </tr>
                                        <tr>
                                            <!-- Poly Bag -->
                                            <th>Poly Bag</th>
                                            <td>
                                                <div class="form-check">
                                                    <input type="checkbox" id="poly_bag" name="poly_bag" class="form-check-input" {{ $product->poly_bag ? 'checked' : '' }}>
                                                    <label for="poly_bag">{{ $product->poly_bag_size ?? 'N/A' }}</label>
                                                </div>
                                            </td>

                                            <!-- Shrink Wrap -->
                                            <th>Shrink Wrap</th>
                                            <td>
                                                <div class="form-check">
                                                    <input type="checkbox" id="shrink_wrap" name="shrink_wrap" class="form-check-input" {{ $product->shrink_wrap ? 'checked' : '' }}>
                                                    <label for="shrink_wrap">{{ $product->shrink_wrap_size ?? 'N/A' }}</label>
                                                </div>
                                            </td>

                                            <!-- Bubble Wrap -->
                                            <th>Bubble Wrap</th>
                                            <td>
                                                {{ $product->bubble_wrap ?? 'N/A' }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="table-responsive mt-3">
                                <table class="table table-bordered align-middle">
                                    <tbody>
                                        <tr>
                                            <th colspan="4"><h4>Labels</h4></th>
                                        </tr>
                                       
                                        <tr>
                                            <th>Label 1</th>
                                            <td>{{ $product->label_1 }}</td>
                                            <th>Label 2</th>
                                            <td>{{ $product->label_2 }}</td>
                                            <th>Label 3</th>
                                            <td>{{ $product->label_3 }}</td>
                                        </tr>
                            
                                        
                                        @foreach($packingTemplates as $template)
                            
                                        @endforeach
                                        
                                    </tbody>
                                </table>
                            </div>

                            <div class="table-responsive mt-3">
                                <table class="table table-bordered align-middle">
                                    <tbody>
                                        <tr>
                                            <th>Packing Link</th>
                                            <td>{{ $product->packing_link }}</td>
                                            <th>Packing Note</th>
                                            <td>{{ $product->packing_note }}</td>
                                        </tr>
                                       
                                    </tbody>
                                </table>
                            </div>

                            <div class="table-responsive mt-3">
                                <table class="table table-bordered align-middle">
                                    <thead>
                                        <th colspan="6">
                                            <h5>Carton Packing Details</h5>
                                        </th>
                                    </thead>
                                    <tbody>
                                        <th>Name</th>
                                        <th>Type</th>
                                        <th>Units per Box</th>
                                        <th>Box Dimensions (L×W×H)</th>
                                        <th>Box Weight</th>
                                        <th>Use Original Pack</th>
                                        @foreach($packingTemplates as $template)
                                        <tr>
                                            <td>{{ $template->template_name }}</td>
                                            <td>{{ ucfirst(str_replace('_', ' ', $template->template_type)) }}</td>
                                            <td>{{ $template->units_per_box }}</td>
                                            <td>{{ $template->box_length }} × {{ $template->box_width }} × {{ $template->box_height }} inches</td>
                                            <td>{{ $template->box_weight }}</td>
                                            <td>{{ ($template->original_pack?'yes':'no') }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </fieldset>
                        
                        <fieldset class="border p-3 mt-5 rounded">
                            <legend class="fw-bold">
                                SELLER Fulfilled / FBM Packing Info
                            </legend>
                        
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle">
                                    <tbody>
                                        <tr>
                                            <th>Carton Size</th>
                                            <td>{{ $product->cotton_size_sales }}</td>
                                        </tr>
                                        <tr>
                                            <th>Weight</th>
                                            <td>{{ $product->weight_lb ?: '' }} x {{ $product->weight_oz ?: '' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </fieldset>
                        
                        <div class="row mt-1">
                                                    
                                <!-- Save Button -->
                                <div class="col-md-12">
                                    <div class="d-flex justify-content-end mt-3">
                                        <button type="submit" class="btn btn-primary ms-2" id="save-button">SAVE</button>
                                        <button type="submit" class="btn btn-secondary ms-2" id="save-close-button">SAVE AND CLOSE</button>
                                    </div>
                                </div>
                        </div>
                        <input type="hidden" name="action" id="form-action" value="save">
                    </form>
                </div>
            </div>
            <div class="card">
                <div class="card-header align-items-center d-flex">
                    <h4 class="card-title mb-0 flex-grow-1">Products Detail Record</h4>
                </div>
                <div class="card-body">
                    <table id="example3" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                        <thead>
                            <tr>
                                <th data-ordering="false">Working Date</th>
                                <th>Employee Name</th>
                                <th>Pack</th>
                                <th>Qty</th>
                                {{-- <th>Action</th> --}}
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($product->dailyInputDetails as $item)
                                <tr>
                                    <td><a href="{{ url('edit-daily-input-qty',$item->id) }}">{{$item->dailyInput->date}}</a> </td>
                                     <td> {{ $item->dailyInput && $item->dailyInput->user ? $item->dailyInput->user->name : 'N/A' }}
                                    </td>
                                      <td>{{$item->pack}}</td>
                                       <td>{{$item->qty}}</td>
                                       {{-- <td>
                                        <a data-id="{{ $item->id }}" data-pack="{{ $item->pack }}" data-qty="{{ $item->qty }}" data-name="{{ $product->name }}" data-fnsku="{{ $item->fnsku }}" data-bs-toggle="modal" data-bs-target="#editmodal" class="d-flex btn btn-primary edit-item-btn me-1"><i class="ri-pencil-fill align-bottom me-2"></i> Edit</a>
                                       </td> --}}
                                </tr>
                            @endforeach
                            
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- end page title -->

</div>

@endsection

@section('script')
<script>
    window.addEventListener('load', function() {
        // Disable all inputs, selects, textareas, and buttons only inside #productEdit form
        const form = document.getElementById('productEdit');
        if (form) {
            const inputs = form.querySelectorAll('input, select, textarea, button');
            inputs.forEach(function(input) {
                input.disabled = true;
            });
        }
    });

</script>
@endsection