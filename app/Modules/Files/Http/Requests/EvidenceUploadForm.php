<?php

namespace App\Modules\Files\Http\Requests;

use App\Modules\Files\Providers\FilesServiceProvider;
use App\Modules\Files\Rules\PrivateFileRule;
use App\Modules\Files\Support\FileOwnerTypes;
use App\Modules\Files\Support\UploadProfile;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

/**
 * An upload for one owner row (FileDropzone): the purpose the owner allows, the file validated for that purpose and an
 * optional label. Any fingerprint or size the client sends is not a field here and is never read.
 */
class EvidenceUploadForm extends FormRequest
{
    public function authorize(): bool
    {
        // A kind of row no module registered is not found, like its page.
        abort_if(app(FileOwnerTypes::class)->find($this->ownerType()) === null, 404);

        return Gate::allows(FilesServiceProvider::MANAGE_EVIDENCE, [$this->ownerType(), $this->ownerId()]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $purposes = app(FileOwnerTypes::class)->find($this->ownerType())?->purposes() ?? [];
        $purpose = $this->input('purpose');
        $profile = UploadProfile::for(is_string($purpose) && in_array($purpose, $purposes, true) ? $purpose : ($purposes[0] ?? 'DEFAULT'));

        return [
            'purpose' => ['required', 'string', Rule::in($purposes)],
            'file' => ['required', 'file', new PrivateFileRule($profile)],
            'label' => ['nullable', 'string', 'max:200'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        $attributes = [];

        foreach (['purpose', 'file', 'label'] as $field) {
            $text = __("files.fields.{$field}");
            $attributes[$field] = is_string($text) ? $text : $field;
        }

        return $attributes;
    }

    public function ownerType(): string
    {
        $type = $this->route('type');

        return is_string($type) ? $type : '';
    }

    public function ownerId(): int
    {
        $id = $this->route('id');

        return is_numeric($id) ? (int) $id : 0;
    }
}
