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

namespace FormatPHP\Icu\MessageFormat;

use FormatPHP\Icu\MessageFormat\Parser\CodePoint;
use FormatPHP\Icu\MessageFormat\Parser\DateTimeSkeletonParser;
use FormatPHP\Icu\MessageFormat\Parser\Error;
use FormatPHP\Icu\MessageFormat\Parser\Exception;
use FormatPHP\Icu\MessageFormat\Parser\NumberSkeletonParser;
use FormatPHP\Icu\MessageFormat\Parser\Options;
use FormatPHP\Icu\MessageFormat\Parser\Result;
use FormatPHP\Icu\MessageFormat\Parser\Type;
use FormatPHP\Icu\MessageFormat\Parser\Util\CodePointHelper;
use Ramsey\Collection\Exception\CollectionMismatchException;

use function abs;
use function array_column;
use function assert;
use function count;
use function in_array;
use function is_int;
use function ltrim;
use function mb_str_split;
use function mb_strlen;
use function mb_strpos;
use function mb_substr;
use function min;
use function rtrim;

/**
 * @psalm-type ArgType = "number" | "date" | "time" | "select" | "plural" | "selectordinal" | ""
 * @psalm-type IdentifierType = array{value: string, location: Type\Location}
 * @psalm-type PluralSelectType = array{0: string, 1: Type\PluralOrSelectOption}
 */
class Parser
{
    public const ENCODING = 'UTF-8';

    /** @var string[] */
    private array $messageArray;

    private string $message;
    private int $messageLength;
    private Type\LocationDetails $position;
    private bool $ignoreTag;
    private bool $requiresOtherClause;
    private bool $shouldParseSkeletons;
    private CodePointHelper $codePointHelper;
    private DateTimeSkeletonParser $dateTimeSkeletonParser;
    private NumberSkeletonParser $numberSkeletonParser;

    public function __construct(string $message, ?Options $options = null)
    {
        $this->message = $message;
        $this->messageLength = mb_strlen($message, self::ENCODING);
        $this->messageArray = mb_str_split($message, 1, self::ENCODING);
        $this->position = new Type\LocationDetails(0, 1, 1);
        $this->ignoreTag = $options->ignoreTag ?? false;
        $this->requiresOtherClause = $options->requiresOtherClause ?? false;
        $this->shouldParseSkeletons = $options->shouldParseSkeletons ?? false;
        $this->codePointHelper = new CodePointHelper();
        $this->dateTimeSkeletonParser = new DateTimeSkeletonParser();
        $this->numberSkeletonParser = new NumberSkeletonParser();
    }

    /**
     * @throws CollectionMismatchException
     * @throws Exception\IllegalParserUsageException
     * @throws Exception\InvalidArgumentException
     * @throws Exception\InvalidOffsetException
     * @throws Exception\InvalidSkeletonOption
     * @throws Exception\InvalidUtf8CodeBoundaryException
     * @throws Exception\InvalidUtf8CodePointException
     */
    public function parse(): Result
    {
        if ($this->offset() !== 0) {
            throw new Exception\IllegalParserUsageException('The parser may only be used once');
        }

        return $this->parseMessage(0, '', false);
    }

    /**
     * @param ArgType $parentArgType
     *
     * @throws CollectionMismatchException
     * @throws Exception\InvalidArgumentException
     * @throws Exception\InvalidOffsetException
     * @throws Exception\InvalidSkeletonOption
     * @throws Exception\InvalidUtf8CodeBoundaryException
     * @throws Exception\InvalidUtf8CodePointException
     */
    private function parseMessage(int $nestingLevel, string $parentArgType, bool $expectingCloseTag): Result
    {
        $elements = new Type\ElementCollection();

        while (!$this->isEof()) {
            $char = $this->char();

            if ($char === CodePoint::LEFT_CURLY_BRACE) {
                $result = $this->parseArgument($nestingLevel, $expectingCloseTag);
                if ($result->err instanceof Error) {
                    return $result;
                }
                assert($result->val !== null);
                $elements = $elements->merge($result->val);
            } elseif ($char === CodePoint::RIGHT_CURLY_BRACE && $nestingLevel > 0) {
                break;
            } elseif (
                $char === CodePoint::NUMBER_SIGN
                && ($parentArgType === 'plural' || $parentArgType === 'selectordinal')
            ) {
                $position = $this->clonePosition();
                $this->bump();
                $elements->add(new Type\PoundElement(new Type\Location($position, $this->clonePosition())));
            } elseif (
                $char === CodePoint::LEFT_ANGLE_BRACKET
                && !$this->ignoreTag
                && $this->peek() === CodePoint::FORWARD_SLASH
            ) {
                if ($expectingCloseTag) {
                    break;
                }

                return $this->error(
                    Error::UNMATCHED_CLOSING_TAG,
                    new Type\Location($this->clonePosition(), $this->clonePosition()),
                );
            } elseif (
                $char === CodePoint::LEFT_ANGLE_BRACKET
                && !$this->ignoreTag
                && $this->codePointHelper->isAlpha($this->peek() ?? 0)
            ) {
                $result = $this->parseTag($nestingLevel, $parentArgType);
                if ($result->err instanceof Error) {
                    return $result;
                }
                assert($result->val !== null);
                $elements = $elements->merge($result->val);
            } else {
                $result = $this->parseLiteral($nestingLevel, $parentArgType);
                assert($result->val !== null);
                $elements = $elements->merge($result->val);
            }
        }

        return new Result($elements);
    }

