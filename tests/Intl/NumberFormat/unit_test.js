/**
 * This script generates the snapshots for UnitTest::testFormat().
 *
 * Usage:
 *
 *     node unit_test.js | jq > __snapshots__/UnitTest__testFormat__1.json
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
 *     perl -0777 -i -pe "s#(signDisplay/always notation/(engineering|scientific).+\n.+)\+1\.235E3#\${1}1.235E+3#gu" __snapshots__/UnitTest__testFormat__1.json
 *     perl -0777 -i -pe "s#(signDisplay/exceptZero notation/(engineering|scientific).+\n.+)\+1\.235E3#\${1}1.235E3#gu" __snapshots__/UnitTest__testFormat__1.json
 */

const LOCALES = [
    'en',
    //'de',
    //'ko',
];

// Use a subset of the units, to reduce the size of the snapshot.
const UNITS = [
    'acre',
    'bit',
    'byte',
    'celsius',
    'centimeter',
    'day',
    'degree',
    'fahrenheit',
    'fluid-ounce',
    'foot',
    //'gallon',
    //'gigabit',
    //'gigabyte',
    //'gram',
    //'hectare',
    //'hour',
    //'inch',
    //'kilobit',
    //'kilobyte',
    //'kilogram',
    //'kilometer',
    //'liter',
    //'megabit',
    //'megabyte',
    //'meter',
    //'mile',
    //'mile-scandinavian',
    //'millimeter',
    //'milliliter',
    //'millisecond',
    //'minute',
    //'month',
    //'ounce',
    //'percent',
    //'petabyte',
    //'pound',
    //'second',
    //'stone',
    //'terabit',
    //'terabyte',
    //'week',
    //'yard',
    //'year',
];

const UNIT_DISPLAYS = [
    'short',
    'long',
    'narrow',
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
    UNITS.forEach(unit => {
        UNIT_DISPLAYS.forEach(unitDisplay => {
            SIGN_DISPLAYS.forEach(signDisplay => {
                NOTATIONS.forEach(notation => {
                    COMPACT_DISPLAYS.forEach(compactDisplay => {
                        results[locale + ' unit ' + unit + ' unitDisplay/' + unitDisplay + ' signDisplay/' + signDisplay + ' notation/' + notation + ' compactDisplay/' + compactDisplay] = {
                            result: new Intl.NumberFormat(locale, {
                                style: 'unit',
                                unit,
                                unitDisplay,
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

});

console.log(JSON.stringify(results));
