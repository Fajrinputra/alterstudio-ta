<?php

namespace App\Support;

class ImageUploadValidation
{
    public const ACCEPT_ATTRIBUTE = '.jpg,.jpeg,.png,.webp,.gif,image/jpeg,image/png,image/webp,image/gif';

    private const ALLOWED_FORMATS = 'jpg,jpeg,png,webp,gif';

    public static function rules(bool $required = false, int $maxKb = 20480): array
    {
        return [
            $required ? 'required' : 'nullable',
            'file',
            'mimes:'.self::ALLOWED_FORMATS,
            'max:'.$maxKb,
        ];
    }

    public static function messages(array $fields, string $maxLabel = '20 MB'): array
    {
        $messages = [];

        foreach ($fields as $field) {
            $label = self::fieldLabel($field);
            $messages[$field.'.file'] = $label.' harus berupa file gambar.';
            $messages[$field.'.mimes'] = $label.' hanya boleh berformat JPG, JPEG, PNG, WEBP, atau GIF. Video tidak diperbolehkan.';
            $messages[$field.'.max'] = $label.' maksimal '.$maxLabel.'.';
        }

        return $messages;
    }

    private static function fieldLabel(string $field): string
    {
        return match ($field) {
            'avatar' => 'Foto profil',
            'image' => 'Foto hero',
            'overview_image' => 'Foto overview',
            'gallery.*', 'packages.*.gallery.*' => 'Foto galeri',
            'photos.*' => 'Foto lokasi',
            'photo' => 'Foto ruangan',
            'packages.*.overview_image' => 'Foto overview paket',
            default => 'File gambar',
        };
    }
}
