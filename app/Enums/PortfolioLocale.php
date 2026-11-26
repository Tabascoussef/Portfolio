<?php

namespace App\Enums;

enum PortfolioLocale: string
{
    case Uzbek = 'uz';
    case English = 'en';
    case Russian = 'ru';

    public function label(): string
    {
        return match ($this) {
            self::Uzbek => 'O‘zbekcha',
            self::English => 'English',
            self::Russian => 'Русский',
        };
    }

    public function publicUrl(?string $slug = null): string
    {
        $localized = $this === self::Uzbek ? '' : $this->value.'.';

        return $slug === null
            ? route($localized.'home')
            : route($localized.'work.show', ['slug' => $slug]);
    }
}
