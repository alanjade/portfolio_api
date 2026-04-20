<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(name="Contact", description="Contact form endpoints")
 */
class ContactController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/contact",
     *     summary="Submit a contact message",
     *     tags={"Contact"},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"name","email","message"},
     *         @OA\Property(property="name",    type="string", example="Ada Okonkwo"),
     *         @OA\Property(property="email",   type="string", example="ada@company.com"),
     *         @OA\Property(property="message", type="string", example="Hello!")
     *     )),
     *     @OA\Response(response=201, description="Message stored")
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:100',
            'email'   => 'required|email|max:150',
            'message' => 'required|string|min:10|max:5000',
        ]);

        $contact = ContactMessage::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Message sent successfully',
            'data'    => $contact,
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/contact",
     *     summary="List all contact messages (admin)",
     *     tags={"Contact"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Array of messages, newest first")
     * )
     */
    public function index(): JsonResponse
    {
        $messages = ContactMessage::orderBy('created_at', 'desc')->get();

        return response()->json($messages);
    }
}
