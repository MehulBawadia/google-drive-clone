<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class ShareFilesRequest extends FileActionsRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'email' => 'required|email',
            'ids.*' => Rule::exists('files', 'id')
                ->where(function ($query) {
                    $query->where('created_by', auth()->id());
                }),
        ]);
    }
}
