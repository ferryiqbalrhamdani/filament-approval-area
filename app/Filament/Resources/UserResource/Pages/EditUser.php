<?php

namespace App\Filament\Resources\UserResource\Pages;

use Filament\Actions;
use Illuminate\Support\Facades\Session;
use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getRedirectUrl(): string
    {
        // Ambil URL sebelumnya dari session
        $previousUrl = Session::get('previous_url', $this->getResource()::getUrl('index'));

        // Hapus URL dari session setelah digunakan
        Session::forget('previous_url');

        return $previousUrl;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Lakukan modifikasi data sebelum menyimpan, jika diperlukan
        return $data;
    }
}
