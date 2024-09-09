<?php

namespace App\Filament\Clusters\General\Pages;

use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Livewire\Attributes\Locked;
use App\Filament\Clusters\General;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Support\Exceptions\Halt;
use Filament\Forms\Components\Section;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use Illuminate\Auth\Access\AuthorizationException;
use Filament\Pages\Concerns\InteractsWithFormActions;
use App\Models\PengaturanCuti as ModelsPengaturanCuti;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

use function Filament\authorize;

class PengaturanCuti extends Page
{
    use InteractsWithFormActions, HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.clusters.general.pages.pengaturan-cuti';

    protected static ?string $cluster = General::class;

    public ?array $data = [];

    #[Locked]
    public ?ModelsPengaturanCuti $record = null;

    public function mount(): void
    {
        $this->record = ModelsPengaturanCuti::firstOrNew();

        abort_unless(static::canView($this->record), 404);

        $this->fillForm();
    }

    public function fillForm(): void
    {
        $data = $this->record->attributesToArray();

        $this->form->fill($data);
    }


    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Detail Pengaturan Cuti')
                    ->schema([
                        TextInput::make('defualt_cuti')
                            ->integer()
                            ->default(6)
                            ->required(),
                        DatePicker::make('tanggal_reset')
                            ->timezone('Asia/Jakarta')
                            ->required(),
                        Radio::make('reset_cuti')
                            ->options([
                                'perminggu' => 'Perminggu',
                                'perbulan' => 'Perbulan',
                                'pertahun' => 'Pertahun',
                            ])
                            ->required()
                            ->columns(3),

                    ])->columns(2),
            ])
            ->model($this->record)
            ->statePath('data')
            ->operation('edit');
    }

    public function save(): void
    {
        try {
            $data = $this->form->getState();
            // dd($data);
            $this->handleRecordUpdate($this->record, $data);
        } catch (Halt $exception) {
            return;
        }

        $this->getSavedNotification()->send();
    }

    protected function handleRecordUpdate($record, array $data): ModelsPengaturanCuti
    {
        if ($record) {
            $record->fill($data);

            $record->save();
        } else {

            $record = ModelsPengaturanCuti::create($data);
        }

        return $record;
    }

    protected function getSavedNotification(): Notification
    {
        return Notification::make()
            ->success()
            ->title(__('filament-panels::resources/pages/edit-record.notifications.saved.title'));
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction(),
        ];
    }

    protected function getSaveFormAction(): Action
    {
        return Action::make('save')
            ->label(__('filament-panels::resources/pages/edit-record.form.actions.save.label'))
            ->submit('save')
            ->keyBindings(['mod+s']);
    }

    public static function canView(Model $record): bool
    {
        try {
            return authorize('update', $record)->allowed();
        } catch (AuthorizationException $exception) {
            return $exception->toResponse()->allowed();
        }
    }
}
