<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CardRoomResource\Pages;
use App\Models\Card;
use App\Models\CardRoom;
use App\Models\Room;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CardRoomResource extends Resource
{
    protected static ?string $model = CardRoom::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('card_id')
                    ->label('Card No')
                    ->options(Card::all()->pluck('card_no', 'id'))
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('room_id')
                    ->label('Room No')
                    ->options(Room::all()->pluck('room_no', 'id'))
                    ->searchable()
                    ->required(),
                Forms\Components\DateTimePicker::make('check_in')->native(false)
                    ->required(),
                Forms\Components\DateTimePicker::make('check_out')->native(false)
                    ->required(),
                Forms\Components\TextInput::make('rent')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('note')
                    ->maxLength(255),

                Forms\Components\Select::make('user_id')
                    ->label('Room allocated by')
                    ->default(auth()->user()->id)
                    ->options(User::all()->pluck('name', 'id'))
                    ->disabled()              // Make the field read-only
                    ->dehydrated(true),      // Ensure it doesn't get saved back to the database

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('card.card_no')
                    ->searchable()
                    ->label('Card No')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('room.room_no')
                    ->label('Room No')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('check_in')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('check_out')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('rent')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Room allocate by')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    // ->sortable('desc')
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

            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCardRooms::route('/'),
            'create' => Pages\CreateCardRoom::route('/create'),
            'edit' => Pages\EditCardRoom::route('/{record}/edit'),
        ];
    }
}
