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
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'title' => 'min:4|required',
            //'entity' => 'numeric|required',
            'description' => 'nullable',
            'parentCategory_id' => 'required|numeric',
            'childCategory_id' => 'nullable|numeric',
            'brand_id' => 'required|numeric',
            'organization_id' => 'required',
            'organization_id.*' => 'numeric',
            'store_id' => 'nullable',
            'store_id.*' => 'numeric',
            'base_unit_id' => 'nullable|numeric',
            'secondary_unit_id' => 'nullable|numeric',
            'product_type' => 'nullable|string',
            'stock_tracking_mode' => 'nullable|string',
            'valuation_method' => 'nullable|string',
            'isActive' => 'nullable',
        ];
    }
}
