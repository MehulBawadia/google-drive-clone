<?php

namespace App\Http\Requests;

use App\Models\File;
use Illuminate\Validation\Rule;

class CreateFolderRequest extends ParentIdBaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'name' => [
                'required',
                Rule::unique(File::class, 'name')
                    ->where('created_by', auth()->id())
                    ->where('parent_id', $this->parent_id)
                    ->whereNull('deleted_at'),
            ],
        ]);
    }

    /**
     * Custom error messages.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'name.unique' => 'Folder ":input" already exists',
        ];
    }
}
