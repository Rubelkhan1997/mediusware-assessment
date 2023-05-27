@extends('layouts.app')

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Product</h1>
    </div>
     @if (Session::has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ Session::get('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    @if (Session::has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ Session::get('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    <form action="{{ route('product.store') }}" method="post" autocomplete="off" spellcheck="false" enctype="multipart/form-data">
        @csrf
        <section>
            <div class="row">
                <div class="col-md-6">
                    <!--                    Product-->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary">Product</h6>
                        </div>
                        <div class="card-body border">
                            <div class="form-group">
                                <label for="product_name">Product Name</label>
                                <input type="text" name="product_name" value="{{ $product->title }}" class="form-control @error('product_name') is-invalid @enderror" id="product_name" placeholder="Product Name">
                                @error('product_name')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="product_sku">Product SKU</label>
                                <input type="text" name="product_sku" value="{{ $product->sku }}"  class="form-control @error('product_sku') is-invalid @enderror" id="product_sku" placeholder="Product Name" class="form-control">
                                @error('product_sku')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                            <div class="form-group mb-0">
                                <label for="product_description">Description</label>
                                <textarea name="product_description" value="{{ $product->description }}" class="form-control @error('product_description') is-invalid @enderror" id="product_description" rows="4">{{ $product->description  }}</textarea>
                                @error('product_description')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <!--                    Media-->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between"><h6
                                class="m-0 font-weight-bold text-primary">Media</h6></div>
                        <div class="card-body border  @error('product_photo') border-danger @enderror">
                            <div id="file-upload" class="dropzone dz-clickable mb-2">
                                <div class="dz-default dz-message"><span>Drop files here to upload</span></div>
                            </div>
                            @error('product_photo')
                            <span style="color:red"><strong>{{ $message }}</strong></span>
                            @enderror
                            @foreach ((object) $product->photos as $p)
                                <img src="{{ url($p) }}" alt="Not Found" width="100" height="100">
                            @endforeach
                        </div>   
                        <input type="hidden" name="product_photo" value="{{ implode(' ', $product->photos) }}">    
                        <input type="hidden" name="product_id" value="{{ $product->id }}">    
                    </div>
                </div>
                <!--                Variants-->
                <div class="col-md-6">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Variants</h6>
                        </div>
                        <div class="card-body pb-0" id="variant-sections">
                            
                        </div>
                        <div class="card-footer bg-white border-top-0" id="add-btn">
                            <div class="row d-flex justify-content-center">
                                <button class="btn btn-primary add-btn" onclick="addVariant(event);">
                                    Add another option
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card shadow">
                        <div class="card-header text-uppercase">Preview</div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                    <tr class="text-center">
                                        <th width="33%">Variant</th>
                                        <th>Price</th>
                                        <th>Stock</th>
                                    </tr>
                                    </thead>
                                    <tbody id="variant-previews">
                                        @foreach ($product->variant_prices as $key => $price)
                                            <tr>
                                                <th>
                                                    <input type="hidden" name="product_preview[{{ $key }}][variant]" value="{{ $price->title }}">
                                                    <span class="font-weight-bold">{{ $price->title }}</span>
                                                </th>
                                                <td>
                                                    <input type="number" name="product_preview[{{ $key }}][price]" value="{{ number_format($price->price,2) }}" class="form-control" required>
                                                </td>
                                                <td>
                                                    <input type="number" name="product_preview[{{ $key }}][stock]" value="{{ $price->stock }}" class="form-control" required>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <button type="sumbit" class="btn btn-lg btn-primary">Save</button>
            <button type="button" class="btn btn-secondary btn-lg">Cancel</button>
        </section>
    </form>
@endsection

<input type="hidden" name="variant_selector" value="{{ json_encode($product->variants, true) }}">

@push('page_js')
    <script type="text/javascript" src="{{ asset('js/product.js') }}"></script>
    <script>
        $(function(){
            $('#variant-sections').html("");
            let variant_selector = $('input[name=variant_selector]').val();
            let index = 0;
            indexs = [];
            // Foreach
            $.each(JSON.parse(variant_selector) , function(variant_id, val) { 
                $("#variant-sections").append(`<div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="">Option</label>
                                                <select id="select2-option-${index}" data-index="${index}" name="product_variant[${index}][option]" class="form-control custom-select select2 select2-option" required>
                                                    <option value="1">
                                                        Color
                                                    </option>
                                                    <option value="2">
                                                        Size
                                                    </option>
                                                    <option value="6">
                                                        Style
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-8">
                                            <div class="form-group">
                                                <label class="d-flex justify-content-between">
                                                    <span>Value</span>
                                                    <a href="#" class="remove-btn" data-index="${index}" onclick="removeVariant(event, this);">Remove</a>
                                                </label>
                                                <select id="select2-value-${index}" data-index="${index}" name="product_variant[${index}][value][]" class="select2 select2-value form-control custom-select" multiple="multiple" required>
                                                </select>
                                            </div>
                                        </div>
                                    </div>`);

                
                $(`#select2-option-${index}`).select2({placeholder: "Select Option", theme: "bootstrap4"});
                $(`#select2-value-${index}`).select2({ 
                    tags: true, 
                    multiple: true,
                    placeholder: "Type tag name", 
                    allowClear: true, 
                    theme: "bootstrap4"
                }).on('change', function () {
                    updateVariantPreview(1);
                });

                $(`#select2-option-${index}`).val(variant_id).trigger('change')    
                $.each(val, function(i, v) { 
                    $(`#select2-value-${index}`).append(`<option value='${v}'}>${v}</option>`);   
                });
                $(`#select2-value-${index}`).val(val).trigger('change')   

                
                indexs.push(index);
                currentIndex = (index +1);
                if (indexs.length >= 3) {
                    $("#add-btn").hide();
                } else {
                    $("#add-btn").show();
                }
                index++;
            });
        });
    </script>
@endpush
