<?php

namespace App\Filament\Resources\Reservations\RelationManagers;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\MorphToSelect\Type;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Models\PaymentType;
use App\Models\Reservation;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\MorphToSelect;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('payment_type_id')
                    ->label('Payment Type')
                    ->options(PaymentType::pluck('name', 'id'))
                    ->required(),

                Select::make('user_id')
                    ->label('Payment received by')
                    ->default(auth()->id())
                    ->disabled()
                    ->dehydrated() // Ensures the value is still saved
                    ->options(User::pluck('name', 'id'))
                    ->required(),
                TextInput::make('amount')
                    ->required()
                    ->numeric(),
                MorphToSelect::make('paymentable')
                    ->default(Type::make(Reservation::class)->titleAttribute('reservation_no'))
                    ->label('Payment Type')
                    ->types([
                        Type::make(Reservation::class)->titleAttribute('reservation_no'),
                        // MorphToSelect\Type::make(User::class)->titleAttribute('email'),
                        // MorphToSelect\Type::make(Comment::class)->titleAttribute('id'),
                    ])
                    ->searchable()
                    ->preload(),
                TextInput::make('txn')
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
