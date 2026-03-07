<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required()
                    ->unique(
                        table: 'users',
                        column: 'email',
                        ignoreRecord: true, 
                    ),
                TextInput::make('password')
                    ->password(),
                TextInput::make('phone')
                    ->tel(),
                Select::make('role')
                    ->options(['admin' => 'Admin', 'content_editor' => 'Content editor', 'customer' => 'Customer'])
                    ->default('customer')
                    ->required(),
                DateTimePicker::make('email_verified_at'),
            ]);
    }
}
