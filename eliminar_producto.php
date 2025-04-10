<?php
// Incluir la configuración de la base de datos
require_once 'config/database.php';

// Obtener el ID del producto
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Eliminar el producto de la base de datos
    $sql = "DELETE FROM productos WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);

    echo "Producto eliminado exitosamente!";
    header('Location: index.php');
    exit();
}
?>
