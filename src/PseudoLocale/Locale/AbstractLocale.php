<?php

/**
 * This file is part of skillshare/formatphp
 *
 * skillshare/formatphp is open source software: you can distribute
 * it and/or modify it under the terms of the MIT License
 * (the "License"). You may not use this file except in
 * compliance with the License.
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or
 * implied. See the License for the specific language governing
 * permissions and limitations under the License.
 *
 * @copyright Copyright (c) Skillshare, Inc. <https://www.skillshare.com>
 * @license https://opensource.org/licenses/MIT MIT License
 */

declare(strict_types=1);

namespace FormatPHP\PseudoLocale\Locale;

use FormatPHP\Icu\MessageFormat\Parser;
use FormatPHP\Icu\MessageFormat\Parser\Type\ElementCollection;
use FormatPHP\Icu\MessageFormat\Printer;
use FormatPHP\PseudoLocale\PseudoLocaleInterface;
use Ramsey\Collection\Exception\CollectionMismatchException;

use function assert;

abstract class AbstractLocale implements PseudoLocaleInterface
{
    protected const ASCII = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r',
        's', 't', 'u', 'v', 'w', 'x', 'y', 'z', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N',
        'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
    ];

    protected const ACCENTED_ASCII = ['â', 'ḃ', 'ć', 'ḋ', 'è', 'ḟ', 'ĝ', 'ḫ', 'í', 'ĵ', 'ǩ', 'ĺ', 'ṁ', 'ń', 'ŏ', 'ṗ',
        'ɋ', 'ŕ', 'ś', 'ṭ', 'ů', 'ṿ', 'ẘ', 'ẋ', 'ẏ', 'ẓ', 'Ḁ', 'Ḃ', 'Ḉ', 'Ḋ', 'Ḕ', 'Ḟ', 'Ḡ', 'Ḣ', 'Ḭ', 'Ĵ', 'Ḵ', 'Ļ',
        'Ḿ', 'Ŋ', 'Õ', 'Ṕ', 'Ɋ', 'Ŕ', 'Ṡ', 'Ṯ', 'Ũ', 'Ṽ', 'Ẅ', 'Ẍ', 'Ÿ', 'Ƶ',
    ];

    abstract protected function generate(ElementCollection $elementCollection): ElementCollection;

    /**
     * @throws Parser\Exception\InvalidOffsetException
     * @throws Parser\Exception\IllegalParserUsageException
     * @throws Parser\Exception\InvalidUtf8CodePointException
     * @throws Parser\Exception\InvalidUtf8CodeBoundaryException
     * @throws Parser\Exception\InvalidArgumentException
     * @throws Parser\Exception\InvalidSkeletonOption
     * @throws CollectionMismatchException
     * @throws Parser\Exception\UnableToParseMessageException
     */
    public function convert(string $message): string
    {
        $printer = new Printer();
        $parser = new Parser($message);
        $result = $parser->parse();

        if ($result->err !== null) {
            throw new Parser\Exception\UnableToParseMessageException($result->err);
        }

        assert($result->val !== null);

        return $printer->printAst($this->generate($result->val));
    }
}
