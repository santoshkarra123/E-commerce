<?php
// Start session and check login
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include '../includes/db.php'; // Make sure path is correct

$user_id = $_SESSION['user_id'];

// Handle cart updates
if (isset($_POST['update_quantity'])) {
    $product_id = $_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$quantity, $user_id, $product_id]);
    header("Location: cart.php"); // Refresh to show updated quantity
    exit();
}

if (isset($_POST['remove_from_cart'])) {
    $product_id = $_POST['product_id'];
    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    header("Location: cart.php"); // Refresh after removal
    exit();
}

// Fetch cart items
$stmt = $conn->prepare("
    SELECT cart.id AS cart_id, products.id AS product_id, products.name, products.price, cart.quantity 
    FROM cart 
    JOIN products ON cart.product_id = products.id 
    WHERE cart.user_id = ?
");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_cost = 0;
foreach ($cart_items as $item) {
    $total_cost += $item['price'] * $item['quantity'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Cart</title>
    <style>
        .cart-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        button {
            padding: 5px 10px;
            background: #ff4c4c;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background: #ff0000;
        }
        .total {
            text-align: right;
            font-weight: bold;
            margin-top: 10px;
        }
        input[type="number"] {
            width: 60px;
        }
    </style>
</head>
<body>

<div class="cart-container">
    <h2>Your Cart</h2>
    <?php if (count($cart_items) > 0): ?>
    <table>
        <tr>
            <th>Product</th>
            <th>Price</th>
            <th>Quantity</th>
            <th>Subtotal</th>
            <th>Action</th>
        </tr>
        <?php foreach ($cart_items as $item): ?>
        <tr>
            <td><?= htmlspecialchars($item['name']); ?></td>
            <td>$<?= number_format($item['price'], 2); ?></td>
            <td>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="product_id" value="<?= $item['product_id']; ?>">
                    <input type="number" name="quantity" value="<?= $item['quantity']; ?>" min="1">
                    <button type="submit" name="update_quantity">Update</button>
                </form>
            </td>
            <td>$<?= number_format($item['price'] * $item['quantity'], 2); ?></td>
            <td>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="product_id" value="<?= $item['product_id']; ?>">
                    <button type="submit" name="remove_from_cart">Remove</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    <div class="total">
        Total: $<?= number_format($total_cost, 2); ?>
    </div>
    <?php else: ?>
    <p>Your cart is empty. <a href="products.php">Go Shopping</a></p>
    <?php endif; ?>
</div>

</body>
</html>
