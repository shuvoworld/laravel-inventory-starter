<?php

namespace App\Modules\Reports\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReportsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('reports.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
        ];
    }
}

class UpdateReportsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('reports.edit') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
        ];
    }
}
