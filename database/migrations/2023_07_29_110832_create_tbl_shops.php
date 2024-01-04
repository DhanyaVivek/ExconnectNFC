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
        Schema::create('tbl_shops', function (Blueprint $table) {
            $table->id();
            $table->integer('category');
            $table->integer('subcategory');
            $table->string('shopname');
            $table->string('mobile');
            $table->string('land');
            $table->string('whatsapp');
            $table->string('email');
            $table->longtext('short_description');
            $table->string('contact_person');
            $table->string('alt_contact');
            $table->string('logo');
            $table->longtext('address');
            $table->longtext('location');
            $table->longtext('fb');
            $table->longtext('tiktok');
            $table->longtext('instagram');
            $table->tinyInteger('status')->default(1)->comment('1:active,0:notactive');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_shops');
    }
};
