<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('professionals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('company_id')->nullable()->after('user_id')->constrained('companies')->nullOnDelete();
            $table->string('type');
            $table->string('registration_body')->nullable();
            $table->string('registration_number')->nullable();
            $table->text('bio')->nullable();
            $table->timestamp('credentials_verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->after('credentials_verified_at')->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('professionals');
    }
};
