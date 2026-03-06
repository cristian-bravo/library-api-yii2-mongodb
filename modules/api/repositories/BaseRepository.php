<?php

declare(strict_types=1);

namespace app\modules\api\repositories;

use app\commons\constants\ErrorCodes;
use app\models\BaseMongoModel;
use app\modules\api\exceptions\DomainException;
use MongoDB\BSON\ObjectId;

abstract class BaseRepository
{
    protected function assertObjectId(string $id): void
    {
        if (!BaseMongoModel::isValidObjectId($id)) {
            throw new DomainException(
                'Invalid resource identifier format.',
                ErrorCodes::INVALID_IDENTIFIER
            );
        }
    }

    protected function toObjectId(string $id): ObjectId
    {
        $this->assertObjectId($id);

        return new ObjectId($id);
    }

    /**
     * @param array<int, string> $ids
     * @return array<int, ObjectId>
     */
    protected function toObjectIds(array $ids): array
    {
        $normalized = [];
        foreach ($ids as $id) {
            $this->assertObjectId($id);
            $normalized[$id] = new ObjectId($id);
        }

        return array_values($normalized);
    }

    /**
     * @param array<int, string|ObjectId> $ids
     * @return array<int, string>
     */
    protected function toStringIds(array $ids): array
    {
        return BaseMongoModel::stringifyObjectIdArray($ids);
    }
}
