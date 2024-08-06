<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;

class Companies extends Cluster
{
    protected static ?string $navigationIcon = 'heroicon-o-cog';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 40;
}
