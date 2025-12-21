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

namespace F0ska\AutoGridBundle\Event;

use F0ska\AutoGridBundle\Model\Parameters;
use Symfony\Contracts\EventDispatcher\Event;

final class MassEvent extends Event
{
    public const EVENT_NAME = 'f0ska.autogrid.mass_action';

    private string $code;
    /**
     * @var int[]
     */
    private array $ids;
    private Parameters $parameters;
    private ?string $redirectUrl = null;

    /**
     * @param string $code
     * @param int[] $ids
     * @param Parameters $parameters
     */
    public function __construct(string $code, array $ids, Parameters $parameters)
    {
        $this->code = $code;
        $this->ids = $ids;
        $this->parameters = $parameters;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @return int[]
     */
    public function getIds(): array
    {
        return $this->ids;
    }

    public function getParameters(): Parameters
    {
        return $this->parameters;
    }

    public function getRedirectUrl(): ?string
    {
        return $this->redirectUrl;
    }

    public function setRedirectUrl(?string $redirectUrl): void
    {
        $this->redirectUrl = $redirectUrl;
    }
}
