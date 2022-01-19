/**
 * This script generates the snapshots for DecimalTest::testFormat().
 *
 * Usage:
 *
 *     node decimal_test.js | jq > __snapshots__/DecimalTest__testFormat__1.json
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
 *     perl -0777 -i -pe "s#(signDisplay/always notation/engineering.+\n.+)\+10E3#\${1}10E+3#gu" __snapshots__/DecimalTest__testFormat__1.json
 *     perl -0777 -i -pe "s#(signDisplay/always notation/scientific.+\n.+)\+1E4#\${1}1E+4#gu" __snapshots__/DecimalTest__testFormat__1.json
 *     perl -0777 -i -pe "s#(signDisplay/exceptZero notation/engineering.+\n.+)\+10E3#\${1}10E3#gu" __snapshots__/DecimalTest__testFormat__1.json
 *     perl -0777 -i -pe "s#(signDisplay/exceptZero notation/scientific.+\n.+)\+1E4#\${1}1E4#gu" __snapshots__/DecimalTest__testFormat__1.json
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
    'th',
    'tr',
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
                results[locale + ' decimal signDisplay/' + signDisplay + ' notation/' + notation + ' compactDisplay/' + compactDisplay] = {
                    result: new Intl.NumberFormat(locale, {
                        style: 'decimal',
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
