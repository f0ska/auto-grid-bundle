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

use Doctrine\ORM\QueryBuilder;
use F0ska\AutoGridBundle\Model\Parameters;
use Symfony\Contracts\EventDispatcher\Event;

final class ExportEvent extends Event
{
    public const EVENT_NAME = 'f0ska.autogrid.export_action';

    private string $code;
    private QueryBuilder $queryBuilder;
    private Parameters $parameters;
    private ?string $redirectUrl = null;

    public function __construct(string $code, QueryBuilder $queryBuilder, Parameters $parameters)
    {
        $this->code = $code;
        $this->queryBuilder = $queryBuilder;
        $this->parameters = $parameters;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getQueryBuilder(): QueryBuilder
    {
        return $this->queryBuilder;
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