    /**
     * A tag name must start with an ASCII lower/upper case letter. The grammar
     * is based on the
     * {@link https://html.spec.whatwg.org/multipage/custom-elements.html#valid-custom-element-name custom element name}
     * except that a dash is NOT always mandatory and uppercase letters are
     * accepted:
     *
     * ```
     * tag ::= "<" tagName (whitespace)* "/>" | "<" tagName (whitespace)* ">" message "</" tagName (whitespace)* ">"
     * tagName ::= [a-z] (PENChar)*
     * PENChar ::=
     *     "-" | "." | [0-9] | "_" | [a-z] | [A-Z] | #xB7 | [#xC0-#xD6] | [#xD8-#xF6] | [#xF8-#x37D] |
     *     [#x37F-#x1FFF] | [#x200C-#x200D] | [#x203F-#x2040] | [#x2070-#x218F] | [#x2C00-#x2FEF] |
     *     [#x3001-#xD7FF] | [#xF900-#xFDCF] | [#xFDF0-#xFFFD] | [#x10000-#xEFFFF]
     * ```
     *
     * NOTE: We're a bit more lax here since HTML technically does not allow uppercase HTML element, but we do
     * since other tag-based engines like React allow it
     *
     * @param ArgType $parentArgType
     *
     * @throws CollectionMismatchException
     * @throws Exception\InvalidArgumentException
     * @throws Exception\InvalidOffsetException
     * @throws Exception\InvalidSkeletonOption
     * @throws Exception\InvalidUtf8CodeBoundaryException
     * @throws Exception\InvalidUtf8CodePointException
     */
    private function parseTag(int $nestingLevel, string $parentArgType): Result
    {
        $startPosition = $this->clonePosition();
        $this->bump(); // '<'

        $tagName = $this->parseTagName();
        $this->bumpSpace();

        if ($this->bumpIf('/>')) {
            // Self-closing tag.
            return new Result(new Type\ElementCollection([
                new Type\LiteralElement("<$tagName/>", new Type\Location($startPosition, $this->clonePosition())),
            ]));
        } elseif ($this->bumpIf('>')) {
            $childrenResult = $this->parseMessage($nestingLevel + 1, $parentArgType, true);
            if ($childrenResult->err !== null) {
                return $childrenResult;
            }

            $endTagStartPosition = $this->clonePosition();

            if ($this->bumpIf('</')) {
                if ($this->isEof() || !$this->codePointHelper->isAlpha($this->char())) {
                    return $this->error(
                        Error::INVALID_TAG,
                        new Type\Location($endTagStartPosition, $this->clonePosition()),
                    );
                }

                $closingTagNameStartPosition = $this->clonePosition();
                $closingTagName = $this->parseTagName();
                if ($tagName !== $closingTagName) {
                    return $this->error(
                        Error::UNMATCHED_CLOSING_TAG,
                        new Type\Location($closingTagNameStartPosition, $this->clonePosition()),
                    );
                }

                $this->bumpSpace();
                if (!$this->bumpIf('>')) {
                    return $this->error(
                        Error::INVALID_TAG,
                        new Type\Location($endTagStartPosition, $this->clonePosition()),
                    );
                }

                $children = $childrenResult->val;
                assert($children !== null);

                return new Result(new Type\ElementCollection([
                    new Type\TagElement($tagName, $children, new Type\Location($startPosition, $this->clonePosition())),
                ]));
            }

            return $this->error(
                Error::UNCLOSED_TAG,
                new Type\Location($startPosition, $this->clonePosition()),
            );
        }

        return $this->error(
            Error::INVALID_TAG,
            new Type\Location($startPosition, $this->clonePosition()),
        );
    }

    /**
     * This method assumes that the caller has peeked ahead for the first tag character.
     *
     * @throws Exception\InvalidOffsetException
     * @throws Exception\InvalidUtf8CodeBoundaryException
     */
    private function parseTagName(): string
    {
        $startOffset = $this->offset();
        $this->bump(); // the first tag name character

        while (!$this->isEof() && $this->codePointHelper->isPotentialElementNameChar($this->char())) {
            $this->bump();
        }

        $length = $this->offset() - $startOffset;

        return mb_substr($this->message, $startOffset, $length, self::ENCODING);
    }

