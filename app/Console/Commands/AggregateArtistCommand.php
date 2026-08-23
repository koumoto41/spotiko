<?php

namespace App\Console\Commands;

use App\Application\UseCases\Aggregation\AggregateArtistUseCase;
use Illuminate\Console\Command;

class AggregateArtistCommand extends Command
{
    private const USER_ID = 1;

    protected $signature = 'command:AggregateArtist';

    protected $description = 'アーティスト集計';

    public function handle(AggregateArtistUseCase $aggregateArtist): int
    {
        $aggregateArtist(self::USER_ID);
        $this->info('【アーティスト集計】集計が終了しました');

        return self::SUCCESS;
    }
}
