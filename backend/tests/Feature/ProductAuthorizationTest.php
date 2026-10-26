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

class ProductAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private function userWith(array $slugs): User
    {
        $role = Role::create([
            'name' => 'Teste de autorização',
            'slug' => 'product-auth-' . uniqid(),
        ]);

        $permissions = collect($slugs)->map(
            fn (string $slug) => Permission::create([
                'name' => $slug,
                'slug' => $slug,
            ]),
        );

        $role->permissions()->sync($permissions->pluck('id'));

        $user = User::factory()->create();
        $user->roles()->attach($role);

        return $user;
    }

    private function product(bool $active = true): Product
    {
        $category = Category::factory()->create([
            'is_active' => true,
        ]);

        return Product::create([
            'name' => 'Produto de autorização',
            'price' => 100,
            'stock' => 5,
            'category_id' => $category->id,
            'is_active' => $active,
        ]);
    }

    public function test_user_without_inactive_permission_cannot_view_inactive_product(): void
    {
        $product = $this->product(false);

        Sanctum::actingAs($this->userWith(['products.view']));

        $this->getJson("/api/products/{$product->id}")
            ->assertForbidden();
    }

    public function test_user_with_inactive_permission_can_view_inactive_product(): void
    {
        $product = $this->product(false);

        Sanctum::actingAs($this->userWith([
            'products.view',
            'products.view_inactive',
        ]));

        $this->getJson("/api/products/{$product->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $product->id);
    }

    public function test_user_without_inactive_permission_cannot_view_inactive_product_stock_movements(): void
    {
        $product = $this->product(false);

        Sanctum::actingAs($this->userWith(['products.view']));

        $this->getJson("/api/products/{$product->id}/stock-movements")
            ->assertForbidden();
    }

    public function test_user_with_view_permission_can_view_active_product_stock_movements(): void
    {
        $product = $this->product(true);

        Sanctum::actingAs($this->userWith(['products.view']));

        $this->getJson("/api/products/{$product->id}/stock-movements")
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }
}
