# Style Guide

## Convenciones de nombres

- clases: `PascalCase`
- métodos y variables: `camelCase`
- constantes: `UPPER_SNAKE_CASE`
- DTOs y Requests: nombrar por caso de uso, por ejemplo `BookCreateDto` y `AuthorUpdateRequest`

## Regla arquitectónica

Siempre respetar este flujo:

1. `Controller` recibe el request HTTP.
2. `Request` valida y normaliza.
3. `DTO` transporta los datos.
4. `Service` aplica lógica de negocio.
5. `Repository` interactúa con MongoDB.
6. `ApiResponse` construye la salida.

## Controllers

- deben ser delgados
- no deben contener queries ni lógica de negocio
- deben declarar verbos HTTP con claridad
- deben delegar la validación compleja a Requests

## Services

- implementan reglas de negocio
- orquestan relaciones entre entidades
- lanzan excepciones de dominio cuando algo falla
- no deben depender de `Yii::$app->request`

## Repositories

- encapsulan toda interacción con persistencia
- centralizan paginación y búsquedas
- documentan índices y operaciones críticas

## Requests y DTOs

- `Request`: valida, normaliza y traduce entrada
- `DTO`: estructura tipada y simple para el dominio
- no enviar arrays HTTP crudos directamente a services

## Respuestas API

### Respuesta exitosa

```json
{
  "status": "success",
  "data": {},
  "meta": {}
}
```

### Respuesta de error

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

## Errores y seguridad

- `500` no debe exponer detalles sensibles
- no registrar contraseñas ni tokens completos
- mantener consistente el mapeo de excepciones en `ApiExceptionFilter`

## OpenAPI

- toda modificación en contrato HTTP debe reflejarse en `modules/api/docs/openapi/`
- después de editar specs modulares, ejecutar `composer openapi:build`
- verificar `docs/swagger.yaml` y `/swagger`

## Testing

- las pruebas API y de services dependen de MongoDB accesible por `MONGO_URI`
- antes de validar cambios grandes, correr `vendor/bin/phpunit`
- usar `composer cs:check` antes de merge cuando el entorno de finales de línea esté normalizado

## Comentarios y docblocks

- comentar solo cuando el código no sea evidente por sí mismo
- usar docblocks en métodos públicos con retornos estructurados o comportamientos no triviales
- preferir nombres descriptivos antes que comentarios redundantes
