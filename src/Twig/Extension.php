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

namespace F0ska\AutoGridBundle\Twig;

use Closure;
use F0ska\AutoGridBundle\Model\AutoGrid;
use F0ska\AutoGridBundle\Model\FieldParameter;
use F0ska\AutoGridBundle\Service\ConfigurationService;
use F0ska\AutoGridBundle\View\ViewServiceRegistry;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment as TwigEnvironment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

use function Symfony\Component\String\u;

class Extension extends AbstractExtension
{
    private TwigEnvironment $twig;
    private ConfigurationService $configurationService;
    private ViewServiceRegistry $viewServiceRegistry;

    public function __construct(
        TwigEnvironment $twig,
        ConfigurationService $configurationService,
        ViewServiceRegistry $viewServiceRegistry
    ) {
        $this->twig = $twig;
        $this->configurationService = $configurationService;
        $this->viewServiceRegistry = $viewServiceRegistry;
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('ag_render', $this->agRender(...)),
            new TwigFunction('ag_run', $this->agRun(...)),
            new TwigFunction('ag_template', $this->agTemplate(...)),
            new TwigFunction('ag_icon', $this->getIcon(...)),
        ];
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('ag_to_int', $this->agToInt(...)),
            new TwigFilter('ag_truncate', $this->agTruncate(...)),
            new TwigFilter('ag_prepare', $this->agPrepare(...)),
        ];
    }

    public function agPrepare(FieldParameter $field, object $entity): array
    {
        $service = $this->viewServiceRegistry->get($field->view['service']);

        return [
            'template' => $field->view['template'],
            'variables' => $service->prepare($entity, $field),
        ];
    }

    public function getIcon(string $name, int $size = 16): string
    {
        $svgStyle = 'style="width:1em;height:1em;margin-bottom:-0.15em;vertical-align:baseline;display:inline-block;"';
        $icons = [
            'arrow-up' => '<i><svg '.$svgStyle.' viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"></polyline></svg></i>',
            'chevron-down' => '<i><svg '.$svgStyle.' viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg></i>',
            'arrow-left' => '<i><svg '.$svgStyle.' viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg></i>',
            'sort-neutral' => '<i><svg '.$svgStyle.' viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 9l7-6 7 6"></path><path d="M5 15l7 6 7-6"></path></svg></i>',
            'sort-asc' => '<i><svg '.$svgStyle.' viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 7l7-5 7 5"></path><path d="M5 16l7-5 7 5"></path></svg></i>',
            'sort-desc' => '<i><svg '.$svgStyle.' viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 17l7 5 7-5"></path><path d="M5 8l7 5 7-5"></path></svg></i>',
            'filter' => '<i><svg '.$svgStyle.' viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg></i>',
            'filter-active' => '<i><svg '.$svgStyle.' viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="1" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg></i>',
            'filter-reset' => '<i><svg '.$svgStyle.' viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon><line x1="3" y1="12" x2="21" y2="12"></line></svg></i>',
            'view' => '<i><svg '.$svgStyle.' viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg></i>',
            'edit' => '<i><svg '.$svgStyle.' viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg></i>',
            'delete' => '<i><svg '.$svgStyle.' viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg></i>',
            'add' => '<i><svg '.$svgStyle.' viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg></i>',
            'search' => '<i><svg '.$svgStyle.' viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg></i>',
            'id' => '<i><svg '.$svgStyle.' viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="4" y1="9" x2="20" y2="9"></line><line x1="4" y1="15" x2="20" y2="15"></line><line x1="10" y1="3" x2="8" y2="21"></line><line x1="16" y1="3" x2="14" y2="21"></line></svg></i>',
            'save' => '<i><svg '.$svgStyle.' viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg></i>',
            'export' => '<i><svg '.$svgStyle.' viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg></i>',
            'warning' => '<i><svg '.$svgStyle.' viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg></i>',
            'boolean-true' => '<i><svg '.$svgStyle.' viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg></i>',
            'boolean-false' => '<i><svg '.$svgStyle.' viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line></svg></i>',
        ];

        return $icons[$name] ?? '';
    }

    public function agRender(AutoGrid $ui): void
    {
        echo $this->twig->render($ui->getTemplate(), $ui->getContext());
    }

    public function agRun(Closure $agClosure, ...$arguments): mixed
    {
        return $agClosure(...$arguments);
    }

    public function agToInt(mixed $value): int
    {
        return (int) $value;
    }

    public function agTruncate(mixed $value, int $length): string
    {
        return u(strip_tags((string) $value))
            ->trim()
            ->truncate($length, '…')
            ->toString();
    }

    public function agTemplate(string $templateCode): string
    {
        return $this->configurationService->getTemplate($templateCode);
    }
}
