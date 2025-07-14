<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('atik_merkezi_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('atik_merkezi_id')->constrained('atik_merkezleri')->onDelete('cascade');
            $table->tinyInteger('rating')->unsigned();
            $table->text('comment')->nullable();
            $table->timestamps();
            
            $table->unique(['user_id', 'atik_merkezi_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('atik_merkezi_ratings');
    }
};
