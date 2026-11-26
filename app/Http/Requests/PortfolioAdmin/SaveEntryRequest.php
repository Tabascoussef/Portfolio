<?php

namespace App\Http\Requests\PortfolioAdmin;

use App\Enums\PortfolioLocale;
use App\Enums\PortfolioSection;
use App\Models\PortfolioEntry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveEntryRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $section = $this->section();
        $translations = $this->input('translations');
        if (is_array($translations)) {
            foreach (PortfolioLocale::cases() as $locale) {
                $fields = $translations[$locale->value] ?? null;
                if (is_array($fields)) {
                    $translations[$locale->value] = $this->parseLists($fields, $section->translatedFields());
                }
            }
            $this->merge(['translations' => $translations]);
        }

        $details = $this->input('details');
        if (is_array($details)) {
            $domain = $details['live'] ?? null;
            if ($section === PortfolioSection::Projects && is_string($domain) && $domain !== '' && ! str_contains($domain, '://')) {
                $details['live'] = 'https://'.$domain;
            }
            $this->merge(['details' => $this->parseLists($details, $section->sharedFields())]);
        }
    }

    /** @return array<string, list<mixed>> */
    public function rules(): array
    {
        $section = $this->section();
        $rules = [
            'position' => ['required', 'integer', 'between:0,1000000'],
            'is_active' => ['sometimes', 'required', 'boolean'],
        ];
        $entry = $this->route('entry');

        if ($section === PortfolioSection::Projects) {
            $unique = Rule::unique(PortfolioEntry::class, 'slug');
            if ($entry instanceof PortfolioEntry) {
                $unique->ignore($entry);
            }

            $rules['slug'] = ['required', 'string', 'max:100', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/D', $unique];
            $rules['image_uploads'] = ['sometimes', 'array', 'list', 'max:10'];
            $rules['image_uploads.*'] = ['bail', 'required', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120', 'dimensions:min_width=1,min_height=1'];
            $rules['remove_images'] = ['sometimes', 'array', 'list'];
            $rules['remove_images.*'] = ['required', 'string', 'max:2048'];
        }

        foreach (PortfolioLocale::cases() as $locale) {
            foreach ($section->translatedFields() as $field => $type) {
                $key = "translations.{$locale->value}.{$field}";
                $rules[$key] = $this->fieldRules($type, $field === 'name');
                if ($type === 'lines') {
                    $rules[$key.'.*'] = ['required', 'string', 'max:500'];
                }
            }
        }

        foreach ($section->sharedFields() as $field => $type) {
            $key = "details.{$field}";
            $rules[$key] = $this->fieldRules($type);
            if ($type === 'lines') {
                $rules[$key.'.*'] = ['required', 'string', 'max:100'];
            }
            if ($type === 'urls') {
                $rules[$key.'.*'] = ['required', 'url:http,https', 'max:2048'];
            }
        }

        return $rules;
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        $labels = ['slug' => __('admin.field.slug'), 'position' => __('admin.field.position')];
        $labels['is_active'] = __('admin.visibility');
        $labels['image_uploads'] = $labels['image_uploads.*'] = __('admin.field.image_uploads');
        $labels['remove_images'] = $labels['remove_images.*'] = __('admin.remove_image');
        foreach (PortfolioLocale::cases() as $locale) {
            foreach ($this->section()->translatedFields() as $field => $type) {
                $key = "translations.{$locale->value}.{$field}";
                $labels[$key] = $labels[$key.'.*'] = __('admin.field.'.$field).' ('.$locale->label().')';
            }
        }
        foreach ($this->section()->sharedFields() as $field => $type) {
            $labels["details.{$field}"] = $labels["details.{$field}.*"] = __('admin.field.'.$field);
        }

        return $labels;
    }

    private function section(): PortfolioSection
    {
        /** @var PortfolioSection $section */
        $section = $this->route('section');

        return $section;
    }

    /** @return list<string> */
    private function fieldRules(string $type, bool $required = false): array
    {
        return match ($type) {
            'lines', 'urls' => ['nullable', 'array', 'list', 'max:30'],
            'url', 'domain' => ['nullable', 'url:http,https', 'max:2048'],
            'year' => ['nullable', 'digits:4'],
            'textarea' => [$required ? 'required' : 'nullable', 'string', 'max:10000'],
            default => [$required ? 'required' : 'nullable', 'string', 'max:255'],
        };
    }

    /**
     * @param  array<string, mixed>  $values
     * @param  array<string, string>  $fields
     * @return array<string, mixed>
     */
    private function parseLists(array $values, array $fields): array
    {
        foreach ($fields as $field => $type) {
            $value = $values[$field] ?? null;
            if (in_array($type, ['lines', 'urls'], true) && is_string($value)) {
                $lines = array_map('trim', preg_split('/\R/u', $value) ?: []);
                $values[$field] = array_values(array_filter($lines, fn (string $line): bool => $line !== ''));
            }
        }

        return $values;
    }
}
