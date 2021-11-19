<?php

namespace RecursiveTree\Seat\InfoPlugin\Validation;

use Illuminate\Foundation\Http\FormRequest;

class UploadResource extends FormRequest
{

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'file' => 'required|file',
        ];
    }
}