# Documentación técnica

Este directorio concentra la documentación operativa y de arquitectura del proyecto **Library API**.

## Mapa de documentos

| Archivo | Propósito |
| --- | --- |
| `docs/INSTALLATION.md` | Instalación paso a paso con Docker, desde cero |
| `docs/USAGE.md` | Guía paso a paso para levantar la app, autenticarte y probar endpoints |
| `docs/ARCHITECTURE.md` | Vista de capas, flujo de request/response y componentes clave |
| `docs/STYLEGUIDE.md` | Convenciones de código, respuestas y mantenimiento |
| `docs/swagger.yaml` | Bundle OpenAPI listo para consumo |

## Cuándo usar cada documento

- Si necesitas **instalar y levantar el proyecto desde cero**, empieza por `docs/INSTALLATION.md`.
- Si ya lo tienes corriendo y quieres **probar endpoints rápido**, sigue con `docs/USAGE.md`.
- Si estás evaluando el diseño técnico, revisa `docs/ARCHITECTURE.md`.
- Si vas a cambiar código o abrir un PR, usa `docs/STYLEGUIDE.md`.
- Si necesitas integrar la API en otra herramienta, consume `docs/swagger.yaml`.

## Enlaces operativos

Con la app arriba en local:

- API root: `http://localhost:8080/api`
- Swagger UI: `http://localhost:8080/swagger`
- OpenAPI YAML: `http://localhost:8080/swagger/openapi.yaml`

## Notas importantes

- El archivo `.env.example` está preparado para Docker (`mongo` como hostname).
- Si ejecutas PHP y MongoDB fuera de Docker, cambia `MONGO_URI` a `mongodb://localhost:27017/library_db`.
- La suite de pruebas toma `MONGO_URI` desde el entorno activo para evitar inconsistencias entre host y contenedor.
