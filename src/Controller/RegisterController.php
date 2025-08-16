<?php

namespace App\Controller;

use App\Entity\User;
use App\Dto\RegisterDto;
use OpenApi\Attributes as OA;
use App\Dto\Response\AuthResponseDto;
use App\Dto\Response\UserResponseDto;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

#[Route('/api/v1/auth', name: 'api_')]
final class RegisterController extends AbstractController
{

    public function __construct(
        private EntityManagerInterface $entityManager, 
        UserPasswordHasherInterface $passwordHasher, 
        ValidatorInterface $validator,
    ) { }

    #[Route('/register', name: 'app_register', methods: ['POST'])]
    #[OA\Tag(name: "Auth")]
    #[OA\Response(
        response: 200,
        description: 'Successful registration',
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
        description: 'Register credentials',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'email', type: 'string', description: 'User email', example: 'test@email.com'),
                new OA\Property(property: 'password', type: 'string', description: 'User password', example: 'password123'),
                new OA\Property(property: 'firstname', type: 'string', description: 'User first name', example: 'John'),
                new OA\Property(property: 'lastname', type: 'string', description: 'User last name', example: 'Doe'),
                new OA\Property(property: 'birthdate', type: 'string', description: 'User birthdate in YYYY-MM-DD format', example: '1990-01-01'),
            ],
            required: ['email', 'password', 'firstname', 'lastname', 'birthdate']
        )
    )]
    public function index(Request $request, 
                            UserPasswordHasherInterface $passwordHasher, 
                            ValidatorInterface $validator,
                            JWTTokenManagerInterface $jwtManager,
                            EntityManagerInterface $manager,
                            SerializerInterface $serializer
    )

    {
        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            return $this->json(['error' => 'JSON invalide ou vide'], 400);
        }

        $requiredFields = array_keys(get_class_vars(RegisterDto::class));

        $missingFields = [];

        foreach ($requiredFields as $field) {
            if (!array_key_exists($field, $data)) {
                $missingFields[$field] = "Input '$field' must not be empty.";
            }
        }

        if (!empty($missingFields)) {
            return $this->json(['errors' => $missingFields], 400);
        }

        $registerDto = new RegisterDto();
        $registerDto->password = $data['password'];
        $registerDto->email = $data['email'];
        $registerDto->firstname = $data['firstname'];
        $registerDto->lastname = $data['lastname'];

        $errors = [];

        if (!empty($data['birthdate'])) {
            try {
                $registerDto->birthdate = new \DateTime($data['birthdate']);
            } catch (\Exception $e) {
                 $errors['birthdate'] = "Invalid date format. Valid format : YYYY-MM-DD.";
            }
        } else {
            $errors['birthdate'] = "Birthdate is required";
        }

        $violations = $validator->validate($registerDto);
        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }
    
            return $this->json([
                'errors' => $errors
            ], 400);
        }

        $existingUser = $manager->getRepository(User::class)
                                ->findOneBy(['email' => $registerDto->email]);

        if ($existingUser) {
            // return $this->json(['error' => 'Email already exists'], 400);
            $errors['email'] = "Email already exists";

        }

        if (!empty($errors)) {
            return $this->json(['errors' => $errors], 400);
        }

        $user = new User();
        $user->setEmail($registerDto->email);
        $user->setFirstname($registerDto->firstname);
        $user->setLastname($registerDto->lastname);
        $user->setBirthdate($registerDto->birthdate);
        $user->setRoles(['ROLE_USER']);         

        $hashedPassword = $passwordHasher->hashPassword($user, $registerDto->password);
        $user->setPassword($hashedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $token = $jwtManager->create($user);

        $dto = new AuthResponseDto($token, new UserResponseDto($user));

        return new JsonResponse(
            json_decode($serializer->serialize($dto, 'json'), true),
            JsonResponse::HTTP_OK
        );
    }
    
}
