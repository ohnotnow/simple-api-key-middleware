<?php

namespace Ohffs\SimpleApiKeyMiddleware\Commands;

use Illuminate\Console\Command;
use Ohffs\SimpleApiKeyMiddleware\SimpleApiKey;

class GenerateApiKey extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'simple-api-key:generate {description}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a new api key with the given description';

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
        $key = SimpleApiKey::generate($this->argument('description'));

        $this->info("The new api key is :");
        $this->info($key->plaintext_token);

        return Command::SUCCESS;
    }
}
