<?php

declare(strict_types=1);

namespace FormatPHP\Test\Intl;

use BadMethodCallException;
use FormatPHP\Exception\InvalidArgumentException;
use FormatPHP\Intl\Locale;
use FormatPHP\Intl\LocaleOptions;
use FormatPHP\Test\TestCase;
use Locale as PhpLocale;

class LocaleTest extends TestCase
{
    public function testExceptionWhenLocaleIsInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to parse "f-oo-bar" as a valid locale string');

        new Locale('f-oo-bar');
    }

    public function testLocaleWithEverythingAsPartOfTheIdentifier(): void
    {
        $localeString = 'zh-cmn-Hans-CN-boont-u-kf-lower-co-trad-kn-false-ca-buddhist-nu-latn-hc-h24';

        $locale = new Locale($localeString);

        $this->assertSame('cmn-Hans-CN-BOONT', $locale->baseName());
        $this->assertSame('cmn', $locale->language());
        $this->assertSame('CN', $locale->region());
        $this->assertSame('Hans', $locale->script());
        $this->assertSame('trad', $locale->collation());
        $this->assertSame('lower', $locale->caseFirst());
        $this->assertFalse($locale->numeric());
        $this->assertSame('buddhist', $locale->calendar());
        $this->assertSame('latn', $locale->numberingSystem());
        $this->assertSame('h24', $locale->hourCycle());
        $this->assertSame(
            'cmn-Hans-CN-BOONT-u-ca-buddhist-kf-lower-co-trad-kn-false-hc-h24-nu-latn',
            $locale->toString(),
        );
    }

    public function testLocaleWithEverythingAsPartOfTheOptions(): void
    {
        $locale = new Locale('en-latn-CA-boont', new LocaleOptions(
            'buddhist',
            'lower',
            'emoji',
            'h24',
            'cmn',
            'latn',
            false,
            'CN',
            'Hans',
        ));

        $this->assertSame(
            'cmn-Hans-CN-BOONT-u-ca-buddhist-kf-lower-co-emoji-hc-h24-nu-latn-kn-false',
            $locale->toString(),
        );
    }

    public function testLocaleWithEmptyLanguage(): void
    {
        $locale = new Locale(
            'en-Latn-US-variant',
            new LocaleOptions(null, null, null, null, ''),
        );

        $this->assertSame('', $locale->baseName());
    }

    public function testLocaleWithOnlyLanguage(): void
    {
        $locale = new Locale('en');

        $this->assertSame('en', $locale->baseName());
        $this->assertSame('en', $locale->language());
        $this->assertNull($locale->region());
        $this->assertNull($locale->script());
        $this->assertNull($locale->collation());
        $this->assertNull($locale->caseFirst());
        $this->assertFalse($locale->numeric());
        $this->assertNull($locale->calendar());
        $this->assertNull($locale->numberingSystem());
        $this->assertNull($locale->hourCycle());
    }

    public function testLocaleWithUndefinedLocale(): void
    {
        $defaultLocale = new Locale(PhpLocale::getDefault());
        $undefinedLocale = new Locale(Locale::UNDEFINED_LOCALE);

        $this->assertSame($defaultLocale->toString(), $undefinedLocale->toString());
    }

    public function testLocaleWithNoLocale(): void
    {
        $defaultLocale = new Locale(PhpLocale::getDefault());
        $noLocale = new Locale();

        $this->assertSame($defaultLocale->toString(), $noLocale->toString());
    }

    public function testBaseNameWithMultipleVariants(): void
    {
        $locale = new Locale('en-US-variant1-variant2-variant3');

        $this->assertSame('en-US-VARIANT1-VARIANT2-VARIANT3', $locale->baseName());
    }

    public function testCalendarReturnsEthioaa(): void
    {
        $locale = new Locale('en-US@calendar=ethiopic-amete-alem');

        $this->assertSame('ethioaa', $locale->calendar());
        $this->assertSame('en-US-u-ca-ethioaa', $locale->toString());
    }

    public function testCalendarReturnsGregory(): void
    {
        $locale = new Locale('en-US@calendar=gregorian');

        $this->assertSame('gregory', $locale->calendar());
        $this->assertSame('en-US-u-ca-gregory', $locale->toString());
    }

    public function testCaseFirstReturnsStringFalse(): void
    {
        $locale = new Locale('en-US@colcasefirst=no');

        $this->assertSame('false', $locale->caseFirst());
        $this->assertSame('en-US-u-kf-false', $locale->toString());
    }

    public function testCollationReturnsDict(): void
    {
        $locale = new Locale('en-US@collation=dictionary');

        $this->assertSame('dict', $locale->collation());
        $this->assertSame('en-US-u-co-dict', $locale->toString());
    }

    public function testCollationReturnsGb2312(): void
    {
        $locale = new Locale('en-US@collation=gb2312han');

        $this->assertSame('gb2312', $locale->collation());
        $this->assertSame('en-US-u-co-gb2312', $locale->toString());
    }

    public function testCollationReturnsPhonebk(): void
    {
        $locale = new Locale('en-US@collation=phonebook');

        $this->assertSame('phonebk', $locale->collation());
        $this->assertSame('en-US-u-co-phonebk', $locale->toString());
    }

    public function testCollationReturnsTraditio(): void
    {
        $locale = new Locale('en-US@numbers=traditional');

        $this->assertSame('traditio', $locale->numberingSystem());
        $this->assertSame('en-US-u-nu-traditio', $locale->toString());
    }

    public function testNumericWithYes(): void
    {
        $locale = new Locale('en-US@colnumeric=yes');

        $this->assertTrue($locale->numeric());
        $this->assertSame('en-US-u-kn-true', $locale->toString());
    }

    public function testNumericWithNo(): void
    {
        $locale = new Locale('en-US@colnumeric=no');

        $this->assertFalse($locale->numeric());
        $this->assertSame('en-US-u-kn-false', $locale->toString());
    }

    public function testNumericWithInvalidValue(): void
    {
        $locale = new Locale('en-US@colnumeric=foo');

        $this->assertFalse($locale->numeric());
        $this->assertSame('en-US-u-kn-foo', $locale->toString());
    }

    public function testPassesThroughUnknownKeyword(): void
    {
        $locale = new Locale('en-US-u-ka-noignore');

        $this->assertSame('en-US-u-colalternate-non-ignorable', $locale->toString());
    }

    public function testMaximizeThrowsException(): void
    {
        $locale = new Locale('en-US');

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Method not implemented');

        $locale->maximize();
    }

    public function testMinimizeThrowsException(): void
    {
        $locale = new Locale('en-US');

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Method not implemented');

        $locale->minimize();
    }

    public function testWithCalendar(): void
    {
        $locale1 = new Locale('en-US');
        $locale2 = $locale1->withCalendar('buddhist');

        $this->assertNull($locale1->calendar());
        $this->assertSame('en-US', $locale1->toString());
        $this->assertSame('buddhist', $locale2->calendar());
        $this->assertSame('en-US-u-ca-buddhist', $locale2->toString());
    }

    public function testWithCaseFirst(): void
    {
        $locale1 = new Locale('en-US');
        $locale2 = $locale1->withCaseFirst('upper');

        $this->assertNotSame($locale1, $locale2);
        $this->assertNull($locale1->caseFirst());
        $this->assertSame('upper', $locale2->caseFirst());
        $this->assertSame('en-US', $locale1->toString());
        $this->assertSame('en-US-u-kf-upper', $locale2->toString());
    }

    public function testWithCollation(): void
    {
        $locale1 = new Locale('en-US');
        $locale2 = $locale1->withCollation('big5han');

        $this->assertNotSame($locale1, $locale2);
        $this->assertNull($locale1->collation());
        $this->assertSame('big5han', $locale2->collation());
        $this->assertSame('en-US', $locale1->toString());
        $this->assertSame('en-US-u-co-big5han', $locale2->toString());
    }

    public function testWithHourCycle(): void
    {
        $locale1 = new Locale('en-US');
        $locale2 = $locale1->withHourCycle('h23');

        $this->assertNotSame($locale1, $locale2);
        $this->assertNull($locale1->hourCycle());
        $this->assertSame('h23', $locale2->hourCycle());
        $this->assertSame('en-US', $locale1->toString());
        $this->assertSame('en-US-u-hc-h23', $locale2->toString());
    }

    public function testWithLanguage(): void
    {
        $locale1 = new Locale('en-US');
        $locale2 = $locale1->withLanguage('es');

        $this->assertNotSame($locale1, $locale2);
        $this->assertSame('en', $locale1->language());
        $this->assertSame('es', $locale2->language());
        $this->assertSame('en-US', $locale1->toString());
        $this->assertSame('es-US', $locale2->toString());
    }

    public function testWithNumberingSystem(): void
    {
        $locale1 = new Locale('en-US');
        $locale2 = $locale1->withNumberingSystem('arab');

        $this->assertNotSame($locale1, $locale2);
        $this->assertNull($locale1->numberingSystem());
        $this->assertSame('arab', $locale2->numberingSystem());
        $this->assertSame('en-US', $locale1->toString());
        $this->assertSame('en-US-u-nu-arab', $locale2->toString());
    }

    public function testWithNumeric(): void
    {
        $locale1 = new Locale('en-US');
        $locale2 = $locale1->withNumeric(true);
        $locale3 = $locale1->withNumeric(false);

        $this->assertNotSame($locale1, $locale2);
        $this->assertNotSame($locale2, $locale3);
        $this->assertFalse($locale1->numeric());
        $this->assertTrue($locale2->numeric());
        $this->assertFalse($locale3->numeric());
        $this->assertSame('en-US', $locale1->toString());
        $this->assertSame('en-US-u-kn-true', $locale2->toString());
        $this->assertSame('en-US-u-kn-false', $locale3->toString());
    }

    public function testWithRegion(): void
    {
        $locale1 = new Locale('en-US');
        $locale2 = $locale1->withRegion('CA');

        $this->assertNotSame($locale1, $locale2);
        $this->assertSame('US', $locale1->region());
        $this->assertSame('CA', $locale2->region());
        $this->assertSame('en-US', $locale1->toString());
        $this->assertSame('en-CA', $locale2->toString());
    }

    public function testWithScript(): void
    {
        $locale1 = new Locale('en-US');
        $locale2 = $locale1->withScript('Latn');

        $this->assertNotSame($locale1, $locale2);
        $this->assertNull($locale1->script());
        $this->assertSame('Latn', $locale2->script());
        $this->assertSame('en-US', $locale1->toString());
        $this->assertSame('en-Latn-US', $locale2->toString());
    }
}
