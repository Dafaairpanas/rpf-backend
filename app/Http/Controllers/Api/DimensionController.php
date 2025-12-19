<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Dimension;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DimensionController extends Controller
{
    private const CACHE_KEY = 'dimensions:list';
    private const CACHE_TTL = 300; // 5 menit

    public function index()
    {
        $dimensions = Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return Dimension::all();
        });

        return ApiResponse::success($dimensions);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'width' => 'nullable|numeric',
            'height' => 'nullable|numeric',
            'depth' => 'nullable|numeric',
        ]);
        $dimension = Dimension::create($data);

        Cache::forget(self::CACHE_KEY);

        return ApiResponse::success($dimension, 'Dimension created successfully', 201);
    }

    public function show($id)
    {
        return ApiResponse::success(Dimension::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $dimension = Dimension::findOrFail($id);
        $data = $request->validate([
            'width' => 'nullable|numeric',
            'height' => 'nullable|numeric',
            'depth' => 'nullable|numeric',
        ]);
        $dimension->update($data);

        Cache::forget(self::CACHE_KEY);

        return ApiResponse::success($dimension, 'Dimension updated successfully');
    }

    public function destroy($id)
    {
        $dimension = Dimension::findOrFail($id);
        $dimension->delete();

        Cache::forget(self::CACHE_KEY);

        return ApiResponse::success(null, 'Dimension deleted successfully');
    }
}
