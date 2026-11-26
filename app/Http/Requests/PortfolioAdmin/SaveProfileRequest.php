<?php

namespace App\Http\Requests\PortfolioAdmin;

use App\Enums\PortfolioLocale;
use Illuminate\Foundation\Http\FormRequest;

class SaveProfileRequest extends FormRequest
{
    /** @return array<string, list<string>> */
    public function rules(): array
    {
        $rules = [];
        foreach (PortfolioLocale::cases() as $locale) {
            foreach (config('portfolio.profile.translations') as $field => $type) {
                $rules["translations.{$locale->value}.{$field}"] = [
                    'required', 'string', $type === 'textarea' ? 'max:3000' : 'max:150',
                ];
            }
        }

        foreach (config('portfolio.profile.details') as $field => $type) {
            $rules["details.{$field}"] = match ($type) {
                'email' => ['nullable', 'email', 'max:254'],
                'url' => ['nullable', 'url:http,https', 'max:2048'],
                'timezone' => ['required', 'timezone:all'],
                default => ['required', 'string', 'max:150'],
            };
        }

        return $rules;
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        $labels = [];
        foreach (PortfolioLocale::cases() as $locale) {
            foreach (config('portfolio.profile.translations') as $field => $type) {
                $labels["translations.{$locale->value}.{$field}"] = __('admin.field.'.$field).' ('.$locale->label().')';
            }
        }
        foreach (config('portfolio.profile.details') as $field => $type) {
            $labels["details.{$field}"] = __('admin.field.'.$field);
        }

        return $labels;
    }
}
