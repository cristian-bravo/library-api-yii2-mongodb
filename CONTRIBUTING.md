# Contributing Guide

## 1) Flujo de ramas

- Rama principal: `main`
- Crear ramas por trabajo:
  - `feat/<descripcion-corta>`
  - `fix/<descripcion-corta>`
  - `refactor/<descripcion-corta>`
  - `docs/<descripcion-corta>`

Ejemplos:

- `feat/books-expand-authors`
- `fix/login-token-expiration`

## 2) Convención de commits (Conventional Commits)

Formato:

```text
type(scope): mensaje
```

Tipos recomendados:

- `feat`
- `fix`
- `refactor`
- `docs`
- `test`
- `chore`

Ejemplos:

- `feat(api): add modular openapi build script`
- `fix(auth): validate bearer token expiration`
- `refactor(books): move business logic to BookService`

## 3) Pull Requests

Reglas:

- PR pequeño y enfocado en un objetivo.
- Describir contexto, cambio, impacto y riesgo.
- Si cambia contrato API, incluir actualización OpenAPI.

Plantilla sugerida para PR:

```md
## Contexto
[Problema o necesidad]

## Cambios
- [x] ...
- [x] ...

## Impacto
- Endpoints afectados: [...]
- Riesgo: bajo/medio/alto

## Validación
- [x] php -l
- [x] prueba manual de endpoint
- [x] docs/OpenAPI actualizado
```

## 4) Checklist obligatorio antes de merge

- [ ] No se rompen rutas públicas `/api/*`.
- [ ] Controller sin lógica de negocio.
- [ ] Validación en Request/DTO.
- [ ] Lógica en Service.
- [ ] Persistencia en Repository.
- [ ] Respuesta estándar `ApiResponse`.
- [ ] Errores mapeados correctamente (400/401/404/422/500).
- [ ] OpenAPI modular actualizado.
- [ ] Documentación actualizada (`docs/README.md` y docs relacionadas si aplica).
