<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReservationRoomResource\Pages;
use App\Models\Reservation;
use App\Models\ReservationRoom;
use App\Models\RoomType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ReservationRoomResource extends Resource
{
    protected static ?string $model = ReservationRoom::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
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

                Forms\Components\Select::make('reservation_id')
                    ->label('Reservation No')
                    ->options(Reservation::pluck('reservation_no', 'id'))
                    ->searchable()
                    ->required(),
                Forms\Components\TextInput::make('quantity')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('rent')
                    ->required()
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('roomType.name')

                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('check_in')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('check_out')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('reservation.reservation_no')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('rent')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListReservationRooms::route('/'),
            'create' => Pages\CreateReservationRoom::route('/create'),
            'edit' => Pages\EditReservationRoom::route('/{record}/edit'),
        ];
    }
}
