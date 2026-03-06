<?php

declare(strict_types=1);

namespace app\modules\api\filters;

use app\commons\constants\ErrorCodes;
use app\commons\constants\HttpStatus;
use app\modules\api\exceptions\DomainException;
use app\modules\api\exceptions\NotFoundException;
use app\modules\api\exceptions\UnauthorizedException;
use app\modules\api\exceptions\ValidationException;
use app\modules\api\responses\ApiResponse;
use Throwable;
use Yii;
use yii\base\ActionFilter;
use yii\web\HttpException;

final class ApiExceptionFilter extends ActionFilter
{
    public function aroundAction($action, $runAction): mixed
    {
        try {
            return $runAction();
        } catch (ValidationException $exception) {
            return ApiResponse::error(
                $exception->getErrorCode(),
                $exception->getMessage(),
                $exception->getDetails(),
                HttpStatus::UNPROCESSABLE_ENTITY
            );
        } catch (UnauthorizedException $exception) {
            return ApiResponse::error(
                $exception->getErrorCode(),
                $exception->getMessage(),
                $exception->getDetails(),
                HttpStatus::UNAUTHORIZED
            );
        } catch (NotFoundException $exception) {
            return ApiResponse::error(
                $exception->getErrorCode(),
                $exception->getMessage(),
                $exception->getDetails(),
                HttpStatus::NOT_FOUND
            );
        } catch (DomainException $exception) {
            return ApiResponse::error(
                $exception->getErrorCode(),
                $exception->getMessage(),
                $exception->getDetails(),
                HttpStatus::BAD_REQUEST
            );
        } catch (HttpException $exception) {
            return ApiResponse::error(
                ErrorCodes::DOMAIN_ERROR,
                $exception->getMessage(),
                [],
                $exception->statusCode
            );
        } catch (Throwable $exception) {
            Yii::error($exception, __METHOD__);

            return ApiResponse::error(
                ErrorCodes::INTERNAL_ERROR,
                'Internal server error.',
                [],
                HttpStatus::INTERNAL_SERVER_ERROR
            );
        }
    }
}
