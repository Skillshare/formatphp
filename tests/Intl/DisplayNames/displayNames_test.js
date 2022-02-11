/**
 * This script generates the snapshots for DisplayNamesTest::testOf.
 *
 * A newer version of Node (i.e., 17.4.0) might be required to run this, for the
 * updated ICU data.
 *
 * Usage:
 *
 *     node displayNames_test.js | jq > __snapshots__/OfTest__testOf__1.json
 *
 * Some data between JS and PHP implementations differs. The data can
 * also differ between browsers and Node versions. This data was generated using
 * Node.js 17.4.0.
 *
 * The following commands clean-up the data generated by this script so that
 * the PHP tests will match (as close as possible) the JS output.
 *
 *     perl -0777 -i -pe "s#(de-DE style/narrow languageDisplay/standard fallback/(?:code|none) type/currency of\(USD\).+\n.+)US-Dollar#\${1}\\\$#gu" __snapshots__/OfTest__testOf__1.json
 *     perl -0777 -i -pe "s#(de-DE style/narrow languageDisplay/standard fallback/(?:code|none) type/currency of\(EUR\).+\n.+)Euro#\${1}€#gu" __snapshots__/OfTest__testOf__1.json
 *     perl -0777 -i -pe "s#(de-DE style/short languageDisplay/standard fallback/(?:code|none) type/currency of\(USD\).+\n.+)US-Dollar#\${1}USD#gu" __snapshots__/OfTest__testOf__1.json
 *     perl -0777 -i -pe "s#(de-DE style/short languageDisplay/standard fallback/(?:code|none) type/currency of\(EUR\).+\n.+)Euro#\${1}EUR#gu" __snapshots__/OfTest__testOf__1.json
 *     perl -0777 -i -pe "s#US Dollar#US dollar#gu" __snapshots__/OfTest__testOf__1.json
 *     perl -0777 -i -pe "s#(en-US style/long languageDisplay/standard fallback/(?:code|none) type/currency of\(EUR\).+\n.+)Euro#\${1}euro#gu" __snapshots__/OfTest__testOf__1.json
 *     perl -0777 -i -pe "s#(de-DE style/(narrow|short) languageDisplay/standard fallback/(?:code|none) type/language of\(en-GB\).+\n.+)UK#\${1}Vereinigtes Königreich#gu" __snapshots__/OfTest__testOf__1.json
 *     perl -0777 -i -pe "s#(de-DE style/(narrow|short) languageDisplay/standard fallback/(?:code|none) type/language of\(en-Latn-US\).+\n.+)USA#\${1}Vereinigte Staaten#gu" __snapshots__/OfTest__testOf__1.json
 *     perl -0777 -i -pe "s#(de-DE style/(narrow|short) languageDisplay/standard fallback/(?:code|none) type/region of\(US\).+\n.+)USA#\${1}Vereinigte Staaten#gu" __snapshots__/OfTest__testOf__1.json
 *     perl -0777 -i -pe "s#(en-US style/(narrow|short) languageDisplay/standard fallback/(?:code|none) type/language of\(en-GB\).+\n.+)UK#\${1}United Kingdom#gu" __snapshots__/OfTest__testOf__1.json
 *     perl -0777 -i -pe "s#(en-US style/(narrow|short) languageDisplay/standard fallback/(?:code|none) type/language of\(en-Latn-US\).+\n.+)US#\${1}United States#gu" __snapshots__/OfTest__testOf__1.json
 *     perl -0777 -i -pe "s#(en-US style/(narrow|short) languageDisplay/standard fallback/(?:code|none) type/region of\(US\).+\n.+)US#\${1}United States#gu" __snapshots__/OfTest__testOf__1.json
 *     perl -0777 -i -pe "s#(en-US style/narrow languageDisplay/standard fallback/(?:code|none) type/currency of\(EUR\).+\n.+)Euro#\${1}€#gu" __snapshots__/OfTest__testOf__1.json
 *     perl -0777 -i -pe "s#(en-US style/narrow languageDisplay/standard fallback/(?:code|none) type/currency of\(USD\).+\n.+)US dollar#\${1}\\\$#gu" __snapshots__/OfTest__testOf__1.json
 *     perl -0777 -i -pe "s#(en-US style/short languageDisplay/standard fallback/(?:code|none) type/currency of\(EUR\).+\n.+)Euro#\${1}EUR#gu" __snapshots__/OfTest__testOf__1.json
 *     perl -0777 -i -pe "s#(en-US style/short languageDisplay/standard fallback/(?:code|none) type/currency of\(USD\).+\n.+)US dollar#\${1}USD#gu" __snapshots__/OfTest__testOf__1.json
 *     perl -0777 -i -pe "s#(ko-KR style/narrow languageDisplay/standard fallback/(?:code|none) type/currency of\(EUR\).+\n.+)유로#\${1}€#gu" __snapshots__/OfTest__testOf__1.json
 *     perl -0777 -i -pe "s#(ko-KR style/narrow languageDisplay/standard fallback/(?:code|none) type/currency of\(USD\).+\n.+)미국 달러#\${1}\\\$#gu" __snapshots__/OfTest__testOf__1.json
 *     perl -0777 -i -pe "s#(ko-KR style/short languageDisplay/standard fallback/(?:code|none) type/currency of\(EUR\).+\n.+)유로#\${1}EUR#gu" __snapshots__/OfTest__testOf__1.json
 *     perl -0777 -i -pe "s#(ko-KR style/short languageDisplay/standard fallback/(?:code|none) type/currency of\(USD\).+\n.+)미국 달러#\${1}USD#gu" __snapshots__/OfTest__testOf__1.json
 */

const LOCALES = [
    'en-US',
    'de-DE',
    'ko-KR',
];

const STYLE = [
    'long',
    'narrow',
    'short',
];

const TYPE = {
    currency: ['USD', 'EUR', 'FOO'],
    language: ['en-Latn-US', 'en-GB', 'pt-BR', 'foobar'],
    region: ['US', 'BR', '419', 'YY', '123'],
    script: ['Latn', 'Arab', 'Foob'],
    // calendar: [], These values have no support in JS.
    // dateTimeField: [],
};

const LANGUAGE_DISPLAY = [
    //'dialect', // PHP isn't able to support "dialect"
    'standard',
];

const FALLBACK = [
    'code',
    'none',
];

let results = {};

LOCALES.forEach(locale => {
    STYLE.forEach(style => {
        LANGUAGE_DISPLAY.forEach(languageDisplay => {
            FALLBACK.forEach(fallback => {
                Object.entries(TYPE).forEach(([type, tests]) => {
                    let options = {style, languageDisplay, fallback, type};
                    tests.forEach(testValue => {
                        results[locale + ' style/' + style + ' languageDisplay/' + languageDisplay + ' fallback/' + fallback + ' type/' + type + ' of(' + testValue + ')'] = {
                            result: new Intl.DisplayNames(locale, options).of(testValue) ?? null,
                            options,
                        }
                    })
                })
            })
        })
    })
});

console.log(JSON.stringify(results));
