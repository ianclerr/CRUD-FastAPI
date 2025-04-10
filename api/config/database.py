import psycopg2
from psycopg2 import sql
from contextlib import contextmanager

# Datos de conexión actualizados
DB_HOST = "localhost"
DB_PORT = "5432"  # Puerto estándar de PostgreSQL
DB_NAME = "prueba_fastapi"  # Nombre de la base de datos
DB_USER = "postgres"  # Usuario de la base de datos
DB_PASSWORD = "root"  # Contraseña de la base de datos

# Conexión a la base de datos PostgreSQL
@contextmanager
def get_db_connection():
    conn = psycopg2.connect(
        dbname=DB_NAME,
        user=DB_USER,
        password=DB_PASSWORD,
        host=DB_HOST,
        port=DB_PORT
    )
    try:
        yield conn
    finally:
        conn.close()

# Función para ejecutar una consulta en la base de datos
def execute_query(query, params=None):
    with get_db_connection() as conn:
        with conn.cursor() as cursor:
            cursor.execute(query, params)
            conn.commit()

# Función para obtener datos de la base de datos
def fetch_all(query, params=None):
    with get_db_connection() as conn:
        with conn.cursor() as cursor:
            cursor.execute(query, params)
            return cursor.fetchall()
