<?php

namespace App\Controller;

use App\Entity\User;
use App\Dto\RegisterDto;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

#[Route('/api/v1/auth', name: 'api_')]
final class RegisterController extends AbstractController
{

    public function __construct(
        private EntityManagerInterface $entityManager, 
        UserPasswordHasherInterface $passwordHasher, 
        ValidatorInterface $validator,
    ) { }

    #[Route('/register', name: 'app_register')]
    public function index(Request $request, 
                            UserPasswordHasherInterface $passwordHasher, 
                            ValidatorInterface $validator,
                            JWTTokenManagerInterface $jwtManager,
                            EntityManagerInterface $manager): JsonResponse
    {
        
        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return new JsonResponse([
                'error' => 'JSON invalide',
                'raw_content' => $request->getContent(),
                'error_message' => json_last_error_msg(),
            ], 400);
        }
        
        if (!is_array($data)) {
            return $this->json(['error' => 'Invalid or missing data'], 400);
        }

        $registerDto = new RegisterDto();
        $registerDto->password = $data['password'];
        $registerDto->email = $data['email'];
        $registerDto->firstname = $data['firstname'];
        $registerDto->lastname = $data['lastname'];

        if (!empty($data['birthdate'])) {
            try {
                $registerDto->birthdate = new \DateTime($data['birthdate']);
            } catch (\Exception $e) {
                return $this->json(['error' => 'Invalid date format, accepted format : YYYY-MM-DD'], 400);
            }
        }

        $violations = $validator->validate($registerDto);
        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = $violation->getMessage();
            }
    
            return $this->json([
                'errors' => $errors
            ], 400);
        }

        $existingUser = $manager->getRepository(User::class)
                                ->findOneBy(['email' => $registerDto->email]);

        if ($existingUser) {
            return $this->json(['error' => 'Email already exists'], 400);
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

        return new JsonResponse([
            'token' => $token,
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'firstname' => $user->getFirstname(),
                'lastname' => $user->getLastname(),
                'birthdate' => $user->getBirthdate()->format('Y-m-d'),
            ],
        ]);
    }
    
}
