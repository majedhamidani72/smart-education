<?php

namespace App\Filament\Resources\PurchaseResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'آیتم‌های خرید';

    // نکته: برخلاف Resource، متد canCreate() توی RelationManager
    // غیر-static است — همون چیزی که باعث خطای Fatal Error شد.
    public function canCreate(): bool
    {
        return false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([

                Tables\Columns\TextColumn::make('title')
                    ->label('عنوان'),

                Tables\Columns\TextColumn::make('item_type')
                    ->label('نوع')
                    ->formatStateUsing(fn($state) => match ($state) {
                        'book' => 'کتاب',
                        'lesson' => 'درس',
                        'subscription' => 'اشتراک',
                        'package' => 'بسته',
                        'quiz' => 'آزمون',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('price')
                    ->label('قیمت')
                    ->formatStateUsing(fn($state) => number_format($state).' تومان'),

                Tables\Columns\TextColumn::make('discount_amount')
                    ->label('تخفیف')
                    ->formatStateUsing(fn($state) => number_format($state).' تومان'),

                Tables\Columns\TextColumn::make('final_price')
                    ->label('قیمت نهایی')
                    ->formatStateUsing(fn($state) => number_format($state).' تومان'),

                Tables\Columns\TextColumn::make('quantity')
                    ->label('تعداد'),

            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
    }
}
