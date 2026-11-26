<?php

namespace App\Http\Controllers;

use App\Models\PortfolioProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class PortfolioPhotoController extends Controller
{
    public function show(): BinaryFileResponse
    {
        $profile = PortfolioProfile::findOrNew(PortfolioProfile::SINGLETON_ID);
        abort_unless($profile->aboutPhotoVisible(), 404);

        return $this->image($profile);
    }

    public function preview(): BinaryFileResponse
    {
        return $this->image(PortfolioProfile::findOrNew(PortfolioProfile::SINGLETON_ID));
    }

    public function update(Request $request): RedirectResponse
    {
        $input = $request->validate([
            'photo' => ['bail', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'extensions:jpg,jpeg,png,webp', 'max:5120', 'dimensions:min_width=1,min_height=1'],
            'visible' => ['required', 'boolean'],
        ], attributes: [
            'photo' => __('admin.about_photo_file'),
            'visible' => __('admin.visibility'),
        ]);

        /** @var UploadedFile|null $photo */
        $photo = $input['photo'] ?? null;
        $path = $photo?->store('portfolio/profile-photos', 'local');
        if ($path === false) {
            throw ValidationException::withMessages(['photo' => __('validation.uploaded', ['attribute' => __('admin.about_photo_file')])]);
        }

        $disk = Storage::disk('local');
        try {
            PortfolioProfile::firstOrCreate(['id' => PortfolioProfile::SINGLETON_ID]);
            $previous = $this->savePhoto($path, $request->boolean('visible'));
        } catch (Throwable $error) {
            if ($path !== null) {
                $disk->delete($path);
            }

            throw $error;
        }

        if ($previous !== null) {
            $disk->delete($previous);
        }

        return to_route('portfolio-admin.profile.edit')->with('status', __('admin.saved'));
    }

    private function savePhoto(?string $path, bool $visible): ?string
    {
        return DB::transaction(function () use ($path, $visible): ?string {
            $profile = PortfolioProfile::query()->lockForUpdate()->findOrFail(PortfolioProfile::SINGLETON_ID);
            $details = $profile->details;
            $previous = null;
            if ($path !== null) {
                $previous = $profile->aboutPhotoPath();
                $details['about_photo_path'] = $path;
            }
            $details['about_photo_visible'] = $visible;
            $profile->update(['details' => $details]);

            return $previous;
        }, 3);
    }

    private function image(PortfolioProfile $profile): BinaryFileResponse
    {
        $upload = $profile->aboutPhotoPath();
        $path = $upload === null ? resource_path('images/about-photo.webp') : Storage::disk('local')->path($upload);
        abort_unless(is_file($path), 404);

        return new BinaryFileResponse($path, headers: [
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ], public: false);
    }
}
