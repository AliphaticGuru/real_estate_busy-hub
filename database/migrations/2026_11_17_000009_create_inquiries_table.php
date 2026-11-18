<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inquiries', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->foreignId('listing_id')->nullable()->after('type')->constrained()->nullOnDelete();
            $table->foreignId('professional_id')->nullable()->after('listing_id')->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('phone');
            $table->string('email');
            $table->text('message');
            $table->timestamp('preferred_date')->nullable();
            $table->string('status')->default('new');
            $table->timestamps();

            $table->index(['listing_id']);
            $table->index(['professional_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inquiries');
    }
};
