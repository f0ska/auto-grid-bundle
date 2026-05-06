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

namespace F0ska\AutoGridBundle\Attribute\Entity;

use Attribute;
use F0ska\AutoGridBundle\Attribute\AbstractAttribute;
use InvalidArgumentException;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class RowActionPermission extends AbstractAttribute
{
    public const Allow = 'allow';
    public const Deny = 'deny';

    /**
     * @param class-string $service
     * @param string[] $actions
     */
    public function __construct(string $service, array $actions, string $effect = self::Allow)
    {
        if (!in_array($effect, [self::Allow, self::Deny], true)) {
            throw new InvalidArgumentException(sprintf('Unknown row action permission effect "%s".', $effect));
        }

        parent::__construct([
            'service' => $service,
            'actions' => array_values(array_unique($actions)),
            'effect' => $effect,
        ]);
    }
}
