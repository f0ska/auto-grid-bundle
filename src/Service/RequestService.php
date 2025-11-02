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

use F0ska\AutoGridBundle\Exception\RequestException;
use F0ska\AutoGridBundle\Model\AutoGrid;
use Symfony\Component\DependencyInjection\Attribute\Lazy;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

#[Lazy]
class RequestService
{
    private const string ERROR_ACTION = 'error';

    private ?Request $request;
    private ?string $agId = null;
    private ?string $action = null;
    private array $parameters = [];
    private array $commonParameters = [];
    private bool $badRequest = true;

    private RequestStack $requestStack;
    private ActionService $actionService;
    private EncoderService $encoderService;
    private ConfigurationService $configuration;

    /**
     * @throws RequestException
     */
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
        $this->init();
    }

    public function processRequest(AutoGrid $autoGrid): void
    {
        if ($this->badRequest) {
            $this->actionService->executeAction(
                $autoGrid,
                self::ERROR_ACTION,
                [],
                $this->commonParameters + ['message' => 'Bad request']
            );
            return;
        }

        if ($autoGrid->getId() === $this->agId) {
            $this->storeNavigation();
            $this->actionService->executeAction($autoGrid, $this->action, $this->parameters, $this->commonParameters);
            return;
        }

        $navigation = $this->restoreNavigation($autoGrid->getId());
        if ($navigation && !empty($this->agId)) {
            $this->actionService->executeAction(
                $autoGrid,
                reset($navigation),
                end($navigation),
                $this->commonParameters
            );
            return;
        }

        $this->actionService->executeAction(
            $autoGrid,
            $autoGrid->getInitialActionName(),
            $autoGrid->getInitialActionParameters(),
            $this->commonParameters
        );
    }

    private function init(): void
    {
        $this->request = $this->requestStack->getCurrentRequest();
        if (empty($this->request)) {
            throw new RequestException('Request not found');
        }
        $this->commonParameters['route'] = [
            'name' => $this->request->attributes->get('_route'),
            'params' => $this->request->attributes->get('_route_params'),
        ];
        $this->defineCurrentAction();
    }

    private function defineCurrentAction(): void
    {
        $isSingleParam = $this->configuration->isSingleParamRequest();
        $action = $isSingleParam ? $this->getSingleParameterAction() : $this->getMultiParameterAction();

        if (empty($action)) {
            return;
        }

        if (
            isset($action[0])
            && is_string($action[0])
            && isset($action[1])
            && is_string($action[1])
            && isset($action[2])
            && is_array($action[2])
        ) {
            $this->agId = $action[0];
            $this->action = $action[1];
            $this->parameters = $action[2];
            $this->badRequest = false;
        }
    }

    private function getSingleParameterAction(): ?array
    {
        $encodedAction = $this->request->get($this->configuration->getSingleParamRequestCode());
        if (empty($encodedAction)) {
            $this->badRequest = false;
            return null;
        }
        if (!is_string($encodedAction)) {
            return null;
        }
        return $this->encoderService->decodeAction($encodedAction);
    }

    private function getMultiParameterAction(): ?array
    {
        $id = $this->request->get($this->configuration->getMultiParamRequestId());
        $action = $this->request->get($this->configuration->getMultiParamRequestAction());
        $params = $this->request->get($this->configuration->getMultiParamRequestParams(), []);

        if (is_null($id) && is_null($action) && empty($params)) {
            $this->badRequest = false;
            return null;
        }
        return [$id, $action, $params];
    }

    private function storeNavigation(): void
    {
        if (!$this->configuration->canStoreNavigationInSession()) {
            return;
        }
        $key = $this->getSessionKey($this->agId);
        $this->requestStack->getSession()->set($key, [$this->action, $this->parameters]);
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
}
