<?php

namespace App\Filament\Resources\Reservations\RelationManagers;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Models\Reservation;
use App\Models\RoomType;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ReservationRoomsRelationManager extends RelationManager
{
    protected static string $relationship = 'reservationRooms';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('room_type_id')
                    ->label('Payment Types')
                    ->options(RoomType::pluck('name', 'id'))
                    ->required(),
                DatePicker::make('check_in')
                    ->native(false)
                    ->required(),
                DatePicker::make('check_out')
                    ->native(false)
                    ->required(),
                // Forms\Components\TextInput::make('reservation_id')
                //     ->required()
                //     ->numeric(),

                TextInput::make('quantity')
                    ->required()
                    ->numeric(),
                TextInput::make('rent')
                    ->required()
                    ->numeric(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('id'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
