<?php

namespace App\Domains\Projects\Controllers;

use App\Domains\Projects\Requests\StoreProjectRequest;
use App\Domains\Projects\Requests\UpdateProjectRequest;
use App\Domains\Projects\Resources\ProjectResource;
use App\Domains\Projects\Services\ProjectService;
use App\Http\Controllers\Controller as BaseController;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProjectController extends BaseController
{
    public function __construct(
        private readonly ProjectService $projectService,
    ) {
    }

    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Project::class);

        $projects = Project::query()
            ->where('user_id', auth()->id())
            ->with(['category'])
            ->latest()
            ->paginate(15);

        return ProjectResource::collection($projects);
    }

    public function store(StoreProjectRequest $request): JsonResponse
    {
        $this->authorize('create', Project::class);

        $project = $this->projectService->create(
            $request->user(),
            $request->validated(),
        );

        return (new ProjectResource($project))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Project $project): ProjectResource
    {
        $this->authorize('view', $project);

        $project->load([
            'user',
            'category',
            'projectServices',
        ]);

        return new ProjectResource($project);
    }

    public function update(
        UpdateProjectRequest $request,
        Project $project,
    ): ProjectResource {
        $this->authorize('update', $project);

        $project = $this->projectService->update(
            $project,
            $request->validated(),
        );

        return new ProjectResource($project);
    }

    public function destroy(Project $project): Response
    {
        $this->authorize('delete', $project);

        $this->projectService->delete($project);

        return response()->noContent();
    }
}