    /**
     * @throws CollectionMismatchException
     * @throws Exception\InvalidArgumentException
     * @throws Exception\InvalidOffsetException
     * @throws Exception\InvalidSkeletonOption
     * @throws Exception\InvalidUtf8CodeBoundaryException
     * @throws Exception\InvalidUtf8CodePointException
     */
    private function parseArgument(int $nestingLevel, bool $expectingCloseTag): Result
    {
        $openingBracePosition = $this->clonePosition();
        $this->bump(); // '{'
        $this->bumpSpace();

        if ($this->isEof()) {
            return $this->error(
                Error::EXPECT_ARGUMENT_CLOSING_BRACE,
                new Type\Location($openingBracePosition, $this->clonePosition()),
            );
        }

        if ($this->char() === CodePoint::RIGHT_CURLY_BRACE) {
            $this->bump();

            return $this->error(
                Error::EMPTY_ARGUMENT,
                new Type\Location($openingBracePosition, $this->clonePosition()),
            );
        }

        // Argument name.
        $value = $this->parseIdentifierIfPossible()['value'];
        if ($value === '') {
            return $this->error(
                Error::MALFORMED_ARGUMENT,
                new Type\Location($openingBracePosition, $this->clonePosition()),
            );
        }

        $this->bumpSpace();

        if ($this->isEof()) {
            return $this->error(
                Error::EXPECT_ARGUMENT_CLOSING_BRACE,
                new Type\Location($openingBracePosition, $this->clonePosition()),
            );
        }

        switch ($this->char()) {
            // Simple argument: `{name}`
            case CodePoint::RIGHT_CURLY_BRACE:
                $this->bump();

                return new Result(new Type\ElementCollection([
                    new Type\ArgumentElement($value, new Type\Location($openingBracePosition, $this->clonePosition())),
                ]));
            // Argument with options: `{name, format, ...}`
            case CodePoint::COMMA:
                $this->bump();
                $this->bumpSpace();

                if ($this->isEof()) {
                    return $this->error(
                        Error::EXPECT_ARGUMENT_CLOSING_BRACE,
                        new Type\Location($openingBracePosition, $this->clonePosition()),
                    );
                }

                return $this->parseArgumentOptions($nestingLevel, $expectingCloseTag, $value, $openingBracePosition);
        }

        return $this->error(
            Error::MALFORMED_ARGUMENT,
            new Type\Location($openingBracePosition, $this->clonePosition()),
        );
    }

    /**
     * @param ArgType $parentArgType
     *
     * @throws Exception\InvalidOffsetException
     * @throws Exception\InvalidUtf8CodeBoundaryException
     * @throws Exception\InvalidUtf8CodePointException
     */
    private function parseLiteral(int $nestingLevel, string $parentArgType): Result
    {
        $start = $this->clonePosition();
        $value = '';

        while (true) {
            $quoted = $this->tryParseQuote($parentArgType);
            if ($quoted) {
                $value .= $quoted;

                continue;
            }

            $unquoted = $this->tryParseUnquoted($nestingLevel, $parentArgType);
            if ($unquoted) {
                $value .= $unquoted;

                continue;
            }

            $leftAngle = $this->tryParseLeftAngleBracket();
            if ($leftAngle) {
                $value .= $leftAngle;

                continue;
            }

            break;
        }

        $location = new Type\Location($start, $this->clonePosition());

        return new Result(new Type\ElementCollection([new Type\LiteralElement($value, $location)]));
    }

    /**
     * Starting with ICU 4.8, an ASCII apostrophe only starts quoted text if it
     * immediately precedes a character that requires quoting (that is, "only
     * where needed"), and works the same in nested messages as on the top
     * level of the pattern. The new behavior is otherwise compatible.
     *
     * @param ArgType $parentArgType
     *
     * @throws Exception\InvalidOffsetException
     * @throws Exception\InvalidUtf8CodeBoundaryException
     * @throws Exception\InvalidUtf8CodePointException
     */
    private function tryParseQuote(string $parentArgType): ?string
    {
        if ($this->isEof() || $this->char() !== CodePoint::STRAIGHT_APOSTROPHE) {
            return null;
        }

        // Parse escaped char following the apostrophe, or early return if
        // there is no escaped char.
        // Check if is valid escaped character.
        switch ($this->peek()) {
            case CodePoint::STRAIGHT_APOSTROPHE:
                // Two apostrophes in a row; should return as a single one.
                $this->bump();
                $this->bump();

                return "'";
            case CodePoint::LEFT_CURLY_BRACE:
            case CodePoint::LEFT_ANGLE_BRACKET:
            case CodePoint::RIGHT_ANGLE_BRACKET:
            case CodePoint::RIGHT_CURLY_BRACE:
                break;
            case CodePoint::NUMBER_SIGN:
                if ($parentArgType === 'plural' || $parentArgType === 'selectordinal') {
                    break;
                }

                return null;
            default:
                return null;
        }

        $this->bump(); // apostrophe

        $codePoints = [$this->char()]; // escaped char
        $this->bump();

        // Read chars until the optional closing apostrophe is found.
        while (!$this->isEof()) {
            $char = $this->char();
            if ($char === CodePoint::STRAIGHT_APOSTROPHE) {
                if ($this->peek() === CodePoint::STRAIGHT_APOSTROPHE) {
                    $codePoints[] = CodePoint::STRAIGHT_APOSTROPHE;
                    // Bump one more time because we need to skip 2 characters.
                    $this->bump();
                } else {
                    // Optional closing apostrophe.
                    $this->bump();

                    break;
                }
            } else {
                $codePoints[] = $char;
            }
            $this->bump();
        }

        return $this->codePointHelper->fromCodePoint(...$codePoints);
    }

