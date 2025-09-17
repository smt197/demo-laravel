<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'email',
                Rule::unique('users', 'email')->ignore($this->route('user'))
            ],
            'password' => ['sometimes', Password::defaults()],
        ];
    }

    public function messages(): array
    {
        return [
            'name.max' => 'Name must not exceed 255 characters',
            'email.email' => 'Email must be a valid email address',
            'email.unique' => 'Email is already registered',
        ];
    }
}