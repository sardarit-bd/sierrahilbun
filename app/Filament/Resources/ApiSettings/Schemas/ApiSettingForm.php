<?php

namespace App\Filament\Resources\ApiSettings\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ApiSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('key')
                    ->required(),
                Textarea::make('value')
                    ->columnSpanFull(),
                TextInput::make('group')
                    ->required()
                    ->default('general'),
                TextInput::make('label')
                    ->required(),
                Select::make('type')
                    ->options(['text' => 'Text', 'secret' => 'Secret', 'boolean' => 'Boolean', 'number' => 'Number'])
                    ->default('text')
                    ->required(),
            ]);
    }
}
