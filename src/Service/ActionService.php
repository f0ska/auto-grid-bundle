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
use F0ska\AutoGridBundle\Exception\ActionParameterException;
use F0ska\AutoGridBundle\Model\AutoGrid;

class ActionService
{
    private ViewService $viewService;
    private ActionListService $actionList;
    private ActionParametersListService $actionParametersList;
    private ParametersService $parametersService;
    private CustomizationService $customizationService;

    public function __construct(
        ViewService $viewService,
        ActionListService $actionList,
        ActionParametersListService $actionParametersList,
        ParametersService $parametersService,
        CustomizationService $customizationService
    ) {
        $this->viewService = $viewService;
        $this->actionList = $actionList;
        $this->actionParametersList = $actionParametersList;
        $this->parametersService = $parametersService;
        $this->customizationService = $customizationService;
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

        if (!$this->actionList->hasAction($action)) {
            $parameters->message = 'Unknown Action';
            $this->actionList->getErrorAction()->execute($autoGrid, $parameters);
            return;
        }

        $actionObject = $this->actionList->getAction($action);

        if (!$parameters->isAllowed($action)) {
            $parameters->message = 'Not Allowed';
            $this->actionList->getErrorAction()->execute($autoGrid, $parameters);
            return;
        }
        if (
            $actionObject->isIdRequired()
            && !$this->actionParametersList->hasParameter('id')
        ) {
            $parameters->message = 'Bad Request';
            $this->actionList->getErrorAction()->execute($autoGrid, $parameters);
            return;
        }

        $parameters->action = $actionObject->getCode();

        foreach ($requestParameters as $key => $value) {
            try {
                $parameters->request[$key] = $this->actionParametersList->normalizeParameter($key, $value, $parameters);
            } catch (ActionParameterException $exception) {
                $parameters->message = $exception->getMessage();
                $this->actionList->getErrorAction()->execute($autoGrid, $parameters);
            }
        }

        $this->viewService->prepareView($parameters);

        try {
            $this->customizationService->executeCustomizations($autoGrid, $parameters);
            $actionObject->execute($autoGrid, $parameters);
        } catch (ActionException $exception) {
            $parameters->message = $exception->getMessage();
            $this->actionList->getErrorAction()->execute($autoGrid, $parameters);
        }
    }
}
