<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Firebase\JWT\JWT;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Response as SlimResponse;

class AuthController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function login(Request $request, Response $response): Response
    {
        $data = (array)$request->getParsedBody();
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        if (empty($email) || empty($password)) {
            $response->getBody()->write(json_encode([
                'error' => 'Email and password are required',
            ]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $userRepo = $this->entityManager->getRepository(User::class);
        $user = $userRepo->findOneBy(['email' => $email]);

        if (!$user || !password_verify($password, $user->getPassword())) {
            $response->getBody()->write(json_encode([
                'error' => 'Invalid credentials',
            ]));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }

        $token = $this->generateToken($user);
        $refreshToken = $this->generateRefreshToken($user);

        $response->getBody()->write(json_encode([
            'access_token' => $token,
            'refresh_token' => $refreshToken,
            'token_type' => 'Bearer',
            'expires_in' => (int)($_ENV['JWT_EXPIRATION'] ?? 3600),
            'user' => [
                'id' => $user->getId()->toString(),
                'email' => $user->getEmail(),
                'name' => $user->getName(),
            ],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        return $response->withHeader('Content-Type', 'application/json');
    }

    public function register(Request $request, Response $response): Response
    {
        $data = (array)$request->getParsedBody();
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';
        $name = $data['name'] ?? '';

        if (empty($email) || empty($password)) {
            $response->getBody()->write(json_encode([
                'error' => 'Email and password are required',
            ]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $userRepo = $this->entityManager->getRepository(User::class);
        if ($userRepo->findOneBy(['email' => $email])) {
            $response->getBody()->write(json_encode([
                'error' => 'User already exists',
            ]));
            return $response->withStatus(409)->withHeader('Content-Type', 'application/json');
        }

        $user = new User();
        $user->setEmail($email);
        $user->setPassword(password_hash($password, PASSWORD_BCRYPT));
        $user->setName($name);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $token = $this->generateToken($user);
        $refreshToken = $this->generateRefreshToken($user);

        $response->getBody()->write(json_encode([
            'access_token' => $token,
            'refresh_token' => $refreshToken,
            'token_type' => 'Bearer',
            'expires_in' => (int)($_ENV['JWT_EXPIRATION'] ?? 3600),
            'user' => [
                'id' => $user->getId()->toString(),
                'email' => $user->getEmail(),
                'name' => $user->getName(),
            ],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
    }

    public function refresh(Request $request, Response $response): Response
    {
        $data = (array)$request->getParsedBody();
        $refreshToken = $data['refresh_token'] ?? '';

        if (empty($refreshToken)) {
            $response->getBody()->write(json_encode([
                'error' => 'Refresh token is required',
            ]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        try {
            $secret = $_ENV['JWT_SECRET'] ?? 'your-secret-key';
            $decoded = JWT::decode($refreshToken, new \Firebase\JWT\Key($secret, 'HS256'));
            
            $userRepo = $this->entityManager->getRepository(User::class);
            $user = $userRepo->find($decoded->sub);
            
            if (!$user) {
                throw new \Exception('User not found');
            }

            $token = $this->generateToken($user);
            $newRefreshToken = $this->generateRefreshToken($user);

            $response->getBody()->write(json_encode([
                'access_token' => $token,
                'refresh_token' => $newRefreshToken,
                'token_type' => 'Bearer',
                'expires_in' => (int)($_ENV['JWT_EXPIRATION'] ?? 3600),
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Throwable $e) {
            $response->getBody()->write(json_encode([
                'error' => 'Invalid refresh token',
            ]));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }
    }

    private function generateToken(User $user): string
    {
        $secret = $_ENV['JWT_SECRET'] ?? 'your-secret-key';
        $expiration = (int)($_ENV['JWT_EXPIRATION'] ?? 3600);
        
        $payload = [
            'iss' => $_ENV['APP_URL'] ?? 'http://localhost:8080',
            'sub' => $user->getId()->toString(),
            'email' => $user->getEmail(),
            'name' => $user->getName(),
            'iat' => time(),
            'exp' => time() + $expiration,
        ];

        return JWT::encode($payload, $secret, 'HS256');
    }

    private function generateRefreshToken(User $user): string
    {
        $secret = $_ENV['JWT_SECRET'] ?? 'your-secret-key';
        
        $payload = [
            'iss' => $_ENV['APP_URL'] ?? 'http://localhost:8080',
            'sub' => $user->getId()->toString(),
            'type' => 'refresh',
            'iat' => time(),
            'exp' => time() + (86400 * 30), // 30 days
        ];

        return JWT::encode($payload, $secret, 'HS256');
    }
}