<?php

namespace App\Domains\Products\Controllers;

use App\Domains\Products\Resources\CategoryResource;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CategoryController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Product::class);

        return CategoryResource::collection(
            Category::query()->where('is_active', true)->orderBy('name')->get(),
        );
    }
}
