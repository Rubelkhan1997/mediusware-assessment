<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
       return [
            'product_name'        => 'required',
            'product_sku'         => 'required|unique:products,sku,'.$this->product_id,
            'product_variant'     => 'required|array',
            // 'product_photo'       => 'required|string',
            'product_description' => 'required',
            'product_id'          => 'nullable|exists:products,id',
        ];
    }
    public function messages()
    {
       return [ 
            'product_variant.required'  => 'The Variants field is required.'
        ];
    }
}
