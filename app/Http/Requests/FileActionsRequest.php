<?php

namespace App\Http\Requests;

use App\Models\File;
use Illuminate\Validation\Rule;

class FileActionsRequest extends ParentIdBaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'all' => 'nullable|bool',
            'ids.*' => [
                Rule::exists('files', 'id'),
                function ($attribute, $value, $fail) {
                    $file = File::query()
                        ->leftJoin('file_shares', 'file_shares.file_id', 'files.id')
                        ->where('files.id', $value)
                        ->where(function ($query) {
                            $query->where('files.created_by', auth()->id())
                                ->orWhere('file_shares.user_id', auth()->id());
                        })->exists();

                    if (! $file) {
                        $fail('Invalid id: '.$value);
                    }
                },
            ],
        ]);
    }
}
