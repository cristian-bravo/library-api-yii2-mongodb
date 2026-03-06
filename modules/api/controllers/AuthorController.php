<?php

declare(strict_types=1);

namespace app\modules\api\controllers;

use app\commons\constants\HttpStatus;
use app\modules\api\requests\AuthorCreateRequest;
use app\modules\api\requests\AuthorUpdateRequest;
use app\modules\api\responses\ApiResponse;
use app\modules\api\services\AuthorService;
use Yii;

final class AuthorController extends ApiController
{
    private ?AuthorService $authorService = null;

    public function verbs(): array
    {
        return [
            'index' => ['GET', 'OPTIONS'],
            'view' => ['GET', 'OPTIONS'],
            'create' => ['POST', 'OPTIONS'],
            'update' => ['PUT', 'PATCH', 'OPTIONS'],
            'delete' => ['DELETE', 'OPTIONS'],
            'options' => ['OPTIONS'],
        ];
    }

    public function actionIndex(): array
    {
        $pagination = $this->resolvePagination();
        $expand = trim((string) Yii::$app->request->get('expand', ''));

        $result = $this->service()->list($pagination['page'], $pagination['per_page'], $expand);

        return ApiResponse::success($result['items'], ['pagination' => $result['pagination']]);
    }

    public function actionView(string $id): array
    {
        $expand = trim((string) Yii::$app->request->get('expand', ''));
        $author = $this->service()->getById($id, $expand);

        return ApiResponse::success($author);
    }

    public function actionCreate(): array
    {
        $request = AuthorCreateRequest::fromPayload((array) Yii::$app->request->getBodyParams());
        $author = $this->service()->create($request->toDto());

        return ApiResponse::success($author, [], HttpStatus::CREATED);
    }

    public function actionUpdate(string $id): array
    {
        $request = AuthorUpdateRequest::fromPayload((array) Yii::$app->request->getBodyParams());
        $author = $this->service()->update($id, $request->toDto());

        return ApiResponse::success($author);
    }

    public function actionDelete(string $id): array
    {
        $this->service()->delete($id);

        return ApiResponse::success(['message' => 'Author deleted successfully.']);
    }

    private function service(): AuthorService
    {
        return $this->authorService ??= new AuthorService();
    }
}
