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
        Schema::create('produits', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
          //  $table->string('tags')->nullable();
            $table->text('description')->nullable();
            $table->string('reference')->unique();
            $table->decimal('prix', 13, 3);
            $table->decimal('prix_achat', 13, 3);
            $table->string('photo');
            $table->unsignedBigInteger('id_promotion')->nullable()->default(null);
            $table->unsignedBigInteger("category_id")->nullable();
            $table->integer("stock")->default(0);
            $table->enum("statut",["disponible","indisponible"])->default("indisponible");
            $table->json('photos')->nullable();
            $table->boolean('top')->default(false);
            $table->boolean('free_shipping')->nullable()->default(false);
            $table->unsignedBigInteger("sous_category_id")->nullable();
            $table->unsignedBigInteger("sous_categorie_id")->nullable();
            $table->unsignedBigInteger("marque_id")->nullable();

        

            $table->boolean('valable')->default(false);
            $table->boolean('cmd')->default(false);
            $table->boolean('vo')->default(false);
            $table->boolean('livrable')->default(false);
            $table->boolean('bonne_affaires')->default(false);

            $table->string("tailles")->nullable();
            $table->boolean('cmd0')->default(false);
            $table->boolean('vble')->default(false);

            $table->boolean('type_taille')->default(false);
            $table->boolean('type_poids')->default(false);
            $table->boolean('type_piece')->default(false);

            $table->integer("taille")->nullable();
            $table->string('poids')->nullable();
            $table->string('piece')->nullable();
            
            $table->softDeletes();
            $table->timestamps();


            $table->foreign('id_promotion')->references('id')->on('promotions')->onDelete('set null');
          //  $table->foreign('id_category')->references('id_category')->on('categories')->onDelete('set null');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produits');
    }
};
