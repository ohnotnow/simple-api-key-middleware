<?php

namespace Ohffs\SimpleApiKeyMiddleware\Commands;

use Illuminate\Console\Command;
use Ohffs\SimpleApiKeyMiddleware\SimpleApiKey;

class RemoveApiKey extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "simple-api-key:remove {token : Eg, 14-af3548anp48294yaer93}";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove the given token from the database';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $result = SimpleApiKey::remove($this->argument('token'));

        if (! $result) {
            $this->error('The token was not found in the database.');
            return Command::FAILURE;
        }

        $this->info("The token was removed from the database.");

        return Command::SUCCESS;
    }
}
