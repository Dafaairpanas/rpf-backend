<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Helpers\HtmlSanitizer;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNewsRequest;
use App\Http\Requests\UpdateNewsRequest;
use App\Models\News;
use App\Models\NewsContent;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    /**
     * Get top news untuk banner/header
     */
    public function topNews()
    {
        $topNews = News::with(['creator', 'content'])
            ->where('is_top_news', true)
            ->first();

        return ApiResponse::success($topNews);
    }

    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 7);

        // Load content untuk extract thumbnail, tapi hanya select field yang diperlukan
        return ApiResponse::success(
            News::with(['creator', 'content:id,news_id,content'])
                ->select(['id', 'title', 'is_top_news', 'create_by', 'created_at', 'updated_at'])
                ->orderBy('created_at', 'desc')
                ->paginate($perPage)
        );
    }

    public function store(StoreNewsRequest $request)
    {
        $data = $request->validated();
        $data['create_by'] = auth()->id();

        // Simpan data utama (tanpa content)
        $news = News::create([
            'title' => $data['title'],
            'is_top_news' => $data['is_top_news'] ?? false,
            'create_by' => $data['create_by'],
        ]);

        // Simpan content ke tabel terpisah jika ada
        if (filled($data['content'])) {
            $sanitizedContent = HtmlSanitizer::sanitize($data['content']);
            NewsContent::create([
                'news_id' => $news->id,
                'content' => $sanitizedContent,
            ]);
        }

        return ApiResponse::success($news->load(['creator', 'content']), 'News created successfully', 201);
    }

    public function show($id)
    {
        // Load content untuk detail
        return ApiResponse::success(News::with(['creator', 'content'])->findOrFail($id));
    }

    public function update(UpdateNewsRequest $request, $id)
    {
        $news = News::findOrFail($id);
        $data = $request->validated();

        // Update data utama
        $news->update([
            'title' => $data['title'],
            'is_top_news' => $data['is_top_news'] ?? $news->is_top_news,
        ]);

        // Update atau create content
        if (isset($data['content'])) {
            $sanitizedContent = HtmlSanitizer::sanitize($data['content']);

            $news->content()->updateOrCreate(
                ['news_id' => $news->id],
                ['content' => $sanitizedContent]
            );
        }

        return ApiResponse::success($news->load(['creator', 'content']), 'News updated successfully');
    }

    public function destroy($id)
    {
        $news = News::findOrFail($id);
        $news->delete(); // Cascade delete akan hapus content otomatis

        return ApiResponse::success(null, 'News deleted successfully');
    }
}
