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

use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class ViewParameter
{
    /**
     * @var int[]|int[][]
     */
    public array $pagination = [
        'page' => 1,
        'count' => 0,
        'limit' => 1,
        'limits' => [1],
    ];

    /**
     * @var array<string, FormInterface>
     */

    public array $filterForms = [];

    /**
     * @var array<string, FormView>
     */
    public array $filterFormViews = [];

    public ?FormInterface $advancedFilterForm = null;

    public ?FormView $advancedFilterFormView = null;

    public ?FormView $massActionFormView = null;

    /**
     * @var array<string, string>
     */
    public array $massActionChoices = [];

    public ?FormView $exportActionFormView = null;

    /**
     * @var array<string, string>
     */
    public array $exportActionChoices = [];

    /**
     * @var array<string, array<string>>
     */
    public array $fieldset = [];

    /**
     * @var string[]
     */
    public array $formThemes = [];
}
