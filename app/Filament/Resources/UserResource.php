<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\Reservation;
use App\Models\User;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\DateTimePicker::make('email_verified_at'),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->required()
                    ->maxLength(255),

                Select::make('roles')->multiple()->relationship('roles', 'name'),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('email_verified_at')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('Total Booking')
                    ->default(function (User $record) {
                        return Reservation::where('user_id', $record->id)->count();

                        return $record->id;
                    })
                    ->searchable(),

                Tables\Columns\TextColumn::make('Monthly Booking')
                    ->default(function (User $record) {
                        return Reservation::where('user_id', $record->id)->whereMonth('created_at', Carbon::now()->format('m'))->whereYear('created_at', date('Y'))->count();

                        return $record->id;
                    }),
                Tables\Columns\TextColumn::make('Monthly  %')
                    ->default(function (User $record) {
                        $monthlyBookingCount = Reservation::where('user_id', $record->id)
                            ->whereMonth('created_at', Carbon::now()->format('m'))
                            ->count();

                        // $totalReservationsCount = Reservation::get()->count();
                        $totalReservationsCount = Reservation::whereMonth('created_at', Carbon::now()->format('m'))->count();

                        return ($totalReservationsCount > 0) ? number_format(($monthlyBookingCount / $totalReservationsCount) * 100, 2) : 0;
                    }),

                Tables\Columns\TextColumn::make('All Time  %')
                    ->default(function (User $record) {
                        $monthlyBookingCount = Reservation::where('user_id', $record->id)
                            ->count();

                        // $totalReservationsCount = Reservation::get()->count();
                        $totalReservationsCount = Reservation::count();

                        return ($totalReservationsCount > 0) ? number_format(($monthlyBookingCount / $totalReservationsCount) * 100, 2) : 0;
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
