/**
 * This script was derived from the FormatJS intl-numberformat package to
 * generate the initial snapshots for PercentTest::testFormat().
 *
 * https://github.com/formatjs/formatjs/blob/78d35580ccd31f143bc1f3884326ecfe234c6929/packages/intl-numberformat/tests/percent.test.ts
 *
 * Usage:
 *
 *     node percent_test.js | jq > __snapshots__/PercentTest__testFormat__1.json
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
 *     perl -0777 -i -pe "s#(signDisplay/always notation/(engineering|scientific).+\n.+)\+1E6#\${1}1E+6#gu" __snapshots__/PercentTest__testFormat__1.json
 *     perl -0777 -i -pe "s#(signDisplay/exceptZero notation/(engineering|scientific).+\n.+)\+1E6#\${1}1E6#gu" __snapshots__/PercentTest__testFormat__1.json
 *
 * Locale specific changes:
 *
 *     perl -0777 -i -pe "s#(sv percent signDisplay/always notation/(engineering|scientific).+\n.+)\+1×10\^6#\${1}1×10^+6#gu" __snapshots__/PercentTest__testFormat__1.json
 *     perl -0777 -i -pe "s#(sv percent signDisplay/exceptZero notation/(engineering|scientific).+\n.+)\+1×10\^6#\${1}1×10^6#gu" __snapshots__/PercentTest__testFormat__1.json
 *     perl -0777 -i -pe "s#(tr percent signDisplay/always notation/(engineering|scientific).+\n.+)\+%1E6#\${1}%1E+6#gu" __snapshots__/PercentTest__testFormat__1.json
 *     perl -0777 -i -pe "s#(tr percent signDisplay/exceptZero notation/(engineering|scientific).+\n.+)\+%1E6#\${1}%1E6#gu" __snapshots__/PercentTest__testFormat__1.json
 *     perl -0777 -i -pe "s#(uk percent signDisplay/always notation/(engineering|scientific).+\n.+)\+1Е6#\${1}1Е+6#gu" __snapshots__/PercentTest__testFormat__1.json
 *     perl -0777 -i -pe "s#(uk percent signDisplay/exceptZero notation/(engineering|scientific).+\n.+)\+1Е6#\${1}1Е6#gu" __snapshots__/PercentTest__testFormat__1.json
 */

const LOCALES = [
    'en',
    'en-GB',
    'da',
    'de',
    'es',
    'fr',
    'id',
    'it',
    'ja',
    'ko',
    'ms',
    'nl',
    'pl',
    'pt',
    'ru',
    'sv',
    'th',
    'tr',
    'uk',
    'zh',
    'en-BS',
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
    SIGN_DISPLAYS.forEach(signDisplay => {
        NOTATIONS.forEach(notation => {
            COMPACT_DISPLAYS.forEach(compactDisplay => {
                results[locale + ' percent signDisplay/' + signDisplay + ' notation/' + notation + ' compactDisplay/' + compactDisplay] = {
                    result: new Intl.NumberFormat(locale, {
                        style: 'percent',
                        signDisplay,
                        notation,
                        compactDisplay,
                    }).format(10000),
                };
            })
        })
    })
});

console.log(JSON.stringify(results));
