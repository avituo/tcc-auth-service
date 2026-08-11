<?php

namespace App\Console\Commands;

use Database\Seeders\BenchmarkDataset;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[Signature('experiment:dataset:validate')]
#[Description('Validate and print the deterministic thesis benchmark users')]
class ValidateBenchmarkDatasetCommand extends Command
{
    public function handle(): int
    {
        $userCount = DB::table('users')->count();
        $benchmarkUser = BenchmarkDataset::user(1);
        $firstUser = DB::table('users')->find(1);

        $this->line('database='.(string) DB::connection()->getDatabaseName());
        $this->line(sprintf('user_count=%d', $userCount));
        $logicalFingerprint = BenchmarkDataset::logicalFingerprint();
        $this->line('logical_fingerprint='.$logicalFingerprint);

        if ($logicalFingerprint !== BenchmarkDataset::LOGICAL_FINGERPRINT
            || $userCount !== BenchmarkDataset::USER_COUNT
            || $firstUser === null
            || $firstUser->name !== $benchmarkUser['name']
            || $firstUser->email !== $benchmarkUser['email']
        ) {
            $this->error('benchmark_users=INVALID');

            return self::FAILURE;
        }

        $this->info('benchmark_users=VALID');

        return self::SUCCESS;
    }
}
