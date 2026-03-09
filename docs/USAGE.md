# Guía de uso

> Si todavía no has instalado o levantado el proyecto, empieza por `docs/INSTALLATION.md`.

## Objetivo

Esta guía sirve para:

- levantar el proyecto rápidamente
- obtener un token
- probar endpoints con `curl`
- validar Swagger UI y OpenAPI

## 1. Preparar variables de entorno

```bash
cp .env.example .env
```

Valor recomendado para Docker:

```env
MONGO_URI=mongodb://mongo:27017/library_db
```

Valor recomendado para ejecución local sin Docker:

```env
MONGO_URI=mongodb://localhost:27017/library_db
```

## 2. Levantar con Docker

```bash
docker compose up --build -d
docker compose exec -T app composer migrate:mongo
```

Servicios esperados:

- API: `http://localhost:8080`
- MongoDB: `mongodb://localhost:27017`

## 3. Verificar puntos de entrada

Abre:

- `http://localhost:8080/api`
- `http://localhost:8080/swagger`
- `http://localhost:8080/swagger/openapi.yaml`

## 4. Login

Request:

```bash
curl --request POST "http://localhost:8080/api/login" \
  --header "Content-Type: application/json" \
  --data "{\"username\":\"admin\",\"password\":\"Admin123!\"}"
```

Respuesta esperada:

```json
{
  "status": "success",
  "data": {
    "token": "TOKEN_GENERADO",
    "expires_in": 1800
  }
}
```

## 5. Consultar libros

```bash
curl --request GET "http://localhost:8080/api/books" \
  --header "Authorization: Bearer <TOKEN>"
```

## 6. Crear un autor

```bash
curl --request POST "http://localhost:8080/api/authors" \
  --header "Authorization: Bearer <TOKEN>" \
  --header "Content-Type: application/json" \
  --data "{\"full_name\":\"Robert C. Martin\",\"birth_date\":\"1952-12-05\",\"books\":[]}"
```

## 7. Crear un libro

Sustituye `<AUTHOR_ID>` por el ID devuelto al crear el autor:

```bash
curl --request POST "http://localhost:8080/api/books" \
  --header "Authorization: Bearer <TOKEN>" \
  --header "Content-Type: application/json" \
  --data "{\"title\":\"Clean Architecture\",\"authors\":[\"<AUTHOR_ID>\"],\"publication_year\":2017,\"description\":\"Practicas de arquitectura para software mantenible.\"}"
```

## 8. Expandir relaciones

Libros con autores expandidos:

```bash
curl --request GET "http://localhost:8080/api/books?expand=authors" \
  --header "Authorization: Bearer <TOKEN>"
```

Autores con libros expandidos:

```bash
curl --request GET "http://localhost:8080/api/authors?expand=books" \
  --header "Authorization: Bearer <TOKEN>"
```

## 9. Testing

Con Docker arriba:

```bash
docker compose exec -T app vendor/bin/phpunit
```

Notas:

- la suite usa `MONGO_URI` desde el entorno activo
- si `MONGO_URI` apunta a un host no accesible desde donde ejecutas PHPUnit, las pruebas de integración se omitirán
- las pruebas en `tests/Unit/` actualmente también requieren MongoDB porque validan services sobre la aplicación real

## 10. Comandos útiles

```bash
docker compose down
docker compose down -v
docker compose exec -T app composer openapi:build
docker compose exec -T app composer cs:check
```
