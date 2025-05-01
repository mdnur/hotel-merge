<?php

namespace App\Filament\Resources\CardResource\RelationManagers;

use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class CardRoomsRelationManager extends RelationManager
{
    protected static string $relationship = 'cardRooms';

    protected $listeners = ['refreshCardRoomsTable' => '$refresh']; // 👈 ADD this

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('room_id')
                ->label('Room No')
                ->options(Room::all()->pluck('room_no', 'id'))
                ->searchable()
                ->required(),
            Forms\Components\TextInput::make('rent')->required()->numeric(),
            Forms\Components\DateTimePicker::make('check_in')->native(false)->default(now())->required()->maxDate(now()),
            Forms\Components\DateTimePicker::make('check_out')
                ->label('Check Out Time')
                ->native(false)
                ->required()
                ->default(Carbon::parse(Carbon::now()->toDateString())->addDay(1)->addHours(10)->addMinute(59)),
            Forms\Components\TextInput::make('note')->maxLength(255),
            Forms\Components\Select::make('user_id')
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
            ->columns([Tables\Columns\TextColumn::make('card.card_no')->numeric()->sortable(), Tables\Columns\TextColumn::make('room.room_no')->numeric()->sortable(), Tables\Columns\TextColumn::make('check_in')->date()->sortable(), Tables\Columns\TextColumn::make('check_out')->date()->sortable(), Tables\Columns\TextColumn::make('rent')->numeric()->sortable(), Tables\Columns\TextColumn::make('user.name')->label('Room allocate by')->numeric()->sortable()])
            ->filters([
                //
            ])
            ->headerActions([Tables\Actions\CreateAction::make()->createAnother(false)->after(function () {
                $this->dispatch('refreshParent');
            })])
            ->actions([

                Tables\Actions\EditAction::make()
                    ->after(function () {
                        $this->dispatch('refreshParent');

                        // Update parent record (Card)
                        $this->ownerRecord->update([
                            'departure_date' => null, // Example: update timestamp or any field
                            'check_out_made_by' => null,
                        ]);
                        // $this->dispatch('refreshParent');
                    }),
                Tables\Actions\Action::make('Check Out')
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
                Tables\Actions\ReplicateAction::make()
                    ->form([
                        Forms\Components\Select::make('room_id')
                            ->label('Select Room to Assign')
                            ->options(Room::all()->pluck('room_no', 'id')) // Fetch available rooms
                            ->searchable()
                            ->required(),
                        Forms\Components\TextInput::make('rent')
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
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()]),
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('Check Out')->color('primary')
                        ->outlined()
                        ->badgeColor('primary')
                        ->icon('heroicon-o-pencil')
                        ->form([
                            Forms\Components\DateTimePicker::make('check_out')
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
                    Tables\Actions\BulkAction::make('Check In')
                        ->color('success')
                        ->outlined()
                        ->badgeColor('primary')
                        ->icon('heroicon-o-pencil')
                        ->form([
                            Forms\Components\DateTimePicker::make('check_in')
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
