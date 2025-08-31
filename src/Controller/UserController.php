<?php

namespace App\Controller;

use OpenApi\Attributes as OA;
use App\Dto\Response\UserPublicProfileDto;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class UserController extends AbstractController
{
    #[Route('/api/v1/user/publicprofile', name: 'app_public_profile', methods:['GET'])]
    #[OA\Tag(name: "User")]
    #[OA\Response(
        response: 200,
        description: 'Successful request',
        content: new OA\JsonContent(
            example: [
                "id" => "550e8400-e29b-41d4-a716-446655440000",
                "email" => "john.doe@example.com",
                "firstname" => "John",
                "lastname" => "Doe",
                "birthdate" => "1995-07-12",
                "phone" => "+33612345678",
                "roles" => ["ROLE_USER"],
                "plays_in" => ["id" => 3, "name" => "U17"],
                "is_coach_of" => ["id" => 1, "name" => "Senior A"],
                "is_parent_of" => [
                    [
                        "id" => 5,
                        "firstname" => "Alice",
                        "lastname" => "Doe",
                        "plays_in" => ["id" => 2, "name" => "U13"]
                    ],
                    [
                        "id" => 6,
                        "firstname" => "Bob",
                        "lastname" => "Doe",
                        "plays_in" => ["id" => 3, "name" => "U15"]
                    ]
                ],
                "stats" => [
                    "matchesConvocated" => 25,
                    "matchesPresent" => 15,
                    "matchesAbsent" => 10,
                    "trainingSessions" => 20,
                    "trainingSessionsPresent" => 18,
                    "trainingSessionsAbsent" => 2,
                    "goals" => 7,
                    "presenceRateMatches" => 60.0, // (matchesPresent / matchesConvocated) × 100.
                    "presenceRateTrainingSessions" => 90.0, // (trainingsPresent / trainingsConvocated) × 100.
                    "globalPresenceRate" => 75.0 // ((matchesPresent + trainingsPresent) / (matchesConvocated + trainingsConvocated)) × 100.
                ]
            ]
        )
    )]

    public function getUserPublicProfile(
        Request $request, 
        UserRepository $userRepository,
        SerializerInterface $serializer
        ): JsonResponse
    {
        $userId = $request->query->get('id');
        $user = $userRepository->find($userId);

        if (!$user) {
            return new JsonResponse(['error' => 'Utilisateur non trouvé'], 404);
        }

        $dto = new UserPublicProfileDto($user);

        return new JsonResponse(
                    json_decode($serializer->serialize($dto, 'json'), true),
                    JsonResponse::HTTP_OK
                );    
        }
}
