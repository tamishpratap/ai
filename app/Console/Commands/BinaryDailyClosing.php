<?php

namespace App\Console\Commands;

use App\Services\BinaryService;
use Illuminate\Console\Command;

class BinaryDailyClosing extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'binary:daily-closing';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Match left/right binary business volume and credit the binary matching bonus for the day';

    /**
     * Execute the console command.
     */
    public function handle(BinaryService $binaryService): void
    {
        $result = $binaryService->processBinaryClosing();

        $this->info(sprintf(
            'Binary closing done: %d member(s) processed, %d already closed today, $%s total credited.',
            $result['processed'],
            $result['skipped_already_closed'],
            $result['total_credited']
        ));
    }
}
