<?php

namespace App\Domains\Services\Controllers;

use App\Domains\Services\Requests\StoreServiceRequest;
use App\Domains\Services\Requests\UpdateServiceRequest;
use App\Domains\Services\Resources\ServiceResource;
use App\Domains\Services\Services\ServiceService;
use App\Http\Controllers\Controller as BaseController;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ServiceController extends BaseController
{
    public function __construct(
        private readonly ServiceService $serviceService,
    ) {
    }

    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Service::class);

        $services = $this->serviceService->list();

        return ServiceResource::collection($services);
    }

    public function store(StoreServiceRequest $request): JsonResponse
    {
        $this->authorize('create', Service::class);

        $service = $this->serviceService->create(
            $request->validated(),
        );

        return (new ServiceResource($service))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Service $service): ServiceResource
    {
        $this->authorize('view', $service);

        return new ServiceResource($service);
    }

    public function update(
        UpdateServiceRequest $request,
        Service $service,
    ): ServiceResource {
        $this->authorize('update', $service);

        $service = $this->serviceService->update(
            $service,
            $request->validated(),
        );

        return new ServiceResource($service);
    }

    public function activate(Service $service): ServiceResource
    {
        $this->authorize('update', $service);

        $service = $this->serviceService->activate($service);

        return new ServiceResource($service);
    }

    public function deactivate(Service $service): ServiceResource
    {
        $this->authorize('update', $service);

        $service = $this->serviceService->deactivate($service);

        return new ServiceResource($service);
    }
}
