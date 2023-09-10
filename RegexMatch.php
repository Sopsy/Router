<?php
declare(strict_types=1);

namespace Router;

use HttpMessage\Contract\Request;
use HttpMessage\Contract\RequestHandler;
use HttpMessage\Contract\Response as ResponseInterface;
use Exception;

use function array_filter;
use function array_shift;
use function preg_match;

use const ARRAY_FILTER_USE_KEY;

final class RegexMatch implements RequestHandler
{
    /**
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
            if (!preg_match("#{$matchTarget}#", $this->matchString, $attributes)) {
                continue;
            }

            // Remove full request uri from attributes
            array_shift($attributes);

            // Remove numbered matches
            /** @var array<string, string> $attributes */
            $attributes = array_filter($attributes, '\is_string', ARRAY_FILTER_USE_KEY);

            $request = $request->withAttributes($attributes);

            /** @var RequestHandler $handler */
            $handler = $routeTo();
            break;
        }

        if (!isset($handler) || !($handler instanceof RequestHandler)) {
            throw new Exception('No route found', 404);
        }

        return $handler->handle($request);
    }
}