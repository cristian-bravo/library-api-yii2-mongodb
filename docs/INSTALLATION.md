# Instalación paso a paso

Esta guía explica cómo levantar **Library API** con Docker de la forma más simple posible.

Si nunca has corrido el proyecto antes, empieza aquí.

## 1. Qué necesitas antes de comenzar

Instala esto primero:

- Docker Desktop
- Git
- Un editor de código (por ejemplo VS Code)

Verifica que Docker esté corriendo:

```bash
docker --version
docker compose version
```

Si alguno de esos comandos falla:

1. abre Docker Desktop
2. espera a que termine de iniciar
3. vuelve a ejecutar los comandos

## 2. Abrir el proyecto

Ubícate dentro de la carpeta del repositorio.

Ejemplo:

```bash
cd library-api-yii2-mongodb
```

## 3. Crear el archivo `.env`

El proyecto usa variables de entorno. Debes copiar `.env.example` a `.env`.

### En PowerShell

```powershell
Copy-Item .env.example .env
```

### En Git Bash / Linux / macOS

```bash
cp .env.example .env
```

## 4. Revisar el contenido del `.env`

Para Docker, este valor es correcto y no debes cambiarlo:

```env
MONGO_URI=mongodb://mongo:27017/library_db
```

También revisa estas variables:

```env
TOKEN_TTL=1800
API_PAGE_SIZE=20
API_MAX_PAGE_SIZE=100
APP_ENV=dev
APP_DEBUG=true
COOKIE_VALIDATION_KEY=change-me
BOOTSTRAP_ADMIN_USER=true
BOOTSTRAP_ADMIN_USERNAME=admin
BOOTSTRAP_ADMIN_PASSWORD=Admin123!
```

Importante:

- `BOOTSTRAP_ADMIN_USER=true` crea el usuario inicial automáticamente
- el usuario por defecto será `admin`
- la contraseña por defecto será `Admin123!`

## 5. Levantar los contenedores

Ejecuta:

```bash
docker compose up --build -d
```

Qué hace este comando:

- construye la imagen de PHP
- levanta `app`
- levanta `nginx`
- levanta `mongo`

## 6. Confirmar que todo arrancó bien

Ejecuta:

```bash
docker compose ps
```

Debes ver algo parecido a esto:

- `library-api-app` en estado `Up`
- `library-api-nginx` en estado `Up`
- `library-api-mongo` en estado `Up`

Si algo no sube, revisa logs:

```bash
docker compose logs --tail=100
```

O para ver logs en tiempo real:

```bash
docker compose logs -f
```

## 7. Ejecutar migraciones

Con los contenedores arriba, crea las colecciones e índices:

```bash
docker compose exec -T app composer migrate:mongo
```

Esto prepara:

- `books`
- `authors`
- `users`

Y sus índices principales.

## 8. Verificar la API en el navegador

Abre estas URLs:

- `http://localhost:8080/api`
- `http://localhost:8080/swagger`
- `http://localhost:8080/swagger/openapi.yaml`

Qué deberías ver:

- `/api`: un resumen JSON con endpoints y links de documentación
- `/swagger`: la interfaz Swagger UI
- `/swagger/openapi.yaml`: el archivo OpenAPI bundleado

## 9. Probar login

Puedes probar el login desde Swagger o con `curl`.

Ejemplo:

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

## 10. Ejecutar pruebas

Con Docker levantado:

```bash
docker compose exec -T app vendor/bin/phpunit
```

## 11. Comandos útiles

### Detener contenedores

```bash
docker compose down
```

### Detener y borrar volúmenes

```bash
docker compose down -v
```

### Reconstruir OpenAPI

```bash
docker compose exec -T app composer openapi:build
```

### Revisar estilo

```bash
docker compose exec -T app composer cs:check
```

## 12. Problemas comunes

### Docker no responde

Causa probable:

- Docker Desktop no está iniciado

Solución:

1. abre Docker Desktop
2. espera a que termine de arrancar
3. vuelve a correr `docker compose up --build -d`

### `localhost:8080` no abre

Causa probable:

- `nginx` no levantó
- el puerto `8080` está ocupado

Solución:

```bash
docker compose ps
docker compose logs nginx --tail=100
```

### Las migraciones fallan

Causa probable:

- MongoDB todavía no estaba listo

Solución:

espera unos segundos y vuelve a correr:

```bash
docker compose exec -T app composer migrate:mongo
```

### Swagger abre pero no carga bien

Prueba:

1. recarga duro el navegador con `Ctrl + F5`
2. verifica que `http://localhost:8080/swagger/openapi.yaml` responda

## 13. Resumen corto

Si solo quieres el flujo mínimo:

```bash
Copy-Item .env.example .env
docker compose up --build -d
docker compose exec -T app composer migrate:mongo
```

Luego abre:

- `http://localhost:8080/api`
- `http://localhost:8080/swagger`
