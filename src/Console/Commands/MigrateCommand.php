<?php

declare(strict_types=1);

namespace Rinvex\Attributable\Console\Commands;

use Illuminate\Console\Command;

class MigrateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rinvex:migrate:attributable';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate Rinvex Attributable Tables.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->warn('Migrate rinvex/attributable:');
        $this->call('migrate', ['--step' => true, '--path' => 'vendor/rinvex/attributable/database/migrations']);
    }
}
