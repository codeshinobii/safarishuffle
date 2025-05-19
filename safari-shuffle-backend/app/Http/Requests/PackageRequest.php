<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PackageRequest extends FormRequest
{
    public function authorize()
    {
        return true; // You can add authentication logic here
    }

    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'duration' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'required|in:draft,active,archived',
            'inclusions' => 'nullable|array',
            'exclusions' => 'nullable|array',
            'highlights' => 'nullable|array',
            'itinerary' => 'nullable|array',
            'destination' => 'nullable|string',
            'type' => 'nullable|string',
            'min_pax' => 'required|integer|min:1',
            'max_pax' => 'nullable|integer|min:1',
            'is_featured' => 'boolean'
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'The package title is required.',
            'description.required' => 'The package description is required.',
            'price.required' => 'The package price is required.',
            'duration.required' => 'The package duration is required.',
            'image.image' => 'The file must be an image.',
            'image.mimes' => 'The image must be a file of type: jpeg, png, jpg, gif.',
            'image.max' => 'The image may not be greater than 2MB.',
            'status.required' => 'The package status is required.',
            'status.in' => 'The selected status is invalid.',
            'min_pax.required' => 'The minimum number of participants is required.',
            'min_pax.min' => 'The minimum number of participants must be at least 1.',
        ];
    }
} 