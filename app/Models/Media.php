<?php

namespace App\Models;

use Database\Factories\MediaFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    /** @use HasFactory<MediaFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'disk',
        'path',
        'original_name',
        'mime',
        'size',
        'width',
        'height',
        'alt_text',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
        ];
    }

    /**
     * The user who uploaded this media.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Articles that use this media as featured image.
     *
     * @return HasMany<Article, $this>
     */
    public function articles(): HasMany
    {
        return $this->hasMany(Article::class, 'featured_media_id');
    }

    /**
     * Get the public URL for the media item.
     *
     * @return Attribute<string, never>
     */
    protected function url(): Attribute
    {
        return Attribute::make(
            get: fn (): string => Storage::disk($this->disk)->url($this->path)
        );
    }

    /**
     * Get the URL for a thumbnail of the media.
     */
    public function getThumbnailUrl(int $size = 400): string
    {
        $dirname = dirname($this->path);
        $filename = pathinfo($this->path, PATHINFO_FILENAME);
        $thumbDir = ($dirname === '.' ? '' : $dirname.'/').'thumbnails/';
        $thumbPath = "{$thumbDir}{$filename}-{$size}.webp";

        if (Storage::disk($this->disk)->exists($thumbPath)) {
            return Storage::disk($this->disk)->url($thumbPath);
        }

        return $this->url;
    }

    /**
     * Accessor for default 400px thumbnail.
     *
     * @return Attribute<string, never>
     */
    protected function thumbnailUrl(): Attribute
    {
        return Attribute::make(
            get: fn (): string => $this->getThumbnailUrl(400)
        );
    }

    /**
     * Delete the media file and its generated thumbnails from disk.
     */
    public function deleteFiles(): void
    {
        $storage = Storage::disk($this->disk);
        if ($storage->exists($this->path)) {
            $storage->delete($this->path);
        }

        $dirname = dirname($this->path);
        $filename = pathinfo($this->path, PATHINFO_FILENAME);
        $thumbDir = ($dirname === '.' ? '' : $dirname.'/').'thumbnails/';

        foreach ([400, 800] as $size) {
            $thumbPath = "{$thumbDir}{$filename}-{$size}.webp";
            if ($storage->exists($thumbPath)) {
                $storage->delete($thumbPath);
            }
        }
    }

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::deleting(function (Media $media): void {
            $media->deleteFiles();
        });
    }
}
