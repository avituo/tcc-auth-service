<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the deterministic thesis benchmark users.
     */
    public function run(): void
    {
        DB::disableQueryLog();

        $users = [];

        for ($ordinal = 1; $ordinal <= BenchmarkDataset::USER_COUNT; $ordinal++) {
            $user = BenchmarkDataset::user($ordinal);
            $users[] = [
                'id' => $user['logical_id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'email_verified_at' => $user['email_verified_at'],
                'password' => BenchmarkDataset::BENCHMARK_PASSWORD_HASH,
                'roles' => json_encode(['user'], JSON_THROW_ON_ERROR),
                'created_at' => $user['created_at'],
                'updated_at' => $user['updated_at'],
            ];
        }

        DB::table('users')->insert($users);
    }
}
