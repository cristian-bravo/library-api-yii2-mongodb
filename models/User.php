<?php

declare(strict_types=1);

namespace app\models;

use MongoDB\BSON\ObjectId;
use Yii;
use yii\web\IdentityInterface;

final class User extends BaseMongoModel implements IdentityInterface
{
    public static function collectionName(): array
    {
        return ['library_db', 'users'];
    }

    public function attributes(): array
    {
        return [
            '_id',
            'username',
            'password_hash',
            'auth_token',
            'token_expires_at',
            'created_at',
            'updated_at',
        ];
    }

    public function rules(): array
    {
        return [
            [['username', 'password_hash'], 'required'],
            [['username'], 'string', 'min' => 3, 'max' => 100],
            [['password_hash', 'auth_token'], 'string'],
            [['token_expires_at'], 'integer', 'min' => 0],
            [['username'], 'unique'],
        ];
    }

    public function beforeSave($insert): bool
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        $this->touchTimestamps((bool) $insert);

        return true;
    }

    public static function findIdentity($id)
    {
        if (!is_string($id) || !static::isValidObjectId($id)) {
            return null;
        }

        return static::findOne(['_id' => new ObjectId($id)]);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        if (!is_string($token) || $token === '') {
            return null;
        }

        return static::find()
            ->where([
                'auth_token' => $token,
                'token_expires_at' => ['$gt' => time()],
            ])
            ->one();
    }

    public static function findByUsername(string $username): ?self
    {
        /** @var self|null $user */
        $user = static::findOne(['username' => $username]);

        return $user;
    }

    public function validatePassword(string $password): bool
    {
        return Yii::$app->security->validatePassword($password, (string) $this->password_hash);
    }

    public function setPassword(string $password): void
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    public function issueAccessToken(int $ttl = 1800): void
    {
        $this->auth_token = Yii::$app->security->generateRandomString(64);
        $this->token_expires_at = time() + $ttl;
    }

    public function revokeAccessToken(): void
    {
        $this->auth_token = null;
        $this->token_expires_at = 0;
    }

    public function getId()
    {
        return (string) $this->_id;
    }

    public function getAuthKey()
    {
        return null;
    }

    public function validateAuthKey($authKey): bool
    {
        return false;
    }

    public function fields(): array
    {
        return [
            'id' => static fn (self $model): ?string => static::stringifyObjectId($model->_id),
            'username',
        ];
    }

    public static function ensureIndexes(): void
    {
        $collection = static::getCollection();
        $collection->createIndex(['username' => 1], ['unique' => true]);
        $collection->createIndex(['auth_token' => 1]);
        $collection->createIndex(['token_expires_at' => 1]);
    }

    public static function ensureUserExists(string $username, string $password): void
    {
        if ($username === '' || $password === '') {
            return;
        }

        if (static::findByUsername($username) !== null) {
            return;
        }

        $user = new self();
        $user->username = $username;
        $user->setPassword($password);
        $user->token_expires_at = 0;
        $user->save(false);
    }
}
