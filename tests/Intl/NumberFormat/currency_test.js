/**
 * This script generates the snapshots for CurrencyTest::testFormat().
 *
 * Usage:
 *
 *     node currency_test.js | jq > __snapshots__/CurrencyTest__testFormat__1.json
 *
 * PHP appears to have some issues with sign properties. Until these issues are
 * addressed in PHP, we need to work around them in our tests by applying the
 * following changes.
 *
 * NOTE: Be careful when copying/pasting these commands, since some characters
 * (i.e., Е) are not ASCII but are UTF-8 code points for math symbols.
 *
 * Global changes:
 *
 *     perl -0777 -i -pe "s#(currencySign/standard.+signDisplay/always.+\n.+)\+#\${1}#gu" __snapshots__/CurrencyTest__testFormat__1.json
 *     perl -0777 -i -pe "s#(currencySign/standard.+signDisplay/exceptZero.+\n.+)\+#\${1}#gu" __snapshots__/CurrencyTest__testFormat__1.json
 */

const LOCALES = [
    'en',
    'de',
    'zh',
];

const CURRENCIES = [
    'USD',
    'EUR',
    'CNY',
];

const CURRENCY_SIGNS = [
    'standard',
    'accounting',
];

const CURRENCY_DISPLAYS = [
    'symbol',
    'narrowSymbol',
    'code',
    'name',
];

const SIGN_DISPLAYS = [
    'auto',
    'always',
    'never',
    'exceptZero',
];

const NOTATIONS = [
    'engineering',
    'scientific',
    'compact',
    'standard',
];

const COMPACT_DISPLAYS = [
    'long',
    'short',
];

let results = {};
LOCALES.forEach(locale => {
    CURRENCIES.forEach(currency => {
        CURRENCY_SIGNS.forEach(currencySign => {
            CURRENCY_DISPLAYS.forEach(currencyDisplay => {
                SIGN_DISPLAYS.forEach(signDisplay => {
                    NOTATIONS.forEach(notation => {
                        COMPACT_DISPLAYS.forEach(compactDisplay => {
                            results[locale + ' currency ' + currency + ' currencySign/' + currencySign + ' currencyDisplay/' + currencyDisplay + ' signDisplay/' + signDisplay + ' notation/' + notation + ' compactDisplay/' + compactDisplay] = {
                                result: new Intl.NumberFormat(locale, {
                                    style: 'currency',
                                    currency,
                                    currencySign,
                                    currencyDisplay,
                                    signDisplay,
                                    notation,
                                    compactDisplay,
                                }).format(1234.567),
                            };
                        })
                    })
                })
            })
        })
    })
});

console.log(JSON.stringify(results));
