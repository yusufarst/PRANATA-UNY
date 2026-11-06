<?php

namespace App\Modules\Files\Rules;

use App\Modules\Files\Support\UploadProfile;
use Closure;
use finfo;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

/**
 * An upload for a purpose (SECURITY_MODEL section 6, SEC-10, SEC-13): one allowed extension, no second extension before it,
 * bytes whose signature matches that extension, not empty and within the size of the purpose. The signature is read from the
 * bytes on disk, never from what the client declared.
 */
final class PrivateFileRule implements ValidationRule
{
    public function __construct(private readonly UploadProfile $profile) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof UploadedFile || ! $value->isValid()) {
            $fail('files.validation.invalid_upload')->translate();

            return;
        }

        $name = $value->getClientOriginalName();
        $extension = strtolower($value->getClientOriginalExtension());

        if ($name === '' || mb_strlen($name) > 255) {
            $fail('files.validation.name_too_long')->translate();

            return;
        }

        if ($extension === '' || ! $this->profile->allowsExtension($extension)) {
            $fail('files.validation.extension')->translate(['allowed' => implode(', ', $this->profile->extensions)]);

            return;
        }

        // laporan.pdf.exe or surat.php.pdf: a name with a second extension before the last one.
        $stem = mb_substr($name, 0, mb_strlen($name) - strlen($extension) - 1);

        if (preg_match('/\.[a-z0-9]{1,5}$/i', $stem) === 1) {
            $fail('files.validation.double_extension')->translate();

            return;
        }

        $path = $value->getRealPath();
        $size = $path === false ? false : filesize($path);

        if ($path === false || $size === false) {
            $fail('files.validation.invalid_upload')->translate();

            return;
        }

        if ($size === 0) {
            $fail('files.validation.empty')->translate();

            return;
        }

        if ($size > $this->profile->maxBytes) {
            $fail('files.validation.size')->translate(['max' => self::megabytes($this->profile->maxBytes)]);

            return;
        }

        $detected = self::detectMimeType($path);

        if ($detected === null || ! $this->profile->allowsMimeType($extension, $detected)) {
            $fail('files.validation.mime')->translate(['extension' => $extension]);
        }
    }

    /**
     * The MIME type of the bytes, as libmagic reads them.
     */
    public static function detectMimeType(string $path): ?string
    {
        $detected = (new finfo(FILEINFO_MIME_TYPE))->file($path);

        return is_string($detected) && $detected !== '' ? $detected : null;
    }

    public static function megabytes(int $bytes): string
    {
        $value = $bytes / (1024 * 1024);

        return rtrim(rtrim(number_format($value, 1, '.', ''), '0'), '.');
    }
}
