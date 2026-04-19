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

namespace F0ska\AutoGridBundle\Service\Provider;

class BinarySizeProvider
{
    public function getFormattedSize(mixed $binaryString): string
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
}
