<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sous_categories', function (Blueprint $table) {
            $table->id();

            $table->string("titre")->nullable();
            $table->string("icone")->nullable();
            $table->string("image")->nullable();
            $table->json("photos")->nullable();
            $table->unsignedBigInteger('categorie_id')->nullable();;
            $table->text("description")->nullable();
        
            $table->softDeletes();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sous_categories');
    }
};