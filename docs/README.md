# Documentacion tecnica - Library API

## 0) Guia rapida de uso

Si quieres una guia operativa paso a paso para levantar la API, obtener el token y probar endpoints, revisa:

- `docs/USAGE.md`

## 1) Requisitos

- PHP 8.1+
- Composer
- MongoDB (local o Docker)
- Extension PHP `mongodb`

## 2) Variables de entorno

Usar `.env` (basado en `.env.example`).

Variables principales:

- `MONGO_URI`
- `TOKEN_TTL`
- `API_PAGE_SIZE`
- `API_MAX_PAGE_SIZE`
- `APP_ENV`
- `APP_DEBUG`
- `COOKIE_VALIDATION_KEY`
- `BOOTSTRAP_ADMIN_USER`
- `BOOTSTRAP_ADMIN_USERNAME`
- `BOOTSTRAP_ADMIN_PASSWORD`

Defaults clave:

- `MONGO_URI`: `mongodb://localhost:27017/library_db`
- `TOKEN_TTL`: `1800`
- `API_PAGE_SIZE`: `20`
- `API_MAX_PAGE_SIZE`: `100`

## 3) Levantar con Docker

```bash
docker compose up --build
```

Servicios:

- API (Nginx): `http://localhost:8080`
- MongoDB: `localhost:27017`

Bajar servicios:

```bash
docker compose down
```

Bajar y limpiar volumenes:

```bash
docker compose down -v
```

## 4) Conexion a MongoDB y migraciones

La app toma la conexion desde `MONGO_URI`.

Migrar esquema inicial (colecciones + indices):

```bash
composer migrate:mongo
```

Revertir ultima migracion:

```bash
composer migrate:mongo:down
```

Migracion incluida:

- `migrations/mongodb/m260305_000001_init_library_api.php`

Esta migracion crea:

- colecciones: `books`, `authors`, `users`
- indices:
  - `books.title`
  - `books.publication_year`
  - `books.authors`
  - `authors.full_name`
  - `authors.books`
  - `users.username` (unique)
  - `users.auth_token`
  - `users.token_expires_at`

## 5) Ejecucion sin Docker

```bash
composer install
php yii serve --port=8080
```

## 6) Tests

Ejecutar todos:

```bash
composer test
```

Alternativa directa:

```bash
vendor/bin/phpunit
```

Estructura:

- `tests/Api/AuthTest.php`
- `tests/Api/BookTest.php`
- `tests/Api/AuthorTest.php`
- `tests/Unit/BookServiceTest.php`
- `tests/Unit/AuthorServiceTest.php`

## 7) OpenAPI modular y build

Fuentes:

- `modules/api/docs/openapi/base.yaml`
- `modules/api/docs/openapi/paths.auth.yaml`
- `modules/api/docs/openapi/paths.books.yaml`
- `modules/api/docs/openapi/paths.authors.yaml`
- `modules/api/docs/openapi/schemas.book.yaml`
- `modules/api/docs/openapi/schemas.author.yaml`
- `modules/api/docs/openapi/schemas.errors.yaml`

Build:

```bash
composer openapi:build
```

Salida:

- `modules/api/docs/openapi.generated.yaml` (local)
- `docs/swagger.yaml` (versionado)

## 8) Contrato de respuesta

Success:

```json
{
  "status": "success",
  "data": {},
  "meta": {}
}
```

Error:

```json
{
  "status": "error",
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Title cannot be blank.",
    "details": {}
  }
}
```
