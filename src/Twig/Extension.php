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
    public function __construct(
        private readonly TwigEnvironment $twig,
        private readonly ConfigurationService $configurationService,
        private readonly ViewServiceRegistry $viewServiceRegistry
    ) {
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
