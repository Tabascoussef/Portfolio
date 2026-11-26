<?php

namespace App\Http\Controllers\PortfolioAdmin;

use App\Enums\PortfolioSection;
use App\Http\Requests\PortfolioAdmin\SaveEntryRequest;
use App\Models\PortfolioEntry;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class EntryController
{
    public function index(PortfolioSection $section): View
    {
        return view('portfolio-admin.entries', [
            'section' => $section,
            'entries' => PortfolioEntry::query()->where('section', $section)->ordered()->simplePaginate(25),
        ]);
    }

    public function create(PortfolioSection $section): View
    {
        return $this->form($section, new PortfolioEntry([
            'position' => (int) PortfolioEntry::query()->where('section', $section)->max('position') + 10,
        ]));
    }

    public function store(SaveEntryRequest $request, PortfolioSection $section): RedirectResponse
    {
        $this->save($request, new PortfolioEntry(['section' => $section]));

        return $this->indexRedirect($section, __('admin.saved'));
    }

    public function edit(PortfolioSection $section, PortfolioEntry $entry): View
    {
        abort_unless($section === $entry->section, 404);

        return $this->form($section, $entry);
    }

    public function update(SaveEntryRequest $request, PortfolioSection $section, PortfolioEntry $entry): RedirectResponse
    {
        abort_unless($section === $entry->section, 404);

        $this->save($request, $entry);

        return $this->indexRedirect($section, __('admin.saved'));
    }

    public function updateVisibility(Request $request, PortfolioSection $section, PortfolioEntry $entry): RedirectResponse
    {
        abort_unless($section === $entry->section, 404);

        $entry->update($request->validate(['is_active' => ['required', 'boolean']], attributes: ['is_active' => __('admin.visibility')]));

        return $this->indexRedirect($section, __('admin.saved'), $request->integer('page', 1));
    }

    public function destroyImage(Request $request, PortfolioEntry $entry): RedirectResponse
    {
        abort_unless($entry->section === PortfolioSection::Projects, 404);

        $reference = $request->validate(['image' => ['required', 'string', 'max:2048']], attributes: ['image' => __('admin.remove_image')])['image'];

        $removed = DB::transaction(function () use ($entry, $reference): array {
            $project = PortfolioEntry::query()->lockForUpdate()->findOrFail($entry->getKey());

            return $this->saveGallery($project, [], [], [$reference]);
        }, 3);

        Storage::disk('public')->delete($removed);

        return to_route('portfolio-admin.entries.edit', ['section' => PortfolioSection::Projects, 'entry' => $entry])
            ->with('status', __('admin.image_deleted'));
    }

    public function destroy(PortfolioSection $section, PortfolioEntry $entry): RedirectResponse
    {
        abort_unless($section === $entry->section, 404);

        $paths = DB::transaction(function () use ($entry): array {
            $entry = PortfolioEntry::query()->lockForUpdate()->findOrFail($entry->getKey());
            $entry->delete();

            return $entry->imagePaths();
        }, 3);

        Storage::disk('public')->delete($paths);

        return $this->indexRedirect($section, __('admin.deleted'));
    }

    private function save(SaveEntryRequest $request, PortfolioEntry $entry): void
    {
        $attributes = $request->safe()->only(['slug', 'position', 'is_active', 'translations', 'details']) + ['details' => []];

        if ($entry->section !== PortfolioSection::Projects) {
            $entry->fill($attributes)->save();

            return;
        }

        /** @var list<UploadedFile> $images */
        $images = $request->validated('image_uploads', []);
        /** @var list<string> $requestedRemovals */
        $requestedRemovals = $request->validated('remove_images', []);

        $disk = Storage::disk('public');
        $entryId = $entry->getKey();
        $uploads = [];
        try {
            foreach ($images as $image) {
                $path = $image->store('portfolio/projects', 'public');
                if ($path === false) {
                    throw ValidationException::withMessages([
                        'image_uploads' => __('validation.uploaded', ['attribute' => __('admin.field.image_uploads')]),
                    ]);
                }
                $uploads[] = $path;
            }

            $removed = DB::transaction(function () use ($entryId, $attributes, $uploads, $requestedRemovals): array {
                $project = $entryId === null
                    ? new PortfolioEntry(['section' => PortfolioSection::Projects])
                    : PortfolioEntry::query()->lockForUpdate()->findOrFail($entryId);

                return $this->saveGallery($project, $attributes, $uploads, $requestedRemovals);
            }, 3);
        } catch (Throwable $exception) {
            $disk->delete($uploads);

            throw $exception;
        }

        $disk->delete($removed);
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @param  list<string>  $uploads
     * @param  list<string>  $references
     * @return array<int, string>
     */
    private function saveGallery(PortfolioEntry $project, array $attributes, array $uploads, array $references): array
    {
        $paths = $project->imagePaths();
        $removed = array_intersect($paths, $references);
        $details = ($attributes['details'] ?? $project->details) + ['images' => $project->imageUrls()];
        $details['images'] = array_values(array_diff((array) $details['images'], $references));
        $details['image_paths'] = [...array_diff($paths, $removed), ...$uploads];
        $project->fill(array_replace($attributes, ['details' => $details]))->save();

        return $removed;
    }

    private function form(PortfolioSection $section, PortfolioEntry $entry): View
    {
        return view('portfolio-admin.entry', compact('section', 'entry'));
    }

    private function indexRedirect(PortfolioSection $section, string $message, int $page = 1): RedirectResponse
    {
        return to_route('portfolio-admin.entries.index', ['section' => $section, 'page' => $page > 1 ? $page : null])
            ->with('status', $message);
    }
}
