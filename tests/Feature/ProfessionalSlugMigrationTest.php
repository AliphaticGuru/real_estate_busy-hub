<?php

namespace Tests\Feature;

use App\Models\Professional;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ProfessionalSlugMigrationTest extends TestCase
{
    use RefreshDatabase;

    private const MIGRATION = 'migrations/2026_11_22_000001_add_slug_to_professionals_table.php';

    public function test_a_slug_is_generated_from_the_user_name_on_create(): void
    {
        $user = User::factory()->professional()->create(['name' => 'Ada Okafor']);

        $professional = Professional::factory()->create([
            'user_id' => $user->id,
            'slug' => null,
        ]);

        $this->assertSame('ada-okafor', $professional->slug);
    }

    public function test_generated_slugs_are_unique(): void
    {
        $firstUser = User::factory()->professional()->create(['name' => 'Ada Okafor']);
        $secondUser = User::factory()->professional()->create(['name' => 'Ada Okafor']);

        $first = Professional::factory()->create(['user_id' => $firstUser->id, 'slug' => null]);
        $second = Professional::factory()->create(['user_id' => $secondUser->id, 'slug' => null]);

        $this->assertSame('ada-okafor', $first->slug);
        $this->assertSame('ada-okafor-2', $second->slug);
    }

    public function test_an_explicit_slug_is_preserved(): void
    {
        $user = User::factory()->professional()->create(['name' => 'Ada Okafor']);

        $professional = Professional::factory()->create([
            'user_id' => $user->id,
            'slug' => 'ada-property-lawyer',
        ]);

        $this->assertSame('ada-property-lawyer', $professional->slug);
    }

    public function test_the_migration_backfills_slugs_and_rolls_back_cleanly(): void
    {
        $ada = User::factory()->professional()->create(['name' => 'Ada Okafor']);
        $adaAgain = User::factory()->professional()->create(['name' => 'Ada Okafor']);

        Professional::factory()->create(['user_id' => $ada->id, 'slug' => 'ada-okafor']);
        Professional::factory()->create(['user_id' => $adaAgain->id, 'slug' => 'ada-okafor-2']);

        $this->artisan('migrate:rollback', [
            '--path' => database_path(self::MIGRATION),
            '--realpath' => true,
        ])->assertSuccessful();

        $this->assertFalse(Schema::hasColumn('professionals', 'slug'));

        $ngozi = User::factory()->professional()->create(['name' => 'Ngozi Eze']);

        DB::table('professionals')->insert([
            'user_id' => $ngozi->id,
            'type' => 'engineer',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->artisan('migrate', [
            '--path' => database_path(self::MIGRATION),
            '--realpath' => true,
        ])->assertSuccessful();

        $this->assertTrue(Schema::hasColumn('professionals', 'slug'));

        $this->assertSame('ada-okafor', DB::table('professionals')->where('user_id', $ada->id)->value('slug'));
        $this->assertSame('ada-okafor-2', DB::table('professionals')->where('user_id', $adaAgain->id)->value('slug'));
        $this->assertSame('ngozi-eze', DB::table('professionals')->where('user_id', $ngozi->id)->value('slug'));
    }
}
