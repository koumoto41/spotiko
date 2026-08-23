<?php

namespace App\Console\Commands;

use App\Application\UseCases\Aggregation\AggregateTrackUseCase;
use Illuminate\Console\Command;

class AggregateTrackCommand extends Command
{
    private const USER_ID = 1;

    protected $signature = 'command:AggregateTrack';

    protected $description = '曲目集計';

    public function handle(AggregateTrackUseCase $aggregateTrack): int
    {
        $aggregateTrack(self::USER_ID);
        $this->info('【曲目集計】集計が終了しました');

        return self::SUCCESS;
    }
}
