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

use F0ska\AutoGridBundle\Action\ErrorAction;
use F0ska\AutoGridBundle\Exception\RequestException;
use F0ska\AutoGridBundle\Model\AutoGrid;
use F0ska\AutoGridBundle\Model\RequestInfo;
use Symfony\Component\DependencyInjection\Attribute\Lazy;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

#[Lazy]
class RequestService
{
    private RequestStack $requestStack;
    private ActionService $actionService;
    private EncoderService $encoderService;
    private ConfigurationService $configuration;

    public function __construct(
        RequestStack $requestStack,
        ActionService $actionService,
        EncoderService $encoderService,
        ConfigurationService $configuration
    ) {
        $this->requestStack = $requestStack;
        $this->actionService = $actionService;
        $this->encoderService = $encoderService;
        $this->configuration = $configuration;
    }

    public function processRequest(AutoGrid $autoGrid): void
    {
        $request = $this->requestStack->getCurrentRequest();
        if (empty($request)) {
            throw new RequestException('Request not found');
        }

        $requestInfo = $this->parseRequest($request);

        $commonParameters = [
            'route' => [
                'name' => $request->attributes->get('_route'),
                'params' => $request->attributes->get('_route_params'),
                'custom_prefix' => $autoGrid->getRoutePrefix(),
                'custom_params' => $autoGrid->getRouteParameters(),
            ]
        ];

        if ($requestInfo->isBadRequest()) {
            $this->actionService->executeAction(
                $autoGrid,
                ErrorAction::getActionCode(),
                [],
                $commonParameters + ['message' => 'Bad request']
            );
            return;
        }

        if ($autoGrid->getId() === $requestInfo->getAgId()) {
            $this->storeNavigation($requestInfo->getAgId(), $requestInfo->getAction(), $requestInfo->getParameters());
            $this->actionService->executeAction($autoGrid, $requestInfo->getAction(), $requestInfo->getParameters(), $commonParameters);
            return;
        }

        $navigation = $this->restoreNavigation($autoGrid->getId());
        if ($navigation && !empty($requestInfo->getAgId())) {
            $this->actionService->executeAction(
                $autoGrid,
                reset($navigation),
                end($navigation),
                $commonParameters
            );
            return;
        }

        $this->actionService->executeAction(
            $autoGrid,
            $autoGrid->getInitialActionName(),
            $autoGrid->getInitialActionParameters(),
            $commonParameters
        );
    }

    private function parseRequest(Request $request): RequestInfo
    {
        $isSingleParam = $this->configuration->isSingleParamRequest();
        $actionData = $isSingleParam ? $this->getSingleParameterAction($request) : $this->getMultiParameterAction($request);

        if (empty($actionData)) {
            return new RequestInfo(null, null, [], false);
        }

        if (
            isset($actionData[0]) && is_string($actionData[0]) &&
            isset($actionData[1]) && is_string($actionData[1]) &&
            isset($actionData[2]) && is_array($actionData[2])
        ) {
            return new RequestInfo($actionData[0], $actionData[1], $actionData[2], false);
        }

        return new RequestInfo(null, null, [], true);
    }

    private function getSingleParameterAction(Request $request): ?array
    {
        $encodedAction = $this->getRequestString($request, $this->configuration->getSingleParamRequestCode());
        if (empty($encodedAction)) {
            return null;
        }
        return $this->encoderService->decodeAction($encodedAction);
    }

    private function getMultiParameterAction(Request $request): ?array
    {
        $id = $this->getRequestString($request, $this->configuration->getMultiParamRequestId());
        $action = $this->getRequestString($request, $this->configuration->getMultiParamRequestAction());
        $params = $request->query->all($this->configuration->getMultiParamRequestParams());

        if (is_null($id) && is_null($action) && empty($params)) {
            return null;
        }
        return [$id, $action, $params];
    }

    private function storeNavigation(string $agId, string $action, array $parameters): void
    {
        if (!$this->configuration->canStoreNavigationInSession()) {
            return;
        }
        $key = $this->getSessionKey($agId);
        $this->requestStack->getSession()->set($key, [$action, $parameters]);
    }

    private function restoreNavigation(string $agId): ?array
    {
        if (!$this->configuration->canStoreNavigationInSession()) {
            return null;
        }
        $key = $this->getSessionKey($agId);
        if (!$this->requestStack->getSession()->has($key)) {
            return null;
        }
        return $this->requestStack->getSession()->get($key);
    }

    private function getSessionKey(string $agId): string
    {
        return sprintf('autogrid_%s', $agId);
    }

    private function getRequestString(Request $request, string $name): ?string
    {
        $value = $request->query->get($name);
        if (is_string($value) && !empty($value)) {
            return $value;
        }

        $value = $request->attributes->get($name);
        if (is_string($value) && !empty($value)) {
            return $value;
        }

        return null;
    }
}
