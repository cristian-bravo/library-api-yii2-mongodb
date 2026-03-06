<?php

declare(strict_types=1);

namespace app\modules\api\controllers;

use app\commons\constants\HttpStatus;
use app\modules\api\requests\BookCreateRequest;
use app\modules\api\requests\BookUpdateRequest;
use app\modules\api\responses\ApiResponse;
use app\modules\api\services\BookService;
use Yii;

final class BookController extends ApiController
{
    private ?BookService $bookService = null;

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
        $book = $this->service()->getById($id, $expand);

        return ApiResponse::success($book);
    }

    public function actionCreate(): array
    {
        $request = BookCreateRequest::fromPayload((array) Yii::$app->request->getBodyParams());
        $book = $this->service()->create($request->toDto());

        return ApiResponse::success($book, [], HttpStatus::CREATED);
    }

    public function actionUpdate(string $id): array
    {
        $request = BookUpdateRequest::fromPayload((array) Yii::$app->request->getBodyParams());
        $book = $this->service()->update($id, $request->toDto());

        return ApiResponse::success($book);
    }

    public function actionDelete(string $id): array
    {
        $this->service()->delete($id);

        return ApiResponse::success(['message' => 'Book deleted successfully.']);
    }

    private function service(): BookService
    {
        return $this->bookService ??= new BookService();
    }
}
