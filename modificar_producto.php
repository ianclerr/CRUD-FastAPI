<?php
require_once 'config/database.php';

$message = '';

// Obtener el ID del producto desde la URL
if (isset($_GET['id'])) {
    $id_producto = $_GET['id'];

    // Llamar a la API para obtener los detalles del producto
    $response = callApi("http://127.0.0.1:8001/productos/{$id_producto}");
    if ($response['status'] === 200) {
        $producto = $response['data'];
    } else {
        $message = "Error al cargar el producto: " . ($response['data']['detail'] ?? 'Error desconocido');
    }
} else {
    // Redirigir si no se proporciona el ID
    header('Location: index.php');
    exit();
}

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

// Procesar la actualización del producto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $data = [
        'nombre' => $_POST['nombre'],
        'descripcion' => $_POST['descripcion'],
        'precio' => (float)$_POST['precio'],
        'stock' => (int)$_POST['stock']
    ];

    $response = callApi("http://127.0.0.1:8001/productos/{$id_producto}", 'PUT', $data);

    if ($response['status'] === 200) {
        $message = "Producto actualizado exitosamente!";
    } else {
        $message = "Error al actualizar: " . ($response['data']['detail'] ?? 'Error desconocido');
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar Producto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <h1 class="text-center my-5">Modificar Producto</h1>

        <?php if ($message): ?>
            <div class="alert alert-info"><?= $message ?></div>
        <?php endif; ?>

        <?php if (isset($producto)): ?>
            <form method="POST">
                <input type="hidden" name="action" value="update">

                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre</label>
                    <input type="text" name="nombre" id="nombre" value="<?= htmlspecialchars($producto['nombre']) ?>" required class="form-control">
                </div>
                <div class="mb-3">
                    <label for="descripcion" class="form-label">Descripción</label>
                    <textarea name="descripcion" id="descripcion" required class="form-control"><?= htmlspecialchars($producto['descripcion']) ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="precio" class="form-label">Precio</label>
                    <input type="number" name="precio" id="precio" value="<?= htmlspecialchars($producto['precio']) ?>" step="0.01" min="0.01" required class="form-control">
                </div>
                <div class="mb-3">
                    <label for="stock" class="form-label">Stock</label>
                    <input type="number" name="stock" id="stock" value="<?= htmlspecialchars($producto['stock']) ?>" min="0" required class="form-control">
                </div>

                <button type="submit" class="btn btn-success">Actualizar Producto</button>
            </form>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
