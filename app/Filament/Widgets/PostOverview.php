<?php

namespace App\Filament\Widgets;

use App\Models\Post;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PostOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Posts', Post::count())
                ->color('primary')
                ->icon('heroicon-o-document-text')
                ->description('Total number of posts in the system.'),
            Stat::make('Published Posts', Post::where('is_published', 1)->count())
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->description('Number of posts that are currently published.'),
            Stat::make('Draft Posts', Post::where('is_published', 0)->count())
                ->color('warning')
                ->icon('heroicon-o-pencil')
                ->description('Number of posts that are currently in draft status.'),
        ];
    }
}
