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
        return strtr(
            base64_encode(json_encode([$agId, $action, $parameters])),
            self::BASE64_REPLACE
        );
    }

    public function decodeAction(string $uiAction): array
    {
        return json_decode(
            base64_decode(
                strtr($uiAction, array_flip(self::BASE64_REPLACE))
            ) ?: '[]',
            true
        ) ?: [];
    }
}
