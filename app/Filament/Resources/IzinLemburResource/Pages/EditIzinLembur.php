<?php

namespace App\Filament\Resources\IzinLemburResource\Pages;

use Carbon\Carbon;
use Filament\Actions;
use Illuminate\Support\Facades\Auth;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\IzinLemburResource;

class EditIzinLembur extends EditRecord
{
    protected static string $resource = IzinLemburResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['user_id'] = Auth::user()->id;

        // Parsing waktu mulai dan selesai
        $jamMulai = Carbon::parse($data['start_time']);
        $sampaiJam = Carbon::parse($data['end_time']);

        // If the end time is earlier than the start time, it means the time has crossed midnight
        if ($sampaiJam->lessThan($jamMulai)) {
            $sampaiJam->addDay(); // Add a day to the end time
        }

        // Menghitung selisih waktu dalam jam
        $diffInHours = $jamMulai->diffInHours($sampaiJam);

        $data['lama_lembur'] = (int)$diffInHours;
        $data['total'] = 0;

        return $data;
    }
}
