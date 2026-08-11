<?php

namespace Tests\Feature;

use Database\Seeders\BenchmarkDataset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class BenchmarkDatasetSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_the_exact_deterministic_benchmark_users(): void
    {
        $this->seed();

        $this->assertSame(BenchmarkDataset::USER_COUNT, DB::table('users')->count());
        $this->assertSame(BenchmarkDataset::LOGICAL_FINGERPRINT, BenchmarkDataset::logicalFingerprint());
        $this->assertDatabaseHas('users', [
            'id' => 1,
            'name' => 'Benchmark User 001',
            'email' => BenchmarkDataset::BENCHMARK_EMAIL,
        ]);
        $roles = DB::table('users')->where('id', 1)->value('roles');
        $this->assertSame(['user'], json_decode((string) $roles, true, flags: JSON_THROW_ON_ERROR));
        $this->assertTrue(Hash::check(BenchmarkDataset::BENCHMARK_PASSWORD, (string) DB::table('users')->where('id', 1)->value('password')));

        $this->artisan('experiment:dataset:validate')->assertSuccessful();
    }
}
