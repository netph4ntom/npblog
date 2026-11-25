<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Media;
use App\Models\User;
use GdImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

class MediaProcessor
{
    /**
     * Maximum allowed file size in bytes (5 MB).
     */
    public const int MAX_FILE_SIZE = 5 * 1024 * 1024;

    /**
     * Allowed MIME types strictly verified on the server.
     *
     * @var list<string>
     */
    public const array ALLOWED_MIMES = [
        'image/jpeg',
        'image/png',
        'image/webp',
    ];

    /**
     * Maximum dimension (width or height) for the main image.
     */
    public const int MAX_DIMENSION = 1600;

    /**
     * Maximum allowed favicon file size in bytes (1 MB).
     */
    public const int MAX_FAVICON_SIZE = 1024 * 1024;

    /**
     * Allowed MIME types for ICO favicons.
     *
     * @var list<string>
     */
    public const array ALLOWED_ICO_MIMES = [
        'image/x-icon',
        'image/vnd.microsoft.icon',
        'image/ico',
    ];

    /**
     * Target widths for generated responsive thumbnails.
     *
     * @var list<int>
     */
    public const array THUMBNAIL_WIDTHS = [400, 800];

    /**
     * Process an uploaded image file:
     * 1. Validate size, filename, and server-detected MIME type.
     * 2. Strip EXIF metadata and preserve JPEG rotation.
     * 3. Proportional downscale if dimensions exceed 1600px.
     * 4. Encode as WebP and generate 400px and 800px thumbnails.
     * 5. Store files under random unique names and persist Media model.
     *
     * @throws InvalidArgumentException
     */
    public function process(
        UploadedFile $file,
        User $user,
        ?string $altText = null,
        string $disk = 'public'
    ): Media {
        $realPath = $this->validateUploadFile($file, self::MAX_FILE_SIZE, ['jpg', 'jpeg', 'png', 'webp']);
        $detectedMime = $this->detectMime($realPath, self::ALLOWED_MIMES);
        $gdImage = $this->decodeOrientedGdImage($realPath, $detectedMime);

        $origWidth = imagesx($gdImage);
        $origHeight = imagesy($gdImage);

        $mainImage = $this->resizeKeepingRatio($gdImage, $origWidth, $origHeight, self::MAX_DIMENSION);
        $finalWidth = imagesx($mainImage);
        $finalHeight = imagesy($mainImage);

        $hash = Str::random(40);
        $mainPath = "media/{$hash}.webp";

        $mainBinary = $this->encodeWebp($mainImage, 85);
        $storage = Storage::disk($disk);
        $storage->put($mainPath, $mainBinary);
        $finalSize = strlen($mainBinary);

        foreach (self::THUMBNAIL_WIDTHS as $targetWidth) {
            $thumbImage = $this->resizeWidth($gdImage, $origWidth, $origHeight, $targetWidth);
            $thumbBinary = $this->encodeWebp($thumbImage, 80);
            imagedestroy($thumbImage);

            $storage->put("media/thumbnails/{$hash}-{$targetWidth}.webp", $thumbBinary);
        }

        if ($mainImage !== $gdImage) {
            imagedestroy($mainImage);
        }
        imagedestroy($gdImage);

        return Media::create([
            'user_id' => $user->id,
            'disk' => $disk,
            'path' => $mainPath,
            'original_name' => pathinfo($file->getClientOriginalName(), PATHINFO_BASENAME),
            'mime' => 'image/webp',
            'size' => $finalSize,
            'width' => $finalWidth,
            'height' => $finalHeight,
            'alt_text' => $altText ? trim($altText) : null,
        ]);
    }

