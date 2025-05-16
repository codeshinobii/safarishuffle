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
        $query = Package::query();
        
        // Search by title or description - Updated search logic
        if ($request->has('search') && !empty($request->search)) {
            $search = trim($request->search);
            \Log::info('Search term:', ['term' => $search]);
            
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('short_description', 'like', "%{$search}%")
                  ->orWhere('overview', 'like', "%{$search}%")
                  ->orWhere('display_tags', 'like', "%{$search}%"); // Added tags to search
            });
        }

        // Filter by type (category)
        if ($request->has('type') && $request->type !== 'all') {
            $query->whereJsonContains('safari_types', $request->type);
        }

        // Filter by duration
        if ($request->has('duration')) {
            $durations = explode(',', $request->duration);
            $query->where(function($q) use ($durations) {
                foreach ($durations as $duration) {
                    if ($duration === '1-3') {
                        $q->orWhere('duration', '<=', 3);
                    } elseif ($duration === '4-7') {
                        $q->orWhereBetween('duration', [4, 7]);
                    } elseif ($duration === '8+') {
                        $q->orWhere('duration', '>=', 8);
                    }
                }
            });
        }

        // Filter by minimum price
        if ($request->has('min_price') && $request->min_price > 0) {
            $query->where('min_price', '<=', $request->min_price);
        }

        // Filter by tags
        if ($request->has('tags')) {
            $tags = explode(',', $request->tags);
            $query->where(function($q) use ($tags) {
                foreach ($tags as $tag) {
                    $q->orWhere('display_tags', 'like', "%{$tag}%");
                }
            });
        }

        // Add detailed logging for debugging
        \Log::info('Fetching packages with filters:', $request->all());
        \Log::info('SQL Query:', ['query' => $query->toSql(), 'bindings' => $query->getBindings()]);
        
        $packages = $query->latest()->paginate(10)->withQueryString();
        \Log::info('Packages found: ' . $packages->count());
        
        return response()->json($packages);
    }

    public function store(Request $request)
    {
        try {
            $validated = $this->validatePackage($request);
        } catch (ValidationException $e) {
            Log::error('Package Store Validation Failed:', $e->errors());
            throw $e;
        }

        // Ensure max_price is null if empty
        if (empty($validated['max_price'])) {
            $validated['max_price'] = null;
        }

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        // Handle hero image upload
        if ($request->hasFile('hero_image')) {
            $file = $request->file('hero_image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('package-images', $filename, 'public');
            $validated['hero_image'] = $path;
        }

        // Handle gallery images upload
        if ($request->hasFile('gallery_images')) {
            $galleryPaths = [];
            foreach ($request->file('gallery_images') as $image) {
                $filename = time() . '_' . $image->getClientOriginalName();
                $path = $image->storeAs('package-gallery', $filename, 'public');
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
            'slug' => 'sometimes|required|string|max:255|unique:packages,slug,' . $package->id,
            'short_description' => 'nullable|string',
            'overview' => 'sometimes|required|string',
            'min_price' => 'required|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0|gte:min_price',
            'duration' => 'sometimes|required|string',
            'hero_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'sometimes|required|in:draft,published,archived',
            'inclusions' => 'nullable|array',
            'exclusions' => 'nullable|array',
            'highlights' => 'nullable|array',
            'itinerary' => 'nullable|array',
            'destinations' => 'nullable|array',
            'safari_types' => 'nullable|array',
            'is_featured' => 'boolean',
            'show_popular_tag' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();

        // Ensure max_price is null if empty
        if (empty($data['max_price'])) {
            $data['max_price'] = null;
        }

        // Handle hero image upload
        if ($request->hasFile('hero_image')) {
            // Delete old image if exists
            if ($package->hero_image) {
                Storage::disk('public')->delete($package->hero_image);
            }
            
            $file = $request->file('hero_image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('package-images', $filename, 'public');
            $data['hero_image'] = $path;
        }

        // Handle gallery images upload
        if ($request->hasFile('gallery_images')) {
            $galleryPaths = $package->gallery_images ?? []; // Get existing gallery images
            foreach ($request->file('gallery_images') as $image) {
                $filename = time() . '_' . $image->getClientOriginalName();
                $path = $image->storeAs('package-gallery', $filename, 'public');
                $galleryPaths[] = $path;
            }
            $data['gallery_images'] = $galleryPaths;
        }

        // Handle deleted gallery images
        if ($request->has('delete_gallery_images')) {
            $galleryPaths = $package->gallery_images ?? [];
            foreach ($request->delete_gallery_images as $imageToDelete) {
                // Remove from storage
                Storage::disk('public')->delete($imageToDelete);
                // Remove from array
                $galleryPaths = array_filter($galleryPaths, function($path) use ($imageToDelete) {
                    return $path !== $imageToDelete;
                });
            }
            $data['gallery_images'] = array_values($galleryPaths); // Reindex array
        }

        $package->update($data);
        return response()->json($package);
    }

    public function destroy($id)
    {
        try {
            $package = Package::findOrFail($id);
            
            // Delete hero image if exists
            if ($package->hero_image) {
                Storage::disk('public')->delete($package->hero_image);
            }
            
            // Delete gallery images if they exist
            if ($package->gallery_images) {
                foreach ($package->gallery_images as $image) {
                    Storage::disk('public')->delete($image);
                }
            }
            
            $package->delete();
            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Package not found'], 404);
        }
    }

    public function updateStatus(Package $package, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:draft,published,archived'
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
        $types = Package::select('safari_types')
            ->whereNotNull('safari_types')
            ->distinct()
            ->get()
            ->pluck('safari_types')
            ->flatten()
            ->unique()
            ->values();

        \Log::info('Available safari types:', ['types' => $types->toArray()]);
        
        return response()->json($types);
    }

    public function getDestinations()
    {
        $destinations = Package::distinct()->pluck('destination')->filter();
        return response()->json($destinations);
    }

    protected function validatePackage(Request $request)
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:packages,slug,' . ($request->id ?? ''),
            'short_description' => 'nullable|string',
            'overview' => 'required|string',
            'min_price' => 'required|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0|gte:min_price',
            'duration' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'rating_display' => 'nullable|string|max:255',
            'display_tags' => 'nullable|string|max:255',
            'hero_image' => 'nullable|string|max:255',
            'highlights' => 'nullable|array',
            'itinerary' => 'nullable|array',
            'inclusions' => 'nullable|array',
            'exclusions' => 'nullable|array',
            'gallery_images' => 'nullable|array',
            'destinations' => 'nullable|array',
            'safari_types' => 'nullable|array',
            'status' => 'required|in:draft,published,archived',
            'is_featured' => 'boolean',
            'show_popular_tag' => 'boolean'
        ]);
    }
} 