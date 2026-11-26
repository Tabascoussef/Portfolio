<?php

namespace App\Http\Controllers\PortfolioAdmin;

use App\Models\PortfolioProfile;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class ResumeController
{
    public function edit(): View
    {
        $profile = PortfolioProfile::findOrNew(PortfolioProfile::SINGLETON_ID);

        return view('portfolio-admin.resume', ['resumeUrl' => $profile->resumeUrl()]);
    }

    public function update(Request $request): RedirectResponse
    {
        /** @var UploadedFile $file */
        $file = $request->validate([
            'resume' => ['required', 'file', 'mimes:pdf,doc,docx', 'extensions:pdf,doc,docx', 'max:10240'],
        ], attributes: ['resume' => __('admin.resume_file')])['resume'];

        $disk = Storage::disk('local');
        $path = $file->store('portfolio/resumes', 'local');
        if ($path === false) {
            throw ValidationException::withMessages(['resume' => __('validation.uploaded', ['attribute' => __('admin.resume_file')])]);
        }

        try {
            PortfolioProfile::firstOrCreate(['id' => PortfolioProfile::SINGLETON_ID]);
            $previous = $this->replaceResume($path);
        } catch (Throwable $error) {
            $disk->delete($path);

            throw $error;
        }

        if ($previous !== null) {
            $disk->delete($previous);
        }

        return to_route('portfolio-admin.resume.edit')->with('status', __('admin.saved'));
    }

    public function destroy(): RedirectResponse
    {
        $previous = $this->replaceResume(null);
        if ($previous !== null) {
            Storage::disk('local')->delete($previous);
        }

        return to_route('portfolio-admin.resume.edit')->with('status', __('admin.deleted'));
    }

    private function replaceResume(?string $path): ?string
    {
        return DB::transaction(function () use ($path): ?string {
            $profile = PortfolioProfile::query()->lockForUpdate()->find(PortfolioProfile::SINGLETON_ID);
            if ($profile === null) {
                return null;
            }

            $previous = $profile->resumePath();
            $details = Arr::except($profile->details, ['resume', 'resume_path']);
            if ($path !== null) {
                $details['resume_path'] = $path;
            }
            $profile->update(['details' => $details]);

            return $previous;
        }, 3);
    }
}
