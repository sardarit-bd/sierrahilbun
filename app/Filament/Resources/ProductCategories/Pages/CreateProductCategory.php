<?php

namespace App\Filament\Resources\ProductCategories\Pages;

use App\Filament\Resources\ProductCategories\ProductCategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProductCategory extends CreateRecord
{
    protected static string $resource = ProductCategoryResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Handle temp images on create
        if (isset($data['temp_images']) && is_array($data['temp_images'])) {
            $this->record->save(); // Save product first to get ID
            
            foreach ($data['temp_images'] as $index => $imagePath) {
                $this->record->images()->create([
                    'image_url' => $imagePath,
                    'is_primary' => $index === 0,
                    'sort_order' => $index,
                ]);
            }
            
            unset($data['temp_images']);
        }
        
        return $data;
    }
}
