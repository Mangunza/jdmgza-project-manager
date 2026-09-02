<?php

namespace App\Domains\Projects\Controllers;

use App\Domains\Projects\Requests\StoreProjectServiceRequest;
use App\Domains\Projects\Requests\UpdateProjectServiceRequest;
use App\Domains\Projects\Resources\ProjectServiceResource;
use App\Domains\Projects\Services\ProjectServiceManager;
use App\Http\Controllers\Controller as BaseController;
use App\Models\Project;
use App\Models\ProjectService;
use Illuminate\Http\Response;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProjectServiceController extends BaseController
{
    public function __construct(
        private readonly ProjectServiceManager $projectServiceManager,
    ) {
    }

    public function index(Project $project): AnonymousResourceCollection
    {
        $this->authorize('view', $project);

        $projectServices = $project->projectServices()
            ->with('service')
            ->latest()
            ->get();

        return ProjectServiceResource::collection($projectServices);
    }

    public function store(
        StoreProjectServiceRequest $request,
        Project $project,
    ): ProjectServiceResource {
        $projectService = $this->projectServiceManager->add(
            $project,
            $request->validated(),
        );

        return new ProjectServiceResource($projectService);
    }

    public function show(
        Project $project,
        ProjectService $projectService,
    ): ProjectServiceResource {
        $this->authorize('view', $project);

        $this->ensureProjectServiceBelongsToProject(
            $project,
            $projectService,
        );

        $projectService->load('service');

        return new ProjectServiceResource($projectService);
    }

    public function update(
        UpdateProjectServiceRequest $request,
        Project $project,
        ProjectService $projectService,
    ): ProjectServiceResource {
        $this->ensureProjectServiceBelongsToProject(
            $project,
            $projectService,
        );

        $projectService = $this->projectServiceManager->update(
            $projectService,
            $request->validated(),
        );

        return new ProjectServiceResource($projectService);
    }

    public function destroy(
        Project $project,
        ProjectService $projectService,
    ): Response {
        $this->authorize('update', $project);

        $this->ensureProjectServiceBelongsToProject(
            $project,
            $projectService,
        );

        $this->projectServiceManager->remove($projectService);

        return response()->noContent();
    }

    private function ensureProjectServiceBelongsToProject(
        Project $project,
        ProjectService $projectService,
    ): void {
        abort_unless(
            $projectService->project_id === $project->id,
            Response::HTTP_NOT_FOUND,
        );
    }
}
