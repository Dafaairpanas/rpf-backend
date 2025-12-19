<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Helpers\HtmlSanitizer;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCsrRequest;
use App\Http\Requests\UpdateCsrRequest;
use App\Models\Csr;
use App\Models\CsrContent;

class CsrController extends Controller
{
    public function index()
    {
        // Load content untuk extract thumbnail, tapi hanya select ID untuk efisiensi
        return ApiResponse::success(
            Csr::with(['creator', 'content:id,csr_id,content'])
                ->select(['id', 'title', 'create_by', 'created_at', 'updated_at'])
                ->paginate(20)
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

        return ApiResponse::success($csr->load(['creator', 'content']), 'Content created successfully', 201);
    }

    public function show($id)
    {
        // Load content untuk detail
        return ApiResponse::success(Csr::with(['creator', 'content'])->findOrFail($id));
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

        return ApiResponse::success($csr->load(['creator', 'content']), 'Content updated successfully');
    }

    public function destroy($id)
    {
        $csr = Csr::findOrFail($id);
        $csr->delete(); // Cascade delete akan hapus content otomatis

        return ApiResponse::success(null, 'Content deleted successfully');
    }
}
