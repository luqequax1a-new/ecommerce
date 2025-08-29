<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CheckTableStructure extends Command
{
    protected $signature = 'db:check-structure {table}';
    protected $description = 'Check table structure';

    public function handle()
    {
        $table = $this->argument('table');
        
        if (!Schema::hasTable($table)) {
            $this->error("Table {$table} does not exist");
            return 1;
        }
        
        $columns = DB::select("DESCRIBE {$table}");
        
        $this->info("Structure of table: {$table}");
        $this->table(
            ['Field', 'Type', 'Null', 'Key', 'Default', 'Extra'],
            collect($columns)->map(function ($column) {
                return [
                    $column->Field,
                    $column->Type,
                    $column->Null,
                    $column->Key,
                    $column->Default,
                    $column->Extra
                ];
            })->toArray()
        );
        
        return 0;
    }
}
