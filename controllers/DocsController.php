<?php

declare(strict_types=1);

namespace app\controllers;

use Symfony\Component\Yaml\Yaml;
use Yii;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

final class DocsController extends Controller
{
    public function actionIndex(): string
    {
        $specFile = dirname(__DIR__) . '/docs/swagger.yaml';

        Yii::$app->response->format = Response::FORMAT_RAW;
        Yii::$app->response->headers->set('Content-Type', 'text/html; charset=UTF-8');

        return $this->renderPartial('index', [
            'specUrl' => Url::to(['/docs/openapi']),
            'apiHomeUrl' => Url::to(['/api/default/index']),
            'loginUrl' => Url::to(['/api/auth/login']),
            'specSummary' => $this->buildSpecSummary($specFile),
        ]);
    }

    public function actionOpenapi(): string
    {
        $specFile = dirname(__DIR__) . '/docs/swagger.yaml';
        if (!is_file($specFile)) {
            throw new NotFoundHttpException('OpenAPI bundle not found. Run "composer openapi:build".');
        }

        $content = file_get_contents($specFile);
        if ($content === false) {
            throw new NotFoundHttpException('Unable to read the OpenAPI bundle.');
        }

        Yii::$app->response->format = Response::FORMAT_RAW;
        Yii::$app->response->headers->set('Content-Type', 'application/yaml; charset=UTF-8');

        return $content;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildSpecSummary(string $specFile): array
    {
        $fallback = [
            'title' => 'Library API',
            'version' => '2.0.0',
            'openapiVersion' => '3.0.3',
            'description' => 'Documentacion interactiva de la API.',
            'serverUrl' => Url::to(['/'], true),
            'serverLabel' => 'local',
            'tags' => ['Auth', 'Books', 'Authors'],
            'tagCount' => 3,
            'pathCount' => 0,
            'operationCount' => 0,
            'generatedAt' => null,
        ];

        if (!is_file($specFile) || !class_exists(Yaml::class)) {
            return $fallback;
        }

        try {
            $document = Yaml::parseFile($specFile);
            if (!is_array($document)) {
                return $fallback;
            }

            $tags = [];
            foreach ((array) ($document['tags'] ?? []) as $tag) {
                $name = trim((string) ($tag['name'] ?? ''));
                if ($name !== '') {
                    $tags[] = $name;
                }
            }

            $paths = (array) ($document['paths'] ?? []);
            $operationCount = 0;
            $httpMethods = ['get', 'post', 'put', 'patch', 'delete', 'options', 'head', 'trace'];

            foreach ($paths as $pathItem) {
                if (!is_array($pathItem)) {
                    continue;
                }

                foreach (array_keys($pathItem) as $method) {
                    if (in_array(strtolower((string) $method), $httpMethods, true)) {
                        $operationCount++;
                    }
                }
            }

            $serverUrl = trim((string) ($document['servers'][0]['url'] ?? $fallback['serverUrl']));
            $serverHost = parse_url($serverUrl, PHP_URL_HOST);
            $serverPort = parse_url($serverUrl, PHP_URL_PORT);
            $serverLabel = is_string($serverHost) && $serverHost !== ''
                ? $serverHost . ($serverPort ? ':' . $serverPort : '')
                : (string) $fallback['serverLabel'];

            return [
                'title' => trim((string) ($document['info']['title'] ?? $fallback['title'])),
                'version' => trim((string) ($document['info']['version'] ?? $fallback['version'])),
                'openapiVersion' => trim((string) ($document['openapi'] ?? $fallback['openapiVersion'])),
                'description' => trim((string) ($document['info']['description'] ?? $fallback['description'])),
                'serverUrl' => $serverUrl,
                'serverLabel' => $serverLabel,
                'tags' => $tags !== [] ? $tags : $fallback['tags'],
                'tagCount' => $tags !== [] ? count($tags) : $fallback['tagCount'],
                'pathCount' => count($paths),
                'operationCount' => $operationCount,
                'generatedAt' => date('Y-m-d H:i', filemtime($specFile) ?: time()),
            ];
        } catch (\Throwable) {
            return $fallback;
        }
    }
}
