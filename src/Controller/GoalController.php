<?php

namespace App\Controller;

use OpenApi\Attributes as OA;
use App\Service\GoalService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class GoalController extends AbstractController
{
    #[Route('/api/v1/goal/new', name: 'create_goal', methods: ['POST'])]
    #[OA\Tag(name: "Goal")]
    #[OA\Response(
        response: 200,
        description: 'Goal created successfully',
        // content: new OA\JsonContent(
        //     example: [
        //         'accessToken' => 'your_jwt_token_here',
        //         'user' => [
        //             'id' => 1,
        //             'email' => 'john.doe@example.com',
        //             'roles' => ['ROLE_USER'],
        //             'firstname' => 'John',
        //             'lastname' => 'Doe',
        //             'birthdate' => '1990-01-01',
        //         ]
        //     ]
        // )
    )]
    #[OA\RequestBody(
        description: <<<MD
            Goal creation data.

            **Note:**
            - If the goal is scored by a visitor team player, set `isVisitor` to `true`.
            - `eventId` and `playerId` are required.
            - `minuteGoal` must be an integer between 0 and 120.
            MD,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'eventId', type: 'string', description: 'ID of the event', example: 1),
                new OA\Property(property: 'playerId', type: 'string', description: 'ID of the player who scored', example: 10),
                new OA\Property(property: 'minuteGoal', type: 'integer', description: 'Minute when the goal was scored', example: 45),
                new OA\Property(property: 'isVisitor', type: 'boolean', description: 'Whether the goal was scored by a visitor team player', example: false),
            ],
            required: ['eventId', 'playerId', 'minuteGoal']
        )
    )]
    public function createGoal(Request $request, GoalService $goalService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['eventId'], $data['playerId'], $data['minuteGoal'])) {
            return new JsonResponse(['message' => 'Données invalides'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $isVisitor = $data['isVisitor'] ?? false; 

        try {
            $goal = $goalService->createGoal(
                $data['eventId'],
                $data['playerId'],
                (int) $data['minuteGoal'],
                (bool) $isVisitor
            );

            return new JsonResponse([
                'message' => 'But enregistré avec succès',
                'goalId' => $goal->getId()
            ], JsonResponse::HTTP_CREATED);

        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        }
    }
}
