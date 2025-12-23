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

use F0ska\AutoGridBundle\Model\Parameters;
use Symfony\Component\Routing\RouterInterface;

class ParametersService
{
    private EncoderService $encoderService;
    private RouterInterface $router;
    private ConfigurationService $configuration;
    private ActionParametersListService $actionParametersList;

    public function __construct(
        EncoderService $encoderService,
        RouterInterface $router,
        ConfigurationService $configuration,
        ActionParametersListService $actionParametersList
    ) {
        $this->encoderService = $encoderService;
        $this->router = $router;
        $this->configuration = $configuration;
        $this->actionParametersList = $actionParametersList;
    }

    public function createParametersModel(array $initialParameters): Parameters
    {
        return new Parameters($initialParameters, $this);
    }

    public function getTemplate(string $code): string
    {
        return $this->configuration->getTemplate($code);
    }

    public function getActionUrl(string $action, array $params, Parameters $parameters): string
    {
        $request = $parameters->request;
        $callback = fn($value) => $value !== null && $value != '' && $value !== [];

        foreach ($params as $key => $value) {
            if (!$this->actionParametersList->hasParameter($key)) {
                continue;
            }
            $value = $this->actionParametersList->normalizeParameter($key, $value, $parameters);
            if (is_array($value) && !empty($request[$key]) && is_array($request[$key])) {
                $request[$key] = array_filter(array_merge($request[$key], $value), $callback);
                continue;
            }
            $request[$key] = is_array($value) ? array_filter($value, $callback) : $value;
        }

        $request = array_filter($request, $callback);

        if (!empty($parameters->attributes['route'][$action])) {
            return $this->buildCustomRouteUrl($action, $request, $parameters);
        }

        return $this->router->generate(
            $parameters->route['name'],
            array_merge(
                $parameters->route['params'],
                $this->buildActionParams($parameters, $action, $request)
            )
        );
    }

    private function buildActionParams(Parameters $parameters, string $action, array $params): array
    {
        $result = ['_fragment' => $parameters->attributes['container_id']];
        if ($this->configuration->isSingleParamRequest()) {
            $result[$this->configuration->getSingleParamRequestCode()] = $this->encoderService
                ->encodeAction($parameters->agId, $action, $params);
            return $result;
        }
        $result[$this->configuration->getMultiParamRequestId()] = $parameters->agId;
        $result[$this->configuration->getMultiParamRequestAction()] = $action;
        $result[$this->configuration->getMultiParamRequestParams()] = $params;
        return $result;
    }

    private function buildCustomRouteUrl(string $action, array $request, Parameters $parameters): string
    {
        $prefix = $parameters->route['custom_prefix'];
        $route = ($prefix ?? '') . ($parameters->attributes['route'][$action]['route'] ?? $action);
        $params = $parameters->attributes['route'][$action]['parameters'];
        $finalParams = [];

        if (!empty($request['id'])) {
            $finalParams['id'] = (int) $request['id'];
        }

        /** Reuse available request parameters you provide */
        foreach ($params as $key) {
            if (isset($parameters->route['custom'][$key])) {
                $finalParams[$key] = $parameters->route['custom_params'][$key];
            } elseif (isset($parameters->route['params'][$key])) {
                $finalParams[$key] = $parameters->route['params'][$key];
            }
        }

        return $this->router->generate($route, $finalParams);
    }
}
