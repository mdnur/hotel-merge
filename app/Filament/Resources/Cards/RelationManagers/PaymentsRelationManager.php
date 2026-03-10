<?php

namespace App\Filament\Resources\Cards\RelationManagers;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DateTimePicker;
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

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
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
                TextInput::make('tnx')
                    // ->required()
                    ->numeric(),
                DateTimePicker::make('created_at')
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
                TextColumn::make('amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('tnx')
                    ->searchable(),
                TextColumn::make('paymentType.name')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Received By')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()->createAnother(false)
                    ->after(function () {
                        $this->dispatch('refreshParent');
                    }),
            ])
            ->recordActions([
                EditAction::make()->after(function () {
                    $this->dispatch('refreshParent');
                }),
                DeleteAction::make()->after(function () {
                    $this->dispatch('refreshParent');
                }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
