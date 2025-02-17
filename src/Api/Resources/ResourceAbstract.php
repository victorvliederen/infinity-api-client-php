<?php

declare(strict_types=1);

namespace KyleWLawrence\Infinity\Api\Resources;

use Doctrine\Inflector\CachedWordInflector;
use Doctrine\Inflector\Inflector;
use Doctrine\Inflector\Rules\English;
use Doctrine\Inflector\RulesetInflector;
use KyleWLawrence\Infinity\Api\Exceptions\RouteException;
use KyleWLawrence\Infinity\Api\HttpClient;
use KyleWLawrence\Infinity\Api\Traits\Utility\ChainedParametersTrait;

/**
 * Abstract class for all endpoints
 */
abstract class ResourceAbstract
{
    use ChainedParametersTrait;

    protected string $resourceName;

    protected string $objectName;

    protected string $objectNamePlural;

    protected HttpClient $client;

    protected int $lastId;

    protected array $routes = [];

    protected array $additionalRouteParams = [];

    protected string $apiBasePath;

    protected bool $includeWorkspace = true;

    protected bool $skipConvObj = false;

    public function __construct(HttpClient $client, $apiBasePath = 'api/v2/')
    {
        if ($this->includeWorkspace) {
            $apiBasePath .= "workspaces/{$client->getWorkspace()}/";
        }

        $this->apiBasePath = $apiBasePath;
        $this->client = $client;
        $this->client->setApiBasePath($this->apiBasePath);
        $inflector = new Inflector(
            new CachedWordInflector(new RulesetInflector(
                English\Rules::getSingularRuleset()
            )),

            new CachedWordInflector(new RulesetInflector(
                English\Rules::getPluralRuleset()
            ))
        );

        if (! isset($this->resourceName)) {
            $this->resourceName = $this->getResourceNameFromClass();
        }

        if (! isset($this->objectName)) {
            $this->objectName = $inflector->singularize($this->resourceName);
        }

        if (! isset($this->objectNamePlural)) {
            $this->objectNamePlural = $inflector->pluralize($this->resourceName);
        }

        $this->setUpRoutes();
    }

    /**
     * This returns the valid relations of this resource. Definition of what is allowed to chain after this resource.
     * Make sure to add in this method when adding new sub resources.
     * Example:
     *    $client->ticket()->comments();
     *    Where ticket would have a comments as a valid sub resource.
     *    The array would look like:
     *      ['comments' => '\Infinity\Api\Resources\TicketComments']
     */
    public static function getValidSubResources(): array
    {
        return [];
    }

    /**
     * Return the resource name using the name of the class (used for endpoints)
     */
    protected function getResourceNameFromClass(): string
    {
        $namespacedClassName = get_class($this);
        $resourceName = implode('', array_slice(explode('\\', $namespacedClassName), -1));

        // This converts the resource name from camel case to underscore case.
        // e.g. MyClass => my_class
        $underscored = strtolower(preg_replace('/(?<!^)([A-Z])/', '_$1', $resourceName));

        return strtolower($underscored);
    }

    public function getResourceName(): string
    {
        return $this->resourceName;
    }

    /**
     * Sets up the available routes for the resource.
     */
    protected function setUpRoutes(): void
    {
    }

    public function setSkipConvObj(): object
    {
        $this->skipConvObj = true;

        return $this;
    }

    /**
     * Saves an id for future methods in the chain
     *
     * @param  int  $id
     * @return $this
     */
    public function setLastId($id): object
    {
        $this->lastId = $id;

        return $this;
    }

    /**
     * Saves an id for future methods in the chain
     */
    public function getLastId(): int
    {
        return $this->lastId;
    }

    /**
     * Check that all parameters have been supplied
     */
    public function hasKeys(array $params, array $mandatory): bool
    {
        for ($i = 0; $i < count($mandatory); $i++) {
            if (! array_key_exists($mandatory[$i], $params)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check that any parameter has been supplied
     */
    public function hasAnyKey(array $params, array $mandatory): bool
    {
        for ($i = 0; $i < count($mandatory); $i++) {
            if (array_key_exists($mandatory[$i], $params)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Wrapper for adding multiple routes via setRoute
     */
    public function setRoutes(array $routes): void
    {
        foreach ($routes as $name => $route) {
            $this->setRoute($name, $route);
        }
    }

    /**
     * Add or override an existing route
     */
    public function setRoute($name, $route): void
    {
        $this->routes[$name] = $route;
    }

    /**
     * Return all routes for this resource
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * Returns a route and replaces tokenized parts of the string with
     * the passed params
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function getRoute($name, array $params = []): string
    {
        if (! isset($this->routes[$name])) {
            throw new RouteException('Route not found.');
        }

        $route = $this->routes[$name];

        $substitutions = array_merge($params, $this->getAdditionalRouteParams());
        foreach ($substitutions as $name => $value) {
            if (is_scalar($value)) {
                $route = str_replace('{'.$name.'}', $value, $route);
            }
        }

        return $route;
    }

    /**
     * @param  array  $additionalRouteParams
     */
    public function setAdditionalRouteParams($additionalRouteParams): void
    {
        $this->additionalRouteParams = $additionalRouteParams;
    }

    public function getAdditionalRouteParams(): array
    {
        return $this->additionalRouteParams;
    }

    public function when($condition, $callback): object
    {
        if ($condition) {
            return $callback($this) ?: $this;
        }

        return $this;
    }

    /**
     * Wrapper for common GET requests
     *
     * @return \stdClass | null
     *
     * @throws ResponseException
     * @throws \Exception
     */
    private function sendGetRequest($route, array $params = []): object
    {
        $response = Http::send(
            $this->client,
            $this->getRoute($route, $params),
            ['queryParams' => $params]
        );

        return $response;
    }
}
