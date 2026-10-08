<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Backup current sequences so we can rollback if needed.
        if (!DB::getSchemaBuilder()->hasTable('program_sequence_backups')) {
            DB::getSchemaBuilder()->create('program_sequence_backups', function ($table) {
                $table->increments('id');
                $table->unsignedBigInteger('program_id');
                $table->integer('sequence')->nullable();
            });
        }

        DB::table('program_sequence_backups')->truncate();

        $programs = DB::table('programs')->orderBy('sequence')->orderBy('id')->get();

        foreach ($programs as $p) {
            DB::table('program_sequence_backups')->insert([
                'program_id' => $p->id,
                'sequence'   => $p->sequence,
            ]);
        }

        // Renumber sequences to 1..N in order
        $i = 1;
        foreach ($programs as $p) {
            DB::table('programs')->where('id', $p->id)->update(['sequence' => $i]);
            $i++;
        }
    }

    public function down(): void
    {
        // Restore from backup if it exists
        if (DB::getSchemaBuilder()->hasTable('program_sequence_backups')) {
            $backups = DB::table('program_sequence_backups')->get();
            foreach ($backups as $b) {
                DB::table('programs')->where('id', $b->program_id)->update(['sequence' => $b->sequence]);
            }
            DB::getSchemaBuilder()->dropIfExists('program_sequence_backups');
        }
    }
};
