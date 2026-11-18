<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('verification_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained()->cascadeOnDelete();
            $table->string('check_key');
            $table->string('status')->default('pending');
            $table->text('notes')->nullable();
            $table->foreignId('checked_by')->nullable()->after('notes')->constrained('users')->nullOnDelete();
            $table->timestamp('checked_at')->nullable();
            $table->timestamps();

            $table->unique(['listing_id', 'check_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verification_checks');
    }
};
