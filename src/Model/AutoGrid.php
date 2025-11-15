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

namespace F0ska\AutoGridBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Expr;
use F0ska\AutoGridBundle\Exception\RenderException;
use Symfony\Component\HttpFoundation\Response;

class AutoGrid
{
    private string $agId;
    private ?Response $response = null;
    private ?string $template = null;
    /**
     * @var array<string, array>
     */
    private array $context = [];
    private Expr\Comparison|Expr\Func|Expr\Andx|Expr\Orx|string|null $queryExpression = null;
    private ?ArrayCollection $queryParameters = null;
    private string $initialActionName = 'grid';
    private array $initialActionParameters = [];

    public function __construct(string $agId)
    {
        $this->agId = $agId;
    }

    public function getId(): string
    {
        return $this->agId;
    }

    public function getTemplate(): string
    {
        if ($this->template) {
            return $this->template;
        }
        if ($this->response) {
            throw new RenderException('Unable to render direct response. Please check your controller code.');
        }
        throw new RenderException('No template. Please set template or provide response instance directly.');
    }

    public function setTemplate(string $template): void
    {
        $this->template = $template;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function setContext(array $context): void
    {
        $this->context = $context;
    }

    public function setResponse(?Response $response): void
    {
        $this->response = $response;
    }

    public function getResponse(): ?Response
    {
        return $this->response;
    }

    public function getQueryExpression(): Expr\Comparison|Expr\Func|Expr\Andx|Expr\Orx|string|null
    {
        return $this->queryExpression;
    }

    public function setQueryExpression(
        Expr\Comparison|Expr\Func|Expr\Andx|Expr\Orx|string|null $queryExpression
    ): void {
        $this->queryExpression = $queryExpression;
    }

    public function getQueryParameters(): ?ArrayCollection
    {
        return $this->queryParameters;
    }

    public function setQueryParameters(?ArrayCollection $queryParameters): void
    {
        $this->queryParameters = $queryParameters;
    }

    public function setInitialAction(?string $actionName): void
    {
        if ($actionName) {
            $this->initialActionName = $actionName;
        }
    }

    public function setInitialParameters(array $initialParameters): void
    {
        $this->initialActionParameters = $initialParameters;
    }

    public function getInitialActionName(): string
    {
        return $this->initialActionName;
    }

    public function getInitialActionParameters(): array
    {
        return $this->initialActionParameters;
    }
}
