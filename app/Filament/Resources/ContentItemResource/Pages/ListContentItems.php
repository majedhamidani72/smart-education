<?php

namespace App\Filament\Resources\ContentItemResource\Pages;

use App\Filament\Resources\ContentItemResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListContentItems extends ListRecords
{
    protected static string $resource = ContentItemResource::class;

    protected function getHeaderActions(): array
    {
        return [

            Actions\CreateAction::make()

                ->label('ایجاد محتوای آموزشی'),

        ];
    }

    public function getTabs(): array
    {
        return [

            'all' => Tab::make('همه')

                ->badge(
                    static::getResource()::getModel()::count()
                ),

            'pending' => Tab::make('در انتظار بررسی')

                ->badge(
                    static::getResource()::getModel()::where(
                        'status',
                        'pending'
                    )->count()
                )

                ->modifyQueryUsing(

                    fn (Builder $query) =>

                    $query->where(
                        'status',
                        'pending'
                    )

                ),

            'approved' => Tab::make('تأیید شده')

                ->badge(
                    static::getResource()::getModel()::where(
                        'status',
                        'approved'
                    )->count()
                )

                ->modifyQueryUsing(

                    fn (Builder $query) =>

                    $query->where(
                        'status',
                        'approved'
                    )

                ),

            'rejected' => Tab::make('رد شده')

                ->badge(
                    static::getResource()::getModel()::where(
                        'status',
                        'rejected'
                    )->count()
                )

                ->modifyQueryUsing(

                    fn (Builder $query) =>

                    $query->where(
                        'status',
                        'rejected'
                    )

                ),

            'published' => Tab::make('منتشر شده')

                ->badge(
                    static::getResource()::getModel()::where(
                        'status',
                        'published'
                    )->count()
                )

                ->modifyQueryUsing(

                    fn (Builder $query) =>

                    $query->where(
                        'status',
                        'published'
                    )

                ),

        ];
    }
}
