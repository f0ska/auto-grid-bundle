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

use Symfony\Component\HttpFoundation\RequestStack;

class ConfigurationService
{
    public function __construct(
        private readonly array $config,
        private readonly RequestStack $requestStack
    )
    {
    }

    public function getTemplate(string $templateCode): string
    {
        $path = explode('.', $templateCode);
        $template = $this->config['template'];
        foreach ($path as $node) {
            $template = $template[$node];
        }

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
        return $this->config['view']['field_formats'][$type];
    }

    public function getPaginationLimits(): array
    {
        return $this->config['grid']['pagination_limits'];
    }

    public function getGridTextTruncate(): int
    {
        return $this->config['grid']['truncate_text'];
    }

    public function canStoreNavigationInSession(): bool
    {
        return $this->config['session']['store_navigation'];
    }

    public function getFriendlyId(): string
    {
        return $this->config['view']['friendly_id'];
    }

    public function isSingleParamRequest(): bool
    {
        return $this->config['request']['single_parameter_mode'];
    }

    public function getSingleParamRequestCode(): string
    {
        return $this->config['request']['single_parameter_code'];
    }

    public function getMultiParamRequestId(): string
    {
        return $this->config['request']['parameter_codes']['id'];
    }

    public function getMultiParamRequestAction(): string
    {
        return $this->config['request']['parameter_codes']['action'];
    }

    public function getMultiParamRequestParams(): string
    {
        return $this->config['request']['parameter_codes']['params'];
    }

    public function getTheme(): string
    {
        $override = $this->requestStack->getCurrentRequest()->attributes->get('_autogrid_theme');
        return $override ?? $this->config['template']['theme'];
    }

    public function getFormThemes(): ?array
    {
        $override = $this->requestStack->getCurrentRequest()->attributes->get('_autogrid_form_themes');
        return $override ?? $this->config['template']['form_themes'];
    }

    public function getDefaultButtonsPositions(): array
    {
        return $this->config['buttons'];
    }

    public function formBooleanAsSelect(): bool
    {
        return $this->config['form']['default_boolean_as_select'];
    }

    public function showEntityTitle(): bool
    {
        return $this->config['view']['show_entity_title'];
    }

    public function formDateAsRange(): bool
    {
        return $this->config['form']['default_date_filter_range'];
    }

    public function getRelationLabelCandidates(): array
    {
        return $this->config['form']['relation_label_candidates'];
    }
}
