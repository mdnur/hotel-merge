<?php

namespace App\Filament\Resources\HotelSettings;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\HotelSettings\Pages\ListHotelSettings;
use App\Filament\Resources\HotelSettings\Pages\CreateHotelSetting;
use App\Filament\Resources\HotelSettings\Pages\EditHotelSetting;
use App\Filament\Resources\HotelSettingResource\Pages;
use App\Filament\Resources\HotelSettingResource\RelationManagers;
use App\Models\HotelSetting;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class HotelSettingResource extends Resource
{
    protected static ?string $model = HotelSetting::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-cog-8-tooth';
    protected static string | \UnitEnum | null $navigationGroup = "Settings";

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Textarea::make('description')
                    ->required()
                    ->maxLength(65535)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHotelSettings::route('/'),
            'create' => CreateHotelSetting::route('/create'),
            'edit' => EditHotelSetting::route('/{record}/edit'),
        ];
    }
}
