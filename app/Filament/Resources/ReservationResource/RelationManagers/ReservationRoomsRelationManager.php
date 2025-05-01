<?php

namespace App\Filament\Resources\ReservationResource\RelationManagers;

use App\Models\Reservation;
use App\Models\RoomType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ReservationRoomsRelationManager extends RelationManager
{
    protected static string $relationship = 'reservationRooms';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('room_type_id')
                    ->label('Payment Types')
                    ->options(RoomType::pluck('name', 'id'))
                    ->required(),
                Forms\Components\DatePicker::make('check_in')
                    ->native(false)
                    ->required(),
                Forms\Components\DatePicker::make('check_out')
                    ->native(false)
                    ->required(),
                // Forms\Components\TextInput::make('reservation_id')
                //     ->required()
                //     ->numeric(),

                Forms\Components\TextInput::make('quantity')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('rent')
                    ->required()
                    ->numeric(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('id'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
