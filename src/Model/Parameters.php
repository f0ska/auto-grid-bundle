<?php
/*
 * This file is part of the F0ska/AutoGrid package.
 *
 * (c) Victor Shvets
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace F0ska\AutoGridBundle\Model;

use F0ska\AutoGridBundle\Service\ParametersService;

class Parameters
{
    private ParametersService $parametersService;

    public string $action = '';
    public array $route;
    public string $agId;
    public array $permissions;
    public array $request = [];
    public array $attributes;
    /** @var FieldParameter[] */
    public array $fields = [];
    public array $query;
    public ?string $message = null;
    public ViewParameter $view;

    public function __construct(array $initial, ParametersService $parametersService)
    {
        foreach ($initial as $key => $value) {
            $this->{$key} = $value;
        }
        $this->view = new ViewParameter();
        $this->parametersService = $parametersService;
    }

    public function isAllowed(string $action): bool
    {
        return !empty($this->permissions[$action]);
    }

    public function isAnyAllowed(): bool
    {
        return !empty(array_filter($this->permissions));
    }

    public function isFieldAllowed(FieldParameter $field, string $action): bool
    {
        return !empty($field->permissions[$action]);
    }

    public function actionUrl(string $action, array $params = []): string
    {
        return $this->parametersService->getActionUrl($action, $params, $this);
    }

    public function initPagination(int $totalResults): void
    {
        $limit = $this->view->pagination['limit'];
        $page = $this->view->pagination['page'];
        if ($totalResults && (int) ceil($totalResults / $limit) < $page) {
            unset($this->request['page']);
            $this->view->pagination['page'] = 1;
        }
        $this->view->pagination['count'] = $totalResults;
    }

    public function getTemplate(string $code): string
    {
        return $this->attributes[$code . '_template'] ?? $this->parametersService->getTemplate($code);
    }

    public function render(array $parameters = []): array
    {
        $parameters['action'] = $this->action;
        $parameters['route'] = $this->route;
        $parameters['agId'] = $this->agId;
        $parameters['permissions'] = $this->permissions;
        $parameters['request'] = $this->request;
        $parameters['attributes'] = $this->attributes;
        $parameters['fields'] = $this->fields;
        $parameters['query'] = $this->query;
        $parameters['view'] = $this->view;
        $parameters['message'] = $parameters['message'] ?? $this->message;

        $parameters['agIsAllowed'] = fn(string $action) => $this->isAllowed($action);
        $parameters['agIsAnyAllowed'] = fn() => $this->isAnyAllowed();
        $parameters['agActionUrl'] = fn(string $action, array $params = []) => $this->actionUrl($action, $params);

        return $parameters;
    }
}
