<?php

namespace App\Filament\Resources\CardResource\RelationManagers;

use App\Models\PaymentType;
use App\Models\Reservation;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // Forms\Components\Select::make('payment_type_id')
                //     ->label('Payment Method')
                //     ->options(PaymentType::all()->pluck('name', 'id'))
                //     ->searchable()
                //     ->required(),

                // Forms\Components\TextInput::make('amount')
                //     ->required()
                //     ->numeric(),
                // Forms\Components\TextInput::make('tnx')
                //     ->maxLength(255),
                // Forms\Components\TextInput::make('description')
                //     ->maxLength(255),

                // Forms\Components\Select::make('user_id')
                //     ->label('Payment Received by')
                //     ->default(auth()->user()->id)
                //     ->options(User::all()->pluck('name', 'id'))
                //     ->disabled()              // Make the field read-only
                //     ->dehydrated(true),      // Ensure it doesn't get saved back to the database

                // // ->readonly()

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
                // MorphToSelect::make('paymentable')
                //     ->default(MorphToSelect\Type::make(Reservation::class)->titleAttribute('reservation_no'))
                //     ->label('Payment Type')
                //     ->types([
                //         MorphToSelect\Type::make(Reservation::class)->titleAttribute('reservation_no'),
                //         // MorphToSelect\Type::make(User::class)->titleAttribute('email'),
                //         // MorphToSelect\Type::make(Comment::class)->titleAttribute('id'),
                //     ])
                //     ->searchable()
                //     ->preload(),
                Forms\Components\TextInput::make('tnx')
                    // ->required()
                    ->numeric(),
                Forms\Components\DateTimePicker::make('created_at')
                    ->native(false)
                    ->readOnly(! auth()->user()->isSuperAdmin())
                    ->default(now()),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('payment_id')
            ->columns([
                Tables\Columns\TextColumn::make('amount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tnx')
                    ->searchable(),
                Tables\Columns\TextColumn::make('paymentType.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Received By')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->createAnother(false)
                    ->after(function () {
                        $this->dispatch('refreshParent');
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->after(function () {
                    $this->dispatch('refreshParent');
                }),
                Tables\Actions\DeleteAction::make()->after(function () {
                    $this->dispatch('refreshParent');
                }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
