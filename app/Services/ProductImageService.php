<?php

namespace App\Services;

use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Support\Facades\Storage;

class ProductImageService
{
    public function optimizeAndStore($file, string $directory = 'products'): string
    {
        $filename = time() . '_' . uniqid() . '.webp';
        $path = $directory . '/' . $filename;

        $image = Image::read($file)
            ->scaleDown(1200, 1200)
            ->toWebp(85);

        Storage::disk('public')->put($path, $image);

        return $path;
    }

    public function delete(string $path): void
    {
        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}