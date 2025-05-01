<?php

namespace App\Filament\Resources;

use App\Classes\BDBulkSms;
use App\Enums\Status;
use App\Filament\Resources\ReservationResource\Pages;
use App\Filament\Resources\ReservationResource\RelationManagers;
use App\Models\Customer;
use App\Models\PaymentType;
use App\Models\Reservation;
use App\Models\RoomType;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ReservationResource extends Resource
{
    protected static ?string $model = Reservation::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Reservation Information')
                    ->description('Make Reservation')
                    ->schema([
                        Forms\Components\TextInput::make('reservation_no')
                            ->label('Reservation No')
                            ->unique(ignoreRecord: true)->columnSpan(2)
                            ->required()
                            ->default(
                                optional(Reservation::latest('created_at')->first())->reservation_no
                                    ? Reservation::latest('created_at')->first()->reservation_no + 1 : null)
                            ->numeric(),
                        Forms\Components\DatePicker::make('booking_date')
                            ->label('Book Date')
                            ->native(false)->columnSpan(2)
                            ->default(today())
                            ->readonly()
                            ->live()
                            ->required(),
                        Forms\Components\DatePicker::make('check_in_date')
                            ->label('Check In Date')
                            ->minDate(today())
                            ->afterOrEqual('today')->columnSpan(1)
                            ->native(false)
                            ->minDate(Carbon::today())
                            ->live()
                            ->required(),
                        Forms\Components\DatePicker::make('check_out_date')
                            ->label('Check Out Date')
                            ->native(false)->columnSpan(1)
                            ->live()
                            ->minDate(Carbon::tomorrow())
                            ->after('today')
                            ->required(),
                        Forms\Components\TextInput::make('total_rent')
                            ->label('Total Rent')
                            ->live()->columnSpan(1)
                            ->required()
                            ->numeric(),

                        Forms\Components\Select::make('status')->columnSpan(1)
                            ->default(Status::Confirm)
                            ->options(Status::class),

                        Forms\Components\TextInput::make('note')->columnSpan(1)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('reference')->columnSpan(1)
                            ->maxLength(255),
                    ])->columnSpan(1),

                Group::make()->schema([

                    Section::make('Customer')->columnSpan(1)
                        ->relationship('customer')
                        ->schema([
                            Forms\Components\TextInput::make('name')
                                ->label('Customer Name')
                                ->live()
                                ->required(),
                            Forms\Components\TextInput::make('phone')
                                ->tel()
                                ->telRegex('/^01[0-9]{9}$/')
                                ->label('Customer Phone')
                                ->live(debounce: 1000)
                                ->Datalist(Customer::pluck('phone', 'id'))
                                ->required()
                                ->afterStateUpdated(function (Set $set, ?string $state) {
                                    $customer = self::findCustomer($state);
                                    if ($customer) {
                                        $set('name', $customer->name);
                                        $set('address', $customer->address);
                                        $set('email', $customer->email);
                                        $set('document', $customer->document);
                                    }
                                }),

                            Forms\Components\TextInput::make('address')
                                ->label('Address'),
                            Forms\Components\TextInput::make('email')
                                ->label('Customer Email'),
                            Forms\Components\TextInput::make('document')
                                ->label('Customer Document Id'),

                        ])->collapsible()->columns(2),

                    Section::make('Payments')
                        ->schema([
                            Forms\Components\Repeater::make('payments')
                                ->label('Payment Entries')
                                ->relationship('payments') // THIS is where the morphMany goes
                                ->schema([
                                    Forms\Components\Select::make('payment_type_id')
                                        ->label('Payment Type')
                                        ->options(fn () => PaymentType::pluck('name', 'id'))
                                        ->required(),

                                    Forms\Components\TextInput::make('advance')
                                        ->label('Advance Amount')
                                        ->required()
                                        ->numeric(),

                                    Forms\Components\TextInput::make('Last3Digit')
                                        ->label('Last 3 digit')
                                        ->numeric(),
                                ])
                                ->columns(2)
                                ->collapsible(),
                        ])
                        ->columnSpan(1), // No ->relationship() on Section

                    Section::make('Assigning Room')
                        ->schema([
                            Repeater::make('Rooms')
                                ->cloneable()
                                ->relationship('rooms')
                                ->schema([
                                    Forms\Components\Select::make('room_type_id')
                                        ->label('Room Type')
                                        ->columnSpan(2)
                                        ->live()
                                        ->options(RoomType::pluck('name', 'id'))
                                        ->afterStateUpdated(fn (Set $set, ?string $state) => $set('rent', self::findRoomRent($state) ? self::findRoomRent($state)->room_rent : null))
                                        ->required(),
                                    Forms\Components\DatePicker::make('check_in')
                                        ->live()
                                        ->minDate(Carbon::today())
                                        ->afterOrEqual('Today')
                                        ->native(false)
                                        ->required(),

                                    Forms\Components\DatePicker::make('check_out')
                                        ->after('today')
                                        ->live()
                                        ->minDate(Carbon::tomorrow())
                                        ->native(false)
                                        ->afterStateHydrated(function (Get $get, Set $set) {
                                            $checkIn = Carbon::parse($get('check_in'))->format('Y-m-d');
                                            $checkOut = Carbon::parse($get('check_out'))->format('Y-m-d');
                                            $checkIn1 = Carbon::parse($checkIn);
                                            $checkOut1 = Carbon::parse($checkOut);

                                            $rent = $get('rent');
                                            $quantity = $get('quantity');

                                            $set('numberOfDays', $checkIn1->diffInDays($checkOut1));
                                            $set('subTotal', intval($rent) * intval($quantity) * intval($get('numberOfDays')));
                                        })
                                        ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                            $checkIn = Carbon::parse($get('check_in'))->format('Y-m-d');
                                            $checkOut = Carbon::parse($get('check_out'))->format('Y-m-d');

                                            $checkIn1 = Carbon::parse($checkIn);
                                            $checkOut1 = Carbon::parse($checkOut);
                                            $rent = $get('rent');
                                            $quantity = $get('quantity');
                                            $difference = $checkIn1->diffInDays($checkOut1);
                                            $set('numberOfDays', $difference);
                                            $set('subTotal', intval($rent) * intval($quantity) * intval($difference));
                                            self::updateTotals($get, $set);
                                        })
                                        // ->afterStateUpdated(fn ($get, $set, $state) =>
                                        // $set('numberOfDays', intval(Carbon::parse($get('check_in'))->diffInDays(Carbon::parse($state)))))
                                        ->required(),

                                    Forms\Components\TextInput::make('numberOfDays')
                                        ->label('Number of Days')
                                        ->live()
                                        ->dehydrated(false)
                                        ->disabled(),

                                    Forms\Components\TextInput::make('quantity')
                                        ->live(debounce: 1000)
                                        ->numeric()
                                        ->rules('min:1')
                                        ->required()
                                        ->afterStateUpdated(fn ($get, $set, $state) => $set('subTotal', intval($get('rent')) * intval($get('quantity')) * intval($get('numberOfDays'))))
                                        ->required(),

                                    Forms\Components\TextInput::make('rent')
                                        ->live(debounce: 1000)
                                        ->integer()
                                        ->afterStateUpdated(
                                            function ($get, $set, $state) {
                                                $set('subTotal', intval($get('quantity')) * intval($state) * intval($get('numberOfDays')));
                                                self::updateTotals($get, $set);
                                            }
                                        )
                                        ->required(),

                                    TextInput::make('subTotal')
                                        ->live()
                                        ->dehydrated(false)
                                        ->disabled()
                                        ->required(),
                                ])
                                // Repeatable field is live so that it will trigger the state update on each change
                                ->live()
                                // After adding a new row, we need to update the totals
                                ->afterStateUpdated(function (Get $get, Set $set) {
                                    self::updateTotals($get, $set);
                                })
                                // After deleting a row, we need to update the totals
                                ->deleteAction(
                                    fn (Action $action) => $action->after(fn (Get $get, Set $set) => self::updateTotals($get, $set)),
                                )
                                // Disable reordering
                                ->reorderable(false)
                                ->columns(2),

                        ])->columnSpan(1)
                        ->collapsible(),
                ]),

            ])->columns(2);
    }

    public static function findCustomer($state)
    {
        return Customer::where('phone', $state)->first();
    }

    public static function findRoomRent($state)
    {
        return RoomType::where('id', $state)->first();
    }

    public static function updateTotals(Get $get, Set $set): void
    {
        $lowestCheckInDate = null;
        $lowestCheckInRecord = null;
        $totalRenst = collect($get('Rooms'));
        foreach ($totalRenst as $key => $value) {
            $checkInDate = Carbon::parse($value['check_in']);
            if ($lowestCheckInDate === null || $checkInDate->lt($lowestCheckInDate)) {
                $lowestCheckInDate = $checkInDate;
                $lowestCheckInRecord = $value['check_in'];
            }
        }

        $highestCheckOutDate = null;
        $highestCheckOutRecord = null;

        foreach ($totalRenst as $key => $value) {
            $checkOutDate = Carbon::parse($value['check_out']);
            if ($highestCheckOutDate === null || $checkOutDate->gt($highestCheckOutDate)) {
                $highestCheckOutDate = $checkOutDate;
                $highestCheckOutRecord = $value['check_out'];
            }
        }

        // dd($lowestCheckInRecord);
        // dd(collect($get('Rooms')));
        $totalRent = collect($get('Rooms'))->sum(function ($item) {
            $checkIn = Carbon::parse($item['check_in'])->format('Y-m-d');
            $checkOut = Carbon::parse($item['check_out'])->format('Y-m-d');
            $checkIn1 = Carbon::parse($checkIn);
            $checkOut1 = Carbon::parse($checkOut);
            $difference = $checkIn1->diffInDays($checkOut1);

            return intval($item['rent']) * intval($item['quantity']) * intval($difference);
        });
        // dd($lowestCheckInDate);
        $set('check_in_date', $lowestCheckInRecord);
        $set('check_out_date', $highestCheckOutRecord);

        $set('total_rent', $totalRent);
    }

    public static function table(Table $table): Table
    {

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('reservation_no')
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('booking_date')
                    ->date()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->searchable()
                    ->label('Name')
                    ->numeric(),
                Tables\Columns\TextColumn::make('customer.phone')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->searchable()
                    ->label('Phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->searchable()
                    ->label('Booked By')
                    ->numeric(),
                Tables\Columns\TextColumn::make('check_in_date')
                    ->searchable()
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('check_out_date')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('rooms_sum_quantity')->sum('rooms', 'quantity')->label('Total Room'),

                Tables\Columns\TextColumn::make('status')
                    ->color(fn (string $state): string => match ($state) {
                        'arrived' => 'success',
                        'cancelled' => 'warning',
                        'confirm' => 'info',
                        'rejected' => 'danger',
                    })->badge(fn (string $state): string => match ($state) {
                        'arrived' => 'success',
                        'cancelled' => 'warning',
                        'confirm' => 'info',
                        'rejected' => 'danger',
                    }),
                //                Tables\Columns\TextColumn::make('status'),
            ])->defaultSort('id', 'desc')
            ->filters([
                Filter::make('check_in_date')
                    ->form([
                        DatePicker::make('check_in_date')->native(false),
                        // DatePicker::make('created_until')->native(false),
                        Forms\Components\Select::make('status')->columnSpan(1)
                            ->options(Status::class),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['check_in_date'],
                                fn (Builder $query, $date): Builder => $query->whereDate('check_in_date', '=', $date),
                            )
                            ->when(
                                // dd($data['status']),
                                $data['status'],
                                fn (Builder $query, $date): Builder => $query->where('status', '=', $date),
                            );
                    })->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['check_in_date'] ?? null) {
                            $indicators[] = Indicator::make('check_in_date '.Carbon::parse($data['check_in_date'])->toFormattedDateString())
                                ->removeField('check_in_date');
                        }

                        if ($data['status'] ?? null) {
                            $indicators[] = Indicator::make('Status is '.$data['status'])
                                ->removeField('status');
                        }

                        return $indicators;
                    }),

            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    // Action::make('viewInfo')->label('View Info')->icon('heroicon-o-eye'),
                    // ViewAction::make('viewInfo')->icon('heroicon-o-eye'),
                    Tables\Actions\Action::make('viewInfo')
                        ->label('View Info')
                        ->icon('heroicon-o-eye')
                        ->action(function (Reservation $record) {
                            return redirect()->to(ReservationResource::getUrl('viewInfo', [$record]));
                        }),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\Action::make('Edit Status')
                        ->icon('heroicon-o-pencil')
                        ->form([
                            Forms\Components\Select::make('status')
                                ->options(Status::class)
                                ->default(function (Reservation $record) {
                                    $status = null;

                                    if ($record->status == 'confirm') {
                                        $status = Status::Confirm->value;
                                    } elseif ($record->status == 'arrived') {
                                        $status = Status::Arrived->value;
                                    } elseif ($record->status == 'cancelled') {
                                        $status = Status::Cancelled->value;
                                    }

                                    return $status;
                                })
                                ->required(),
                        ])->action(function (Reservation $record, array $data) {
                            $record->status = $data['status'];
                            $record->save();

                            Notification::make()
                                ->title('status updated successfully')
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\DeleteAction::make()->visible(fn (Reservation $record): bool => auth()->user()->email == 'mdnur701@gmail.com'),

                ]),
                Tables\Actions\Action::make('send confirmation')
                    ->color('info')
                    ->button()
                    ->icon('heroicon-o-envelope-open')
                    ->action(function (Reservation $record) {
                        $reservation = Reservation::whereId($record->id)->with('customer')->with('rooms')->with('user')->with('payment')->first();

                        self::sendConfirmation($reservation);
                    })->visible(function (Reservation $record) {
                        return $record->confrim_message_sent_at == null;
                    })->requiresConfirmation(),
                Tables\Actions\Action::make('Resend confirmation')
                    ->color('danger')
                    ->button()
                    ->icon('heroicon-o-envelope-open')
                    ->action(function (Reservation $record) {
                        $reservation = Reservation::whereId($record->id)->with('customer')->with('rooms')->with('user')->with('payment')->first();
                        self::sendConfirmation($reservation, 'Re-');
                    })->visible(function (Reservation $record) {
                        return $record->confrim_message_sent_at != null;
                    })->requiresConfirmation(),
                // ...
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ])->recordAction(Tables\Actions\ViewAction::class);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PaymentRelationManager::class,
            RelationManagers\CustomerRelationManager::class,
            RelationManagers\RoomsRelationManager::class,
        ];
    }

    // This function updates totals based on the selected products and quantities

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReservations::route('/'),
            'create' => Pages\CreateReservation::route('/create'),
            'edit' => Pages\EditReservation::route('/{record}/edit'),
            'viewInfo' => Pages\ViewReservationInfo::route('/{record}/viewInfo'),
        ];
    }

    public static function sendConfirmation($reservation, $type = '')
    {
        $message = $type."Confirmation Message from Hotel Amin International.\n".
            'Reservation No: '.$reservation->reservation_no."\n".
            'Booking Date: '.Carbon::parse($reservation->booking_date)->format('Y-m-d')."\n".
            'Name: '.$reservation->customer->name."\n".
            'Mobile Number: '.$reservation->customer->phone."\n".
            'Address: '.$reservation->customer->address."\n";
        $message .= 'Room Type: ';
        $sum = 0;
        foreach ($reservation->rooms as $room) {
            $sum += $room->quantity;
            $message .= RoomType::find($room->room_type_id)->name.'('.$room->quantity.')';
        }
        $message .= "\nTotal room: ".$sum."\n";
        $message .= 'Check-in: '.Carbon::parse($reservation->check_in_date)->format('Y-m-d').' ('.\App\Models\HotelSetting::find(1)->description.")\n".
            'Check-out: '.Carbon::parse($reservation->check_out_date)->format('Y-m-d').' ('.\App\Models\HotelSetting::find(2)->description.")\n";

        $rent = '';
        foreach ($reservation->rooms as $room) {
            $rent .= $room['rent'].' / ';
        }
        $message .= 'Room price: '.rtrim($rent, ' / ')." Taka\n";
        $message .= 'Total price: '.$reservation->total_rent." Taka\n";
        $message .= 'Advance: '.$reservation->payment->advance." Taka\n";
        $message .= 'Due: '.$reservation->total_rent - $reservation->payment->advance." Taka\n";
        $message .= '('.\App\Models\PaymentType::find($reservation->payment->payment_type_id)->name.')'.'Last 3 Digits:'.$reservation->payment->Last3Digit."\n";
        $message .= 'Booked By: '.$reservation->user->name."\n";
        if ($reservation->reference != null) {
            $message .= 'Reference by: '.$reservation->reference."\n";
        }
        $message .= 'Note: '.\App\Models\HotelSetting::find(3)->description."\n";
        $message .= 'Our Cancellation Policy:'.\App\Models\HotelSetting::find(4)->description;

        $sms = new BDBulkSms($reservation->customer->phone, $message);
        if ($sms->send()) {
            $reservation->confrim_message_sent_at = Carbon::now();
            $reservation->save();
            Notification::make()
                ->title($type.'Confirmation Message sent successfully')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title($type.'Confirmation Message sent failed')
                ->error()
                ->send();
        }
    }
}
