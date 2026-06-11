<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StockRequest extends FormRequest
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
            'entity' => 'numeric|required',
            'description' => 'nullable',
            'parentCategory_id' => 'required|numeric',
            'childCategory_id' => 'nullable|numeric',
            'brand_id' => 'required|numeric',
            'organization_id' => 'required|numeric',
            'store_id' => 'nullable|numeric',
            'employee_id' => 'nullable|numeric',
            'inputDate' => 'required',
            'isActive' => 'nullable',
        ];
    }
}