    /**
     * @param ArgType $parentArgType
     *
     * @throws Exception\InvalidOffsetException
     * @throws Exception\InvalidUtf8CodeBoundaryException
     * @throws Exception\InvalidUtf8CodePointException
     */
    private function tryParseUnquoted(int $nestingLevel, string $parentArgType): ?string
    {
        if ($this->isEof()) {
            return null;
        }

        $char = $this->char();

        if (
            $char === CodePoint::LEFT_ANGLE_BRACKET
            || $char === CodePoint::LEFT_CURLY_BRACE
            || (
                $char === CodePoint::NUMBER_SIGN
                && ($parentArgType === 'plural' || $parentArgType === 'selectordinal')
            )
            || ($char === CodePoint::RIGHT_CURLY_BRACE && $nestingLevel > 0)
        ) {
            return null;
        }

        $this->bump();

        return $this->codePointHelper->fromCodePoint($char);
    }

    /**
     * @throws Exception\InvalidOffsetException
     * @throws Exception\InvalidUtf8CodeBoundaryException
     */
    private function tryParseLeftAngleBracket(): ?string
    {
        if (
            !$this->isEof()
            && $this->char() === CodePoint::LEFT_ANGLE_BRACKET
            && (
                $this->ignoreTag
                // If at the opening tag or closing tag position, bail.
                || !$this->codePointHelper->isAlphaOrSlash($this->peek() ?? 0)
            )
        ) {
            $this->bump(); // '<'

            return '<';
        }

        return null;
    }

    /**
     * Advance the parser until the end of the identifier, if it is currently on
     * an identifier character. Return an empty string otherwise.
     *
     * @throws Exception\InvalidArgumentException
     * @throws Exception\InvalidOffsetException
     * @throws Exception\InvalidUtf8CodeBoundaryException
     * @throws Exception\InvalidUtf8CodePointException
     *
     * @psalm-return IdentifierType
     */
    private function parseIdentifierIfPossible(): array
    {
        $startingPosition = $this->clonePosition();
        $startOffset = $this->offset();
        $value = $this->matchIdentifierAtIndex($startOffset);
        $endOffset = $startOffset + mb_strlen($value, self::ENCODING);

        $this->bumpTo($endOffset);

        $endPosition = $this->clonePosition();
        $location = new Type\Location($startingPosition, $endPosition);

        return [
            'value' => $value,
            'location' => $location,
        ];
    }

