<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Entity\Preference;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Contrôleur API pour la gestion des utilisateurs
 * 
 * Ce contrôleur gère toutes les opérations CRUD sur les utilisateurs
 * et leurs préférences via une API REST
 */
#[Route('/api')]
class UserController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator,
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    /**
     * Récupère la liste paginée des utilisateurs
     * 
     * @param Request $request La requête HTTP
     * @return JsonResponse La réponse JSON contenant la liste des utilisateurs et les métadonnées de pagination
     * 
     * Query parameters:
     * - page: Numéro de la page (défaut: 1)
     * - limit: Nombre d'éléments par page (défaut: 10, max: 50)
     * - sort: Champ de tri (défaut: id)
     * - order: Ordre de tri (ASC ou DESC, défaut: DESC)
     */
    #[Route('/users', name: 'api_users_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $page = max(1, $request->query->getInt('page', 1));
        $limit = max(1, min(50, $request->query->getInt('limit', 10)));
        $sort = $request->query->get('sort', 'id');
        $order = $request->query->get('order', 'DESC');
        
        $users = $this->userRepository->findBy([], [$sort => $order], $limit, ($page - 1) * $limit);
        $total = $this->userRepository->count([]);
        
        $data = $this->serializer->serialize($users, 'json', ['groups' => 'user:read']);
        
        return new JsonResponse([
            'data' => json_decode($data),
            'meta' => [
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'pages' => ceil($total / $limit),
                'sort' => $sort,
                'order' => $order
            ]
        ]);
    }

    /**
     * Gère les erreurs de validation et les formate pour la réponse
     * 
     * @param ConstraintViolationListInterface $errors Les erreurs de validation
     * @return JsonResponse La réponse JSON formatée avec les erreurs
     */
    private function handleValidationErrors(ConstraintViolationListInterface $errors): JsonResponse
    {
        $errorMessages = [];
        foreach ($errors as $error) {
            $errorMessages[$error->getPropertyPath()] = $error->getMessage();
        }
        
        return new JsonResponse([
            'status' => 'error',
            'message' => 'Validation failed',
            'errors' => $errorMessages
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Crée un nouvel utilisateur
     * 
     * @param Request $request La requête HTTP contenant les données de l'utilisateur
     * @return JsonResponse La réponse JSON avec l'utilisateur créé ou les erreurs
     * 
     * Body JSON requis:
     * {
     *     "email": "string",
     *     "nom": "string",
     *     "prenom": "string",
     *     "password": "string"
     * }
     */
    #[Route('/users', name: 'api_users_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            if (!$data) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Invalid JSON data'
                ], Response::HTTP_BAD_REQUEST);
            }
            
            $user = new User();
            $user->setEmail($data['email'] ?? '');
            $user->setNom($data['nom'] ?? '');
            $user->setPrenom($data['prenom'] ?? '');
            $user->setPassword($data['password'] ?? '');
            
            $errors = $this->validator->validate($user);
            if (count($errors) > 0) {
                return $this->handleValidationErrors($errors);
            }
            
            $user->setPassword($this->passwordHasher->hashPassword($user, $data['password']));
            
            $this->entityManager->persist($user);
            $this->entityManager->flush();
            
            $data = $this->serializer->serialize($user, 'json', ['groups' => 'user:read']);
            return new JsonResponse([
                'status' => 'success',
                'message' => 'User created successfully',
                'data' => json_decode($data)
            ], Response::HTTP_CREATED);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'An error occurred while creating the user',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/users/{id}', name: 'api_users_show', methods: ['GET'])]
    public function show(User $user): JsonResponse
    {
        $data = $this->serializer->serialize($user, 'json', ['groups' => 'user:read']);
        return new JsonResponse(json_decode($data));
    }

    #[Route('/users/{id}', name: 'api_users_update', methods: ['PUT'])]
    public function update(User $user, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (isset($data['email'])) {
            $user->setEmail($data['email']);
        }
        if (isset($data['nom'])) {
            $user->setNom($data['nom']);
        }
        if (isset($data['prenom'])) {
            $user->setPrenom($data['prenom']);
        }
        if (isset($data['password'])) {
            $user->setPassword($this->passwordHasher->hashPassword($user, $data['password']));
        }
        
        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            return new JsonResponse(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }
        
        $this->entityManager->flush();  
        
        $data = $this->serializer->serialize($user, 'json', ['groups' => 'user:read']);
        return new JsonResponse(json_decode($data));
    }

    #[Route('/users/{id}', name: 'api_users_delete', methods: ['DELETE'])]
    public function delete(User $user): JsonResponse
    {
        $this->entityManager->remove($user);
        $this->entityManager->flush();
        
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/users/{id}/preferences', name: 'api_users_preferences_show', methods: ['GET'])]
    public function showPreferences(User $user): JsonResponse
    {
        $preference = $user->getPreference();
        if (!$preference) {
            return new JsonResponse(['error' => 'No preferences found'], Response::HTTP_NOT_FOUND);
        }
        
        $data = $this->serializer->serialize($preference, 'json', ['groups' => 'preference:read']);
        return new JsonResponse(json_decode($data));
    }

    #[Route('/users/{id}/preferences', name: 'api_users_preferences_update', methods: ['PUT'])]
    public function updatePreferences(User $user, Request $request): JsonResponse
    {
        $preference = $user->getPreference();
        if (!$preference) {
            $preference = new Preference();
            $user->setPreference($preference);
        }
        
        $data = json_decode($request->getContent(), true);
        
        if (isset($data['langue'])) {
            $preference->setLangue($data['langue']);
        }
        if (isset($data['theme'])) {
            $preference->setTheme($data['theme']);
        }
        if (isset($data['notifications'])) {
            $preference->setNotifications($data['notifications']);
        }
        
        $errors = $this->validator->validate($preference);
        if (count($errors) > 0) {
            return new JsonResponse(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }
        
        $this->entityManager->flush();
        
        $data = $this->serializer->serialize($preference, 'json', ['groups' => 'preference:read']);
        return new JsonResponse(json_decode($data));
    }
} 