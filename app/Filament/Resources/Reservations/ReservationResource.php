<?php

namespace App\Filament\Resources\Reservations;

use App\Filament\Resources\Reservations\ReservationResource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\Reservations\RelationManagers\PaymentRelationManager;
use App\Filament\Resources\Reservations\RelationManagers\CustomerRelationManager;
use App\Filament\Resources\Reservations\RelationManagers\ReservationRoomsRelationManager;
use App\Filament\Resources\Reservations\Pages\ListReservations;
use App\Filament\Resources\Reservations\Pages\CreateReservation;
use App\Filament\Resources\Reservations\Pages\EditReservation;
use App\Filament\Resources\Reservations\Pages\ViewReservationInfo;
use App\Models\HotelSetting;
use App\Classes\BDBulkSms;
use App\Enums\Status;
use App\Filament\Resources\ReservationResource\Pages;
use App\Filament\Resources\ReservationResource\RelationManagers;
use App\Models\Customer;
use App\Models\PaymentType;
use App\Models\Reservation;
use App\Models\RoomType;
use App\Models\User;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ReservationResource extends Resource
{
    protected static ?string $model = Reservation::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Reservation Information')
                    ->description('Make Reservation')
                    ->schema([
                        TextInput::make('reservation_no')
                            ->label('Reservation No')
                            ->unique(ignoreRecord: true)->columnSpan(2)
                            ->required()
                            ->default(
                                optional(Reservation::latest('created_at')->first())->reservation_no
                                    ? Reservation::latest('created_at')->first()->reservation_no + 1 : null)
                            ->numeric(),
                        DatePicker::make('booking_date')
                            ->label('Book Date')
                            ->native(false)->columnSpan(2)
                            ->default(today())
                            ->readonly()
                            ->live()
                            ->required(),
                        DatePicker::make('check_in_date')
                            ->label('Check In Date')
                            ->minDate(today())
                            ->afterOrEqual('today')->columnSpan(1)
                            ->native(false)
                            ->minDate(Carbon::today())
                            ->live()
                            ->required(),
                        DatePicker::make('check_out_date')
                            ->label('Check Out Date')
                            ->native(false)->columnSpan(1)
                            ->live()
                            ->minDate(Carbon::tomorrow())
                            ->after('today')
                            ->required(),
                        TextInput::make('total_rent')
                            ->label('Total Rent')
                            ->live()->columnSpan(1)
                            ->required()
                            ->numeric(),

                        Select::make('status')->columnSpan(1)
                            ->default(Status::Confirm)
                            ->options(Status::class),

                        TextInput::make('note')->columnSpan(1)
                            ->maxLength(255),
                        TextInput::make('reference')->columnSpan(1)
                            ->maxLength(255),
                    ])->columnSpan(1),

                Group::make()->schema([

                    Section::make('Customer')->columnSpan(1)
                        ->relationship('customer')
                        ->schema([
                            TextInput::make('name')
                                ->label('Customer Name')
                                ->live()
                                ->required(),
                            TextInput::make('phone')
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

                            TextInput::make('address')
                                ->label('Address'),
                            TextInput::make('email')
                                ->label('Customer Email'),
                            TextInput::make('document')
                                ->label('Customer Document Id'),

                        ])->collapsible()->columns(2),

                    Section::make('Payments')
                        ->schema([
                            Repeater::make('payments')
                                ->relationship('payments') // THIS is where the morphMany goes
                                ->schema([
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
                                    TextInput::make('tnx'),
                                ])
                                ->columns(2),
                        ])->collapsible()->columnSpan(1), // No ->relationship() on Section

                    Section::make('Assigning Room')
                        ->schema([
                            Repeater::make('reservationRooms')
                                ->cloneable()
                                ->relationship('reservationRooms')
                                ->schema([
                                    Select::make('room_type_id')
                                        ->label('Room Type')
                                        ->columnSpan(2)
                                        ->live()
                                        ->options(RoomType::pluck('name', 'id'))
                                        ->afterStateUpdated(fn (Set $set, ?string $state) => $set('rent', self::findRoomRent($state) ? self::findRoomRent($state)->room_rent : null))
                                        ->required(),
                                    DatePicker::make('check_in')
                                        ->live()
                                        ->minDate(Carbon::today())
                                        ->afterOrEqual('Today')
                                        ->native(false)
                                        ->required(),

                                    DatePicker::make('check_out')
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

                                    TextInput::make('numberOfDays')
                                        ->label('Number of Days')
                                        ->live()
                                        ->dehydrated(false)
                                        ->disabled(),

                                    TextInput::make('quantity')
                                        ->live(debounce: 1000)
                                        ->numeric()
                                        ->rules('min:1')
                                        ->required()
                                        ->afterStateUpdated(fn ($get, $set, $state) => $set('subTotal', intval($get('rent')) * intval($get('quantity')) * intval($get('numberOfDays'))))
                                        ->required(),

                                    TextInput::make('rent')
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
        $totalRenst = collect($get('reservationRooms'));
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
        // dd(collect($get('reservationRooms')));
        $totalRent = collect($get('reservationRooms'))->sum(function ($item) {
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
                TextColumn::make('id')
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('reservation_no')
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('booking_date')
                    ->date()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('customer.name')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->searchable()
                    ->label('Name')
                    ->numeric(),
                TextColumn::make('customer.phone')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->searchable()
                    ->label('Phone')
                    ->searchable(),
                TextColumn::make('user.name')
                    ->searchable()
                    ->label('Booked By')
                    ->numeric(),
                TextColumn::make('check_in_date')
                    ->searchable()
                    ->date()
                    ->sortable(),
                TextColumn::make('check_out_date')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('rooms_sum_quantity')->sum('reservationRooms', 'quantity')->label('Total Room'),

                TextColumn::make('status')
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
                    ->schema([
                        DatePicker::make('check_in_date')->native(false),
                        // DatePicker::make('created_until')->native(false),
                        Select::make('status')->columnSpan(1)
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
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    // Action::make('viewInfo')->label('View Info')->icon('heroicon-o-eye'),
                    // ViewAction::make('viewInfo')->icon('heroicon-o-eye'),
                    Action::make('viewInfo')
                        ->label('View Info')
                        ->icon('heroicon-o-eye')
                        ->action(function (Reservation $record) {
                            return redirect()->to(ReservationResource::getUrl('viewInfo', [$record]));
                        }),
                    EditAction::make(),
                    Action::make('Edit Status')
                        ->icon('heroicon-o-pencil')
                        ->schema([
                            Select::make('status')
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
                    DeleteAction::make()->visible(fn (Reservation $record): bool => auth()->user()->email == 'mdnur701@gmail.com'),

                ]),
                Action::make('send confirmation')
                    ->color('info')
                    ->button()
                    ->icon('heroicon-o-envelope-open')
                    ->action(function (Reservation $record) {
                        $reservation = Reservation::whereId($record->id)->with('customer')->with('reservationRooms')->with('user')->with('payments')->first();

                        self::sendConfirmation($reservation);
                    })->visible(function (Reservation $record) {
                        return $record->confrim_message_sent_at == null;
                    })->requiresConfirmation(),
                Action::make('Resend confirmation')
                    ->color('danger')
                    ->button()
                    ->icon('heroicon-o-envelope-open')
                    ->action(function (Reservation $record) {
                        $reservation = Reservation::whereId($record->id)->with('customer')->with('reservationRooms')->with('user')->with('payments')->first();
                        self::sendConfirmation($reservation, 'Re-');
                    })->visible(function (Reservation $record) {
                        return $record->confrim_message_sent_at != null;
                    })->requiresConfirmation(),
                // ...
            ])
            ->toolbarActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ])->recordAction(ViewAction::class);
    }

    public static function getRelations(): array
    {
        return [
            PaymentRelationManager::class,
            CustomerRelationManager::class,
            // RelationManagers\RoomsRelationManager::class,
            ReservationRoomsRelationManager::class,

        ];
    }

    // This function updates totals based on the selected products and quantities

    public static function getPages(): array
    {
        return [
            'index' => ListReservations::route('/'),
            'create' => CreateReservation::route('/create'),
            'edit' => EditReservation::route('/{record}/edit'),
            'viewInfo' => ViewReservationInfo::route('/{record}/viewInfo'),
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
        foreach ($reservation->reservation as $room) {
            $sum += $room->quantity;
            $message .= RoomType::find($room->room_type_id)->name.'('.$room->quantity.') ';
        }
        $message .= "\nTotal room: ".$sum."\n";

        $message .= 'Check-in: '.Carbon::parse($reservation->check_in_date)->format('Y-m-d').' ('.HotelSetting::find(1)->description.")\n".
            'Check-out: '.Carbon::parse($reservation->check_out_date)->format('Y-m-d').' ('.HotelSetting::find(2)->description.")\n";

        $rent = '';
        foreach ($reservation->reservation as $room) {
            $rent .= $room['rent'].' / ';
        }
        $message .= 'Room price: '.rtrim($rent, ' / ')." Taka\n";
        $message .= 'Total price: '.$reservation->total_rent." Taka\n";

        // 🔁 Loop through all payments
        $totalAdvance = 0;
        $index = 1;
        foreach ($reservation->payments as $payment) {
            $paymentType = PaymentType::find($payment->payment_type_id)?->name ?? 'N/A';
            if ($reservation->payments->count() > 1) {
                $message .= 'Advance '.$index++.': '.$payment->amount.' Taka by '.$paymentType;
                if ($payment->tnx) {
                    $message .= ' ('.$payment->tnx.')';
                }
                $message .= "\n";

            }
            $totalAdvance += $payment->amount;
        }
        // ;
        // dd(\App\Models\PaymentType::find($reservation->payments->first()->payment_type_id)?->name);
        if ($reservation->payments->count() > 1) {
            $message .= 'Total Advance: '.$totalAdvance." Taka\n";
        } else {
            $firstPayment = $reservation->payments->first();
            $paymentTypeName = PaymentType::find($firstPayment->payment_type_id)?->name ?? 'N/A';

            $message .= 'Total Advance: '.$totalAdvance.' Taka by '.$paymentTypeName;

            if (! empty($firstPayment->tnx)) {
                $message .= ' ('.$firstPayment->tnx.')';
            }

            $message .= "\n";
        }
        // $message .= ' by '.$reservation->payments()->first()->
        $message .= 'Due: '.($reservation->total_rent - $totalAdvance)."Taka\n";

        $message .= 'Booked By: '.$reservation->user->name."\n";
        if ($reservation->reference != null) {
            $message .= 'Reference by: '.$reservation->reference."\n";
        }

        $message .= 'Note: '.HotelSetting::find(3)->description."\n";
        $message .= 'Our Cancellation Policy: '.HotelSetting::find(4)->description;

        // dd($message);
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