    /**
     * Validate and store a site favicon using randomized filenames and EXIF/WebP sanitization for raster uploads.
     *
     * @throws InvalidArgumentException
     */
    public function processFavicon(UploadedFile $file, string $disk = 'public'): string
    {
        $realPath = $this->validateUploadFile($file, self::MAX_FAVICON_SIZE, ['ico', 'png', 'jpg', 'jpeg', 'webp']);
        $allowedMimes = array_merge(self::ALLOWED_MIMES, self::ALLOWED_ICO_MIMES);
        $detectedMime = $this->detectMime($realPath, $allowedMimes);
        $hash = Str::random(40);
        $storage = Storage::disk($disk);

        if (in_array($detectedMime, self::ALLOWED_ICO_MIMES, true)) {
            $binary = file_get_contents($realPath);
            if ($binary === false || $binary === '') {
                throw new InvalidArgumentException('File favicon tidak terbaca.');
            }

            $path = "media/favicon-{$hash}.ico";
            $storage->put($path, $binary);

            return $storage->url($path);
        }

        $gdImage = $this->decodeOrientedGdImage($realPath, $detectedMime);
        $iconImage = $this->resizeKeepingRatio($gdImage, imagesx($gdImage), imagesy($gdImage), 512);
        $binary = $this->encodeWebp($iconImage, 90);

        if ($iconImage !== $gdImage) {
            imagedestroy($iconImage);
        }
        imagedestroy($gdImage);

        $path = "media/favicon-{$hash}.webp";
        $storage->put($path, $binary);

        return $storage->url($path);
    }

    /**
     * Validate file size, safe filename, and readability.
     *
     * @param  list<string>  $allowedExtensions
     */
    protected function validateUploadFile(UploadedFile $file, int $maxBytes, array $allowedExtensions): string
    {
        if ($file->getSize() > $maxBytes) {
            $maxMb = (int) max(1, round($maxBytes / (1024 * 1024)));
            throw new InvalidArgumentException("Ukuran gambar melebihi batas maksimum {$maxMb} MB.");
        }

        $this->validateFilename($file->getClientOriginalName(), $allowedExtensions);

        $realPath = $file->getRealPath();
        if (! $realPath || ! file_exists($realPath)) {
            throw new InvalidArgumentException('File gambar tidak valid atau tidak terbaca.');
        }

        return $realPath;
    }

    /**
     * Detect and verify server-side MIME type via fileinfo.
     *
     * @param  list<string>  $allowedMimes
     */
    protected function detectMime(string $realPath, array $allowedMimes): string
    {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $detectedMime = $finfo->file($realPath);

        if (! is_string($detectedMime) || ! in_array($detectedMime, $allowedMimes, true)) {
            $mimeLabel = is_string($detectedMime) ? $detectedMime : 'unknown';
            throw new InvalidArgumentException(
                "Format file tidak didukung: {$mimeLabel}. Gunakan format gambar yang diizinkan."
            );
        }

        return $detectedMime;
    }

    /**
     * Decode an image with GD and normalize EXIF orientation (stripping metadata).
     */
    protected function decodeOrientedGdImage(string $realPath, string $detectedMime): GdImage
    {
        $orientation = 1;
        if ($detectedMime === 'image/jpeg' && function_exists('exif_read_data')) {
            $exif = @exif_read_data($realPath);
            if (! empty($exif['Orientation']) && is_numeric($exif['Orientation'])) {
                $orientation = (int) $exif['Orientation'];
            }
        }

        $gdImage = match ($detectedMime) {
            'image/jpeg' => @imagecreatefromjpeg($realPath),
            'image/png' => @imagecreatefrompng($realPath),
            default => @imagecreatefromwebp($realPath),
        };

        if ($gdImage === false) {
            throw new InvalidArgumentException('Data gambar rusak atau tidak valid.');
        }

        if ($orientation === 3) {
            $rotated = imagerotate($gdImage, 180, 0);
            if ($rotated !== false) {
                imagedestroy($gdImage);
                $gdImage = $rotated;
            }
        } elseif ($orientation === 6) {
            $rotated = imagerotate($gdImage, -90, 0);
            if ($rotated !== false) {
                imagedestroy($gdImage);
                $gdImage = $rotated;
            }
        } elseif ($orientation === 8) {
            $rotated = imagerotate($gdImage, 90, 0);
            if ($rotated !== false) {
                imagedestroy($gdImage);
                $gdImage = $rotated;
            }
        }

        return $gdImage;
    }

