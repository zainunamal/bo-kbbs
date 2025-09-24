<?php

namespace App\Http\Requests\SnapQris;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;
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
            'grantType' => 'required|in:client_credentials',
            'additionalInfo' => 'nullable|array',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        $response = response()->json([
            'responseCode' => '4007302',
            'responseMessage' => 'Invalid Mandatory Field ' . $validator->errors()->first()
        ], 400);

        throw new HttpResponseException($response);
    }
}
