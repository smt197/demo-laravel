<?php

declare(strict_types=1);

namespace App\Infrastructure\User\Controllers;

use App\Application\User\Commands\RegisterUserCommand;
use App\Application\User\Commands\VerifyEmailCommand;
use App\Application\User\Queries\GetUserQuery;
use App\Application\User\Queries\GetUsersQuery;
use App\Application\User\DTOs\RegisterUserDTO;
use App\Application\User\Handlers\RegisterUserHandler;
use App\Application\User\Handlers\VerifyEmailHandler;
use App\Application\User\Handlers\GetUserHandler;
use App\Application\User\Handlers\GetUsersHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\ValidationException;

final class UserController extends Controller
{
    public function __construct(
        private RegisterUserHandler $registerUserHandler,
        private VerifyEmailHandler $verifyEmailHandler,
        private GetUserHandler $getUserHandler,
        private GetUsersHandler $getUsersHandler
    ) {}

    public function register(Request $request): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'name' => ['required', 'string', 'min:2', 'max:255'],
                'email' => ['required', 'email', 'max:254'],
                'password' => ['required', 'string', 'min:8'],
            ]);

            $registerDTO = RegisterUserDTO::fromArray($validatedData);
            $command = new RegisterUserCommand($registerDTO);

            $userResponse = $this->registerUserHandler->handle($command);

            return response()->json([
                'success' => true,
                'message' => 'User registered successfully',
                'data' => $userResponse->toArray()
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 409);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    public function show(string $userId): JsonResponse
    {
        try {
            $query = new GetUserQuery($userId);
            $userResponse = $this->getUserHandler->handle($query);

            if (!$userResponse) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $userResponse->toArray()
            ]);

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid user ID format'
            ], 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $limit = min((int) $request->get('limit', 50), 100);
            $offset = max((int) $request->get('offset', 0), 0);
            $searchTerm = $request->get('search');

            $query = new GetUsersQuery($limit, $offset, $searchTerm);
            $users = $this->getUsersHandler->handle($query);

            return response()->json([
                'success' => true,
                'data' => array_map(fn($user) => $user->toArray(), $users),
                'meta' => [
                    'limit' => $limit,
                    'offset' => $offset,
                    'count' => count($users)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    public function verifyEmail(string $userId): JsonResponse
    {
        try {
            $command = new VerifyEmailCommand($userId);
            $userResponse = $this->verifyEmailHandler->handle($command);

            return response()->json([
                'success' => true,
                'message' => 'Email verified successfully',
                'data' => $userResponse->toArray()
            ]);

        } catch (\DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 409);

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid user ID format'
            ], 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }
}