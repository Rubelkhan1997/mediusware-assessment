@extends('layouts.app')

@section('content')

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Products</h1>
    </div>
    <div class="card">
        <form action="" method="get" class="card-header">
            <div class="form-row justify-content-between">
                <div class="col-md-2">
                    <input type="text" name="title" value="{{ Request::get('title') }}" placeholder="Product Title" class="form-control">
                </div>
                <div class="col-md-2">
                    <select name="variant" class="form-control">
                        <option disabled selected>-- Select A Variant --</option>
                        @foreach ($variants as $variant)
                        <optgroup label="{{ $variant->title }}">
                            @foreach ($variant->product_variants as $p_varient)
                                <option {{ Request::get('variant') == $p_varient->variant? 'selected' : '' }} value="{{ $p_varient->variant }}">
                                    {{ $p_varient->variant }}
                                </option>
                            @endforeach
                        </optgroup>
                        @endforeach 
                    </select>
                </div>

                <div class="col-md-3">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">Price Range</span>
                        </div>
                        <input type="text" name="price_from" value="{{ Request::get('price_from') }}" aria-label="First name" placeholder="From" class="form-control">
                        <input type="text" name="price_to"   value="{{ Request::get('price_to') }}"   aria-label="Last name" placeholder="To" class="form-control">
                    </div>
                </div>
                <div class="col-md-2">
                    <input type="date" name="date" value="{{ Request::get('date') }}" placeholder="Date" class="form-control">
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary float-left"><i class="fa fa-search"></i></button>
                    {{-- <a href="{{ url('product') }}" class="btn-info float-right">Clear</a> --}}
                </div>
            </div>
        </form>

        <div class="card-body">
            <div class="table-response">
                <table class="table">
                    <thead>
                    <tr>
                        <th style="width: 5%">#</th>
                        <th style="width: 20%">Title</th>
                        <th style="width: 20%">Description</th>
                        <th style="width: 45%">Variant</th>
                        <th style="width: 10%">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                        @foreach ($products as $p)
                             @php
                                foreach ($p->variants as $v) {
                                    $variant[$v->id] = $v->variant; 
                                }
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $p->title }} <br> Created at : {{ date('d-M-Y', strtotime($p->created_at)) }}</td>
                                <td>{{ Str::limit($p->description, 100, '...') }}</td>
                                <td>
                                    <dl class="row mb-0" style="height: 80px; overflow: hidden" id="variant{{ $p->id }}">
                                        @foreach ($p->variant_prices as $pri)
                                        @php
                                            $v_title  = isset($variant[$pri->product_variant_one])? $variant[$pri->product_variant_one] : null;
                                            $v_title .= isset($variant[$pri->product_variant_two])? '/'.$variant[$pri->product_variant_two] : null;
                                            $v_title .= isset($variant[$pri->product_variant_three])? '/'.$variant[$pri->product_variant_three] : null;   
                                        @endphp
                                        <dt class="col-sm-3 pb-0">
                                            {{ $v_title }} 
                                       </dt>
                                        <dd class="col-sm-9">
                                            <dl class="row mb-0">
                                                <dt class="col-sm-4 pb-0">Price : {{ number_format($pri->price,2) }}</dt>
                                                <dd class="col-sm-8 pb-0">InStock : {{ $pri->stock }}</dd>
                                            </dl>
                                        </dd>
                                         @endforeach
                                    </dl>
                                    <button onclick="$('#variant{{ $p->id }}').toggleClass('h-auto')" class="btn btn-sm btn-link">Show more</button>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('product.edit',  $p->id) }}" class="btn btn-success">Edit</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            <div class="row justify-content-between">
                <div class="col-md-6">
                    <p>Showing {{ $products->firstItem() }} to {{ $products->lastItem() }} out of {{ $products->total() }}</p>
                </div>
                <div class="col-md-2">
                    {{ $products->links() }}
                </div>
            </div>
        </div>
    </div>

@endsection
