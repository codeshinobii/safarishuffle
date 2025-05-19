<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PackageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'price' => $this->price,
            'duration' => $this->duration,
            'image' => $this->image ? asset('storage/' . $this->image) : null,
            'status' => $this->status,
            'inclusions' => $this->inclusions,
            'exclusions' => $this->exclusions,
            'highlights' => $this->highlights,
            'itinerary' => $this->itinerary,
            'destination' => $this->destination,
            'type' => $this->type,
            'min_pax' => $this->min_pax,
            'max_pax' => $this->max_pax,
            'is_featured' => $this->is_featured,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
} 