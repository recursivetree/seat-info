<?php

namespace RecursiveTree\Seat\InfoPlugin\Validation;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmModalRequest extends FormRequest
{

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'data' => '',
        ];
    }
}