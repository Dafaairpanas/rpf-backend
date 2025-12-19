<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\MasterCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class MasterCategoryController extends Controller
{
    private const CACHE_KEY = 'master_categories:list';
    private const CACHE_TTL = 300; // 5 menit

    public function index()
    {
        $categories = Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return MasterCategory::all();
        });

        return ApiResponse::success($categories);
    }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:100|unique:master_categories,name']);
        $masterCategory = MasterCategory::create($data);

        Cache::forget(self::CACHE_KEY);

        return ApiResponse::success($masterCategory, 'Master Category created successfully', 201);
    }

    public function show($id)
    {
        return ApiResponse::success(MasterCategory::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $masterCategory = MasterCategory::findOrFail($id);
        $data = $request->validate(['name' => "required|string|max:100|unique:master_categories,name,{$id}"]);
        $masterCategory->update($data);

        Cache::forget(self::CACHE_KEY);

        return ApiResponse::success($masterCategory, 'Master Category updated successfully');
    }

    public function destroy($id)
    {
        $masterCategory = MasterCategory::findOrFail($id);
        $masterCategory->delete();

        Cache::forget(self::CACHE_KEY);

        return ApiResponse::success(null, 'Master Category deleted successfully');
    }
}
