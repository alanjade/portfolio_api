<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @OA\Tag(name="Projects", description="CRUD for portfolio projects")
 */
class ProjectController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/projects",
     *     summary="List all projects (newest first)",
     *     tags={"Projects"},
     *     @OA\Parameter(name="limit", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Array of projects with image_url appended")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $query = Project::orderBy('created_at', 'desc');

        // Support ?limit=3 used by the home page hero section
        if ($request->filled('limit') && is_numeric($request->limit)) {
            $query->limit((int) $request->limit);
        }

        return response()->json($query->get());
    }

    /**
     * @OA\Get(
     *     path="/api/projects/{id}",
     *     summary="Get a single project",
     *     tags={"Projects"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="{ success: true, project: {...} }"),
     *     @OA\Response(response=404, description="Not found")
     * )
     */
    public function show(int $id): JsonResponse
    {
        $project = Project::findOrFail($id);

        return response()->json(['success' => true, 'project' => $project]);
    }

    /**
     * @OA\Post(
     *     path="/api/projects",
     *     summary="Create a project (multipart/form-data)",
     *     tags={"Projects"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(mediaType="multipart/form-data", @OA\Schema(
     *             required={"title","summary"},
     *             @OA\Property(property="title",       type="string"),
     *             @OA\Property(property="summary",     type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="stack",       type="string", example="React, Laravel"),
     *             @OA\Property(property="github",      type="string"),
     *             @OA\Property(property="website",     type="string"),
     *             @OA\Property(property="image",       type="string", format="binary")
     *         ))
     *     ),
     *     @OA\Response(response=201, description="Project created")
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'summary'     => 'required|string',
            'description' => 'nullable|string',
            'stack'       => 'nullable|string|max:255',
            'github'      => 'nullable|url|max:255',
            'website'     => 'nullable|url|max:255',
            'image'       => 'nullable|image|mimes:jpeg,png,webp,gif|max:2048',
        ]);

        $project          = new Project();
        $project->title   = $validated['title'];
        $project->slug    = Str::slug($validated['title']) . '-' . time();
        $project->summary = $validated['summary'];
        $project->description = $validated['description'] ?? null;
        $project->stack   = $validated['stack']   ?? null;
        $project->github  = $validated['github']  ?? null;
        $project->website = $validated['website'] ?? null;

        if ($request->hasFile('image')) {
            $project->image = $request->file('image')->store('projects', 'public');
        }

        $project->save();

        return response()->json([
            'success' => true,
            'message' => 'Project created successfully',
            'project' => $project,
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/api/projects/{id}",
     *     summary="Update a project (POST used for multipart support)",
     *     tags={"Projects"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(mediaType="multipart/form-data", @OA\Schema(
     *             required={"title","summary"},
     *             @OA\Property(property="title",       type="string"),
     *             @OA\Property(property="summary",     type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="stack",       type="string"),
     *             @OA\Property(property="github",      type="string"),
     *             @OA\Property(property="website",     type="string"),
     *             @OA\Property(property="image",       type="string", format="binary")
     *         ))
     *     ),
     *     @OA\Response(response=200, description="Project updated")
     * )
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'summary'     => 'required|string',
            'description' => 'nullable|string',
            'stack'       => 'nullable|string|max:255',
            'github'      => 'nullable|url|max:255',
            'website'     => 'nullable|url|max:255',
            'image'       => 'nullable|image|mimes:jpeg,png,webp,gif|max:2048',
        ]);

        $project              = Project::findOrFail($id);
        $project->title       = $validated['title'];
        $project->summary     = $validated['summary'];
        $project->description = $validated['description'] ?? $project->description;
        $project->stack       = $validated['stack']   ?? $project->stack;
        $project->github      = $validated['github']  ?? $project->github;
        $project->website     = $validated['website'] ?? $project->website;

        if ($request->hasFile('image')) {
            // Delete the old image before storing the new one
            if ($project->image) {
                Storage::disk('public')->delete($project->image);
            }
            $project->image = $request->file('image')->store('projects', 'public');
        }

        $project->save();

        return response()->json([
            'success' => true,
            'message' => 'Project updated successfully',
            'project' => $project,
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/projects/{id}",
     *     summary="Delete a project",
     *     tags={"Projects"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Project deleted")
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        $project = Project::findOrFail($id);

        if ($project->image) {
            Storage::disk('public')->delete($project->image);
        }

        $project->delete();

        return response()->json(['success' => true, 'message' => 'Project deleted']);
    }
}
