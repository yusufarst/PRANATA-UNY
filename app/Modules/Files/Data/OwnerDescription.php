<?php

namespace App\Modules\Files\Data;

/**
 * The owner row of a file link as the evidence page (PG-012) shows it, with the parent it returns to (RT-031).
 */
final readonly class OwnerDescription
{
    public function __construct(
        public string $title,
        public ?string $subtitle,
        public string $parentUrl,
        public string $parentTitle,
    ) {}

    /**
     * @return array{title: string, subtitle: string|null, parent_url: string, parent_title: string}
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'parent_url' => $this->parentUrl,
            'parent_title' => $this->parentTitle,
        ];
    }
}
