<?php

declare(strict_types=1);

namespace Rinvex\Attributes\Console\Commands;

use Illuminate\Console\Command;

class RollbackCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rinvex:rollback:attributes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rollback Rinvex Attributes Tables.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->warn($this->description);
        $this->call('migrate:reset', ['--path' => 'vendor/rinvex/attributes/database/migrations']);
    }
}
