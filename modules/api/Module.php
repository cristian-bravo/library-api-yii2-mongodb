<?php

declare(strict_types=1);

namespace app\modules\api;

use app\modules\api\repositories\AuthorRepository;
use app\modules\api\repositories\BookRepository;
use app\modules\api\repositories\UserRepository;
use Throwable;
use yii\base\Module as BaseModule;
use Yii;

final class Module extends BaseModule
{
    public $controllerNamespace = 'app\\modules\\api\\controllers';

    public function init(): void
    {
        parent::init();

        $bookRepository = new BookRepository();
        $authorRepository = new AuthorRepository();
        $userRepository = new UserRepository();

        try {
            $bookRepository->ensureIndexes();
            $authorRepository->ensureIndexes();
            $userRepository->ensureIndexes();

            if ((bool) (Yii::$app->params['bootstrapAdminUser'] ?? false)) {
                $userRepository->ensureUserExists(
                    (string) Yii::$app->params['bootstrapAdminUsername'],
                    (string) Yii::$app->params['bootstrapAdminPassword']
                );
            }
        } catch (Throwable $exception) {
            Yii::warning(
                'No se pudieron asegurar indices o usuario inicial: ' . $exception->getMessage(),
                __METHOD__
            );
        }
    }
}
