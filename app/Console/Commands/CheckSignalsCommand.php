<?php

namespace App\Console\Commands;

use App\Services\Signals\SignalsEngine;
use Illuminate\Console\Command;

class CheckSignalsCommand extends Command
{
    protected $signature = 'signals:check';

    protected $description = 'Evaluate all 8 active signals and log any new signal events.';

    public function handle(SignalsEngine $engine): int
    {
        $fired = $engine->run();

        foreach ($fired as $key => $count) {
            $this->line("{$key}: {$count} new event(s)");
        }

        return self::SUCCESS;
    }
}
