<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'short_description',
        'overview',
        'price',
        'duration',
        'location',
        'rating_display',
        'display_tags',
        'hero_image',
        'highlights',
        'itinerary',
        'inclusions',
        'exclusions',
        'gallery_images',
        'destinations',
        'safari_types',
        'status',
        'is_featured',
        'show_popular_tag'
    ];

    protected $casts = [
        'highlights' => 'array',
        'itinerary' => 'array',
        'inclusions' => 'array',
        'exclusions' => 'array',
        'gallery_images' => 'array',
        'destinations' => 'array',
        'safari_types' => 'array',
        'is_featured' => 'boolean',
        'show_popular_tag' => 'boolean',
        'price' => 'decimal:2'
    ];

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'slug'; // Use 'slug' column for route model binding
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->whereJsonContains('safari_types', $type);
    }

    public function scopeByDestination($query, $destination)
    {
        return $query->whereJsonContains('destinations', $destination);
    }
} 