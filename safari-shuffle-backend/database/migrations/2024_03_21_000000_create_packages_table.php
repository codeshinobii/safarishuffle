<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('short_description')->nullable();
            $table->text('overview');
            $table->decimal('price', 10, 2);
            $table->string('duration');
            $table->string('location');
            $table->string('rating_display')->nullable();
            $table->string('display_tags')->nullable();
            $table->string('hero_image')->nullable();
            $table->json('highlights')->nullable(); // Array of {title, description}
            $table->json('itinerary')->nullable(); // Array of {title, description, accommodation}
            $table->json('inclusions')->nullable(); // Array of strings
            $table->json('exclusions')->nullable(); // Array of strings
            $table->json('gallery_images')->nullable(); // Array of image paths
            $table->json('destinations')->nullable(); // Array of selected destinations
            $table->json('safari_types')->nullable(); // Array of selected safari types
            $table->integer('min_pax')->unsigned()->nullable();
            $table->integer('max_pax')->unsigned()->nullable();
            $table->string('status')->default('draft'); // draft, published, archived
            $table->boolean('is_featured')->default(false);
            $table->boolean('show_popular_tag')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('packages');
    }
}; 