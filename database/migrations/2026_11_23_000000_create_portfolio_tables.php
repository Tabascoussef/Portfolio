<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->boolean('is_admin')->default(false);
        });

        Schema::create('portfolio_profiles', function (Blueprint $table): void {
            $table->id();
            $table->json('translations');
            $table->json('details');
            $table->timestamps();
        });

        Schema::create('portfolio_entries', function (Blueprint $table): void {
            $table->id();
            $table->string('section', 24);
            $table->string('slug', 100)->nullable()->unique();
            $table->unsignedInteger('position')->default(0);
            $table->json('translations');
            $table->json('details');
            $table->timestamps();
            $table->index(['section', 'position', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portfolio_entries');
        Schema::dropIfExists('portfolio_profiles');

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('is_admin');
        });
    }
};
