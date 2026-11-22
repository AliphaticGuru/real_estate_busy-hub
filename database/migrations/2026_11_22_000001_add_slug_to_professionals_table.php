<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('professionals', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('type');
        });

        $this->backfillSlugs();
    }

    public function down(): void
    {
        Schema::table('professionals', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }

    private function backfillSlugs(): void
    {
        $professionals = DB::table('professionals')
            ->leftJoin('users', 'users.id', '=', 'professionals.user_id')
            ->select('professionals.id', 'professionals.type', 'users.name as user_name')
            ->orderBy('professionals.id')
            ->get();

        foreach ($professionals as $professional) {
            $base = Str::limit(Str::slug($professional->user_name ?: $professional->type.' professional'), 200, '');
            $base = $base !== '' ? $base : 'professional';
            $slug = $base;
            $suffix = 2;

            while (DB::table('professionals')
                ->where('slug', $slug)
                ->where('id', '!=', $professional->id)
                ->exists()) {
                $slug = $base.'-'.$suffix++;
            }

            DB::table('professionals')->where('id', $professional->id)->update(['slug' => $slug]);
        }
    }
};
