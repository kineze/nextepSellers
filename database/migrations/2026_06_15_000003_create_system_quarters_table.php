<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $months = [
        1 => 'January',
        2 => 'February',
        3 => 'March',
        4 => 'April',
        5 => 'May',
        6 => 'June',
        7 => 'July',
        8 => 'August',
        9 => 'September',
        10 => 'October',
        11 => 'November',
        12 => 'December',
    ];

    private array $monthEndDays = [
        1 => '31',
        2 => '28/29',
        3 => '31',
        4 => '30',
        5 => '31',
        6 => '30',
        7 => '31',
        8 => '31',
        9 => '30',
        10 => '31',
        11 => '30',
        12 => '31',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('system_quarters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('system_data_id')->constrained('system_data')->cascadeOnDelete();
            $table->unsignedTinyInteger('quarter_number');
            $table->string('name', 10);
            $table->unsignedTinyInteger('start_month');
            $table->unsignedTinyInteger('start_day')->default(1);
            $table->unsignedTinyInteger('end_month');
            $table->unsignedTinyInteger('end_day');
            $table->string('start_label');
            $table->string('end_label');
            $table->timestamps();

            $table->unique(['system_data_id', 'quarter_number']);
        });

        DB::table('system_data')
            ->select(['id', 'year_start_month'])
            ->orderBy('id')
            ->get()
            ->each(function ($systemData) {
                $this->insertQuarters((int) $systemData->id, (int) ($systemData->year_start_month ?: 1));
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_quarters');
    }

    private function insertQuarters(int $systemDataId, int $yearStartMonth): void
    {
        foreach ($this->quarterRows($systemDataId, $yearStartMonth) as $quarter) {
            DB::table('system_quarters')->insert(array_merge($quarter, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    private function quarterRows(int $systemDataId, int $yearStartMonth): array
    {
        return collect([0, 1, 2, 3])->map(function (int $quarterIndex) use ($systemDataId, $yearStartMonth) {
            $startMonth = $this->addMonths($yearStartMonth, $quarterIndex * 3);
            $endMonth = $this->addMonths($startMonth, 2);

            return [
                'system_data_id' => $systemDataId,
                'quarter_number' => $quarterIndex + 1,
                'name' => 'Q' . ($quarterIndex + 1),
                'start_month' => $startMonth,
                'start_day' => 1,
                'end_month' => $endMonth,
                'end_day' => $endMonth === 2 ? 29 : (int) $this->monthEndDays[$endMonth],
                'start_label' => $this->months[$startMonth] . ' 1',
                'end_label' => $this->months[$endMonth] . ' ' . $this->monthEndDays[$endMonth],
            ];
        })->all();
    }

    private function addMonths(int $month, int $amount): int
    {
        return (($month + $amount - 1) % 12) + 1;
    }
};
