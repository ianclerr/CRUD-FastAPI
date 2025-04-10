from fastapi import FastAPI, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel
import psycopg2
from typing import List

app = FastAPI()

# Configuración CORS
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Modelo Pydantic
class Producto(BaseModel):
    nombre: str
    descripcion: str
    precio: float
    stock: int

class ProductoResponse(Producto):
    id: int

# Conexión a la base de datos
def get_db_connection():
    return psycopg2.connect(
        host="localhost",
        database="prueba_fastapi",
        user="postgres",
        password="root"
    )

# Endpoint para crear producto
@app.post("/productos/", response_model=ProductoResponse)
async def create_producto(producto: Producto):
    conn = get_db_connection()
    cursor = conn.cursor()
    try:
        cursor.execute(
            "INSERT INTO productos (nombre, descripcion, precio, stock) VALUES (%s, %s, %s, %s) RETURNING id",
            (producto.nombre, producto.descripcion, producto.precio, producto.stock)
        )
        new_id = cursor.fetchone()[0]
        conn.commit()
        return {
            "id": new_id,
            "nombre": producto.nombre,
            "descripcion": producto.descripcion,
            "precio": producto.precio,
            "stock": producto.stock
        }
    except Exception as e:
        conn.rollback()
        raise HTTPException(status_code=400, detail=str(e))
    finally:
        cursor.close()
        conn.close()

# Endpoint para listar todos los productos
@app.get("/productos/", response_model=List[ProductoResponse])
async def list_productos():
    conn = get_db_connection()
    cursor = conn.cursor()
    try:
        cursor.execute("SELECT id, nombre, descripcion, precio, stock FROM productos")
        productos = cursor.fetchall()
        return [{
            "id": p[0],
            "nombre": p[1],
            "descripcion": p[2],
            "precio": float(p[3]),
            "stock": p[4]
        } for p in productos]
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))
    finally:
        cursor.close()
        conn.close()

# Endpoint para obtener un producto específico
@app.get("/productos/{producto_id}", response_model=ProductoResponse)
async def get_producto(producto_id: int):
    conn = get_db_connection()
    cursor = conn.cursor()
    try:
        cursor.execute("SELECT id, nombre, descripcion, precio, stock FROM productos WHERE id = %s", (producto_id,))
        producto = cursor.fetchone()
        
        if producto is None:
            raise HTTPException(status_code=404, detail="Producto no encontrado")
            
        return {
            "id": producto[0],
            "nombre": producto[1],
            "descripcion": producto[2],
            "precio": float(producto[3]),
            "stock": producto[4]
        }
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))
    finally:
        cursor.close()
        conn.close()

# Endpoint para actualizar producto
@app.put("/productos/{producto_id}")
async def update_producto(producto_id: int, producto: Producto):
    conn = get_db_connection()
    cursor = conn.cursor()
    try:
        cursor.execute("""
            UPDATE productos
            SET nombre = %s, descripcion = %s, precio = %s, stock = %s
            WHERE id = %s
            RETURNING id
        """, (producto.nombre, producto.descripcion, producto.precio, producto.stock, producto_id))
        
        if cursor.fetchone() is None:
            raise HTTPException(status_code=404, detail="Producto no encontrado")
            
        conn.commit()
        return {"message": "Producto actualizado exitosamente"}
    except Exception as e:
        conn.rollback()
        raise HTTPException(status_code=400, detail=str(e))
    finally:
        cursor.close()
        conn.close()

# Endpoint para eliminar producto
@app.delete("/productos/{producto_id}", status_code=204)
async def delete_producto(producto_id: int):
    conn = get_db_connection()
    cursor = conn.cursor()
    try:
        cursor.execute("DELETE FROM productos WHERE id = %s RETURNING id", (producto_id,))
        
        if cursor.fetchone() is None:
            raise HTTPException(status_code=404, detail="Producto no encontrado")
            
        conn.commit()
    except Exception as e:
        conn.rollback()
        raise HTTPException(status_code=400, detail=str(e))
    finally:
        cursor.close()
        conn.close()