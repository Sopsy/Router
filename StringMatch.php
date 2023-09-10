<?php
declare(strict_types=1);

namespace Router;

use HttpMessage\Contract\Request;
use HttpMessage\Contract\RequestHandler;
use HttpMessage\Contract\Response as ResponseInterface;
use Exception;

final class StringMatch implements RequestHandler
{
    /**
     * @param string $matchString
     * @param array<string, callable> $routes
     */
    public function __construct(
        private string $matchString,
        private array $routes
    ) {
    }

    public function handle(Request $request): ResponseInterface
    {
        foreach ($this->routes as $matchTarget => $routeTo) {
            if ($this->matchString === $matchTarget) {
                /** @var RequestHandler $handler */
                $handler = $routeTo();
                break;
            }
        }

        if (!isset($handler) || !($handler instanceof RequestHandler)) {
            throw new Exception('No route found', 404);
        }

        return $handler->handle($request);
    }
}