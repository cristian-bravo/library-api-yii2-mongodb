# Arquitectura

## Visión general

Library API está construida con **Yii2 + MongoDB** y sigue una arquitectura por capas para separar responsabilidades y mantener el contrato HTTP estable.

Flujo principal:

`Controller -> Request -> DTO -> Service -> Repository -> MongoDB`

## Capas del proyecto

### Controllers

Ubicación: `modules/api/controllers/`

Responsabilidades:

- exponer endpoints HTTP
- delegar lógica a services
- devolver datos usando `ApiResponse`
- declarar verbos permitidos y acciones públicas

### Requests

Ubicación: `modules/api/requests/`

Responsabilidades:

- validar payloads de entrada
- normalizar tipos y formatos
- convertir datos en DTOs

### DTOs

Ubicación: `modules/api/dto/`

Responsabilidades:

- transportar datos tipados entre capa HTTP y dominio
- evitar pasar arrays crudos hacia los services

### Services

Ubicación: `modules/api/services/`

Responsabilidades:

- implementar casos de uso
- aplicar reglas de negocio
- sincronizar relaciones entre libros y autores
- lanzar excepciones de dominio

Servicios principales:

- `AuthService`
- `BookService`
- `AuthorService`

### Repositories

Ubicación: `modules/api/repositories/`

Responsabilidades:

- encapsular acceso a MongoDB
- centralizar búsquedas, paginación y persistencia
- definir operaciones de sincronización entre entidades relacionadas

### Filters y Responses

Ubicación:

- `modules/api/filters/`
- `modules/api/responses/`

Responsabilidades:

- autenticar requests (`TokenAuthFilter`)
- mapear excepciones a HTTP (`ApiExceptionFilter`)
- unificar el contrato de salida (`ApiResponse`)

## Contrato de respuesta

### Éxito

```json
{
  "status": "success",
  "data": {},
  "meta": {}
}
```

### Error

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

## Seguridad

### TokenAuthFilter

- lee `Authorization: Bearer <token>`
- valida formato del header
- verifica expiración del token
- protege endpoints excepto acciones públicas declaradas en cada controller

### ApiExceptionFilter

Mapeo de excepciones:

- `ValidationException` -> `422`
- `UnauthorizedException` -> `401`
- `NotFoundException` -> `404`
- `DomainException` -> `400`
- `Throwable` -> `500`

## OpenAPI modular

Fuente modular:

- `modules/api/docs/openapi/base.yaml`
- `modules/api/docs/openapi/paths.*.yaml`
- `modules/api/docs/openapi/schemas.*.yaml`

Build:

- script: `scripts/tools/openapi-build.php`
- comando: `composer openapi:build`

Artefactos:

- `modules/api/docs/openapi.generated.yaml` (local)
- `docs/swagger.yaml` (versionado)

Consumo:

- Swagger UI: `http://localhost:8080/swagger`
- YAML: `http://localhost:8080/swagger/openapi.yaml`

## Estructura resumida

```text
modules/api/
  controllers/
  dto/
  exceptions/
  filters/
  repositories/
  requests/
  responses/
  services/
  docs/openapi/
```

## Decisiones relevantes

- **MongoDB** permite modelar relaciones flexibles entre libros y autores.
- **Services** centralizan la lógica de sincronización cruzada para evitar inconsistencias.
- **OpenAPI modular** reduce duplicación y facilita mantenimiento del contrato.
- **Swagger UI integrado** mejora la revisión técnica y la validación manual del API.