    /**
     * @throws CollectionMismatchException
     * @throws Exception\InvalidArgumentException
     * @throws Exception\InvalidOffsetException
     * @throws Exception\InvalidSkeletonOption
     * @throws Exception\InvalidUtf8CodeBoundaryException
     * @throws Exception\InvalidUtf8CodePointException
     */
    private function parseArgumentOptions(
        int $nestingLevel,
        bool $expectingCloseTag,
        string $value,
        Type\LocationDetails $openingBracePosition
    ): Result {
        // Parse this range:
        // {name, type, style}
        //        ^---^
        $typeStartPosition = $this->clonePosition();

        /** @var ArgType $argType */
        $argType = $this->parseIdentifierIfPossible()['value'];
        $typeEndPosition = $this->clonePosition();

        switch ($argType) {
            case '':
                // Expecting a style string number, date, time, plural, selectordinal, or select.
                return $this->error(
                    Error::EXPECT_ARGUMENT_TYPE,
                    new Type\Location($typeStartPosition, $typeEndPosition),
                );
            case 'number':
            case 'date':
            case 'time':
                // Parse this range:
                // {name, number, style}
                //              ^-------^
                $this->bumpSpace();

                /** @var array{style: string, styleLocation: Type\Location} | null $styleAndLocation */
                $styleAndLocation = null;

                if ($this->bumpIf(',')) {
                    $this->bumpSpace();

                    $startStylePosition = $this->clonePosition();
                    $result = $this->parseSimpleArgStyleIfPossible();
                    if ($result['error'] !== null) {
                        return new Result(null, $result['error']);
                    }

                    $style = rtrim((string) $result['value'], CodePointHelper::WHITE_SPACE_TOKENS);
                    if (mb_strlen($style) === 0) {
                        return $this->error(
                            Error::EXPECT_ARGUMENT_STYLE,
                            new Type\Location($this->clonePosition(), $this->clonePosition()),
                        );
                    }

                    $styleLocation = new Type\Location($startStylePosition, $this->clonePosition());
                    $styleAndLocation = [
                        'style' => $style,
                        'styleLocation' => $styleLocation,
                    ];
                }

                $argCloseResult = $this->tryParseArgumentClose($openingBracePosition);
                if ($argCloseResult !== null) {
                    return new Result(null, $argCloseResult);
                }

                $location = new Type\Location($openingBracePosition, $this->clonePosition());

                // Extract style or skeleton.
                if (
                    $styleAndLocation !== null
                    && mb_strpos($styleAndLocation['style'], '::', 0, self::ENCODING) === 0
                ) {
                    $skeleton = ltrim(
                        mb_substr($styleAndLocation['style'], 2, null, self::ENCODING),
                        CodePointHelper::WHITE_SPACE_TOKENS,
                    );

                    if ($argType === 'number') {
                        try {
                            $numberSkeleton = $this->numberSkeletonParser->parse(
                                $skeleton,
                                $styleAndLocation['styleLocation'],
                                $this->shouldParseSkeletons,
                            );

                            return new Result(new Type\ElementCollection([
                                new Type\NumberElement($value, $location, $numberSkeleton),
                            ]));
                        } catch (Exception\ParserExceptionInterface $exception) {
                            return new Result(
                                null,
                                new Error(
                                    Error::INVALID_NUMBER_SKELETON,
                                    $this->message,
                                    $styleAndLocation['styleLocation'],
                                ),
                            );
                        }
                    } else {
                        if (mb_strlen($skeleton, self::ENCODING) === 0) {
                            return $this->error(Error::EXPECT_DATE_TIME_SKELETON, $location);
                        }

                        $dateTimeSkeleton = new Type\DateTimeSkeleton(
                            $skeleton,
                            $styleAndLocation['styleLocation'],
                            $this->shouldParseSkeletons ? $this->dateTimeSkeletonParser->parse($skeleton) : null,
                        );

                        if ($argType === 'date') {
                            $element = new Type\DateElement($value, $location, $dateTimeSkeleton);
                        } else {
                            $element = new Type\TimeElement($value, $location, $dateTimeSkeleton);
                        }

                        return new Result(new Type\ElementCollection([$element]));
                    }
                }

                // Regular style or no style.
                if ($argType === 'number') {
                    $element = new Type\NumberElement($value, $location, $styleAndLocation['style'] ?? null);
                } elseif ($argType === 'date') {
                    $element = new Type\DateElement($value, $location, $styleAndLocation['style'] ?? null);
                } else {
                    $element = new Type\TimeElement($value, $location, $styleAndLocation['style'] ?? null);
                }

                return new Result(new Type\ElementCollection([$element]));
            case 'plural':
            case 'selectordinal':
            case 'select':
                // Parse this range:
                // {name, plural, options}
                //              ^---------^
                $typeEndPosition = $this->clonePosition();
                $this->bumpSpace();

                if (!$this->bumpIf(',')) {
                    return $this->error(
                        Error::EXPECT_SELECT_ARGUMENT_OPTIONS,
                        new Type\Location($typeEndPosition, clone $typeEndPosition),
                    );
                }

                $this->bumpSpace();

                // Parse offset:
                // {name, plural, offset:1, options}
                //                ^-----^
                //
                // or the first option:
                //
                // {name, plural, one {...} other {...}}
                //                ^--^
                $identifierAndLocation = $this->parseIdentifierIfPossible();

                $pluralOffset = 0;
                if ($argType !== 'select' && $identifierAndLocation['value'] === 'offset') {
                    if (!$this->bumpIf(':')) {
                        return $this->error(
                            Error::EXPECT_PLURAL_ARGUMENT_OFFSET_VALUE,
                            new Type\Location($this->clonePosition(), $this->clonePosition()),
                        );
                    }
                    $this->bumpSpace();

                    $decimalResult = $this->tryParseDecimalInteger(
                        Error::EXPECT_PLURAL_ARGUMENT_OFFSET_VALUE,
                        Error::INVALID_PLURAL_ARGUMENT_OFFSET_VALUE,
                    );

                    if ($decimalResult['error'] !== null) {
                        return new Result(null, $decimalResult['error']);
                    }

                    // Parse another identifier for option parsing
                    $this->bumpSpace();
                    $identifierAndLocation = $this->parseIdentifierIfPossible();

                    $pluralOffset = $decimalResult['value'];
                }

                $optionsResult = $this->tryParsePluralOrSelectOptions(
                    $nestingLevel,
                    $argType,
                    $expectingCloseTag,
                    $identifierAndLocation,
                );

                if ($optionsResult['error'] !== null) {
                    return new Result(null, $optionsResult['error']);
                }

                $argCloseResult = $this->tryParseArgumentClose($openingBracePosition);
                if ($argCloseResult !== null) {
                    return new Result(null, $argCloseResult);
                }

                $location = new Type\Location($openingBracePosition, $this->clonePosition());
                assert($optionsResult['value'] !== null);

                if ($argType === 'select') {
                    return new Result(new Type\ElementCollection([
                        new Type\SelectElement(
                            $value,
                            array_column($optionsResult['value'], 1, 0),
                            $location,
                        ),
                    ]));
                } else {
                    return new Result(new Type\ElementCollection([
                        new Type\PluralElement(
                            $value,
                            array_column($optionsResult['value'], 1, 0),
                            $pluralOffset,
                            $argType === 'plural' ? 'cardinal' : 'ordinal',
                            $location,
                        ),
                    ]));
                }
        }

        return $this->error(
            Error::INVALID_ARGUMENT_TYPE,
            new Type\Location($typeStartPosition, $typeEndPosition),
        );
    }