    /**
     * Encode a GD resource into WebP binary bytes.
     */
    protected function encodeWebp(GdImage $image, int $quality): string
    {
        ob_start();
        imagewebp($image, null, $quality);

        return (string) ob_get_clean();
    }

    /**
     * Validate against dangerous filenames (double extensions, executables, scripts).
     *
     * @param  list<string>  $allowedExtensions
     */
    protected function validateFilename(string $filename, array $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp']): void
    {
        $lowercase = strtolower($filename);
        $forbiddenExtensions = [
            'php', 'phtml', 'php3', 'php4', 'php5', 'php7', 'phps', 'pht', 'phar',
            'exe', 'sh', 'bash', 'bat', 'cmd', 'bin', 'js', 'html', 'htm', 'svg',
            'py', 'pl', 'cgi', 'asp', 'aspx', 'jsp',
        ];

        $parts = explode('.', $lowercase);
        if (count($parts) > 2) {
            for ($i = 1; $i < count($parts) - 1; $i++) {
                if (in_array($parts[$i], $forbiddenExtensions, true)) {
                    throw new InvalidArgumentException('Nama file mengandung ekstensi berbahaya.');
                }
            }
        }

        $ext = end($parts);
        if (! in_array($ext, $allowedExtensions, true)) {
            throw new InvalidArgumentException('Ekstensi file tidak diizinkan.');
        }
    }

    /**
     * Proportional resize if width or height exceeds maxDimension.
     */
    protected function resizeKeepingRatio(GdImage $src, int $w, int $h, int $maxDimension): GdImage
    {
        if ($w <= $maxDimension && $h <= $maxDimension) {
            $copy = imagecreatetruecolor($w, $h);
            if ($copy !== false) {
                imagealphablending($copy, false);
                imagesavealpha($copy, true);
                imagecopy($copy, $src, 0, 0, 0, 0, $w, $h);

                return $copy;
            }
        }

        $ratio = min($maxDimension / $w, $maxDimension / $h);
        $newW = (int) max(1, round($w * $ratio));
        $newH = (int) max(1, round($h * $ratio));

        $target = imagecreatetruecolor($newW, $newH);
        if ($target === false) {
            throw new InvalidArgumentException('Gagal mengalokasikan memori grafis GD.');
        }

        imagealphablending($target, false);
        imagesavealpha($target, true);
        $transparent = imagecolorallocatealpha($target, 0, 0, 0, 127);
        imagefilledrectangle($target, 0, 0, $newW, $newH, $transparent);

        imagecopyresampled($target, $src, 0, 0, 0, 0, $newW, $newH, $w, $h);

        return $target;
    }

    /**
     * Resize based on target width (preserving aspect ratio).
     */
    protected function resizeWidth(GdImage $src, int $w, int $h, int $targetWidth): GdImage
    {
        if ($w <= $targetWidth) {
            $copy = imagecreatetruecolor($w, $h);
            if ($copy !== false) {
                imagealphablending($copy, false);
                imagesavealpha($copy, true);
                imagecopy($copy, $src, 0, 0, 0, 0, $w, $h);

                return $copy;
            }
        }

        $ratio = $targetWidth / $w;
        $newW = $targetWidth;
        $newH = (int) max(1, round($h * $ratio));

        $target = imagecreatetruecolor($newW, $newH);
        if ($target === false) {
            throw new InvalidArgumentException('Gagal mengalokasikan memori grafis GD.');
        }

        imagealphablending($target, false);
        imagesavealpha($target, true);
        $transparent = imagecolorallocatealpha($target, 0, 0, 0, 127);
        imagefilledrectangle($target, 0, 0, $newW, $newH, $transparent);

        imagecopyresampled($target, $src, 0, 0, 0, 0, $newW, $newH, $w, $h);

        return $target;
    }
}
