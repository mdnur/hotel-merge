<?php

namespace App\Filament\Resources\CardRooms;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Filament\Actions\EditAction;
use App\Filament\Resources\CardRooms\Pages\ListCardRooms;
use App\Filament\Resources\CardRooms\Pages\CreateCardRoom;
use App\Filament\Resources\CardRooms\Pages\EditCardRoom;
use App\Filament\Resources\CardRoomResource\Pages;
use App\Models\Card;
use App\Models\CardRoom;
use App\Models\Room;
use App\Models\User;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CardRoomResource extends Resource
{
    protected static ?string $model = CardRoom::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('card_id')
                    ->label('Card No')
                    ->options(Card::all()->pluck('card_no', 'id'))
                    ->searchable()
                    ->required(),
                Select::make('room_id')
                    ->label('Room No')
                    ->options(Room::all()->pluck('room_no', 'id'))
                    ->searchable()
                    ->required(),
                DateTimePicker::make('check_in')->native(false)
                    ->required(),
                DateTimePicker::make('check_out')->native(false)
                    ->required(),
                TextInput::make('rent')
                    ->required()
                    ->numeric(),
                TextInput::make('note')
                    ->maxLength(255),

                Select::make('user_id')
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
                TextColumn::make('card.card_no')
                    ->searchable()
                    ->label('Card No')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('room.room_no')
                    ->searchable()
                    ->label('Room No')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('check_in')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('check_out')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('rent')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('Room allocate by')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    // ->sortable('desc')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('business_date')
                    ->label('Business Date')
                    ->schema([
                        DatePicker::make('date')
                            ->label('Select Date')
                            ->native(false)
                            ->default(now()->toDateString()),
                    ])
                    ->query(function ($query, array $data) {
                        if (! $data['date']) {
                            return $query;
                        }

                        return $query->customDateTotalRoom($data['date']);
                    }),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([

            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCardRooms::route('/'),
            'create' => CreateCardRoom::route('/create'),
            'edit' => EditCardRoom::route('/{record}/edit'),
        ];
    }
}