    /**
     * @throws Exception\InvalidOffsetException
     * @throws Exception\InvalidUtf8CodeBoundaryException
     */
    private function tryParseArgumentClose(Type\LocationDetails $openingBracePosition): ?Error
    {
        // Parse: {value, number, ::currency/GBP }
        if ($this->isEof() || $this->char() !== CodePoint::RIGHT_CURLY_BRACE) {
            return new Error(
                Error::EXPECT_ARGUMENT_CLOSING_BRACE,
                $this->message,
                new Type\Location($openingBracePosition, $this->clonePosition()),
            );
        }

        $this->bump(); // '}'

        return null;
    }

    /**
     * See: https://github.com/unicode-org/icu/blob/af7ed1f6d2298013dc303628438ec4abe1f16479/icu4c/source/common/messagepattern.cpp#L659
     *
     * @return array{value: string | null, error: Error | null}
     *
     * @throws Exception\InvalidArgumentException
     * @throws Exception\InvalidOffsetException
     * @throws Exception\InvalidUtf8CodeBoundaryException
     */
    private function parseSimpleArgStyleIfPossible(): array
    {
        $nestedBraces = 0;
        $startPosition = $this->clonePosition();

        while (!$this->isEof()) {
            $char = $this->char();
            switch ($char) {
                case CodePoint::STRAIGHT_APOSTROPHE:
                    // Treat apostrophe as quoting but include it in the style part.
                    // Find the end of the quoted literal text.
                    $this->bump();
                    $apostrophePosition = $this->clonePosition();

                    if (!$this->bumpUntil("'")) {
                        return [
                            'value' => null,
                            'error' => new Error(
                                Error::UNCLOSED_QUOTE_IN_ARGUMENT_STYLE,
                                $this->message,
                                new Type\Location($apostrophePosition, $this->clonePosition()),
                            ),
                        ];
                    }

                    $this->bump();

                    break;
                case CodePoint::LEFT_CURLY_BRACE:
                    ++$nestedBraces;
                    $this->bump();

                    break;
                case CodePoint::RIGHT_CURLY_BRACE:
                    if ($nestedBraces > 0) {
                        --$nestedBraces;
                    } else {
                        $length = $this->offset() - $startPosition->offset;

                        return [
                            'value' => mb_substr($this->message, $startPosition->offset, $length, self::ENCODING),
                            'error' => null,
                        ];
                    }

                    break;
                default:
                    $this->bump();

                    break;
            }
        }

        $length = $this->offset() - $startPosition->offset;

        return [
            'value' => mb_substr($this->message, $startPosition->offset, $length, self::ENCODING),
            'error' => null,
        ];
    }

    /**
     * @return array{value: int | null, error: Error | null}
     *
     * @throws Exception\InvalidOffsetException
     * @throws Exception\InvalidUtf8CodeBoundaryException
     *
     * @psalm-param Error::* $expectNumberError
     * @psalm-param Error::* $invalidNumberError
     */
    private function tryParseDecimalInteger(int $expectNumberError, int $invalidNumberError): array
    {
        $sign = 1;
        $startingPosition = $this->clonePosition();

        // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedIf
        if ($this->bumpIf('+')) {
            // Empty on purpose.
        } elseif ($this->bumpIf('-')) {
            $sign = -1;
        }

        $hasDigits = false;
        $decimal = 0;

        while (!$this->isEof()) {
            $char = $this->char();
            if ($char >= CodePoint::ZERO && $char <= CodePoint::NINE) {
                $hasDigits = true;
                $decimal = $decimal * 10 + $char - CodePoint::ZERO;
                $this->bump();
            } else {
                break;
            }
        }

        $location = new Type\Location($startingPosition, $this->clonePosition());

        if (!$hasDigits) {
            return [
                'value' => null,
                'error' => new Error($expectNumberError, $this->message, $location),
            ];
        }

        $decimal *= $sign;
        if (!$this->isSafeInteger($decimal)) {
            return [
                'value' => null,
                'error' => new Error($invalidNumberError, $this->message, $location),
            ];
        }

        return ['value' => $decimal, 'error' => null];
    }

