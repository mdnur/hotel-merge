<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Payment;
use App\Models\PaymentType;
use App\Models\Reservation;
use Filament\Forms;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationGroup = 'Payment';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('payment_type_id')
                    ->label('Payment Type')
                    ->options(PaymentType::pluck('name', 'id'))
                    ->required(),
                Forms\Components\TextInput::make('advance')
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
                Forms\Components\TextInput::make('Last3Digit')
                    ->required()
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('paymentType.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('advance')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('Last3Digit')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
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
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }
}
