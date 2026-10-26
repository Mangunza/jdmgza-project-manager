<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Permission;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProductsApiTest extends TestCase
{
    use RefreshDatabase;

    private function userWith(array $slugs): User
    {
        $role = Role::create(['name' => 'Teste', 'slug' => 'test-' . uniqid()]);
        $permissions = collect($slugs)->map(fn(string $slug) => Permission::create([
            'name' => $slug,
            'slug' => $slug,
        ]));
        $role->permissions()->sync($permissions->pluck('id'));
        $user = User::factory()->create();
        $user->roles()->attach($role);

        return $user;
    }

    private function category(bool $active = true): Category
    {
        return Category::factory()->create(['is_active' => $active]);
    }

    private function product(Category $category, bool $active = true, int $stock = 5): Product
    {
        return Product::create([
            'name' => 'Produto de teste',
            'price' => 100,
            'stock' => $stock,
            'category_id' => $category->id,
            'is_active' => $active,
        ]);
    }

    public function test_unauthenticated_user_cannot_list_products(): void
    {
        $this->getJson('/api/products')->assertUnauthorized();
    }

    public function test_list_hides_inactive_products_without_permission(): void
    {
        $category = $this->category();
        $this->product($category);
        $this->product($category, false);
        Sanctum::actingAs($this->userWith(['products.view']));

        $this->getJson('/api/products')->assertOk()->assertJsonCount(1, 'data');
        $this->getJson('/api/products?include_inactive=true')->assertForbidden();
    }

    public function test_user_with_permission_can_create_product_for_active_category(): void
    {
        $category = $this->category();
        Sanctum::actingAs($this->userWith(['products.create']));

        $response = $this->postJson('/api/products', [
            'name' => 'Produto novo',
            'price' => 99.99,
            'stock' => 2,
            'category_id' => $category->id,
            'primary_image_url' => 'https://example.test/image.jpg',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Produto novo')
            ->assertJsonPath('data.stock', 2);

        $this->assertDatabaseHas('products', [
            'name' => 'Produto novo',
            'stock' => 2,
        ]);

        $productId = $response->json('data.id');

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $productId,
            'quantity_before' => 0,
            'quantity_change' => 2,
            'quantity_after' => 2,
            'type' => 'initial_stock',
            'reason' => 'Stock inicial na criação do produto',
        ]);
    }

    public function test_create_rejects_negative_stock_and_inactive_category(): void
    {
        Sanctum::actingAs($this->userWith(['products.create']));
        $category = $this->category(false);

        $this->postJson('/api/products', [
            'name' => '',
            'price' => -1,
            'stock' => -1,
            'category_id' => $category->id,
            'primary_image_url' => 'file:///unsafe.jpg',
        ])->assertUnprocessable()->assertJsonValidationErrors([
            'name',
            'price',
            'stock',
            'category_id',
            'primary_image_url',
        ]);
    }

    public function test_update_cannot_change_stock_outside_adjustment_endpoint(): void
    {
        $category = $this->category();
        $product = $this->product($category, true, 3);
        Sanctum::actingAs($this->userWith(['products.update']));

        $this->patchJson("/api/products/{$product->id}", ['name' => 'Atualizado', 'stock' => 99])
            ->assertOk()->assertJsonPath('data.stock', 3);
    }

    public function test_adjustment_is_authorized_and_cannot_make_stock_negative(): void
    {
        $category = $this->category();
        $product = $this->product($category, true, 3);
        Sanctum::actingAs($this->userWith(['products.update']));

        $this->patchJson("/api/products/{$product->id}/stock", ['quantity' => -4])
            ->assertUnprocessable();
        $this->patchJson("/api/products/{$product->id}/stock", ['quantity' => 2])
            ->assertOk()->assertJsonPath('data.stock', 5);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'quantity_before' => 3,
            'quantity_change' => 2,
            'quantity_after' => 5,
            'type' => 'adjustment',
        ]);
    }

    public function test_delete_product_endpoint_does_not_exist(): void
    {
        $product = $this->product($this->category());
        Sanctum::actingAs($this->userWith(['products.update']));

        $this->deleteJson("/api/products/{$product->id}")->assertMethodNotAllowed();
    }


    public function test_stock_entry_exit_and_return_apply_correct_directions(): void
    {
        $category = $this->category();
        $product = $this->product($category, true, 10);
        Sanctum::actingAs($this->userWith(['products.update']));

        $this->patchJson("/api/products/{$product->id}/stock", [
            'quantity' => 5,
            'type' => 'entry',
        ])->assertOk()->assertJsonPath('data.stock', 15);

        $this->patchJson("/api/products/{$product->id}/stock", [
            'quantity' => 4,
            'type' => 'exit',
        ])->assertOk()->assertJsonPath('data.stock', 11);

        $this->patchJson("/api/products/{$product->id}/stock", [
            'quantity' => 2,
            'type' => 'return',
        ])->assertOk()->assertJsonPath('data.stock', 13);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'quantity_before' => 15,
            'quantity_change' => -4,
            'quantity_after' => 11,
            'type' => 'exit',
        ]);
    }

    public function test_stock_entry_exit_and_return_reject_non_positive_quantities(): void
    {
        $category = $this->category();
        $product = $this->product($category, true, 10);
        Sanctum::actingAs($this->userWith(['products.update']));

        foreach (['entry', 'exit', 'return'] as $type) {
            $this->patchJson("/api/products/{$product->id}/stock", [
                'quantity' => -2,
                'type' => $type,
            ])->assertUnprocessable();
        }

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 10,
        ]);
    }
}
