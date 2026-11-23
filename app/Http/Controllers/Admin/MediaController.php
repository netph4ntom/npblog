<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreMediaRequest;
use App\Http\Requests\Admin\UpdateMediaRequest;
use App\Models\Media;
use App\Models\User;
use App\Services\MediaProcessor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\View\View;

class MediaController extends Controller
{
    /**
     * Display a paginated listing of media items.
     */
    public function index(Request $request): View
    {
        $search = $request->query('q');

        $query = Media::query()->with('user')->latest('created_at');

        if (is_string($search) && trim($search) !== '') {
            $term = '%'.trim($search).'%';
            $query->where(function ($q) use ($term) {
                $q->where('original_name', 'like', $term)
                    ->orWhere('alt_text', 'like', $term);
            });
        }

        $media = $query->paginate(24)->withQueryString();

        return view('admin.media.index', compact('media', 'search'));
    }

    /**
     * Store a newly uploaded media item.
     */
    public function store(StoreMediaRequest $request, MediaProcessor $processor): RedirectResponse|JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        /** @var UploadedFile $file */
        $file = $request->file('image');

        $altText = $request->input('alt_text');
        $altText = is_string($altText) ? $altText : null;

        try {
            $mediaItem = $processor->process($file, $user, $altText);
        } catch (\InvalidArgumentException $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => $e->getMessage(),
                    'errors' => ['image' => [$e->getMessage()]],
                ], 422);
            }

            return back()->withInput()->withErrors(['image' => $e->getMessage()]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'id' => $mediaItem->id,
                'url' => $mediaItem->url,
                'thumbnail_url' => $mediaItem->thumbnail_url,
                'alt_text' => $mediaItem->alt_text,
                'original_name' => $mediaItem->original_name,
                'width' => $mediaItem->width,
                'height' => $mediaItem->height,
            ], 201);
        }

        return redirect()->route('admin.media.index')->with('success', 'Gambar berhasil diunggah dan diproses ke format WebP.');
    }

    /**
     * Update alt text for the specified media item.
     */
    public function update(UpdateMediaRequest $request, Media $media): RedirectResponse
    {
        $altText = $request->input('alt_text');
        $altText = is_string($altText) ? $altText : null;

        $media->update([
            'alt_text' => $altText,
        ]);

        return redirect()->route('admin.media.index')->with('success', 'Alt text media berhasil diperbarui.');
    }

    /**
     * Remove the specified media item from storage and database.
     */
    public function destroy(Media $media): RedirectResponse
    {
        $articlesCount = $media->articles()->count();

        if ($articlesCount > 0) {
            return redirect()->back()->with('error', "Media tidak dapat dihapus karena masih digunakan sebagai gambar utama oleh {$articlesCount} artikel.");
        }

        $media->delete();

        return redirect()->route('admin.media.index')->with('success', 'Media dan file terkait berhasil dihapus.');
    }
}
