<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait HasPublicCode
{
    protected static function bootHasPublicCode(): void
    {
        static::creating(function (Model $model) {
            $column = $model->publicCodeColumn();

            if (! filled($model->{$column})) {
                $model->{$column} = $model->makePublicCode();
            }
        });
    }

    protected function makePublicCode(): string
    {
        $column = $this->publicCodeColumn();
        $prefix = $this->publicCodePrefix();

        do {
            $code = $prefix.'-'.Str::upper(Str::random(8));
        } while (static::query()->where($column, $code)->exists());

        return $code;
    }

    abstract protected function publicCodeColumn(): string;

    abstract protected function publicCodePrefix(): string;
}
