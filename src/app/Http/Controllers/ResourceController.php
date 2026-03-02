<?php
namespace App\Http\Controllers;

use App\Models\Resource;
use Illuminate\Http\Request;

class ResourceController extends Controller
{
    public function index()
    {
        return response()->json(Resource::where('is_active', true)->get());
    }

    public function show(Resource $resource)
    {
        return response()->json($resource);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'type'           => 'required|string',
            'capacity'       => 'required|integer|min:1',
            'location'       => 'required|string',
            'price_per_hour' => 'required|numeric|min:0',
            'description'    => 'nullable|string',
        ]);

        $resource = Resource::create($request->all());

        return response()->json($resource, 201);
    }

    public function update(Request $request, Resource $resource)
    {
        $request->validate([
            'name'           => 'sometimes|string|max:255',
            'type'           => 'sometimes|string',
            'capacity'       => 'sometimes|integer|min:1',
            'location'       => 'sometimes|string',
            'price_per_hour' => 'sometimes|numeric|min:0',
            'description'    => 'nullable|string',
            'is_active'      => 'sometimes|boolean',
        ]);

        $resource->update($request->all());

        return response()->json($resource);
    }

    public function destroy(Resource $resource)
    {
        $resource->delete();
        return response()->json(['message' => 'Столик удалён']);
    }
}
