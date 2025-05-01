<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CardResource\Pages;
use App\Filament\Resources\CardResource\RelationManagers\CardRoomsRelationManager;
use App\Filament\Resources\CardResource\RelationManagers\PaymentsRelationManager;
use App\Models\Card;
use App\Models\Payment;
use App\Models\PaymentType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CardResource extends Resource
{
    protected static ?string $model = Card::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    // public static function canAccess(): bool
    // {
    //     // return false;

    //     return auth()->user()->isSuperAdmin();
    // }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('card_no')->label('Card No')->unique(ignoreRecord: true)->required()->maxLength(255),
            Forms\Components\TextInput::make('reservation_id')->maxLength(255)->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('total_rent')
                ->label('Total Rent')
                ->visibleOn('edit')
                ->readOnly()
                ->dehydrated(false)
                ->formatStateUsing(fn ($state, $record) => $record?->total_rent)
                ->reactive(),

            Forms\Components\DateTimePicker::make('arrival_date')->native(false)->default(now())->required()->readOnly(fn () => ! auth()->user()->isSuperAdmin()),
            Forms\Components\DateTimePicker::make('departure_date')
                ->native(false)
                ->nullable()
                ->readOnly(fn () => ! auth()->user()->isSuperAdmin())
                // ->dehydrated() // Ensures the value is still saved
                ->default(null),
            Forms\Components\Select::make('check_in_made_by')
                ->label('Checked In By')
                ->relationship('check_in_made_by_user', 'name')
                ->default(auth()->id())
                ->disabled(fn () => auth()->user()->isSuperAdmin())
                ->dehydrated(), // Ensures the value is still saved

            Forms\Components\Select::make('check_out_made_by')
                ->label('Checked Out By')
                ->relationship('check_out_made_by_user', 'name')
                ->default(auth()->id())
                ->visibleOn('edit')
                // ->visible(fn () => auth()->user()->isSuperAdmin()) // 👈 Only visible if Superadmin

                ->disabled(fn () => ! auth()->user()->isSuperAdmin())
                ->dehydrated(), // Ensures the value is still saved

            Forms\Components\TextInput::make('note')->maxLength(255),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('card_no')->searchable(),
                Tables\Columns\TextColumn::make('reservation_id')->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('arrival_date')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('departure_date')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('total_rent')
                    ->label('Total Rent')
                    ->money('bdt') // or your preferred currency code
                    ->sortable(),

                Tables\Columns\TextColumn::make('check_in_made_by_user.name')->label('Check In Made By')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('check_out_made_by_user.name')->label('Check Out Made By')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                // ->sortby('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([

                Tables\Actions\Action::make('make payment')
                    ->color('gray')
                    ->form([
                        Forms\Components\Select::make('payment_type_id')
                            ->options(PaymentType::all()->pluck('name', 'id'))
                            ->required(),
                        Forms\Components\TextInput::make('amount')
                            ->numeric()
                            ->required(),
                        Forms\Components\TextInput::make('transactions_no'),
                    ])->action(function (Card $record, array $data) {
                        $data['card_id'] = $record->id;
                        $data['user_id'] = auth()->user()->id;
                        // dd($data);
                        Payment::create($data);

                        Notification::make()
                            ->title('Payment accepted successfully')
                            ->success()
                            ->send();
                    })->color('gray')->visible(fn (Card $record) => is_null($record->departure_date)), // <--- only show if departure_date is null

                Tables\Actions\Action::make('Checkout')
                    ->visible(fn (Card $record) => is_null($record->departure_date)) // <--- only show if departure_date is null
                    ->requiresConfirmation()
                    ->action(fn (Card $record) => $record->update([
                        'departure_date' => now(),
                        'check_out_made_by' => auth()->id(),
                    ]))
                    ->action(function (Card $record) {
                        $now = now();
                        // dd($this->record->cardRooms);
                        $record->cardRooms->each(function ($room) use ($record, $now) {
                            if ($room->check_out > $record->departure_date) {
                                $room->update([
                                    'check_out' => $now,
                                ]);
                            }
                        });
                        $record->update([
                            'departure_date' => $now,
                            'check_out_made_by' => auth()->id(),
                        ]);
                        $record->refresh();

                        Notification::make()
                            ->title('Check Out made successfully.')
                            ->success()
                            ->send();
                    })
                    ->color('info')->icon('heroicon-o-check'),

                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),

                    Tables\Actions\DeleteAction::make()
                        ->requiresConfirmation()
                        ->action(function (Card $record) {
                            // First delete related models
                            $record->payments()->delete(); // example
                            $record->cardRooms()->delete(); // example

                            // Then delete the main record

                            $record->delete();
                            Notification::make()
                                ->title('Card deleted SuccessFully.')
                                ->success()
                                ->send();

                            return redirect(\App\Filament\Resources\CardResource::getUrl());

                            // $this->notify('success', 'Card deleted successfully.');
                        })
                        ->color('danger')
                        ->visible(fn () => auth()->user()->isSuperAdmin()) // 👈 Only visible if Superadmin
                        ->icon('heroicon-o-trash')
                        ->label(__('Delete Card')),
                ])->label('More actions')->button()->outlined(),

            ])->defaultPaginationPageOption(25);
    }

    public static function getRelations(): array
    {
        return [CardRoomsRelationManager::class, PaymentsRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCards::route('/'),
            'create' => Pages\CreateCard::route('/create'),
            'edit' => Pages\EditCard::route('/{record}/edit'),
        ];
    }
}
