<?php

namespace App\Filament\Resources\ReservationRooms;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\ReservationRooms\Pages\ListReservationRooms;
use App\Filament\Resources\ReservationRooms\Pages\CreateReservationRoom;
use App\Filament\Resources\ReservationRooms\Pages\EditReservationRoom;
use App\Filament\Resources\ReservationRoomResource\Pages;
use App\Models\Reservation;
use App\Models\ReservationRoom;
use App\Models\RoomType;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ReservationRoomResource extends Resource
{
    protected static ?string $model = ReservationRoom::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Schema $schema): Schema
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

                Select::make('reservation_id')
                    ->label('Reservation No')
                    ->options(Reservation::pluck('reservation_no', 'id'))
                    ->searchable()
                    ->required(),
                TextInput::make('quantity')
                    ->required()
                    ->numeric(),
                TextInput::make('rent')
                    ->required()
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('roomType.name')

                    ->numeric()
                    ->sortable(),
                TextColumn::make('check_in')
                    ->date()
                    ->sortable(),
                TextColumn::make('check_out')
                    ->date()
                    ->sortable(),
                TextColumn::make('reservation.reservation_no')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('quantity')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('rent')
                    ->numeric()
                    ->sortable(),
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
            'index' => ListReservationRooms::route('/'),
            'create' => CreateReservationRoom::route('/create'),
            'edit' => EditReservationRoom::route('/{record}/edit'),
        ];
    }
}
