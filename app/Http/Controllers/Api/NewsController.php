<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Helpers\HtmlSanitizer;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNewsRequest;
use App\Http\Requests\UpdateNewsRequest;
use App\Models\News;
use App\Models\NewsContent;
use App\Services\Base64ImageProcessor;
use App\Services\CacheService;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    /**
     * Get top news untuk banner/header
     */
    public function topNews()
    {
        return CacheService::remember(
            'news:top',
            CacheService::TAG_NEWS,
            CacheService::TTL_MEDIUM,
            function () {
                $topNews = News::with(['creator', 'content'])
                    ->where('is_top_news', true)
                    ->first();

                return ApiResponse::success($topNews);
            }
        );
    }

    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 7);

        $cacheKey = CacheService::generateKey('news:index', [
            'per_page' => $perPage,
            'page' => $request->get('page', 1),
        ]);

        return CacheService::remember(
            $cacheKey,
            CacheService::TAG_NEWS,
            CacheService::TTL_MEDIUM,
            function () use ($perPage) {
                return ApiResponse::success(
                    News::with(['creator', 'content:id,news_id,content'])
                        ->select(['id', 'title', 'is_top_news', 'create_by', 'created_at', 'updated_at'])
                        ->orderBy('created_at', 'desc')
                        ->paginate($perPage)
                );
            }
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
            // Ekstrak gambar base64 dan simpan sebagai file
            $imageProcessor = new Base64ImageProcessor();
            $processedContent = $imageProcessor->process($data['content']);

            // Sanitize HTML
            $sanitizedContent = HtmlSanitizer::sanitize($processedContent);

            NewsContent::create([
                'news_id' => $news->id,
                'content' => $sanitizedContent,
            ]);
        }

        // Invalidate news cache
        CacheService::invalidate(CacheService::TAG_NEWS);

        return ApiResponse::success($news->load(['creator', 'content']), 'News created successfully', 201);
    }

    public function show($id)
    {
        $cacheKey = CacheService::generateKey('news:show', ['id' => $id]);

        return CacheService::remember(
            $cacheKey,
            CacheService::TAG_NEWS,
            CacheService::TTL_LONG,
            function () use ($id) {
                return ApiResponse::success(News::with(['creator', 'content'])->findOrFail($id));
            }
        );
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
            // Ekstrak gambar base64 dan simpan sebagai file
            $imageProcessor = new Base64ImageProcessor();
            $processedContent = $imageProcessor->process($data['content']);

            // Sanitize HTML
            $sanitizedContent = HtmlSanitizer::sanitize($processedContent);

            $news->content()->updateOrCreate(
                ['news_id' => $news->id],
                ['content' => $sanitizedContent]
            );
        }

        // Invalidate news cache
        CacheService::invalidate(CacheService::TAG_NEWS);

        return ApiResponse::success($news->load(['creator', 'content']), 'News updated successfully');
    }

    public function destroy($id)
    {
        $news = News::findOrFail($id);
        $news->delete(); // Cascade delete akan hapus content otomatis

        // Invalidate news cache
        CacheService::invalidate(CacheService::TAG_NEWS);

        return ApiResponse::success(null, 'News deleted successfully');
    }
}
