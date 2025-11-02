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

namespace F0ska\AutoGridBundle\Service;

use F0ska\AutoGridBundle\Exception\ActionException;
use F0ska\AutoGridBundle\Model\AutoGrid;

class ActionService
{
    private PermissionService $permissionService;
    private AttributeService $attributeService;
    private ViewService $viewService;
    private ActionListService $actionList;
    private ActionParametersListService $actionParametersList;
    private ParametersService $parametersService;

    public function __construct(
        PermissionService $permissionService,
        AttributeService $attributeService,
        ViewService $viewService,
        ActionListService $actionList,
        ActionParametersListService $actionParametersList,
        ParametersService $parametersService
    ) {
        $this->permissionService = $permissionService;
        $this->attributeService = $attributeService;
        $this->viewService = $viewService;
        $this->actionList = $actionList;
        $this->actionParametersList = $actionParametersList;
        $this->parametersService = $parametersService;
    }

    public function executeAction(
        AutoGrid $autoGrid,
        string $action,
        array $requestParameters = [],
        array $commonParameters = []
    ): void {
        $parameters = $this->parametersService->createParametersModel(
            $commonParameters + [
                'agId' => $autoGrid->getId(),
                'query' => [
                    'expression' => $autoGrid->getQueryExpression(),
                    'parameters' => $autoGrid->getQueryParameters(),
                ],
            ]
        );

        $this->attributeService->buildAttributes($parameters);

        if (!$this->actionList->hasAction($action)) {
            $parameters->message = 'Unknown Action';
            $this->actionList->getErrorAction()->execute($autoGrid, $parameters);
            return;
        }
        if (!$parameters->isAllowed($action)) {
            $parameters->message = 'Not Allowed';
            $this->actionList->getErrorAction()->execute($autoGrid, $parameters);
            return;
        }
        if ($this->actionList->getAction($action)->isIdRequired() && empty($requestParameters['id'])) {
            $parameters->message = 'Bad Request';
            $this->actionList->getErrorAction()->execute($autoGrid, $parameters);
            return;
        }

        $parameters->action = $this->actionList->getAction($action)->getCode();

        foreach ($requestParameters as $key => $value) {
            if ($this->actionParametersList->validateParameter($key, $value, $action, $parameters)) {
                $parameters->request[$key] = $this->actionParametersList->normalizeParameter($key, $value);
            }
        }

        $this->viewService->prepareView($parameters);
        try {
            $this->actionList->getAction($action)->execute($autoGrid, $parameters);
        } catch (ActionException $exception) {
            $parameters->message = $exception->getMessage();
            $this->actionList->getErrorAction()->execute($autoGrid, $parameters);
        }
    }
}
