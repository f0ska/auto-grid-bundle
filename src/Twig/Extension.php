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
use F0ska\AutoGridBundle\Service\AttributeService;
use F0ska\AutoGridBundle\Service\ConfigurationService;
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
        ];
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('agToInt', $this->agToInt(...)),
            new TwigFilter('agTruncate', $this->agTruncate(...)),
            new TwigFilter('agTheme', $this->agTheme(...)),
        ];
    }

    /**
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function agRender(AutoGrid $ui): void
    {
        echo $this->twig->render($this->agTheme($ui->getTemplate()), $ui->getContext());
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

    public function agTheme(string $value): string
    {
        return str_replace('%theme%', $this->configurationService->getTheme(), $value);
    }

    public function agChoiceLabels(mixed $values, FieldParameter $field): array
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
        if ($field->mappingType === AttributeService::MAPPING_VIRTUAL) {
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
