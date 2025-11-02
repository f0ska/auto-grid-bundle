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
            if (!$this->actionParametersList->validateParameter($key, $value, $action, $parameters)) {
                continue;
            }
            if (is_array($value) && !empty($request[$key]) && is_array($request[$key])) {
                $request[$key] = array_filter(array_merge($request[$key], $value), $callback);
                continue;
            }
            $request[$key] = is_array($value) ? array_filter($value, $callback) : $value;
        }

        return $this->router->generate(
            $parameters->route['name'],
            array_merge(
                $parameters->route['params'],
                $this->buildActionParams($parameters, $action, array_filter($request, $callback))
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
}
