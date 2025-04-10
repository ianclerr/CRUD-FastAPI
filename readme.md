## CRUD con PHP Nativo, FastAPI (Python) y PostgreSQL

Este proyecto implementa un sistema CRUD (Create, Read, Update, Delete) utilizando PHP nativo para el frontend, FastAPI (Python) como middleware/backend y PostgreSQL como base de datos.

## Tecnologías Utilizadas
### Frontend: 
- PHP 8.0+
- HTML5, CSS3
- JavaScript (para interacciones básicas)

### BackEnd
- Python 3.8+
- FastAPI
- Uvicorn (servidor ASGI)

## Librerias Principales
- fastapi
- uvicorn
- psycopg2-binary
- pydantic
- python-dotenv(Variables de Entorno)

## Base de datos
- PostgreSQL+12

## EndPoints de la API
- GET /items - Listar todos los registros
- GET /items/{id} - Obtener un registro específico
- POST /items/ - Crear un nuevo registro
- PUT /items/{id} - Actualizar un registro
- DELETE /items/{id} - Eliminar un registro