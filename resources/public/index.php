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

namespace FormatPHP\Resources;

use DateTimeImmutable;
use FormatPHP\Intl\DateTimeFormat;
use FormatPHP\Intl\DateTimeFormatOptions;
use FormatPHP\Intl\Locale;
use FormatPHP\Intl\NumberFormat;
use FormatPHP\Intl\NumberFormatOptions;
use Locale as PhpLocale;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use Symfony\Component\VarExporter\VarExporter;
use Throwable;
use Twig\TwigFunction;

use function array_walk;
use function in_array;
use function ksort;
use function str_replace;
use function trim;

require_once __DIR__ . '/../../vendor/autoload.php';

const INTEGER_KEYS = [
    'fractionalSecondDigits',
    'minimumIntegerDigits',
    'minimumFractionDigits',
    'maximumFractionDigits',
    'minimumSignificantDigits',
    'maximumSignificantDigits',
];

const NUMERIC_KEYS = [
    'scale',
    'number',
];

(static function (): void {
    $systemLocale = str_replace(['_POSIX', '-POSIX', '_posix', '-posix'], '', PhpLocale::getDefault());
    $systemLocale = str_replace('_', '-', $systemLocale);

    $twig = Twig::create(__DIR__ . '/../templates');
    $twig->getEnvironment()->addFunction(new TwigFunction('export', fn ($value) => VarExporter::export($value)));

    $app = AppFactory::create();
    $app->add(TwigMiddleware::create($app, $twig));

    $app->get('/', function (Request $request, Response $response): Response {
        $view = Twig::fromRequest($request);

        return $view->render($response, 'home.twig');
    });

    $app->get('/skeleton/numbers', function (Request $request, Response $response) use ($systemLocale): Response {
        $params = $request->getQueryParams();
        array_walk($params, function (&$value, $key) {
            if (trim($value) === '') {
                $value = null;
            } elseif (in_array($key, NUMERIC_KEYS)) {
                $value += 0;
            } elseif (in_array($key, INTEGER_KEYS)) {
                $value = (int) $value;
            }
        });

        $localeParam = $params['locale'] ?? $systemLocale;
        $numberProvided = $params['number'] ?? 1234.5678;
        unset($params['locale'], $params['number']);

        $exception = null;
        $numberFormatter = null;
        $options = null;
        $skeleton = null;
        $formattedNumber = null;

        try {
            $options = new NumberFormatOptions($params);
            $numberFormatter = new NumberFormat(new Locale($localeParam), $options);
            $skeleton = $numberFormatter->getSkeleton();
            $formattedNumber = $numberFormatter->format($numberProvided);
        } catch (Throwable $exception) {
            // Do nothing.
        }

        $view = Twig::fromRequest($request);
        $options = (array) $options;
        ksort($options);

        if ($options['style'] === 'currency') {
            unset($options['style'], $options['currency']);
        }

        return $view->render($response, 'numbers.twig', [
            'localeEvaluated' => $numberFormatter ? $numberFormatter->getEvaluatedLocale() : null,
            'localeProvided' => $localeParam,
            'formattedNumber' => $formattedNumber,
            'numberProvided' => $numberProvided,
            'skeleton' => $skeleton,
            'options' => $options,
            'params' => $params,
            'exception' => $exception,
        ]);
    });

    $app->get('/skeleton/dates', function (Request $request, Response $response) use ($systemLocale): Response {
        $params = $request->getQueryParams();
        array_walk($params, function (&$value, $key) {
            if (trim($value) === '') {
                $value = null;
            } elseif ($key === 'hour12') {
                $value = (bool) $value;
            } elseif (in_array($key, INTEGER_KEYS)) {
                $value = (int) $value;
            }
        });

        $localeParam = $params['locale'] ?? $systemLocale;
        $dateProvided = $params['date'] ?? 'now';
        unset($params['locale'], $params['date']);

        $exception = null;
        $dateFormatter = null;
        $options = null;
        $skeleton = null;
        $formattedDate = null;

        try {
            $options = new DateTimeFormatOptions($params);
            $dateFormatter = new DateTimeFormat(new Locale($localeParam), $options);
            $skeleton = $dateFormatter->getSkeleton();
            $date = new DateTimeImmutable($dateProvided);
            $formattedDate = $dateFormatter->format($date);
        } catch (Throwable $exception) {
            // Do nothing.
        }

        $view = Twig::fromRequest($request);
        $options = (array) $options;
        ksort($options);

        return $view->render($response, 'dates.twig', [
            'localeEvaluated' => $dateFormatter ? $dateFormatter->getEvaluatedLocale() : null,
            'localeProvided' => $localeParam,
            'formattedDate' => $formattedDate,
            'dateProvided' => $dateProvided,
            'skeleton' => $skeleton,
            'options' => $options,
            'params' => $params,
            'exception' => $exception,
        ]);
    });

    $app->run();
})();
