<?php

namespace App\Filament\Resources\Cards\RelationManagers;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DateTimePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\CreateAction;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\ReplicateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\BulkAction;
use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class CardRoomsRelationManager extends RelationManager
{
    protected static string $relationship = 'cardRooms';

    protected $listeners = ['refreshCardRoomsTable' => '$refresh']; // 👈 ADD this

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('room_id')
                ->label('Room No')
                ->options(Room::all()->pluck('room_no', 'id'))
                ->searchable()
                ->required(),
            TextInput::make('rent')->required()->numeric(),
            DateTimePicker::make('check_in')->native(false)->default(now())->required(), // ->maxDate(now())
            DateTimePicker::make('check_out')
                ->label('Check Out Time')
                ->native(false)
                ->required()
                ->default(Carbon::parse(Carbon::now()->toDateString())->addDay(1)->addHours(10)->addMinute(59)),
            TextInput::make('note')->maxLength(255),
            Select::make('user_id')
                ->label('Room allocated by')
                ->default(auth()->user()->id)
                ->options(User::all()->pluck('name', 'id'))
                ->disabled() // Make the field read-only
                ->dehydrated(true), // Ensure it doesn't get saved back to the database
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('card_id')
            // ->livewire() // 👈 important
            ->columns([
                TextColumn::make('card.card_no')->numeric()->sortable(),
                TextColumn::make('room.room_no')->numeric()->sortable()->searchable(),
                TextColumn::make('check_in')->date()->sortable(),
                TextColumn::make('check_out')->date()->sortable(),
                TextColumn::make('rent')->numeric()->sortable(),
                TextColumn::make('user.name')->label('Room allocate by')->numeric()->sortable(),
            ])->searchable()
            ->filters([
                //
            ])
            ->headerActions([CreateAction::make()->createAnother(false)->after(function () {
                $this->dispatch('refreshParent');
            }),
                Action::make('addMultipleRooms')
                    ->label('Add Multiple Rooms')
                    ->icon('heroicon-o-plus-circle')
                    ->color('primary')
                    ->schema([
                        Select::make('room_ids')
                            ->label('Select Rooms')
                            ->multiple()
                            ->searchable()
                            ->options(
                                Room::query()->pluck('room_no', 'id')
                            )
                            ->required(),

                        TextInput::make('rent')
                            ->label('Room Rent (Same for all)')
                            ->numeric()
                            ->required(),

                        DateTimePicker::make('check_in')
                            ->native(false)
                            ->default(now())
                            ->required(),

                        DateTimePicker::make('check_out')
                            ->native(false)
                            ->default(
                                Carbon::today()->addDay()->setTime(10, 59)
                            )
                            ->required(),

                        Textarea::make('note')
                            ->maxLength(255),
                    ])
                    ->action(function (array $data) {
                        foreach ($data['room_ids'] as $roomId) {
                            $this->ownerRecord->cardRooms()->create([
                                'room_id' => $roomId,
                                'rent' => $data['rent'],
                                'check_in' => $data['check_in'],
                                'check_out' => $data['check_out'],
                                'note' => $data['note'] ?? null,
                                'user_id' => auth()->id(),
                            ]);
                        }

                        // Reset parent card checkout info
                        $this->ownerRecord->update([
                            'departure_date' => null,
                            'check_out_made_by' => null,
                        ]);

                        $this->dispatch('refreshParent');
                        $this->dispatch('refreshCardRoomsTable');

                        Notification::make()
                            ->title('Multiple rooms added successfully')
                            ->success()
                            ->send();
                    }),
            ]
            )
            ->recordActions([

                EditAction::make()
                    ->after(function () {
                        $this->dispatch('refreshParent');

                        // Update parent record (Card)
                        $this->ownerRecord->update([
                            'departure_date' => null, // Example: update timestamp or any field
                            'check_out_made_by' => null,
                        ]);
                        // $this->dispatch('refreshParent');
                    }),

                DeleteAction::make()
                    ->after(function () {
                        $this->dispatch('refreshParent');

                        // Update parent record (Card)
                        $this->ownerRecord->update([
                            'departure_date' => null, // Example: update timestamp or any field
                            'check_out_made_by' => null,
                        ]);
                        // $this->dispatch('refreshParent');
                    }),
                Action::make('Check Out')
                    ->after(function () {
                        $this->dispatch('refreshParent');
                        // $this->dispatch('refreshParent');
                    })->color('info')->action(function ($record) {
                        // dd($record->room->room_no);
                        $record->update([
                            'check_out' => now(),
                        ]);
                        $record->refresh();

                        $this->dispatch('refreshParent', $record);
                        $this->dispatch('refreshCardRoomsTable'); // refresh child table too

                        Notification::make()
                            ->title($record->room->room_no.'Check Out made successfully. ')
                            ->success()
                            ->send();
                    }),

                // Tables\Actions\EditAction::make(),
                ReplicateAction::make()
                    ->schema([
                        Select::make('room_id')
                            ->label('Select Room to Assign')
                            ->options(Room::all()->pluck('room_no', 'id')) // Fetch available rooms
                            ->searchable()
                            ->required(),
                        TextInput::make('rent')
                            ->label('Room Rent')
                            ->required(),
                    ])
                    ->beforeReplicaSaved(function ($replica) {
                        // Clone the original record
                        $newRecord = $replica->replicate();

                        // Modify the necessary fields
                        $newRecord->room_id = $replica->room_id;

                        // Save the new record
                        // $newRecord->save();

                        return $newRecord;
                    })->after(function () {
                        $this->dispatch('refreshParent');

                        // Update parent record (Card)
                        $this->ownerRecord->update([
                            'departure_date' => null, // Example: update timestamp or any field
                            'check_out_made_by' => null,
                        ]);
                        // $this->dispatch('refreshParent');
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
                BulkActionGroup::make([
                    BulkAction::make('Check Out')->color('primary')
                        ->outlined()
                        ->badgeColor('primary')
                        ->icon('heroicon-o-pencil')
                        ->form([
                            DateTimePicker::make('check_out')
                                ->native(false)
                                ->default(now())
                                ->required()
                                ->maxDate(now())
                                ->required(),
                        ])->action(function (Collection $record, array $data) {
                            foreach ($record as $rec) {
                                $rec->update([
                                    'check_out' => $data['check_out'],
                                ]);
                            }
                            $this->dispatch('refreshParent');

                            Notification::make()
                                ->title('Check In date updated successfully')
                                ->success()
                                ->send();
                        }),
                    BulkAction::make('Check In')
                        ->color('success')
                        ->outlined()
                        ->badgeColor('primary')
                        ->icon('heroicon-o-pencil')
                        ->form([
                            DateTimePicker::make('check_in')
                                ->native(false)
                                ->default(now())
                                ->required()
                                ->maxDate(now())
                                ->required(),
                        ])->action(function (Collection $record, array $data) {
                            foreach ($record as $rec) {
                                $rec->update([
                                    'check_in' => $data['check_in'],
                                ]);
                            }
                            $this->dispatch('refreshParent');

                            Notification::make()
                                ->title('Check In date updated successfully')
                                ->success()
                                ->send();
                        }),
                ])->label('Check In/Out'),

            ]);
    }
}
