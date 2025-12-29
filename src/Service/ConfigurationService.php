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

use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class ConfigurationService
{
    private ContainerBagInterface $params;
    private RequestStack $requestStack;

    public function __construct(ContainerBagInterface $params, RequestStack $requestStack)
    {
        $this->params = $params;
        $this->requestStack = $requestStack;
    }

    public function getTemplate(string $templateCode): string
    {
        $template = $this->params->get('f0ska.autogrid.template.' . $templateCode);
        if (str_starts_with($template, '@')) {
            return $template;
        }
        return sprintf('%s/%s', $this->getTheme(), $template);
    }

    public function getFieldTemplate(string $type): string
    {
        return $this->getTemplate('field.' . $type);
    }

    public function getFieldFormat(string $type): string
    {
        return $this->params->get('f0ska.autogrid.view.field.format.' . $type);
    }

    public function getPaginationLimits(): array
    {
        return $this->params->get('f0ska.autogrid.grid.pagination_limits');
    }

    public function getGridTextTruncate(): int
    {
        return $this->params->get('f0ska.autogrid.grid.text.truncate');
    }

    public function canStoreNavigationInSession(): bool
    {
        return $this->params->get('f0ska.autogrid.store_navigation_in_session');
    }

    public function getFriendlyId(): string
    {
        return $this->params->get('f0ska.autogrid.view.friendly_id');
    }

    public function isSingleParamRequest(): bool
    {
        return $this->params->get('f0ska.autogrid.request.single_parameter_mode');
    }

    public function getSingleParamRequestCode(): string
    {
        return $this->params->get('f0ska.autogrid.request.single_parameter_code');
    }

    public function getMultiParamRequestId(): string
    {
        return $this->params->get('f0ska.autogrid.request.parameter_code.id');
    }

    public function getMultiParamRequestAction(): string
    {
        return $this->params->get('f0ska.autogrid.request.parameter_code.action');
    }

    public function getMultiParamRequestParams(): string
    {
        return $this->params->get('f0ska.autogrid.request.parameter_code.params');
    }

    public function getTheme(): string
    {
        $default = $this->params->get('f0ska.autogrid.template.theme');
        $override = $this->requestStack->getCurrentRequest()->attributes->get('_autogrid_theme');
        return $override ?? $default;
    }

    public function getFormThemes(): ?array
    {
        $default = $this->params->get('f0ska.autogrid.template.form_themes');
        $override = $this->requestStack->getCurrentRequest()->attributes->get('_autogrid_form_themes');
        return $override ?? $default;
    }

    public function getDefaultButtonsPositions(): array
    {
        return [
            'view_button_in' => [
                'grid' => $this->params->get('f0ska.autogrid.grid.view_button_in_grid'),
                'edit' => $this->params->get('f0ska.autogrid.grid.view_button_in_edit'),
            ],
            'edit_button_in' => [
                'grid' => $this->params->get('f0ska.autogrid.grid.edit_button_in_grid'),
                'view' => $this->params->get('f0ska.autogrid.grid.edit_button_in_view'),
            ],
            'delete_button_in' => [
                'grid' => $this->params->get('f0ska.autogrid.grid.delete_button_in_grid'),
                'view' => $this->params->get('f0ska.autogrid.grid.delete_button_in_view'),
                'edit' => $this->params->get('f0ska.autogrid.grid.delete_button_in_edit'),
            ],
        ];
    }

    public function formBooleanAsSelect(): bool
    {
        return $this->params->get('f0ska.autogrid.form.default_boolean_as_select');
    }

    public function showEntityTitle(): bool
    {
        return $this->params->get('f0ska.autogrid.common.show_entity_title');
    }

    public function formDateAsRange(): bool
    {
        return $this->params->get('f0ska.autogrid.form.default_date_filter_range');
    }
}
