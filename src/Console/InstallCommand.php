<?php

namespace Ringierimu\Experiments\Console;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'experiments:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the experiments package';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->comment('Publishing Experiments Service Provider...');
        $this->call('vendor:publish', ['--provider' => 'Ringierimu\Experiments\ExperimentsServiceProvider']);
    }
}
