<?php

namespace App\Http\Controllers\PortfolioAdmin;

use App\Http\Requests\PortfolioAdmin\SaveProfileRequest;
use App\Models\PortfolioProfile;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class ProfileController
{
    public function edit(): View
    {
        return view('portfolio-admin.profile', [
            'profile' => PortfolioProfile::findOrNew(PortfolioProfile::SINGLETON_ID),
            'fields' => config('portfolio.profile'),
        ]);
    }

    public function update(SaveProfileRequest $request): RedirectResponse
    {
        $attributes = $request->validated();
        $profile = PortfolioProfile::firstOrCreate(['id' => PortfolioProfile::SINGLETON_ID]);

        DB::transaction(function () use ($profile, $attributes): void {
            $lockedProfile = PortfolioProfile::query()->lockForUpdate()->findOrFail($profile->getKey());
            $attributes['details'] = array_replace($lockedProfile->details, $attributes['details']);
            $lockedProfile->update($attributes);
        }, 3);

        return to_route('portfolio-admin.profile.edit')->with('status', __('admin.saved'));
    }
}
