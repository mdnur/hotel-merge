<?php

namespace App\Filament\Resources\Users;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DateTimePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\ViewUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\UserResource\Pages;
use App\Models\Reservation;
use App\Models\User;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                DateTimePicker::make('email_verified_at'),
                TextInput::make('password')
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
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                TextColumn::make('email_verified_at')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('Total Booking')
                    ->default(function (User $record) {
                        return Reservation::where('user_id', $record->id)->count();

                        return $record->id;
                    })
                    ->searchable(),

                TextColumn::make('Monthly Booking')
                    ->default(function (User $record) {
                        return Reservation::where('user_id', $record->id)->whereMonth('created_at', Carbon::now()->format('m'))->whereYear('created_at', date('Y'))->count();

                        return $record->id;
                    }),
                TextColumn::make('Monthly  %')
                    ->default(function (User $record) {
                        $monthlyBookingCount = Reservation::where('user_id', $record->id)
                            ->whereMonth('created_at', Carbon::now()->format('m'))
                            ->count();

                        // $totalReservationsCount = Reservation::get()->count();
                        $totalReservationsCount = Reservation::whereMonth('created_at', Carbon::now()->format('m'))->count();

                        return ($totalReservationsCount > 0) ? number_format(($monthlyBookingCount / $totalReservationsCount) * 100, 2) : 0;
                    }),

                TextColumn::make('All Time  %')
                    ->default(function (User $record) {
                        $monthlyBookingCount = Reservation::where('user_id', $record->id)
                            ->count();

                        // $totalReservationsCount = Reservation::get()->count();
                        $totalReservationsCount = Reservation::count();

                        return ($totalReservationsCount > 0) ? number_format(($monthlyBookingCount / $totalReservationsCount) * 100, 2) : 0;
                    }),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
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
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'view' => ViewUser::route('/{record}'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
