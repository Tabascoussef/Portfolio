<?php

namespace App\Enums;

enum PortfolioSection: string
{
    case Projects = 'projects';
    case Skills = 'skills';
    case Journey = 'journey';
    case Principles = 'principles';

    /** @return array<string, string> */
    public function translatedFields(): array
    {
        return match ($this) {
            self::Projects => [
                'name' => 'text', 'about' => 'textarea', 'category' => 'text', 'role' => 'text',
                'problem' => 'textarea', 'solution' => 'textarea',
                'architecture' => 'lines', 'challenges' => 'lines', 'outcomes' => 'lines',
            ],
            self::Skills => ['name' => 'text', 'established' => 'lines', 'learning' => 'lines'],
            self::Journey => ['name' => 'text', 'when' => 'text', 'text' => 'textarea'],
            self::Principles => ['name' => 'text'],
        };
    }

    /** @return array<string, string> */
    public function sharedFields(): array
    {
        return match ($this) {
            self::Projects => ['year' => 'year', 'github' => 'url', 'live' => 'domain', 'stack' => 'lines', 'images' => 'urls'],
            default => [],
        };
    }
}
