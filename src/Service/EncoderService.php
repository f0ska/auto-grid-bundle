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

class EncoderService
{
    private const BASE64_REPLACE = ['+' => '$', '/' => '!', '=' => ''];

    public function encodeAction(string $agId, string $action, array $parameters): string
    {
        return str_replace(
            array_keys(self::BASE64_REPLACE),
            array_values(self::BASE64_REPLACE),
            base64_encode(json_encode([$agId, $action, $parameters]))
        );
    }

    public function decodeAction(string $uiAction): array
    {
        return json_decode(
            base64_decode(
                str_replace(
                    array_values(self::BASE64_REPLACE),
                    array_keys(self::BASE64_REPLACE),
                    $uiAction
                )
            ) ?: '[]',
            true
        ) ?: [];
    }
}
