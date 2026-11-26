<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property array<string, array<string, string|null>> $translations
 * @property array<string, bool|string|null> $details
 */
class PortfolioProfile extends Model
{
    public const SINGLETON_ID = 1;

    protected $fillable = ['id', 'translations', 'details'];

    protected $attributes = [
        'translations' => '{}',
        'details' => '{}',
    ];

    protected function casts(): array
    {
        return ['translations' => 'array', 'details' => 'array'];
    }

    /** @return array<string, bool|string|null> */
    public function localized(): array
    {
        return array_replace(
            $this->details,
            $this->translations[app()->getLocale()] ?? [],
        );
    }

    public function resumePath(): ?string
    {
        $path = $this->details['resume_path'] ?? null;

        return is_string($path) ? $path : null;
    }

    public function resumeUrl(): ?string
    {
        if ($this->resumePath() !== null) {
            return route('portfolio.resume.download');
        }

        $url = $this->details['resume'] ?? null;

        return is_string($url) ? $url : null;
    }

    public function aboutPhotoPath(): ?string
    {
        $path = $this->details['about_photo_path'] ?? null;

        return is_string($path) ? $path : null;
    }

    public function aboutPhotoVisible(): bool
    {
        return ($this->details['about_photo_visible'] ?? true) === true;
    }
}
