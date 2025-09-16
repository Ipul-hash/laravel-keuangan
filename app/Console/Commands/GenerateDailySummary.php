<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Transaction;
use App\Models\DailySummary;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GenerateDailySummary extends Command
{
    protected $signature = 'reports:daily-summary {--date= : Date to summarize (YYYY-MM-DD). Default = yesterday (Asia/Jakarta)}';
    protected $description = 'Generate daily summaries for all users (by date)';

    public function handle()
    {
        // default: yesterday in Asia/Jakarta (so we capture full day)
        $dateOption = $this->option('date');
        $date = $dateOption
            ? Carbon::parse($dateOption)->toDateString()
            : Carbon::now('Asia/Jakarta')->subDay()->toDateString();

        $this->info("Generating daily summary for: {$date}");

        // chunk users for scalability
        User::chunk(100, function ($users) use ($date) {
            foreach ($users as $user) {
                DB::transaction(function () use ($user, $date) {
                    $pemasukan = Transaction::where('user_id', $user->id)
                        ->where('type', 'pemasukan')
                        ->whereDate('date', $date)
                        ->sum('amount');

                    $pengeluaran = Transaction::where('user_id', $user->id)
                        ->where('type', 'pengeluaran')
                        ->whereDate('date', $date)
                        ->sum('amount');

                    $saldo = $pemasukan - $pengeluaran;

                    DailySummary::updateOrCreate(
                        ['user_id' => $user->id, 'date' => $date],
                        [
                            'pemasukan' => $pemasukan,
                            'pengeluaran' => $pengeluaran,
                            'saldo' => $saldo,
                        ]
                    );
                });
            }
        });

        $this->info("Daily summary generated.");
        return 0;
    }
}
