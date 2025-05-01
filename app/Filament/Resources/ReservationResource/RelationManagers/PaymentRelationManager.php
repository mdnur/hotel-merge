<?php

namespace App\Filament\Resources\ReservationResource\RelationManagers;

use App\Models\PaymentType;
use App\Models\Reservation;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('payment_type_id')
                    ->label('Payment Type')
                    ->options(PaymentType::pluck('name', 'id'))
                    ->required(),

                Forms\Components\Select::make('user_id')
                    ->label('Payment received by')
                    ->default(auth()->id())
                    ->disabled()
                    ->dehydrated() // Ensures the value is still saved
                    ->options(User::pluck('name', 'id'))
                    ->required(),
                Forms\Components\TextInput::make('amount')
                    ->required()
                    ->numeric(),
                MorphToSelect::make('paymentable')
                    ->default(MorphToSelect\Type::make(Reservation::class)->titleAttribute('reservation_no'))
                    ->label('Payment Type')
                    ->types([
                        MorphToSelect\Type::make(Reservation::class)->titleAttribute('reservation_no'),
                        // MorphToSelect\Type::make(User::class)->titleAttribute('email'),
                        // MorphToSelect\Type::make(Comment::class)->titleAttribute('id'),
                    ])
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('txn')
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
