# STYLEGUIDE.md - Convenciones de código (Library API)

## 1) Convenciones de nombres

- Clases: `PascalCase`
- Métodos/variables: `camelCase`
- Constantes: `UPPER_SNAKE_CASE`
- DTO/Request: nombres explícitos por caso de uso:
  - `BookCreateDto`, `BookUpdateRequest`
- Evitar abreviaturas ambiguas (`usr`, `bk`, etc.).

## 2) Arquitectura por capas

Flujo obligatorio:

1. `Controller` recibe request HTTP.
2. `Request` valida y normaliza entrada.
3. `DTO` transporta datos tipados al dominio.
4. `Service` aplica reglas de negocio.
5. `Repository` interactúa con MongoDB.
6. `ApiResponse` estandariza salida.

## 3) Controllers (delgados)

- No lógica de negocio.
- No queries Mongo directas.
- No validaciones complejas.
- Tamaño objetivo por acción: `10-30` líneas.

Ejemplo:

```php
public function actionCreate(): array
{
    $request = BookCreateRequest::fromPayload((array) Yii::$app->request->getBodyParams());
    $book = $this->service()->create($request->toDto());
    return ApiResponse::success($book, [], HttpStatus::CREATED);
}
```

## 4) Services

- Implementan reglas de negocio y consistencia entre entidades.
- Lanzan excepciones de dominio (`ValidationException`, `NotFoundException`, etc.).
- No usan superglobales ni parsing HTTP.

Ejemplo:

```php
if (!$this->authorRepository->existsByIds($dto->authors)) {
    throw new ValidationException('One or more authors do not exist.', [
        'authors' => ['One or more authors do not exist.'],
    ]);
}
```

## 5) Repositories

- Único lugar con acceso a persistencia MongoDB.
- Deben documentar índices y consultas clave.
- Paginación obligatoria para listados.
- Para relaciones, usar actualización en lote (evitar N+1).

Índices mínimos:

- `books.title`
- `books.publication_year`
- `authors.full_name`

## 6) Requests y DTOs

- `Request`: validar y normalizar (`trim`, tipos, ObjectId, defaults).
- `DTO`: estructura de datos tipada y limpia.
- Nunca pasar `Yii::$app->request` al service.

## 7) Respuestas API

Formato éxito:

```json
{
  "status": "success",
  "data": {},
  "meta": {}
}
```

Formato error:

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

## 8) Manejo de errores

Mapeo centralizado en `ApiExceptionFilter`:

- `ValidationException` -> `422`
- `UnauthorizedException` -> `401`
- `NotFoundException` -> `404`
- `DomainException` -> `400`
- `Throwable` -> `500`

Regla: los errores `500` no deben exponer datos sensibles.

## 9) Logging

- Loggear excepciones `500` con contexto técnico.
- Nunca registrar contraseñas, tokens completos ni payload sensible.

## 10) Comentarios y docblocks

- Usar comentarios solo cuando aporten contexto no obvio.
- Docblocks requeridos para métodos públicos complejos y estructuras de retorno.
