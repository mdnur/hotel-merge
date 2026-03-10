<?php

namespace App\Filament\Resources\Payments;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\MorphToSelect\Type;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\Payments\Pages\ListPayments;
use App\Filament\Resources\Payments\Pages\CreatePayment;
use App\Filament\Resources\Payments\Pages\EditPayment;
use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Payment;
use App\Models\PaymentType;
use App\Models\Reservation;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\MorphToSelect;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-credit-card';

    protected static string | \UnitEnum | null $navigationGroup = 'Payment';

    public static function form(Schema $schema): Schema
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
                TextInput::make('tnx')
                    ->required()
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('paymentType.name')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('tnx')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
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
            'index' => ListPayments::route('/'),
            'create' => CreatePayment::route('/create'),
            'edit' => EditPayment::route('/{record}/edit'),
        ];
    }
}
