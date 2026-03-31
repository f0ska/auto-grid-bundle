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

namespace F0ska\AutoGridBundle\DBAL;

/**
 * Provides a compatibility layer for Doctrine DBAL types that may differ across versions.
 */
final class TypesCompatibility
{
    /**
     * The 'array' type, which is deprecated in newer versions.
     */
    public const TYPES_ARRAY = 'array';

    /**
     * The 'object' type, which is deprecated in newer versions.
     */
    public const TYPES_OBJECT = 'object';

    /**
     * Custom type for date points, for compatibility with older Symfony/Doctrine versions.
     */
    public const TYPES_DATE_POINT = 'date_point';
    public const TYPES_DAY_POINT  = 'day_point';
    public const TYPES_TIME_POINT = 'time_point';

    /**
     * The 'jsonb' type, which may not be a standard constant.
     */
    public const TYPES_JSONB        = 'jsonb';
    public const TYPES_JSON_OBJECT  = 'json_object';
    public const TYPES_JSONB_OBJECT = 'jsonb_object';
}
