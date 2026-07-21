<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Support\ImageUploadValidation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validasi update profil user.
 */
class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Email tetap unik kecuali milik user yang sedang login.
        return [
            'name' => ['required', 'string', 'max:50'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:50',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'no_hp' => ['nullable', 'string', 'max:20', 'regex:/^(?:\+62|62|0)[0-9]{9,13}$/'],
            'avatar' => ImageUploadValidation::rules(maxKb: 2048),
        ];
    }

    /**
     * Pesan khusus untuk field profil yang sering membuat user bingung.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'no_hp.regex' => 'Nomor HP harus menggunakan format Indonesia, misalnya 081234567890 atau +6281234567890.',
            ...ImageUploadValidation::messages(['avatar'], '2 MB'),
        ];
    }
}
