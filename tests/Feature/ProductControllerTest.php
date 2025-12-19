<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'Super Admin']);
    }

    public function test_can_list_products(): void
    {
        Product::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data',
                    'current_page',
                ],
            ]);
    }

    public function test_can_filter_featured_products(): void
    {
        Product::factory()->create(['name' => 'Regular Product', 'is_featured' => false]);
        Product::factory()->create(['name' => 'Featured Product 1', 'is_featured' => true]);
        Product::factory()->create(['name' => 'Featured Product 2', 'is_featured' => true]);

        $response = $this->getJson('/api/v1/products?featured=true');

        $response->assertStatus(200);

        $data = $response->json('data');

        // Karena pakai limit langsung return collection, bukan paginate
        if (isset($data['data'])) {
            // Paginated response
            $this->assertCount(2, $data['data']);
        } else {
            // Collection response
            $this->assertCount(2, $data);
        }
    }

    public function test_can_limit_products_for_carousel(): void
    {
        Product::factory()->count(10)->create(['is_featured' => true]);

        $response = $this->getJson('/api/v1/products?featured=true&limit=5');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertCount(5, $data);
    }

    public function test_can_search_products_by_name(): void
    {
        Product::factory()->create(['name' => 'Wooden Chair']);
        Product::factory()->create(['name' => 'Metal Table']);
        Product::factory()->create(['name' => 'Wooden Table']);

        $response = $this->getJson('/api/v1/products?q=Wooden');

        $response->assertStatus(200);

        $data = $response->json('data.data');
        $this->assertCount(2, $data);
    }

    public function test_products_include_images_with_alt_text(): void
    {
        $product = Product::factory()->create();
        $product->productImages()->create([
            'image_url' => 'test-image.jpg',
            'alt' => 'Product Image Alt Text',
            'order' => 1,
        ]);

        $response = $this->getJson('/api/v1/products/' . $product->id);

        $response->assertStatus(200)
            ->assertJsonPath('data.product_images.0.alt', 'Product Image Alt Text');
    }
}
