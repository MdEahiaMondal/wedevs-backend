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
        $id = isset($this->product) ? $this->product->id : null;
        $rules = [
            'title' => 'required|max:255|unique:products,title,' . $id,
            'description' => 'nullable|string',
            'price' => 'required|numeric|between:0,9999.99',
        ];
        if (request()->isMethod('post')) {
            $rules['photo'] = 'required|image';
        }
        if (request()->isMethod('put') || request()->isMethod('patch')) {
            $rules['photo'] = 'nullable|image';
        }
        return $rules;
    }
}
