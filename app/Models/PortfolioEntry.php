<?php

namespace App\Models;

use App\Enums\PortfolioSection;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @property PortfolioSection $section
 * @property string|null $slug
 * @property int $position
 * @property bool $is_active
 * @property array<string, array<string, string|list<string>|null>> $translations
 * @property array<string, string|list<string>|null> $details
 */
class PortfolioEntry extends Model
{
    protected $fillable = ['section', 'slug', 'position', 'is_active', 'translations', 'details'];

    protected $attributes = [
        'position' => 0,
        'is_active' => true,
        'translations' => '{}',
        'details' => '{}',
    ];

    protected function casts(): array
    {
        return [
            'section' => PortfolioSection::class,
            'position' => 'integer',
            'is_active' => 'boolean',
            'translations' => 'array',
            'details' => 'array',
        ];
    }

    /** @param Builder<self> $query */
    #[Scope]
    protected function ordered(Builder $query): void
    {
        $query->orderBy('position')->orderBy('id');
    }

    /** @param Builder<self> $query */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /** @return array<string, string|list<string>|null> */
    public function localized(): array
    {
        return array_replace(
            $this->details,
            $this->translations[app()->getLocale()] ?? [],
        );
    }

    /** @return list<string> */
    public function imagePaths(): array
    {
        $paths = $this->details['image_paths'] ?? [];

        return is_array($paths) ? $paths : [];
    }

    /** @return list<string> */
    public function imageUrls(): array
    {
        return (array) ($this->details['images'] ?? []);
    }

    /** @return list<array{reference: string, url: string}> */
    public function galleryItems(): array
    {
        return [
            ...array_map(fn (string $path): array => ['reference' => $path, 'url' => asset('storage/'.$path)], $this->imagePaths()),
            ...array_map(fn (string $url): array => ['reference' => $url, 'url' => $url], $this->imageUrls()),
        ];
    }

    /** @return list<string> */
    public function galleryImages(): array
    {
        return array_column($this->galleryItems(), 'url');
    }
}
