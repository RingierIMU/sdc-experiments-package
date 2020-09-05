<?php

namespace Ringierimu\Recommend\Console;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recommend:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the recommend package';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->comment('Publishing Recommend Service Provider...');
        $this->call('vendor:publish', ['--provider' => 'Ringierimu\Recommend\RecommendServiceProvider']);
    }
}
