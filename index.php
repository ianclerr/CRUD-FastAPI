<?php
require_once 'config/database.php';

$message = '';

function callApi($url, $method = 'GET', $data = null) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    if ($method === 'POST' || $method === 'PUT' || $method === 'DELETE') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'status' => $httpCode,
        'data' => json_decode($response, true)
    ];
}

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'add':
            $data = [
                'nombre' => $_POST['nombre'],
                'descripcion' => $_POST['descripcion'],
                'precio' => (float)$_POST['precio'],
                'stock' => (int)$_POST['stock']
            ];
            
            $response = callApi("http://127.0.0.1:8001/productos/", 'POST', $data);
            
            if ($response['status'] === 200) {
                $message = "Producto agregado exitosamente!";
            } else {
                $message = "Error al agregar: " . ($response['data']['detail'] ?? 'Error desconocido');
            }
            break;
            
        case 'update':
            $data = [
                'nombre' => $_POST['nombre'],
                'descripcion' => $_POST['descripcion'],
                'precio' => (float)$_POST['precio'],
                'stock' => (int)$_POST['stock']
            ];
            
            $id = $_POST['id'];
            $response = callApi("http://127.0.0.1:8001/productos/" . $id, 'PUT', $data);
            
            if ($response['status'] === 200) {
                $message = "Producto actualizado exitosamente!";
            } else {
                $message = "Error al actualizar: " . ($response['data']['detail'] ?? 'Error desconocido');
            }
            break;
            
        case 'delete':
            $id = $_POST['id'];
            $response = callApi("http://127.0.0.1:8001/productos/" . $id, 'DELETE');
            
            if ($response['status'] === 204) {
                $message = "Producto eliminado exitosamente!";
            } else {
                $message = "Error al eliminar: " . ($response['data']['detail'] ?? 'Error desconocido (Código: ' . $response['status'] . ')');
            }
            break;
    }
}

