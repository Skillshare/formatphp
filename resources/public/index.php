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

namespace FormatPHP\Resources;

use DateTimeImmutable;
use FormatPHP\Intl\DateTimeFormat;
use FormatPHP\Intl\DateTimeFormatOptions;
use FormatPHP\Intl\Locale;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use Symfony\Component\VarExporter\VarExporter;
use Throwable;
use Twig\TwigFunction;

use function array_walk;
use function ksort;
use function trim;

require_once __DIR__ . '/../../vendor/autoload.php';

(static function (): void {
    $twig = Twig::create(__DIR__ . '/../templates');
    $twig->getEnvironment()->addFunction(new TwigFunction('export', fn ($value) => VarExporter::export($value)));

    $app = AppFactory::create();
    $app->add(TwigMiddleware::create($app, $twig));

    $app->get('/', function (Request $request, Response $response): Response {
        $view = Twig::fromRequest($request);

        return $view->render($response, 'home.twig');
    });

    $app->get('/skeleton/numbers', function (Request $request, Response $response): Response {
        $view = Twig::fromRequest($request);

        return $view->render($response, 'numbers.twig');
    });

    $app->get('/skeleton/dates', function (Request $request, Response $response): Response {
        $params = $request->getQueryParams();
        array_walk($params, function (&$value, $key) {
            if (trim($value) === '') {
                $value = null;
            } elseif ($key === 'hour12') {
                $value = (bool) $value;
            } elseif ($key === 'fractionalSecondDigits') {
                $value = (int) $value;
            }
        });

        $localeParam = $params['locale'] ?? null;
        unset($params['locale']);

        $exception = null;

        try {
            $locale = new Locale($localeParam);
            $options = new DateTimeFormatOptions($params);
            $dateFormatter = new DateTimeFormat($locale, $options);
            $skeleton = $dateFormatter->getSkeleton();

            $date = new DateTimeImmutable();
            $formattedDate = $dateFormatter->format($date);
        } catch (Throwable $exception) {
            $dateFormatter = null;
            $options = null;
            $skeleton = null;
            $formattedDate = null;
        }

        $view = Twig::fromRequest($request);
        $options = (array) $options;
        ksort($options);

        return $view->render($response, 'dates.twig', [
            'localeEvaluated' => $dateFormatter ? $dateFormatter->getEvaluatedLocale() : null,
            'localeProvided' => $localeParam,
            'formattedDate' => $formattedDate,
            'skeleton' => $skeleton,
            'options' => $options,
            'params' => $params,
            'exception' => $exception,
        ]);
    });

    $app->run();
})();
