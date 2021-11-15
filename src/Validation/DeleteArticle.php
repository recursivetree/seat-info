<?php

namespace RecursiveTree\Seat\InfoPlugin\Validation;

use Illuminate\Foundation\Http\FormRequest;

class DeleteArticle extends FormRequest
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