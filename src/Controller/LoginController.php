<?php

namespace App\Controller;

use App\Entity\User;

use OpenApi\Attributes as OA;
use App\Repository\UserRepository;
use App\Dto\Response\UserResponseDto;
use App\Dto\Response\LoginResponseDto;
use Nelmio\ApiDocBundle\Attribute\Model;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

#[Route('/api/v1/auth', name: 'api_')]
final class LoginController extends AbstractController
{
    #[Route('/login', name: 'api_login', methods: ['POST'])]
    #[OA\Tag(name: "Authentification")]
    #[OA\Response(
        response: 200,
        description: 'Successful login',
        content: new OA\JsonContent(
            example: [
                'accessToken' => 'your_jwt_token_here',
                'user' => [
                    'id' => 1,
                    'email' => 'john.doe@example.com',
                    'roles' => ['ROLE_USER'],
                    'firstname' => 'John',
                    'lastname' => 'Doe',
                    'birthdate' => '1990-01-01',
                ]
            ]
        )
    )]
    #[OA\RequestBody(
        description: 'Login credentials',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'email', type: 'string', description: 'User email', example: 'test@email.com'),
                new OA\Property(property: 'password', type: 'string', description: 'User password', example: 'password123'),
            ],
            required: ['email', 'password']
        )
    )]
    public function login(
        Request $request,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher,
        JWTTokenManagerInterface $jwtManager,
        SerializerInterface $serializer
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $user = $userRepository->findOneBy(['email' => $data['email'] ?? null]);

        if (!$user || !$passwordHasher->isPasswordValid($user, $data['password'] ?? '')) {
            return new JsonResponse(['error' => 'Invalid credentials'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $token = $jwtManager->create($user);

        $dto = new LoginResponseDto($token, new UserResponseDto($user));

        return new JsonResponse(
            json_decode($serializer->serialize($dto, 'json'), true),
            JsonResponse::HTTP_OK
        );
    }
}
