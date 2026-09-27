<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ServiceManagementController extends Controller
{
    public function index()
    {
        $services = Service::orderBy('sort_order', 'asc')->orderBy('id', 'desc')->get();
        return view('admin.services.index', compact('services'));
    }

    public function create()
    {
        return view('admin.services.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'price' => 'nullable|string|max:100',
            'duration' => 'nullable|string|max:100',
            'short_description' => 'nullable|string|max:500',
            'description' => 'nullable|string|max:2000',
            'is_featured' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $slug = Str::slug($validated['name']);
        $originalSlug = $slug;
        $counter = 1;
        while (Service::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter++;
        }

        $service = Service::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'category' => $validated['category'] ?? 'General',
            'price' => $validated['price'] ?? 'Custom Quote',
            'duration' => $validated['duration'] ?? '1 hr',
            'short_description' => $validated['short_description'] ?? ($validated['description'] ? Str::limit($validated['description'], 120) : null),
            'full_description' => $validated['description'] ?? null,
            'description' => $validated['description'] ?? null,
            'is_featured' => $request->has('is_featured') ? true : false,
            'is_active' => $request->has('is_active') ? true : true,
            'sort_order' => Service::max('sort_order') + 1,
        ]);

        return redirect()->route('admin.services.index')->with('success', "Service '{$service->name}' created successfully.");
    }

    public function edit($id)
    {
        $service = Service::findOrFail($id);
        return view('admin.services.edit', compact('service'));
    }

    public function update(Request $request, $id)
    {
        $service = Service::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'price' => 'nullable|string|max:100',
            'duration' => 'nullable|string|max:100',
            'short_description' => 'nullable|string|max:500',
            'description' => 'nullable|string|max:2000',
            'is_featured' => 'nullable',
            'is_active' => 'nullable',
        ]);

        $service->update([
            'name' => $validated['name'],
            'category' => $validated['category'] ?? 'General',
            'price' => $validated['price'] ?? 'Custom Quote',
            'duration' => $validated['duration'] ?? '1 hr',
            'short_description' => $validated['short_description'] ?? ($validated['description'] ? Str::limit($validated['description'], 120) : $service->short_description),
            'full_description' => $validated['description'] ?? $service->full_description,
            'description' => $validated['description'] ?? $service->description,
            'is_featured' => $request->has('is_featured') ? (bool) $request->is_featured : false,
            'is_active' => $request->has('is_active') ? (bool) $request->is_active : true,
        ]);

        return redirect()->route('admin.services.index')->with('success', "Service '{$service->name}' updated successfully.");
    }

    public function destroy($id)
    {
        $service = Service::findOrFail($id);
        $name = $service->name;
        $service->delete();

        return redirect()->route('admin.services.index')->with('success', "Service '{$name}' deleted successfully.");
    }
}
