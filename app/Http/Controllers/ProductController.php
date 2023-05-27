<?php

namespace App\Http\Controllers;

use App\Models\Variant;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\ProductVariantPrice;
use Illuminate\Http\Request;
use App\Http\Requests\ProductRequest;
use Illuminate\Support\Str;
use Exception;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $variants = Variant::with('product_variants')->selectRaw('id, title')->get();
        $query    = Product::with('photos', 'variants', 'variant_prices');
        // Filter
        if($request->title){
            $query->where('title', 'like',"%$request->title%");
        }
        if($request->date){
            $query->whereDate('created_at', $request->date);
        }
        if($request->variant){
            $variant = $request->variant;
            $query->whereHas('variants',  function($q) use($variant){
                $q->where('variant', 'like', "%$variant%");
            });
        }
        if(is_numeric($request->price_from) && is_numeric($request->price_to)){
            $from_price = $request->price_from;
            $price_to   = $request->price_to;
            // 
            $query->whereHas('variant_prices',  function($q) use($from_price, $price_to){
                $q->whereBetween('price', [$from_price, $price_to]);
            });
        }
        $products = $query->orderBy('id', 'DESC')->paginate(30);
        // View 
        return view('products.index', ['products' => $products, 'variants' => $variants]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function create()
    {
        $variants = Variant::all();
        return view('products.create', compact('variants'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(ProductRequest $request)
    {
        $input      = $request->input();
        $product_id = isset($input['product_id'])? $input['product_id'] : 0;
        // return $input;
        
        if($product_id == 0 && $input['product_photo'] == null){
            return redirect()->back()->with('error', 'The Media field is required.');
        }
        // Product entry
        if($product_id){
            $product = Product::find($input['product_id']);
        }else{
            $product = new Product(); 
        }
        $product->title       = $input['product_name'];
        $product->sku         = $input['product_sku'];
        $product->description = $input['product_description'];
        $product->save();
        // Product photo
        $photos = explode(',', $input['product_photo']);
        $image_ids = [];
        foreach((array) $photos as $photo){
            $image = ProductImage::where(['product_id' => $product->id, 'file_path' => $photo])->first();
            if(empty($image)){
                $image              = new ProductImage();
                $image->product_id  = $product->id;
                $image->file_path   = $photo;
                $image->save();
            }
            $image_ids[] = $image->id;
        }
        ProductImage::where('product_id', $product->id)->whereNotIn('id', $image_ids)->first();
        ProductVariant::where('product_id', $product->id)->delete();
        $c_variant = [];
        // Product variant
        foreach($input['product_variant'] as $variant){
            $value_ids = [];
            foreach($variant['value'] as $key => $v){
                $p_variant             = new ProductVariant();
                $p_variant->product_id = $product->id;   
                $p_variant->variant_id = $variant['option'];
                $p_variant->variant    = $v;
                $p_variant->save();
                $value_ids[] = $p_variant->id;
            }
            $c_variant[] = [
                'option' => $variant['option'],
                'value' => $value_ids,
            ];
        }
        $combinations  =  $this->cartesian($c_variant);
        $product_price = []; 
        // Generate product previews
        foreach ($combinations as  $key => $combination) {
            $product_price[] = [
                "variant" => array_values($combination),
                "price"   => $input['product_preview'][$key]['price'],
                "stock"   => $input['product_preview'][$key]['stock']
            ];
        }
        ProductVariantPrice::where('product_id', $product->id)->delete();
        // Product price
        foreach($product_price as $p){
            $p_price                        = new ProductVariantPrice();
            $p_price->product_variant_one   = isset($p['variant'][0])? $p['variant'][0]: null;
            $p_price->product_variant_two   = isset($p['variant'][1])? $p['variant'][1]: null;
            $p_price->product_variant_three = isset($p['variant'][2])? $p['variant'][2]: null;
            $p_price->price = $p['price'];   
            $p_price->stock = $p['stock'];   
            $p_price->product_id = $product->id;   
            $p_price->save();
        }
        $message = $product_id? 'updated' : 'created';

        return redirect()->back()->with('success', "Product $message successfully.");
    }


    /**
     * Display the specified resource.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function show($product)
    {

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        try{
            $variants = Variant::all();
            $product  = Product::with('variants', 'variant_prices')->where('id', $id)->first();
            $product->photos = ProductImage::where('product_id', $id)->pluck('file_path', 'id')->toArray();
            // Variant prices
            $variant= [];
            $variants= [];
            foreach ($product->variants as $v) {
                $variant[$v->id] = $v->variant; 
                $variants[$v->variant_id][] = $v->variant; 
            }
            $variant_prices = [];
            foreach ((object) $product->variant_prices as $pri){
                $v_title  = isset($variant[$pri->product_variant_one])? $variant[$pri->product_variant_one] : null;
                $v_title .= isset($variant[$pri->product_variant_two])? '/'.$variant[$pri->product_variant_two] : null;
                $v_title .= isset($variant[$pri->product_variant_three])? '/'.$variant[$pri->product_variant_three] : null;
                
                $variant_prices[] = (object) [
                    'title' => $v_title,
                    'price' => $pri->price,
                    'stock' => $pri->stock,
                ];
            }
            $product->variant_prices = $variant_prices;
            $product->variants = $variants;


            // return $product;
    
            return view('products.edit', compact('variants', 'product'));
        }catch(Exception $ex){
            abort(404);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        //
    }

    public function fileUpdate(Request $request)
    {
        $image = $request->file('file');
        $image_name = Str::slug($image->getClientOriginalName()); 
        $request->file->move(public_path('uploads/images'), $image_name);
        $path = 'uploads/images/'.$image_name;

        return response()->json(['path' => $path]);
    } 
    public function cartesian($input)
    {
        $result = [[]];
        foreach ($input as $key => $values) {
            $append = [];
            foreach($result as $product) {
                foreach($values['value'] as $value) {
                    $product[$values['option']] = $value;
                    $append[] = $product;
                }
            }
            $result = $append;
        }
        return $result;
    }
}
