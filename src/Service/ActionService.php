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

use F0ska\AutoGridBundle\Exception\GridAccessDeniedException;
use F0ska\AutoGridBundle\Exception\GridActionNotFoundException;
use F0ska\AutoGridBundle\Exception\GridException;
use F0ska\AutoGridBundle\Exception\InvalidGridParameterException;
use F0ska\AutoGridBundle\Model\AutoGrid;

class ActionService
{
    public function __construct(
        private readonly ViewService $viewService,
        private readonly ActionListService $actionList,
        private readonly ActionParametersListService $actionParametersList,
        private readonly ParametersService $parametersService,
        private readonly CustomizationService $customizationService
    ) {
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
                    'context' => $autoGrid->getGridContext(),
                    'has_dql' => false,
                    'virtual_alias_map' => [],
                ],
                'customization' => $autoGrid->getCustomizationParameters(),
                'mode' => $autoGrid->getMode(),
            ]
        );

        if (isset($commonParameters['message'])) {
            $parameters->message = $commonParameters['message'];
        }

        try {
            if (!$this->actionList->hasAction($action)) {
                throw new GridActionNotFoundException();
            }

            $actionObject = $this->actionList->getAction($action);

            if (!$parameters->isAllowed($action)) {
                throw new GridAccessDeniedException();
            }
            if (
                $actionObject->isIdRequired()
                && !array_key_exists('id', $requestParameters)
            ) {
                throw new InvalidGridParameterException('Bad Request');
            }

            $parameters->action = $actionObject->getCode();

            foreach ($requestParameters as $key => $value) {
                $parameters->request[$key] = $this->actionParametersList->normalizeParameter($key, $value, $parameters);
            }

            $this->viewService->prepareView($parameters);
            $this->customizationService->executeCustomizations($autoGrid, $parameters);
            $actionObject->execute($autoGrid, $parameters);
        } catch (GridException $exception) {
            $parameters->message = $exception->getMessage();
            $this->actionList->getErrorAction()->execute($autoGrid, $parameters);
        }
    }
}