    /**
     * @param int $nestingLevel The current nesting level of messages.
     *     This can be positive when parsing message fragment in select or plural argument options.
     * @param ArgType $parentArgType The parent argument's type.
     * @param bool $expectCloseTag If true, this message is directly or indirectly nested inside
     *     between a pair of opening and closing tags. The nested message will not parse beyond
     *     the closing tag boundary.
     * @param array{value: string, location: Type\Location} $parsedFirstIdentifier If provided,
     *     this is the first identifier-like selector of the argument. It is a by-product
     *     of a previous parsing attempt.
     *
     * @return array{value: array<PluralSelectType> | null, error: Error | null}
     *
     * @throws CollectionMismatchException
     * @throws Exception\InvalidArgumentException
     * @throws Exception\InvalidOffsetException
     * @throws Exception\InvalidUtf8CodeBoundaryException
     * @throws Exception\InvalidUtf8CodePointException
     * @throws Exception\InvalidSkeletonOption
     */
    private function tryParsePluralOrSelectOptions(
        int $nestingLevel,
        string $parentArgType,
        bool $expectCloseTag,
        array $parsedFirstIdentifier
    ): array {
        $hasOtherClause = false;
        $parsedSelectors = [];
        $selector = $parsedFirstIdentifier['value'];
        $selectorLocation = $parsedFirstIdentifier['location'];

        /** @var array<array{string, Type\PluralOrSelectOption}> $options */
        $options = [];

        // Parse:
        // one {one apple}
        // ^--^
        while (true) {
            if (mb_strlen($selector, self::ENCODING) === 0) {
                $startPosition = $this->clonePosition();

                if ($parentArgType !== 'select' && $this->bumpIf('=')) {
                    // Try parse `={number}` selector
                    $decimalResult = $this->tryParseDecimalInteger(
                        Error::EXPECT_PLURAL_ARGUMENT_SELECTOR,
                        Error::INVALID_PLURAL_ARGUMENT_SELECTOR,
                    );

                    if ($decimalResult['error'] !== null) {
                        return ['value' => null, 'error' => $decimalResult['error']];
                    }

                    $selectorLocation = new Type\Location($startPosition, $this->clonePosition());
                    $length = $this->offset() - $startPosition->offset;
                    $selector = mb_substr($this->message, $startPosition->offset, $length, self::ENCODING);
                } else {
                    break;
                }
            }

            // Duplicate selector clauses
            if (in_array($selector, $parsedSelectors)) {
                return [
                    'value' => null,
                    'error' => new Error(
                        $parentArgType === 'select'
                            ? Error::DUPLICATE_SELECT_ARGUMENT_SELECTOR
                            : Error::DUPLICATE_PLURAL_ARGUMENT_SELECTOR,
                        $this->message,
                        $selectorLocation,
                    ),
                ];
            }

            if ($selector === 'other') {
                $hasOtherClause = true;
            }

            // Parse:
            // one {one apple}
            //     ^----------^
            $this->bumpSpace();
            $openingBracePosition = $this->clonePosition();
            if (!$this->bumpIf('{')) {
                return [
                    'value' => null,
                    'error' => new Error(
                        $parentArgType === 'select'
                            ? Error::EXPECT_SELECT_ARGUMENT_SELECTOR_FRAGMENT
                            : Error::EXPECT_PLURAL_ARGUMENT_SELECTOR_FRAGMENT,
                        $this->message,
                        new Type\Location($this->clonePosition(), $this->clonePosition()),
                    ),
                ];
            }

            $fragmentResult = $this->parseMessage($nestingLevel + 1, $parentArgType, $expectCloseTag);
            if ($fragmentResult->err !== null) {
                return [
                    'value' => null,
                    'error' => $fragmentResult->err,
                ];
            }

            $argCloseResult = $this->tryParseArgumentClose($openingBracePosition);
            if ($argCloseResult !== null) {
                return ['value' => null, 'error' => $argCloseResult];
            }

            assert($fragmentResult->val !== null);
            $options[] = [
                $selector,
                new Type\PluralOrSelectOption(
                    $fragmentResult->val,
                    new Type\Location($openingBracePosition, $this->clonePosition()),
                ),
            ];

            // Keep track of the existing selectors.
            $parsedSelectors[] = $selector;

            // Prep next selector clause.
            $this->bumpSpace();
            $parsedIdentifier = $this->parseIdentifierIfPossible();
            $selector = $parsedIdentifier['value'];
            $selectorLocation = $parsedIdentifier['location'];
        }

        if (count($options) === 0) {
            return [
                'value' => null,
                'error' => new Error(
                    $parentArgType === 'select'
                        ? Error::EXPECT_SELECT_ARGUMENT_SELECTOR
                        : Error::EXPECT_PLURAL_ARGUMENT_SELECTOR,
                    $this->message,
                    new Type\Location($this->clonePosition(), $this->clonePosition()),
                ),
            ];
        }

        if ($this->requiresOtherClause && !$hasOtherClause) {
            return [
                'value' => null,
                'error' => new Error(
                    Error::MISSING_OTHER_CLAUSE,
                    $this->message,
                    new Type\Location($this->clonePosition(), $this->clonePosition()),
                ),
            ];
        }

        return [
            'value' => $options,
            'error' => null,
        ];
    }

    private function offset(): int
    {
        return $this->position->offset;
    }

    private function isEof(): bool
    {
        return $this->offset() === $this->messageLength;
    }

    /**
     * @psalm-param Error::* $kind
     */
    private function error(int $kind, Type\Location $location): Result
    {
        return new Result(null, new Error($kind, $this->message, $location));
    }

    /**
     * Returns the Unicode code point at the current position of the parser
     *
     * @throws Exception\InvalidOffsetException
     * @throws Exception\InvalidUtf8CodeBoundaryException
     */
    private function char(): int
    {
        $offset = $this->offset();
        if ($offset >= $this->messageLength) {
            throw new Exception\InvalidOffsetException("Offset $offset is out of bounds");
        }

        $code = $this->codePointHelper->charCodeAt($this->messageArray, $offset);
        if ($code === null) {
            throw new Exception\InvalidUtf8CodeBoundaryException(
                "Offset $offset is an invalid UTF-8 code unit boundary",
            );
        }

        return $code;
    }

