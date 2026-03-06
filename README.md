<div align="center">

# Library API
### RESTful API for Library Management

API REST desarrollada con **PHP, Yii2 y MongoDB** para administrar una biblioteca virtual de libros y autores.

![PHP](https://img.shields.io/badge/PHP-8%2B-blue?style=for-the-badge&logo=php)
![Yii2](https://img.shields.io/badge/Yii2-Framework-green?style=for-the-badge)
![MongoDB](https://img.shields.io/badge/MongoDB-Database-brightgreen?style=for-the-badge&logo=mongodb)
![REST API](https://img.shields.io/badge/API-REST-orange?style=for-the-badge)
![License](https://img.shields.io/badge/license-MIT-lightgrey?style=for-the-badge)

</div>

---

## Descripción

**Library API** es una API REST para gestionar una biblioteca virtual con arquitectura por capas:

`Request -> DTO -> Service -> Repository`

Incluye:

- Autenticación basada en token
- Validación de datos
- Manejo estructurado de errores
- Documentación OpenAPI
- Tests unitarios e integración

---

## Tecnologías utilizadas

| Tecnologia | Uso |
|------------|-----|
| PHP 8+ | Lenguaje backend |
| Yii2 | Framework de desarrollo |
| MongoDB | Base de datos NoSQL |
| REST API | Arquitectura de comunicación |
| Swagger / OpenAPI | Documentación |
| PHPUnit | Pruebas |
| Docker | Entorno local |

---

## Arquitectura del sistema

La API sigue una estructura modular basada en Yii2.

| Carpeta / Archivo | Descripción |
|-------------------|-------------|
| `modules/api/controllers` | Endpoints REST (`Auth`, `Book`, `Author`) |
| `modules/api/services` | Lógica de negocio |
| `modules/api/repositories` | Acceso a datos |
| `modules/api/requests` | Validación de entrada |
| `modules/api/dto` | Objetos de transferencia |
| `models` | Modelos MongoDB |
| `config` | Configuración de aplicación y Mongo |
| `migrations/mongodb` | Migraciones de colecciones/índices |
| `modules/api/docs/openapi` | Especificaciones modulares OpenAPI |
| `docs/swagger.yaml` | Especificación OpenAPI versionada |

---

## Modelo de datos

### Books

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `_id` | ObjectId | Identificador del libro |
| `title` | string | Título del libro |
| `authors` | ObjectId[] | Autores relacionados |
| `publication_year` | integer | Año de publicación |
| `description` | string | Descripción del libro |

### Authors

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `_id` | ObjectId | Identificador del autor |
| `full_name` | string | Nombre completo |
| `birth_date` | date | Fecha de nacimiento |
| `books` | ObjectId[] | Libros escritos |

Relaciones:

- Un libro puede tener múltiples autores.
- Un autor puede haber escrito múltiples libros.

---

## Autenticación

La API usa token con TTL configurable.

- Endpoint: `POST /api/login`
- TTL default: `TOKEN_TTL=1800` (30 minutos)

Ejemplo de respuesta:

```json
{
  "token": "TOKEN_GENERADO",
  "expires_in": 1800
}
```

---

## Endpoints de la API

### Público

- `POST /api/login`

### Books

- `GET /api/books`
- `GET /api/books/{id}`
- `POST /api/books`
- `PUT /api/books/{id}`
- `DELETE /api/books/{id}`

### Authors

- `GET /api/authors`
- `GET /api/authors/{id}`
- `POST /api/authors`
- `PUT /api/authors/{id}`
- `DELETE /api/authors/{id}`

---

## Manejo de errores

Códigos HTTP comunes:

- `200` OK
- `201` Created
- `400` Bad Request
- `401` Unauthorized
- `404` Not Found
- `422` Validation Error
- `500` Internal Server Error

Formato estándar de error:

```json
{
  "status": "error",
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Title is required",
    "details": []
  }
}
```

---

## Configuración de entorno

1. Copiar `.env.example` a `.env`.
2. Ajustar valores según tu entorno.

Variables base:

- `MONGO_URI=mongodb://localhost:27017/library_db`
- `TOKEN_TTL=1800`
- `API_PAGE_SIZE=20`
- `API_MAX_PAGE_SIZE=100`
- `APP_ENV=dev`
- `APP_DEBUG=true`

---

## Instalación y ejecución

### Local

```bash
composer install
php yii serve
```

Servidor local:

- `http://localhost:8080`

### Docker

```bash
docker compose up --build
```

API en Docker:

- `http://localhost:8080/api`

Detener:

```bash
docker compose down
```

Eliminar volúmenes:

```bash
docker compose down -v
```

---

## Base de datos y migraciones

Ejecutar migraciones Mongo:

```bash
composer migrate:mongo
```

Revertir última migración:

```bash
composer migrate:mongo:down
```

---

## Testing y calidad

Correr tests:

```bash
composer test
```

Alternativa:

```bash
vendor/bin/phpunit
```

Code style:

```bash
composer cs:check
composer cs:fix
```

---

## OpenAPI

Generar bundle desde specs modulares:

```bash
composer openapi:build
```

Salida:

- `modules/api/docs/openapi.generated.yaml` (local)
- `docs/swagger.yaml` (versionado)

---

## Documentación adicional

- `docs/README.md`
- `docs/ARCHITECTURE.md`
- `docs/STYLEGUIDE.md`
- `CONTRIBUTING.md`

---

## Objetivo del proyecto

Proyecto para demostrar el diseño e implementación de una API REST escalable con Yii2 y MongoDB, aplicando buenas prácticas de arquitectura backend.

## Autor

Cristian Bravo  
Desarrollador Full-Stack 

