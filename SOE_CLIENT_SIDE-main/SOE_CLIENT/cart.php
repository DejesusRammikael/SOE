<!DOCTYPE html>
<html>
<head>
    <title>Your Cart</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            max-width: 600px;
            margin: auto;
        }
        h1 { text-align: center; }
        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #ccc;
            padding: 12px 0;
        }
        .cart-item-name { flex: 2; }
        .cart-item-qty { flex: 1; }
        .cart-item-qty input {
            width: 50px;
            text-align: center;
            font-size: 16px;
            padding: 4px;
        }
        .cart-item-price {
            flex: 1;
            text-align: right;
            font-weight: bold;
            color: #e67e22;
        }
        .remove-btn {
            flex: 0;
            background: none;
            border: none;
            color: #e74c3c;
            font-size: 22px;
            cursor: pointer;
        }
        #total {
            margin-top: 20px;
            text-align: right;
            font-size: 20px;
            font-weight: bold;
            color: #2c3e50;
        }
        #emptyMsg {
            text-align: center;
            color: #999;
            margin-top: 40px;
            font-size: 18px;
        }
        .actions {
            margin-top: 30px;
            text-align: center;
        }
        .btn {
            padding: 10px 20px;
            font-weight: bold;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            margin: 0 10px;
        }
        .btn-clear {
            background-color:rgb(207, 175, 28);
            color: white;
        }
        .btn-back {
            background-color: #34495e;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            display: inline-block;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<a href="stall_dashboard.php" class="btn-back">← Back to Menu</a>

<h1>Your Cart</h1>

<div id="cartContainer"></div>

<div id="total"></div>

<div class="actions">
    <button id="clearCart" class="btn btn-clear">Clear Cart</button>
</div>

<script>
const cartContainer = document.getElementById('cartContainer');
const totalDiv = document.getElementById('total');
const clearCartBtn = document.getElementById('clearCart');

async function fetchCart() {
    const response = await fetch('fetch_cart.php');
    const cart = await response.json();
    return cart;
}

async function updateQuantity(id, newQty) {
    await fetch('add_to_cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `menu_item_id=${encodeURIComponent(id)}&quantity=${encodeURIComponent(newQty)}`
    });
    renderCart(); // Refresh cart display
}

async function removeItem(id) {
    await updateQuantity(id, -9999); // Trick: negative value to trigger 0 qty (you can customize)
    renderCart();
}

async function clearCart() {
    const response = await fetch('clear_cart.php');
    const data = await response.json();
    if (data.success) renderCart();
}

async function renderCart() {
    const cart = await fetchCart();
    cartContainer.innerHTML = '';
    totalDiv.textContent = '';
    clearCartBtn.style.display = cart.length > 0 ? 'inline-block' : 'none';

    if (cart.length === 0) {
        cartContainer.innerHTML = '<div id="emptyMsg">Your cart is empty.</div>';
        return;
    }

    let total = 0;

    cart.forEach(item => {
        total += item.price * item.quantity;

        const div = document.createElement('div');
        div.className = 'cart-item';
        div.innerHTML = `
            <div class="cart-item-name">${item.name}</div>
            <div class="cart-item-qty">
                <input type="number" min="1" value="${item.quantity}" data-id="${item.id}" />
            </div>
            <div class="cart-item-price">₱${(item.price * item.quantity).toFixed(2)}</div>
            <button class="remove-btn" data-id="${item.id}" title="Remove">&times;</button>
        `;

        cartContainer.appendChild(div);
    });

    totalDiv.textContent = 'Total: ₱' + total.toFixed(2);

    document.querySelectorAll('.cart-item-qty input').forEach(input => {
        input.addEventListener('change', e => {
            const id = e.target.getAttribute('data-id');
            let qty = parseInt(e.target.value);
            if (isNaN(qty) || qty < 1) qty = 1;
            updateQuantity(id, qty);
        });
    });

    document.querySelectorAll('.remove-btn').forEach(btn => {
        btn.addEventListener('click', e => {
            const id = e.target.getAttribute('data-id');
            removeItem(id);
        });
    });
}

clearCartBtn.addEventListener('click', () => {
    if (confirm('Clear all items from your cart?')) {
        clearCart();
    }
});

renderCart();
</script>

</body>
</html>