    /**
     * Bump the parser to the next UTF-8 code unit
     *
     * @throws Exception\InvalidOffsetException
     * @throws Exception\InvalidUtf8CodeBoundaryException
     */
    private function bump(): void
    {
        if ($this->isEof()) {
            return;
        }

        $code = $this->char();

        if ($code === CodePoint::NEWLINE) {
            $this->position->line++;
            $this->position->column = 1;
            $this->position->offset++;
        } else {
            $this->position->column++;

            // If the code point is above the BMP, skip the surrogate pair.
            $this->position->offset += $code < CodePoint::BMP ? 1 : 2;
        }
    }

    /**
     * If the substring starting at the current position of the parser has
     * the given prefix, then bump the parser to the character immediately
     * following the prefix and return true. Otherwise, don't bump the parser
     * and return false.
     *
     * @throws Exception\InvalidOffsetException
     * @throws Exception\InvalidUtf8CodeBoundaryException
     */
    private function bumpIf(string $prefix): bool
    {
        $messageStartingAtOffset = mb_substr($this->message, $this->offset(), null, self::ENCODING);

        if (mb_strpos($messageStartingAtOffset, $prefix, 0, self::ENCODING) === 0) {
            for ($i = 0; $i < mb_strlen($prefix, self::ENCODING); $i++) {
                $this->bump();
            }

            return true;
        }

        return false;
    }

    /**
     * Bump the parser until the pattern character is found and return `true`.
     * Otherwise, bump to the end of the file and return `false`.
     *
     * @throws Exception\InvalidArgumentException
     * @throws Exception\InvalidOffsetException
     * @throws Exception\InvalidUtf8CodeBoundaryException
     */
    private function bumpUntil(string $pattern): bool
    {
        $currentOffset = $this->offset();
        $index = mb_strpos($this->message, $pattern, $currentOffset, self::ENCODING);

        if ($index !== false) {
            $this->bumpTo($index);

            return true;
        }

        $this->bumpTo($this->messageLength);

        return false;
    }

    /**
     * Bump the parser to the target offset.
     *
     * If target offset is beyond the end of the input, bump the parser to the
     * end of the input.
     *
     * @throws Exception\InvalidArgumentException
     * @throws Exception\InvalidOffsetException
     * @throws Exception\InvalidUtf8CodeBoundaryException
     */
    private function bumpTo(int $targetOffset): void
    {
        if ($this->offset() > $targetOffset) {
            throw new Exception\InvalidArgumentException(
                "targetOffset $targetOffset must be greater than or equal to the current offset {$this->offset()}",
            );
        }

        $targetOffset = min($targetOffset, $this->messageLength);

        while (true) {
            $offset = $this->offset();

            if ($offset === $targetOffset) {
                break;
            }

            if ($offset > $targetOffset) {
                throw new Exception\InvalidUtf8CodeBoundaryException(
                    "targetOffset $targetOffset is an invalid UTF-8 code unit boundary",
                );
            }

            $this->bump();

            if ($this->isEof()) {
                break;
            }
        }
    }

    /**
     * Advance the parser through all whitespace to the next non-whitespace code unit.
     *
     * @throws Exception\InvalidOffsetException
     * @throws Exception\InvalidUtf8CodeBoundaryException
     */
    private function bumpSpace(): void
    {
        while (!$this->isEof() && $this->codePointHelper->isWhiteSpace($this->char())) {
            $this->bump();
        }
    }

    /**
     * Peek at the *next* Unicode codepoint in the input without advancing the parser.
     *
     * If the input has been exhausted, then this returns null.
     *
     * @throws Exception\InvalidOffsetException
     * @throws Exception\InvalidUtf8CodeBoundaryException
     */
    private function peek(): ?int
    {
        if ($this->isEof()) {
            return null;
        }

        return $this->codePointHelper->charCodeAt(
            $this->messageArray,
            $this->offset() + ($this->char() >= CodePoint::BMP ? 2 : 1),
        );
    }

    /**
     * @throws Exception\InvalidUtf8CodePointException
     */
    private function matchIdentifierAtIndex(int $index): string
    {
        /** @var int[] $match */
        $match = [];

        while (true) {
            $char = $this->codePointHelper->charCodeAt($this->messageArray, $index);
            if (
                $char === null
                || $this->codePointHelper->isWhiteSpace($char)
                || $this->codePointHelper->isPatternSyntax($char)
            ) {
                break;
            }
            $match[] = $char;
            $index += $char >= CodePoint::BMP ? 2 : 1;
        }

        return $this->codePointHelper->fromCodePoint(...$match);
    }

    /**
     * @param mixed $value
     */
    private function isSafeInteger($value): bool
    {
        return is_int($value) && abs($value) <= 0x1fffffffffffff;
    }

    private function clonePosition(): Type\LocationDetails
    {
        return clone $this->position;
    }
}
