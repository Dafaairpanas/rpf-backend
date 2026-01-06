<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Helpers\HtmlSanitizer;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCsrRequest;
use App\Http\Requests\UpdateCsrRequest;
use App\Models\Csr;
use App\Models\CsrContent;
use App\Services\CacheService;
use Illuminate\Http\Request;

class CsrController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 6);

        $cacheKey = CacheService::generateKey('csr:index', [
            'per_page' => $perPage,
            'page' => $request->get('page', 1),
        ]);

        return CacheService::remember(
            $cacheKey,
            CacheService::TAG_CSR,
            CacheService::TTL_MEDIUM,
            function () use ($perPage) {
                return ApiResponse::success(
                    Csr::with(['creator', 'content:id,csr_id,content'])
                        ->select(['id', 'title', 'create_by', 'created_at', 'updated_at'])
                        ->orderBy('created_at', 'desc')
                        ->paginate($perPage)
                );
            }
        );
    }

    public function store(StoreCsrRequest $request)
    {
        $data = $request->validated();
        $data['create_by'] = auth()->id();

        // Simpan data utama (tanpa content)
        $csr = Csr::create([
            'title' => $data['title'],
            'create_by' => $data['create_by'],
        ]);

        // Simpan content ke tabel terpisah jika ada
        if (filled($data['content'])) {
            $sanitizedContent = HtmlSanitizer::sanitize($data['content']);
            CsrContent::create([
                'csr_id' => $csr->id,
                'content' => $sanitizedContent,
            ]);
        }

        // Invalidate CSR cache
        CacheService::invalidate(CacheService::TAG_CSR);

        return ApiResponse::success($csr->load(['creator', 'content']), 'Content created successfully', 201);
    }

    public function show($id)
    {
        $cacheKey = CacheService::generateKey('csr:show', ['id' => $id]);

        return CacheService::remember(
            $cacheKey,
            CacheService::TAG_CSR,
            CacheService::TTL_LONG,
            function () use ($id) {
                return ApiResponse::success(Csr::with(['creator', 'content'])->findOrFail($id));
            }
        );
    }

    public function update(UpdateCsrRequest $request, $id)
    {
        $csr = Csr::findOrFail($id);
        $data = $request->validated();

        // Update data utama
        $csr->update([
            'title' => $data['title'],
        ]);

        // Update atau create content
        if (isset($data['content'])) {
            $sanitizedContent = HtmlSanitizer::sanitize($data['content']);

            $csr->content()->updateOrCreate(
                ['csr_id' => $csr->id],
                ['content' => $sanitizedContent]
            );
        }

        // Invalidate CSR cache
        CacheService::invalidate(CacheService::TAG_CSR);

        return ApiResponse::success($csr->load(['creator', 'content']), 'Content updated successfully');
    }

    public function destroy($id)
    {
        $csr = Csr::findOrFail($id);
        $csr->delete(); // Cascade delete akan hapus content otomatis

        // Invalidate CSR cache
        CacheService::invalidate(CacheService::TAG_CSR);

        return ApiResponse::success(null, 'Content deleted successfully');
    }
}
