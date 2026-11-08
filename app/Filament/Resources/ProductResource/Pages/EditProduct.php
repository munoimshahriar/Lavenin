<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Concerns\TranslatableForm;
use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    use TranslatableForm;

    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
