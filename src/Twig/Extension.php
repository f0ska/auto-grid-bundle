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
use Doctrine\Common\Collections\Collection;
use F0ska\AutoGridBundle\Exception\RenderException;
use F0ska\AutoGridBundle\Model\AutoGrid;
use F0ska\AutoGridBundle\Model\FieldParameter;
use F0ska\AutoGridBundle\Service\ConfigurationService;
use F0ska\AutoGridBundle\Service\ParametersService;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment as TwigEnvironment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

use function Symfony\Component\String\u;

class Extension extends AbstractExtension
{
    private TwigEnvironment $twig;
    private TranslatorInterface $translator;
    private ConfigurationService $configurationService;

    public function __construct(
        TwigEnvironment $twig,
        TranslatorInterface $translator,
        ConfigurationService $configurationService
    ) {
        $this->twig = $twig;
        $this->translator = $translator;
        $this->configurationService = $configurationService;
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('agRender', $this->agRender(...)),
            new TwigFunction('agRun', $this->agRun(...)),
            new TwigFunction('agChoiceLabels', $this->agChoiceLabels(...)),
            new TwigFunction('agChoiceValues', $this->agChoiceValues(...)),
            new TwigFunction('agFieldValue', $this->agFieldValue(...)),
            new TwigFunction('agBinarySize', $this->agBinarySize(...)),
            new TwigFunction('agTemplate', $this->agTemplate(...)),
            new TwigFunction('ag_icon', $this->getIcon(...)),
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


    public function getFilters(): array
    {
        return [
            new TwigFilter('agToInt', $this->agToInt(...)),
            new TwigFilter('agTruncate', $this->agTruncate(...)),
        ];
    }

    /**
     * @param AutoGrid $ui
     * @throws LoaderError
     * @throws RenderException
     * @throws RuntimeError
     * @throws SyntaxError
     */
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

    public function agChoiceLabels(mixed $values, FieldParameter $field): array
    {
        if ($values === null) {
            return [];
        }
        if (!is_iterable($values)) {
            $values = [$values];
        }
        if (empty($values)) {
            return [];
        }
        $result = [];
        foreach ($values as $value) {
            $key = $value;
            if (is_object($value)) {
                $key = enum_exists($value::class) ? $value->value : $value->getId();
            }
            $choice = $this->getSelectedChoice($field, $key);
            if ($choice) {
                $label = $choice->label;
                if ($label instanceof TranslatableInterface) {
                    $label = $label->trans($this->translator);
                } elseif (is_string($label)) {
                    $label = $this->translator->trans($label);
                }
                $result[] = $label;
            }
        }
        return $result;
    }

    public function agChoiceValues(mixed $values, FieldParameter $field): array
    {
        if (!is_iterable($values)) {
            $values = [$values];
        }
        $result = [];
        foreach ($values as $value) {
            $key = $value;
            if (is_object($value)) {
                $key = enum_exists($value::class) ? $value->value : $value->getId();
            }
            $choice = $this->getSelectedChoice($field, $key);
            if ($choice) {
                $result[] = $choice->value;
            }
        }
        return $result;
    }

    public function agFieldValue(object $entity, FieldParameter $field): mixed
    {
        $object = $entity;
        $property = $field->name;
        if ($field->mappingType === ParametersService::MAPPING_VIRTUAL) {
            $object = $entity->{"get$field->subObject"}();
            $property = $field->subName;
        }
        if ($object instanceof Collection) {
            $result = [];
            foreach ($object as $item) {
                $result[] = $this->getPropertyValue($item, $property);
            }
            return implode(', ', $result);
        }
        return $this->getPropertyValue($object, $property);
    }

    public function agBinarySize(mixed $binaryString): string
    {
        switch (gettype($binaryString)) {
            case 'string':
                $size = strlen($binaryString);
                break;
            case 'resource':
                $size = fstat($binaryString)['size'];
                break;
            default:
                return '-';
        }

        foreach (['B', 'KB', 'MB', 'GB'] as $suffix) {
            if ($size <= 1024) {
                break;
            }
            $size /= 1024;
        }
        return sprintf('%s %s', round($size, 2), $suffix);
    }

    private function getPropertyValue(object $object, string $property): mixed
    {
        if (method_exists($object, "get$property")) {
            return $object->{"get$property"}();
        }
        if (method_exists($object, "is$property")) {
            return $object->{"is$property"}();
        }
        throw new RenderException("Invalid property $property");
    }

    private function getSelectedChoice(FieldParameter $field, mixed $key): ?ChoiceView
    {
        $type = gettype($key);
        foreach ($field->view['choices'] ?? [] as $choice) {
            $value = $choice->value;
            settype($value, $type);
            if ($value === $key) {
                return $choice;
            }
        }
        return null;
    }
}
