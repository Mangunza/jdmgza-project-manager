<?php

namespace App\Domains\Products\Controllers;

use App\Domains\Products\Requests\AdjustProductStockRequest;
use App\Domains\Products\Requests\StoreProductRequest;
use App\Domains\Products\Requests\UpdateProductRequest;
use App\Domains\Products\Resources\ProductResource;
use App\Domains\Products\Resources\StockMovementResource;
use App\Domains\Products\Services\ProductService;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ProductController extends Controller
{
    public function __construct(private readonly ProductService $productService) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Product::class);
        $includeInactive = $request->boolean('include_inactive');
        if ($includeInactive) {
            $this->authorize('viewInactive', Product::class);
        }

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'status' => ['nullable', 'in:active,inactive'],
        ]);
        if (($filters['status'] ?? null) === 'inactive') {
            $this->authorize('viewInactive', Product::class);
        }

        return ProductResource::collection(
            $this->productService->list(
                $filters,
                $includeInactive || ($filters['status'] ?? null) === 'inactive',
            ),
        );
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $this->authorize('create', Product::class);
        $product = $this->productService->create($request->validated(), $request->user());

        return (new ProductResource($product))->response()->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Product $product): ProductResource
    {
        $this->authorize('view', $product);

        return new ProductResource($product->load('category'));
    }

    public function update(UpdateProductRequest $request, Product $product): ProductResource
    {
        $this->authorize('update', $product);

        return new ProductResource($this->productService->update($product, $request->validated()));
    }

    public function adjustStock(AdjustProductStockRequest $request, Product $product): ProductResource
    {
        $this->authorize('update', $product);

        try {
            return new ProductResource($this->productService->adjustStock(
                $product,
                $request->integer('quantity'),
                $request->user(),
                $request->safe()->only(['type', 'reason', 'external_reference']),
            ));
        } catch (\DomainException $exception) {
            throw new HttpException(Response::HTTP_UNPROCESSABLE_ENTITY, $exception->getMessage(), $exception);
        }
    }

    public function stockMovements(Product $product): AnonymousResourceCollection
    {
        $this->authorize('view', $product);

        return StockMovementResource::collection(
            $product->stockMovements()->latest()->get(),
        );
    }

    public function activate(Product $product): ProductResource
    {
        $this->authorize('update', $product);

        return new ProductResource($this->productService->setActive($product, true));
    }

    public function deactivate(Product $product): ProductResource
    {
        $this->authorize('update', $product);

        return new ProductResource($this->productService->setActive($product, false));
    }
}
