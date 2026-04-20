<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(name="Authentication", description="Admin authentication endpoints")
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
class AuthController extends Controller
{
    // ── Helpers ──────────────────────────────────────────────────────────

    private function success(mixed $data, string $message = 'Success', int $status = 200): JsonResponse
    {
        return response()->json(['message' => $message, 'data' => $data], $status);
    }

    private function error(string $message, int $status = 400, array $errors = []): JsonResponse
    {
        return response()->json(['message' => $message, 'errors' => $errors], $status);
    }

    // ── Endpoints ─────────────────────────────────────────────────────────

    /**
     * @OA\Post(
     *     path="/api/auth/login",
     *     summary="Admin login",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email",    type="string", example="admin@example.com"),
     *             @OA\Property(property="password", type="string", example="password123")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Login successful — returns { data: { token } }"),
     *     @OA\Response(response=401, description="Invalid credentials")
     * )
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');

        if (! $token = auth('api')->attempt($credentials)) {
            return $this->error('Invalid login credentials', 401);
        }

        return $this->success(['token' => $token], 'Login successful');
    }

    /**
     * @OA\Get(
     *     path="/api/auth/me",
     *     summary="Get the authenticated admin's profile",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Admin profile — top-level user object"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     *
     * NOTE: The Next.js dashboard reads res.data?.data || res.data so both
     * wrapped { data: user } and bare user shapes are handled on the frontend.
     * We return bare here for simplicity; adjust if you prefer wrapped.
     */
    public function me(): JsonResponse
    {
        $user = auth('api')->user();

        if (! $user) {
            return $this->error('Unauthenticated', 401);
        }

        return response()->json($user);
    }

    /**
     * @OA\Post(
     *     path="/api/auth/logout",
     *     summary="Logout — invalidates the JWT",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Logout successful")
     * )
     */
    public function logout(): JsonResponse
    {
        auth('api')->logout();

        return response()->json(['message' => 'Logged out successfully']);
    }
}