// Obtener todos los productos desde la API
$response = callApi("http://127.0.0.1:8001/productos/");
if ($response['status'] === 200) {
    $productos = $response['data'];

    usort($productos, function($a, $b) {
        return $a['id'] <=> $b['id'];
    });

} else {
    $message = "Error al cargar productos: " . ($response['data']['detail'] ?? 'Error desconocido');
    $productos = [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Productos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="public/css/index.css">
</head>
<body class="bg-light">
    
    <div class="container">
        <!-- Título principal -->
        <h1 class="text-left mb-4">Gestión de Productos</h1>
        
        <!-- Mostrar mensajes de éxito/error -->
        <?php if ($message): ?>
    <div class="alert <?= strpos($message, 'exitosamente') !== false ? 'alert-success' : 'alert-danger' ?> alert-dismissible fade show" id="autoCloseAlert">
        <?= $message ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>
        <!-- Botón para abrir el modal -->
        <div class="d-flex justify-content-left mb-4">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#insertProductModal">
                <i class="fas fa-plus-circle"></i> Insertar Producto
            </button>
        </div>

        <!-- Modal para agregar producto -->
        <div class="modal fade" id="insertProductModal" tabindex="-1" aria-labelledby="insertProductModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <!-- Encabezado del modal -->
                    <div class="modal-header">
                        <h5 class="modal-title" id="insertProductModalLabel">Insertar Producto</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <!-- Cuerpo del modal con el formulario -->
                    <div class="modal-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="add">
                            
                            <div class="mb-3">
                                <label for="nombre" class="form-label">Nombre</label>
                                <input type="text" name="nombre" id="nombre" required class="form-control">
                            </div>
                            <div class="mb-3">
                                <label for="descripcion" class="form-label">Descripción</label>
                                <textarea name="descripcion" id="descripcion" required class="form-control"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="precio" class="form-label">Precio</label>
                                <input type="number" name="precio" id="precio" step="0.01" min="0.01" required class="form-control">
                            </div>
                            <div class="mb-3">
                                <label for="stock" class="form-label">Stock</label>
                                <input type="number" name="stock" id="stock" min="0" required class="form-control">
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Agregar Producto</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal para editar producto -->
        <div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <!-- Encabezado del modal -->
                    <div class="modal-header">
                        <h5 class="modal-title" id="editProductModalLabel">Editar Producto</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <!-- Cuerpo del modal con el formulario -->
                    <div class="modal-body">
                        <form method="POST" id="editProductForm">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="id" id="editProductId">
                            
                            <div class="mb-3">
                                <label for="editNombre" class="form-label">Nombre</label>
                                <input type="text" name="nombre" id="editNombre" required class="form-control">
                            </div>
                            <div class="mb-3">
                                <label for="editDescripcion" class="form-label">Descripción</label>
                                <textarea name="descripcion" id="editDescripcion" required class="form-control"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="editPrecio" class="form-label">Precio</label>
                                <input type="number" name="precio" id="editPrecio" step="0.01" min="0.01" required class="form-control">
                            </div>
                            <div class="mb-3">
                                <label for="editStock" class="form-label">Stock</label>
                                <input type="number" name="stock" id="editStock" min="0" required class="form-control">
                            </div>
                            
                            <button type="submit" class="btn btn-success">Actualizar Producto</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <h2 class="text-center mt-4 mb-4">Lista de Productos</h2>
        
        <?php if (empty($productos)): ?>
            <div class="alert alert-warning text-center">No hay productos registrados.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Precio</th>
                            <th>Stock</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($productos as $producto): ?>
                            <tr>
                                <td><?= htmlspecialchars($producto['id']) ?></td>
                                <td><?= htmlspecialchars($producto['nombre']) ?></td>
                                <td><?= htmlspecialchars($producto['descripcion']) ?></td>
                                <td><?= htmlspecialchars($producto['precio']) ?></td>
                                <td><?= htmlspecialchars($producto['stock']) ?></td>
                                <td>
                                    <!-- Botón para editar producto -->
                                    <button type="button" class="btn btn-warning btn-edit" 
                                            data-id="<?= htmlspecialchars($producto['id']) ?>"
                                            data-nombre="<?= htmlspecialchars($producto['nombre']) ?>"
                                            data-descripcion="<?= htmlspecialchars($producto['descripcion']) ?>"
                                            data-precio="<?= htmlspecialchars($producto['precio']) ?>"
                                            data-stock="<?= htmlspecialchars($producto['stock']) ?>">
                                        <i class="fas fa-edit"></i> Editar
                                    </button>
                                    <!-- Botón para eliminar producto -->
                                    <form action="index.php" method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= htmlspecialchars($producto['id']) ?>">
                                        <button type="submit" class="btn btn-danger" onclick="return confirm('¿Estás seguro de que deseas eliminar este producto?')">
                                            <i class="fas fa-trash-alt"></i> Eliminar
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Incluir Bootstrap JS y Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
    
    <!-- Script para manejar el modal y el ID -->
    <script>
document.addEventListener('DOMContentLoaded', function() {
    // Manejar clic en botones de editar
    document.querySelectorAll('.btn-edit').forEach(button => {
        button.addEventListener('click', function() {
            // Obtener datos del producto desde los atributos DATA
            const productData = {
                id: this.getAttribute('data-id'),
                nombre: this.getAttribute('data-nombre'),
                descripcion: this.getAttribute('data-descripcion'),
                precio: this.getAttribute('data-precio'),
                stock: this.getAttribute('data-stock')
            };
            
            // Llenar el formulario del modal con los datos del producto
            document.getElementById('editProductId').value = productData.id;
            document.getElementById('editNombre').value = productData.nombre;
            document.getElementById('editDescripcion').value = productData.descripcion;
            document.getElementById('editPrecio').value = productData.precio;
            document.getElementById('editStock').value = productData.stock;
            
            // Mostrar el modal
            const editModal = new bootstrap.Modal(document.getElementById('editProductModal'));
            editModal.show();
        });
    });
    // Cerrar automáticamente la alerta después de 5 segundos
    const alert = document.getElementById('autoCloseAlert');
    if (alert) {
        setTimeout(() => {
            const bootstrapAlert = new bootstrap.Alert(alert);
            bootstrapAlert.close();
        }, 2000);
    }
    
});
</script>
</body>
</html>