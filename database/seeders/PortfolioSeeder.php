<?php

namespace Database\Seeders;

use App\Models\PortfolioEntry;
use App\Models\PortfolioProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class PortfolioSeeder extends Seeder
{
    public function run(): void
    {
        /** @var array{profile: array<string, mixed>, entries: list<array<string, mixed>>} $content */
        $content = json_decode(File::get(__DIR__.'/data/portfolio.json'), true, 512, JSON_THROW_ON_ERROR);

        DB::transaction(function () use ($content): void {
            $profile = PortfolioProfile::firstOrCreate(['id' => PortfolioProfile::SINGLETON_ID], $content['profile']);

            if ($profile->wasRecentlyCreated) {
                foreach ($content['entries'] as $entry) {
                    PortfolioEntry::create($entry);
                }
            }
        });
    }
}
