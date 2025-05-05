<?php

namespace App\Http\Controllers;

use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class PackageController extends Controller
{
    public function index(Request $request)
    {
        $query = Package::query()->where('status', 'published');

        // Search Filter
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                  ->orWhere('short_description', 'like', "%{$searchTerm}%");
            });
        }

        // Category Filter
        if ($request->filled('category') && $request->input('category') !== 'all') {
            $category = $request->input('category');
            $query->whereJsonContains('safari_types', $category);
        }

        // Duration Filter
        if ($request->filled('duration')) {
            $durations = explode(',', $request->input('duration'));
            $query->where(function ($q) use ($durations) {
                foreach ($durations as $duration) {
                    if (strpos($duration, '+') !== false) {
                        $minDays = (int)trim($duration, '+');
                        $q->orWhereRaw('CAST(REGEXP_SUBSTR(duration, \'[0-9]+\') AS UNSIGNED) >= ?', [$minDays]);
                    } elseif (strpos($duration, '-') !== false) {
                        list($min, $max) = explode('-', $duration);
                        $q->orWhereRaw('CAST(REGEXP_SUBSTR(duration, \'[0-9]+\') AS UNSIGNED) BETWEEN ? AND ?', [(int)$min, (int)$max]);
                    } else {
                        $q->orWhere('duration', 'like', $duration.'%');
                    }
                }
            });
        }

        // Max Price Filter
        if ($request->filled('max_price')) {
            $maxPrice = (float)$request->input('max_price');
            if ($maxPrice > 0) {
                $query->where('price', '<=', $maxPrice);
            }
        }

        // Tags Filter
        if ($request->filled('tags')) {
            $tags = explode(',', $request->input('tags'));
            $query->where(function ($q) use ($tags) {
                foreach ($tags as $tag) {
                    $q->orWhereJsonContains('display_tags', trim($tag));
                }
            });
        }

        // Featured Filter
        if ($request->has('featured')) {
            $query->where('is_featured', true);
        }

        $packages = $query->latest()->paginate(10)->withQueryString();
        return response()->json($packages);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'slug' => 'nullable|string|max:255|unique:packages',
                'short_description' => 'nullable|string',
                'overview' => 'required|string',
                'price' => 'required|numeric|min:0',
                'duration' => 'required|string|max:255',
                'location' => 'required|string|max:255',
                'rating_display' => 'nullable|string|max:255',
                'display_tags' => 'nullable|string|max:255',
                // 'hero_image' => 'nullable|file|max:2048', // Temporarily commented out
                'highlights' => 'nullable|array',
                'highlights.*.title' => 'required|string|max:255',
                'highlights.*.description' => 'required|string',
                'itinerary' => 'nullable|array',
                'itinerary.*.title' => 'required|string|max:255',
                'itinerary.*.description' => 'required|string',
                'itinerary.*.accommodation' => 'nullable|string|max:255',
                'inclusions' => 'nullable|array',
                'inclusions.*' => 'string|max:255',
                'exclusions' => 'nullable|array',
                'exclusions.*' => 'string|max:255',
                // 'gallery_images' => 'nullable|array', // Temporarily commented out
                // 'gallery_images.*' => 'file|max:2048', // Temporarily commented out
                'destinations' => 'nullable|array',
                'destinations.*' => 'string|max:255',
                'safari_types' => 'nullable|array',
                'safari_types.*' => 'string|max:255',
                'status' => 'required|in:draft,published,archived',
                'is_featured' => 'boolean',
                'show_popular_tag' => 'boolean'
            ]);
        } catch (ValidationException $e) {
            Log::error('Package Store Validation Failed:', $e->errors());
            throw $e;
        }

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        // Handle hero image upload
        if ($request->hasFile('hero_image')) {
            $path = $request->file('hero_image')->store('package-images', 'public');
            $validated['hero_image'] = $path;
        }

        // Handle gallery images upload
        if ($request->hasFile('gallery_images')) {
            $galleryPaths = [];
            foreach ($request->file('gallery_images') as $image) {
                $path = $image->store('package-gallery', 'public');
                $galleryPaths[] = $path;
            }
            $validated['gallery_images'] = $galleryPaths;
        }

        // Create the package
        $package = Package::create($validated);

        return response()->json([
            'message' => 'Package created successfully',
            'package' => $package
        ], 201);
    }

    public function show(Package $package)
    {
        return response()->json($package);
    }

    public function update(Request $request, Package $package)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'price' => 'sometimes|required|numeric|min:0',
            'duration' => 'sometimes|required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'sometimes|required|in:draft,published,archived',
            'inclusions' => 'nullable|array',
            'exclusions' => 'nullable|array',
            'highlights' => 'nullable|array',
            'itinerary' => 'nullable|array',
            'destination' => 'nullable|string',
            'type' => 'nullable|string',
            'min_pax' => 'sometimes|required|integer|min:1',
            'max_pax' => 'nullable|integer|min:1',
            'is_featured' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($package->image) {
                Storage::disk('public')->delete($package->image);
            }
            $path = $request->file('image')->store('packages', 'public');
            $data['image'] = $path;
        }

        $package->update($data);
        return response()->json($package);
    }

    public function destroy(Package $package)
    {
        if ($package->image) {
            Storage::disk('public')->delete($package->image);
        }
        $package->delete();
        return response()->json(null, 204);
    }

    public function updateStatus(Package $package, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:draft,active,archived'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $package->update(['status' => $request->status]);
        return response()->json($package);
    }

    public function toggleFeatured(Package $package)
    {
        $package->update(['is_featured' => !$package->is_featured]);
        return response()->json($package);
    }

    public function getTypes()
    {
        $types = Package::distinct()->pluck('type')->filter();
        return response()->json($types);
    }

    public function getDestinations()
    {
        $destinations = Package::distinct()->pluck('destination')->filter();
        return response()->json($destinations);
    }
} 