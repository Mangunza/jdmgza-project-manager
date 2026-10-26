<?php

namespace App\Domains\Products\Services;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ProductService
{
    /** @return Collection<int, Product> */
    public function list(array $filters, bool $includeInactive): Collection
    {
        return Product::query()
            ->with('category')
            ->when(! $includeInactive, fn($query) => $query->where('is_active', true))
            ->when($filters['category_id'] ?? null, fn($query, $id) => $query->where('category_id', $id))
            ->when($filters['status'] ?? null, fn($query, $status) => $query->where('is_active', $status === 'active'))
            ->when($filters['search'] ?? null, function ($query, $search): void {
                $query->where(fn($searchQuery) => $searchQuery
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%"));
            })
            ->orderBy('name')
            ->get();
    }

    /** @param array<string, mixed> $data */
    public function create(array $data, ?User $responsible = null): Product
    {
        return DB::transaction(function () use ($data, $responsible): Product {
            $product = Product::create($data);
            $initialStock = (int) $product->stock;

            if ($initialStock > 0) {
                StockMovement::create([
                    'product_id' => $product->id,
                    'quantity_before' => 0,
                    'quantity_change' => $initialStock,
                    'quantity_after' => $initialStock,
                    'type' => StockMovement::TYPE_INITIAL_STOCK,
                    'reason' => 'Stock inicial na criação do produto',
                    'responsible_user_id' => $responsible?->id,
                ]);
            }

            return $product->fresh('category');
        });
    }

    /** @param array<string, mixed> $data */
    public function update(Product $product, array $data): Product
    {
        return DB::transaction(function () use ($product, $data): Product {
            $product->update($data);

            return $product->fresh('category');
        });
    }

    /** @param array<string, mixed> $metadata */

    /** @param array<string, mixed> $metadata */
    public function adjustStock(
        Product $product,
        int $quantity,
        User $responsible,
        array $metadata,
    ): Product {
        return DB::transaction(function () use (
            $product,
            $quantity,
            $responsible,
            $metadata,
        ): Product {
            $type = $metadata['type'] ?? StockMovement::TYPE_ADJUSTMENT;

            if ($quantity === 0) {
                throw new \DomainException(
                    'A quantidade do movimento não pode ser zero.',
                );
            }

            $quantityChange = match ($type) {
                'entry', 'return' => $quantity > 0
                    ? $quantity
                    : throw new \DomainException(
                        'Entradas e devoluções exigem quantidade positiva.',
                    ),
                'exit' => $quantity > 0
                    ? -$quantity
                    : throw new \DomainException(
                        'As saídas exigem quantidade positiva.',
                    ),
                StockMovement::TYPE_ADJUSTMENT => $quantity,
                default => throw new \DomainException(
                    'Tipo de movimento de stock inválido.',
                ),
            };

            $lockedProduct = Product::query()
                ->lockForUpdate()
                ->findOrFail($product->id);

            $quantityBefore = (int) $lockedProduct->stock;
            $nextStock = $quantityBefore + $quantityChange;

            if ($nextStock < 0) {
                throw new \DomainException(
                    'O movimento deixaria o stock negativo.',
                );
            }

            $lockedProduct->update(['stock' => $nextStock]);

            StockMovement::create([
                'product_id' => $lockedProduct->id,
                'quantity_before' => $quantityBefore,
                'quantity_change' => $quantityChange,
                'quantity_after' => $nextStock,
                'type' => $type,
                'reason' => $metadata['reason'] ?? null,
                'external_reference' => $metadata['external_reference'] ?? null,
                'responsible_user_id' => $responsible->id,
            ]);

            return $lockedProduct->fresh('category');
        });
    }

    public function setActive(Product $product, bool $isActive): Product
    {
        return $this->update($product, ['is_active' => $isActive]);
    }
}
