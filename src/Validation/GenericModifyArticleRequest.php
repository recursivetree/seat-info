<?php

namespace RecursiveTree\Seat\InfoPlugin\Validation;

use Illuminate\Foundation\Http\FormRequest;

class GenericModifyArticleRequest extends FormRequest
{

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id' => 'required|int',
        ];
    }
}