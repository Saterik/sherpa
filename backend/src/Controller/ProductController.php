<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\HttpException;
use App\Service\ProductService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Response as SlimResponse;

class ProductController
{
    public function __construct(
        private ProductService $productService
    ) {}

    public function list(Request $request, Response $response): Response
    {
        $queryParams = $request->getQueryParams();
        
        $page = max(1, (int)($queryParams['page'] ?? 1));
        $limit = min(100, max(1, (int)($queryParams['limit'] ?? 20)));
        $search = $queryParams['search'] ?? null;
        $priceMin = isset($queryParams['price_min']) ? (float)$queryParams['price_min'] : null;
        $priceMax = isset($queryParams['price_max']) ? (float)$queryParams['price_max'] : null;

        $result = $this->productService->getPaginated($page, $limit, $search, $priceMin, $priceMax);
        
        $items = array_map(fn($product) => $product->toArray(), $result['items']);
        
        $payload = [
            'data' => $items,
            'meta' => [
                'page' => $result['page'],
                'limit' => $result['limit'],
                'total' => $result['total'],
                'total_pages' => $result['total_pages'],
            ],
        ];

        $response->getBody()->write(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function get(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'] ?? '';
        
        $product = $this->productService->getById($id);
        
        if (!$product) {
            throw HttpException::notFound('Product not found');
        }

        $response->getBody()->write(json_encode([
            'data' => $product->toArray(),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function create(Request $request, Response $response): Response
    {
        $data = (array)$request->getParsedBody();
        
        if (empty($data['external_code']) || empty($data['name']) || empty($data['price'])) {
            throw HttpException::badRequest('Missing required fields: external_code, name, price');
        }

        $product = $this->productService->create($data);
        
        $response->getBody()->write(json_encode([
            'data' => $product->toArray(),
            'message' => 'Product created successfully',
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        
        return $response
            ->withStatus(201)
            ->withHeader('Content-Type', 'application/json');
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'] ?? '';
        $data = (array)$request->getParsedBody();
        
        $product = $this->productService->getById($id);
        
        if (!$product) {
            throw HttpException::notFound('Product not found');
        }

        $product = $this->productService->update($product, $data);
        
        $response->getBody()->write(json_encode([
            'data' => $product->toArray(),
            'message' => 'Product updated successfully',
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'] ?? '';
        
        $product = $this->productService->getById($id);
        
        if (!$product) {
            throw HttpException::notFound('Product not found');
        }

        $this->productService->delete($product);
        
        $response->getBody()->write(json_encode([
            'message' => 'Product deleted successfully',
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        
        return $response->withHeader('Content-Type', 'application/json');
    }
}