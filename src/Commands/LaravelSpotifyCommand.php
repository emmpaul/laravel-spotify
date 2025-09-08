<?php

namespace emmpaul\LaravelSpotify\Commands;

use Illuminate\Console\Command;

class LaravelSpotifyCommand extends Command
{
    public $signature = 'laravel-spotify';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
