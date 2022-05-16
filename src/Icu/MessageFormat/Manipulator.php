<?php

/**
 * This file is part of skillshare/formatphp
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or
 * implied. See the License for the specific language governing
 * permissions and limitations under the License.
 *
 * @copyright Copyright (c) Skillshare, Inc. <https://www.skillshare.com>
 * @license https://opensource.org/licenses/Apache-2.0 Apache License, Version 2.0
 */

declare(strict_types=1);

namespace FormatPHP\Icu\MessageFormat;

use FormatPHP\Icu\MessageFormat\Parser\Type\ElementCollection;
use FormatPHP\Icu\MessageFormat\Parser\Type\PluralElement;
use FormatPHP\Icu\MessageFormat\Parser\Type\SelectElement;

use function array_slice;
use function array_values;

/**
 * Provides functionality to manipulate a parsed AST
 *
 * @internal
 */
class Manipulator
{
    /**
     * Hoist all selectors to the beginning of the AST & flatten the
     * resulting options
     *
     * For example,
     *
     *     I have {count, plural, one{a dog} other{many dogs}}
     *
     * becomes
     *
     *     {count, plural, one{I have a dog} other{I have many dogs}}
     *
     * If there are multiple selectors, the order of which one is hoisted 1st
     * is non-deterministic.
     *
     * The goal is to provide as many full sentences as possible since
     * fragmented sentences are not translator-friendly.
     */
    public function hoistSelectors(ElementCollection $ast): ElementCollection
    {
        for ($i = 0; $i < $ast->count(); $i++) {
            $element = $ast[$i];

            if ($element instanceof PluralElement || $element instanceof SelectElement) {
                $cloned = clone $element;
                $options = $cloned->options;
                foreach ($options as $option) {
                    $option->value = $this->hoistSelectors(new ElementCollection([
                        ...array_values(array_slice($ast->toArray(), 0, $i)),
                        ...array_values($option->value->toArray()),
                        ...array_values(array_slice($ast->toArray(), $i + 1)),
                    ]));
                }

                return new ElementCollection([$cloned]);
            }
        }

        return $ast;
    }
}
