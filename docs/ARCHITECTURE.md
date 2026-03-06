# ARCHITECTURE.md - Library API

## 1) Visión general

Library API está construida en Yii2 con MongoDB y sigue una arquitectura por capas para mantener separadas las responsabilidades:

- `Controller`: capa HTTP
- `Request`: validación/normalización de entrada
- `DTO`: transporte de datos tipados
- `Service`: reglas de negocio
- `Repository`: persistencia MongoDB
- `ApiResponse`: contrato de salida uniforme

## 2) Estructura principal

```text
modules/api
  controllers/
  requests/
  dto/
  services/
  repositories/
  filters/
  exceptions/
  responses/
  docs/openapi/
```

## 3) Responsabilidades por capa

### Controllers

- Exponen endpoints públicos existentes (`/api/login`, `/api/books`, `/api/authors`).
- Delegan todo a services.
- Responden usando `ApiResponse`.

### Requests

- Validan payloads de entrada.
- Normalizan tipos y formato (ObjectId, fechas, strings).
- Convierten a DTO.

### DTO

- Objetos inmutables para transferir datos entre capa HTTP y dominio.

### Services

- Implementan casos de uso:
  - `BookService`
  - `AuthorService`
  - `AuthService`
- Lógica de relaciones Books <-> Authors.
- Lógica de token login.

### Repositories

- Acceso a MongoDB con queries y paginación.
- Encapsulan operaciones de lectura/escritura.
- Definen índices recomendados en bootstrap del módulo.

### Exceptions

- Excepciones de dominio explícitas:
  - `ValidationException`
  - `UnauthorizedException`
  - `NotFoundException`
  - `DomainException`

### Responses

- `ApiResponse` unifica contrato:
  - `success` => `{status,data,meta}`
  - `error` => `{status,error:{code,message,details}}`

## 4) Flujo request -> response

```text
Client -> Controller -> Request -> DTO -> Service -> Repository -> MongoDB
                                              |
                                              v
                                        Domain Exceptions
                                              |
                                              v
                                       ApiExceptionFilter
                                              |
                                              v
                                         ApiResponse
```

## 5) Seguridad

### TokenAuthFilter

- Lee `Authorization: Bearer <token>`.
- Valida formato del header.
- Valida token y expiración (TTL default 1800 segundos configurable).
- Bloquea endpoints protegidos con `401` cuando no aplica.

### ApiExceptionFilter

Mapeo centralizado:

- `ValidationException` -> `422`
- `UnauthorizedException` -> `401`
- `NotFoundException` -> `404`
- `DomainException` -> `400`
- `Throwable` -> `500` (con logging técnico)

## 6) OpenAPI modular

Fuente modular:

- `modules/api/docs/openapi/base.yaml`
- `modules/api/docs/openapi/paths.*.yaml`
- `modules/api/docs/openapi/schemas.*.yaml`

Bundling:

- Script: `scripts/tools/openapi-build.php`
- Comando: `composer openapi:build`
- Salidas:
  - `modules/api/docs/openapi.generated.yaml` (artefacto local, no versionado)
  - `docs/swagger.yaml` (artefacto versionado para consumo)

## 7) Compatibilidad de contrato

El refactor mantiene compatibilidad con:

- Endpoints/verbos públicos existentes
- Formato de `ApiResponse`
- Header Bearer token
