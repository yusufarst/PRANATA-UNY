<?php

namespace App\Modules\Files\Support;

/**
 * What an upload for a purpose may be (config/files.php, SECURITY_MODEL section 6): the extensions, the MIME types the bytes
 * of each may carry, and the size limit.
 */
final readonly class UploadProfile
{
    /**
     * @param  list<string>  $extensions
     * @param  array<string, list<string>>  $mimeTypes  extension => the MIME types its bytes may carry
     */
    public function __construct(public string $purpose, public array $extensions, public array $mimeTypes, public int $maxBytes) {}

    public static function for(string $purpose): self
    {
        $known = (array) config('files.extensions', []);
        $setting = (array) (config("files.purposes.{$purpose}") ?? []);
        $extensions = array_values(array_filter(
            array_map(strval(...), (array) ($setting['extensions'] ?? array_keys($known))),
            fn (string $extension): bool => array_key_exists($extension, $known),
        ));
        $mimeTypes = [];

        foreach ($extensions as $extension) {
            $mimeTypes[$extension] = array_values(array_map(strval(...), (array) $known[$extension]));
        }

        return new self($purpose, $extensions, $mimeTypes, (int) ($setting['max_bytes'] ?? config('files.max_bytes', 20 * 1024 * 1024)));
    }

    public function allowsExtension(string $extension): bool
    {
        return in_array(strtolower($extension), $this->extensions, true);
    }

    public function allowsMimeType(string $extension, string $mimeType): bool
    {
        return in_array($mimeType, $this->mimeTypes[strtolower($extension)] ?? [], true);
    }

    /**
     * @return array{purpose: string, extensions: list<string>, max_bytes: int}
     */
    public function toArray(): array
    {
        return ['purpose' => $this->purpose, 'extensions' => $this->extensions, 'max_bytes' => $this->maxBytes];
    }
}
