<?php
session_start();

// Inicializar el carrito si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// Eliminar producto del carrito
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar'])) {
    $id = $_POST['eliminar'];
    unset($_SESSION['carrito'][$id]);
}

// Calcular total
$total = 0;
foreach ($_SESSION['carrito'] as $item) {
    $total += $item['precio'] * $item['cantidad'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Carrito de Compras</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f4f4f4;
            padding: 20px;
        }
        .container {
            max-width: 900px;
            margin: auto;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            text-align: center;
            margin-bottom: 25px;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        th, td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #007bff;
            color: white;
        }
        .btn {
            padding: 8px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
        .btn-eliminar {
            background: #dc3545;
            color: white;
        }
        .btn-finalizar {
            background: #28a745;
            color: white;
            float: right;
            text-decoration: none;
            padding: 10px 18px;
        }
        .total {
            font-weight: bold;
            font-size: 1.2rem;
            text-align: right;
            margin-top: 10px;
        }
        .empty {
            text-align: center;
            color: #888;
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Carrito de Compras</h1>

        <?php if (empty($_SESSION['carrito'])): ?>
            <div class="empty">Tu carrito está vacío.</div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Paquete</th>
                        <th>Precio Unitario</th>
                        <th>Cantidad</th>
                        <th>Subtotal</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($_SESSION['carrito'] as $id => $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['nombre']) ?></td>
                            <td>$<?= number_format($item['precio'], 2) ?> USD</td>
                            <td><?= $item['cantidad'] ?></td>
                            <td>$<?= number_format($item['precio'] * $item['cantidad'], 2) ?> USD</td>
                            <td>
                                <form method="post">
                                    <button type="submit" name="eliminar" value="<?= $id ?>" class="btn btn-eliminar">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="total">Total: $<?= number_format($total, 2) ?> USD</div>
            <a href="finalizar_compra.php" class="btn-finalizar">Finalizar Compra</a>
        <?php endif; ?>
    </div>
</body>
</html>
{}