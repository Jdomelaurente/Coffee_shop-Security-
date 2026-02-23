<?php
session_start();
// Security Check: Only allow logged-in users
if (!isset($_SESSION['logged_in'])) {
    header("Location: index.php");
    exit();
}
require_once 'db.php';
require_once 'log_functions.php';

// Fetch menu items from DB
$menu_items = [];
try {
    $stmt = $conn->query("SELECT * FROM menu_items ORDER BY category, name");
    $menu_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Table might not exist yet; continue with empty array
}

// Group items by category
$categories = [];
foreach ($menu_items as $item) {
    $cat = $item['category'] ?? 'Other';
    $categories[$cat][] = $item;
}

// User info for display
$initials = '';
$name_parts = explode(' ', $_SESSION['user_name'] ?? 'User');
foreach ($name_parts as $p) { $initials .= strtoupper(substr($p,0,1)); }
$initials = substr($initials, 0, 2);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coffee POS – Order</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/pos.css">
</head>
<body>

<!-- ============ TOPBAR ============ -->
<header class="topbar">
    <div class="topbar-brand">
        <i class="fas fa-mug-hot"></i>
        <h1>Brew<span>POS</span></h1>
    </div>

    <div class="topbar-center">
        <i class="fas fa-search search-icon"></i>
        <input type="text" class="search-box" id="searchInput" placeholder="Search menu…" oninput="filterProducts(this.value)">
    </div>

    <div class="topbar-right">
        <div class="user-chip">
            <div class="user-avatar"><?php echo $initials; ?></div>
            <div>
                <div class="user-name"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?></div>

            </div>
        </div>
        <button class="logout-btn" onclick="confirmLogout()">
            <i class="fas fa-sign-out-alt"></i> Logout
        </button>
    </div>
</header>

<!-- ============ LAYOUT ============ -->
<div class="pos-layout">

    <!-- Category Sidebar -->
    <aside class="category-sidebar" id="categorySidebar">
        <div class="sidebar-label">Categories</div>
        <div class="cat-item active" onclick="filterCategory('all', this)" id="cat-all">
            <i class="fas fa-th-large"></i>
            <span>All Items</span>
            <span class="cat-count"><?php echo count($menu_items); ?></span>
        </div>
        <?php
        $cat_icons = [
            'Coffee'     => 'fas fa-coffee',
            'Tea'        => 'fas fa-leaf',
            'Pastry'     => 'fas fa-bread-slice',
            'Snacks'     => 'fas fa-cookie-bite',
            'Cold Drinks'=> 'fas fa-glass-whiskey',
            'Hot Drinks' => 'fas fa-mug-hot',
            'Dessert'    => 'fas fa-ice-cream',
            'Other'      => 'fas fa-box-open',
        ];
        foreach ($categories as $catName => $items):
            $icon = $cat_icons[$catName] ?? 'fas fa-tag';
        ?>
        <div class="cat-item" onclick="filterCategory('<?php echo htmlspecialchars($catName); ?>', this)" id="cat-<?php echo htmlspecialchars($catName); ?>">
            <i class="<?php echo $icon; ?>"></i>
            <span><?php echo htmlspecialchars($catName); ?></span>
            <span class="cat-count"><?php echo count($items); ?></span>
        </div>
        <?php endforeach; ?>

        <?php if (empty($categories)): ?>
        <!-- Demo categories if DB has no items -->
        <div class="cat-item" onclick="filterCategory('Coffee', this)">
            <i class="fas fa-coffee"></i><span>Coffee</span><span class="cat-count">6</span>
        </div>
        <div class="cat-item" onclick="filterCategory('Pastry', this)">
            <i class="fas fa-bread-slice"></i><span>Pastry</span><span class="cat-count">4</span>
        </div>
        <div class="cat-item" onclick="filterCategory('Cold Drinks', this)">
            <i class="fas fa-glass-whiskey"></i><span>Cold Drinks</span><span class="cat-count">3</span>
        </div>
        <?php endif; ?>
    </aside>

    <!-- Products Area -->
    <main class="products-area">
        <!-- Pills (mobile category filter) -->
        <div class="category-pills" id="categoryPills">
            <div class="pill active" onclick="filterCategory('all', this)">
                <i class="fas fa-th-large"></i> All
            </div>
            <?php foreach (array_keys($categories) as $catName): ?>
            <div class="pill" onclick="filterCategory('<?php echo htmlspecialchars($catName); ?>', this)">
                <?php echo htmlspecialchars($catName); ?>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Products Grid -->
        <div class="products-grid" id="productsGrid">
            <?php if (!empty($menu_items)): ?>
                <?php foreach ($menu_items as $item):
                    $stock = (int)($item['stock_quantity'] ?? 99);
                    $out   = $stock <= 0;
                ?>
                <div class="product-card <?php echo $out ? 'out-of-stock' : ''; ?>"
                     onclick="<?php echo $out ? '' : "addToCart({$item['id']}, '".addslashes($item['name'])."', {$item['price']}, '".addslashes($item['category'] ?? 'Other')."')"; ?>"
                     data-category="<?php echo htmlspecialchars($item['category'] ?? 'Other'); ?>"
                     data-name="<?php echo htmlspecialchars(strtolower($item['name'])); ?>">
                    <div class="product-img">
                        <?php if (!empty($item['image_url'])): ?>
                        <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" style="width:100%;height:100%;object-fit:cover;">
                        <?php else: ?>
                        <i class="fas fa-mug-hot"></i>
                        <?php endif; ?>
                    </div>
                    <?php if ($out): ?>
                    <div class="stock-badge">Out of Stock</div>
                    <?php endif; ?>
                    <div class="product-info">
                        <div class="product-name"><?php echo htmlspecialchars($item['name']); ?></div>
                        <div class="product-desc"><?php echo htmlspecialchars($item['description'] ?? ''); ?></div>
                        <div class="product-footer">
                            <span class="product-price">₱<?php echo number_format((float)$item['price'], 2); ?></span>
                            <?php if (!$out): ?>
                            <button class="add-to-cart-btn" onclick="event.stopPropagation(); addToCart(<?php echo $item['id']; ?>, '<?php echo addslashes($item['name']); ?>', <?php echo $item['price']; ?>, '<?php echo addslashes($item['category'] ?? 'Other'); ?>')">
                                <i class="fas fa-plus"></i>
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Demo products when DB is empty -->
                <?php
                $demo = [
                    ['id'=>1,'name'=>'Espresso','price'=>80,'cat'=>'Coffee','desc'=>'Rich & bold shot','icon'=>'fas fa-coffee'],
                    ['id'=>2,'name'=>'Cappuccino','price'=>120,'cat'=>'Coffee','desc'=>'Espresso + steamed milk','icon'=>'fas fa-mug-hot'],
                    ['id'=>3,'name'=>'Caramel Latte','price'=>150,'cat'=>'Coffee','desc'=>'Sweet caramel twist','icon'=>'fas fa-mug-hot'],
                    ['id'=>4,'name'=>'Cold Brew','price'=>160,'cat'=>'Cold Drinks','desc'=>'Slow-steeped perfection','icon'=>'fas fa-glass-whiskey'],
                    ['id'=>5,'name'=>'Matcha Latte','price'=>140,'cat'=>'Tea','desc'=>'Premium Japanese matcha','icon'=>'fas fa-leaf'],
                    ['id'=>6,'name'=>'Croissant','price'=>95,'cat'=>'Pastry','desc'=>'Flaky, buttery delight','icon'=>'fas fa-bread-slice'],
                    ['id'=>7,'name'=>'Blueberry Muffin','price'=>85,'cat'=>'Pastry','desc'=>'Fresh-baked daily','icon'=>'fas fa-cookie-bite'],
                    ['id'=>8,'name'=>'Iced Americano','price'=>110,'cat'=>'Cold Drinks','desc'=>'Espresso over ice','icon'=>'fas fa-glass-whiskey'],
                    ['id'=>9,'name'=>'Mocha','price'=>145,'cat'=>'Coffee','desc'=>'Chocolate espresso blend','icon'=>'fas fa-coffee'],
                    ['id'=>10,'name'=>'Cheesecake','price'=>165,'cat'=>'Dessert','desc'=>'Classic New York style','icon'=>'fas fa-ice-cream'],
                ];
                foreach ($demo as $d):
                ?>
                <div class="product-card"
                     onclick="addToCart(<?php echo $d['id']; ?>, '<?php echo $d['name']; ?>', <?php echo $d['price']; ?>, '<?php echo $d['cat']; ?>')"
                     data-category="<?php echo $d['cat']; ?>"
                     data-name="<?php echo strtolower($d['name']); ?>">
                    <div class="product-img">
                        <i class="<?php echo $d['icon']; ?>"></i>
                    </div>
                    <div class="product-info">
                        <div class="product-name"><?php echo $d['name']; ?></div>
                        <div class="product-desc"><?php echo $d['desc']; ?></div>
                        <div class="product-footer">
                            <span class="product-price">₱<?php echo number_format($d['price'], 2); ?></span>
                            <button class="add-to-cart-btn" onclick="event.stopPropagation(); addToCart(<?php echo $d['id']; ?>, '<?php echo $d['name']; ?>', <?php echo $d['price']; ?>, '<?php echo $d['cat']; ?>')">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <!-- Cart Panel -->
    <aside class="cart-panel" id="cartPanel">
        <div class="cart-header">
            <div class="cart-title">
                <i class="fas fa-shopping-basket"></i>
                Your Order
                <span class="cart-badge" id="cartBadge">0</span>
            </div>
            <button class="clear-cart-btn" onclick="clearCart()">
                <i class="fas fa-trash-alt"></i> Clear
            </button>
        </div>

        <div class="cart-items" id="cartItems">
            <div class="cart-empty" id="cartEmpty">
                <i class="fas fa-coffee"></i>
                <p>Your cart is empty</p>
                <small>Tap an item to add it</small>
            </div>
        </div>

        <div class="cart-footer">
            <div class="subtotals" id="subtotalsArea">
                <div class="subtotal-row">
                    <span>Subtotal</span>
                    <span id="subtotalVal">₱0.00</span>
                </div>
                <div class="subtotal-row" id="discountRow" style="display:none; color:#E65100;">
                    <span>Discount</span>
                    <span id="discountVal">-₱0.00</span>
                </div>
                <div class="subtotal-row total">
                    <span>Total</span>
                    <span id="totalVal">₱0.00</span>
                </div>
            </div>

            <div class="discount-row">
                <input type="number" class="discount-input" id="discountInput" placeholder="Discount %" min="0" max="100" step="any">
                <button class="apply-disc-btn" onclick="applyDiscount()">Apply</button>
            </div>

            <div class="payment-methods" id="paymentMethods">
                <button class="pay-method-btn selected" id="pm-cash" onclick="selectPayment('cash')">
                    <i class="fas fa-money-bill-wave"></i> Cash
                </button>
                <button class="pay-method-btn" id="pm-card" onclick="selectPayment('card')">
                    <i class="fas fa-credit-card"></i> Card
                </button>
                <button class="pay-method-btn" id="pm-gcash" onclick="selectPayment('gcash')">
                    <i class="fas fa-mobile-alt"></i> GCash
                </button>
                <button class="pay-method-btn" id="pm-maya" onclick="selectPayment('maya')">
                    <i class="fas fa-wallet"></i> Maya
                </button>
            </div>

            <button class="checkout-btn" id="checkoutBtn" onclick="openCheckout()" disabled>
                <i class="fas fa-receipt"></i> Checkout
            </button>
        </div>
    </aside>
</div>

<!-- Mobile FAB (cart toggle) -->
<button class="mobile-fab" id="mobileFab" onclick="toggleMobileCart()" style="position:relative;">
    <i class="fas fa-shopping-basket"></i>
    <span class="cart-item-count-fab" id="fabCount" style="display:none;">0</span>
</button>

<!-- ============ CHECKOUT MODAL ============ -->
<div class="modal-overlay" id="checkoutModal">
    <div class="modal-box">
        <div class="modal-header-stripe">
            <h2><i class="fas fa-receipt"></i> Checkout</h2>
            <button class="modal-close" onclick="closeCheckout()">✕</button>
        </div>
        <div class="modal-body">
            <!-- Order summary -->
            <ul class="order-summary-list" id="checkoutSummaryList"></ul>

            <div class="total-display">
                <span>Total Amount</span>
                <strong id="checkoutTotal">₱0.00</strong>
            </div>

            <!-- Cash fields (hidden for non-cash) -->
            <div id="cashSection">
                <div class="cash-input-group">
                    <label>Cash Tendered</label>
                    <input type="number" id="cashInput" placeholder="0.00" oninput="computeChange()" min="0" step="0.01">
                </div>

                <div class="quick-amounts" id="quickAmounts"></div>

                <div class="change-display" id="changeDisplay">
                    <label><i class="fas fa-coins"></i> Change</label>
                    <strong id="changeVal">₱0.00</strong>
                </div>
            </div>

            <button class="confirm-btn" id="confirmBtn" onclick="confirmOrder()" disabled>
                <i class="fas fa-check-circle"></i> Confirm Order
            </button>
        </div>
    </div>
</div>

<!-- ============ RECEIPT MODAL ============ -->
<div class="receipt" id="receiptModal">
    <div class="receipt-box" id="receiptContent">
        <div class="receipt-header">
            <i class="fas fa-check-circle"></i>
            <h2>Order Confirmed!</h2>
            <p id="receiptOrderId">Order #0001</p>
        </div>
        <div class="receipt-body">
            <div class="receipt-shop-name">☕ BrewPOS Coffee Shop</div>
            <div style="text-align:center;font-size:1.2rem;color:#999;margin-bottom:.4rem;" id="receiptDate"></div>
            <hr class="receipt-divider">

            <div class="receipt-items" id="receiptItems"></div>

            <hr class="receipt-divider">
            <div id="receiptDiscount" style="display:none;" class="receipt-row discount">
                <span>Discount</span><span id="receiptDiscVal"></span>
            </div>
            <div class="receipt-row total">
                <span>TOTAL</span><span id="receiptTotal"></span>
            </div>
            <div class="receipt-row">
                <span>Payment</span><span id="receiptPayment"></span>
            </div>
            <div class="receipt-row" id="receiptCashRow">
                <span>Cash</span><span id="receiptCash"></span>
            </div>
            <div class="receipt-row change" id="receiptChangeRow">
                <span>Change</span><span id="receiptChange"></span>
            </div>
            <hr class="receipt-divider">
            <div style="text-align:center;font-size:1.2rem;color:#999;">Thank you! Come again ☕</div>
        </div>
        <div class="receipt-actions">
            <button class="receipt-btn print" onclick="printReceipt()">
                <i class="fas fa-print"></i> Print
            </button>
            <button class="receipt-btn new-order" onclick="newOrder()">
                <i class="fas fa-plus"></i> New Order
            </button>
        </div>
    </div>
</div>

<!-- Toast container -->
<div class="toast-container" id="toastContainer"></div>

<!-- ============ JAVASCRIPT ============ -->
<script>
// ===== STATE =====
let cart = [];           // [{id, name, price, category, qty, note}]
let discount = 0;        // percentage
let paymentMethod = 'cash';
let orderCounter = parseInt(localStorage.getItem('posOrderCount') || '0');

// ===== CART OPERATIONS =====
function addToCart(id, name, price, category) {
    const existing = cart.find(i => i.id === id);
    if (existing) {
        existing.qty++;
    } else {
        cart.push({ id, name, price: parseFloat(price), category, qty: 1, note: '' });
    }
    renderCart();
    showToast(`${name} added!`, 'success');
}

function removeFromCart(id) {
    cart = cart.filter(i => i.id !== id);
    renderCart();
}

function changeQty(id, delta) {
    const item = cart.find(i => i.id === id);
    if (!item) return;
    item.qty += delta;
    if (item.qty <= 0) removeFromCart(id);
    else renderCart();
}

function updateNote(id, note) {
    const item = cart.find(i => i.id === id);
    if (item) item.note = note;
}

function clearCart() {
    if (cart.length === 0) return;
    if (!confirm('Clear all items?')) return;
    cart = [];
    discount = 0;
    document.getElementById('discountInput').value = '';
    renderCart();
    showToast('Cart cleared');
}

// ===== TOTALS =====
function getSubtotal() {
    return cart.reduce((s, i) => s + i.price * i.qty, 0);
}

function getTotal() {
    const sub = getSubtotal();
    return sub - (sub * discount / 100);
}

// ===== DISCOUNT =====
function applyDiscount() {
    const d = parseFloat(document.getElementById('discountInput').value);
    if (isNaN(d) || d < 0 || d > 100) {
        showToast('Enter a valid discount (0–100%)', 'error');
        return;
    }
    discount = d;
    renderCart();
    showToast(`${d}% discount applied!`, 'success');
}

// ===== PAYMENT =====
function selectPayment(method) {
    paymentMethod = method;
    document.querySelectorAll('.pay-method-btn').forEach(b => b.classList.remove('selected'));
    document.getElementById('pm-' + method).classList.add('selected');
}

// ===== RENDER CART =====
function renderCart() {
    const container = document.getElementById('cartItems');
    const empty     = document.getElementById('cartEmpty');
    const badge     = document.getElementById('cartBadge');
    const fabCount  = document.getElementById('fabCount');
    const checkBtn  = document.getElementById('checkoutBtn');

    const totalItems = cart.reduce((s, i) => s + i.qty, 0);
    badge.textContent  = totalItems;
    fabCount.textContent = totalItems;
    fabCount.style.display = totalItems > 0 ? 'flex' : 'none';
    checkBtn.disabled  = totalItems === 0;

    if (cart.length === 0) {
        empty.style.display = 'flex';
        // remove all cart item nodes
        document.querySelectorAll('.cart-item').forEach(n => n.remove());
    } else {
        empty.style.display = 'none';
        // rebuild
        document.querySelectorAll('.cart-item').forEach(n => n.remove());
        cart.forEach(item => {
            const div = document.createElement('div');
            div.className = 'cart-item';
            div.id = 'ci-' + item.id;
            div.innerHTML = `
                <div>
                    <div class="cart-item-name">${item.name}</div>
                    <div class="cart-item-price">₱${item.price.toFixed(2)} each</div>
                    <div class="cart-item-subtotal">₱${(item.price * item.qty).toFixed(2)}</div>
                </div>
                <div style="display:flex;flex-direction:column;align-items:flex-end;gap:.6rem;">
                    <div class="qty-controls">
                        <button class="qty-btn minus" onclick="changeQty(${item.id}, -1)">−</button>
                        <span class="qty-value">${item.qty}</span>
                        <button class="qty-btn plus" onclick="changeQty(${item.id}, +1)">+</button>
                    </div>
                    <button onclick="removeFromCart(${item.id})" style="background:none;border:none;color:#C62828;cursor:pointer;font-size:1.2rem;">
                        <i class="fas fa-times"></i> Remove
                    </button>
                </div>
                <input class="item-note" placeholder="Special note (optional)" value="${item.note}" oninput="updateNote(${item.id}, this.value)">
            `;
            container.insertBefore(div, document.getElementById('cartEmpty'));
        });
    }

    // Update totals
    const sub   = getSubtotal();
    const total = getTotal();
    document.getElementById('subtotalVal').textContent = '₱' + sub.toFixed(2);
    document.getElementById('totalVal').textContent    = '₱' + total.toFixed(2);

    const discRow = document.getElementById('discountRow');
    if (discount > 0) {
        discRow.style.display = 'flex';
        document.getElementById('discountVal').textContent = '-₱' + (sub - total).toFixed(2);
    } else {
        discRow.style.display = 'none';
    }
}

// ===== FILTER =====
function filterCategory(cat, el) {
    // Highlight sidebar
    document.querySelectorAll('.cat-item').forEach(c => c.classList.remove('active'));
    if (el.classList.contains('cat-item')) el.classList.add('active');

    // Highlight pills
    document.querySelectorAll('.pill').forEach(p => p.classList.remove('active'));
    if (el.classList.contains('pill')) el.classList.add('active');

    document.querySelectorAll('.product-card').forEach(card => {
        const match = cat === 'all' || card.dataset.category === cat;
        card.style.display = match ? 'block' : 'none';
    });
}

function filterProducts(q) {
    const query = q.toLowerCase().trim();
    document.querySelectorAll('.product-card').forEach(card => {
        card.style.display = (!query || card.dataset.name.includes(query)) ? 'block' : 'none';
    });
}

// ===== CHECKOUT MODAL =====
function openCheckout() {
    if (cart.length === 0) return;

    // Build summary list
    const list = document.getElementById('checkoutSummaryList');
    list.innerHTML = cart.map(i => `
        <li>
            <span class="name">${i.name}</span>
            <span class="qty">x${i.qty}</span>
            <span class="price">₱${(i.price * i.qty).toFixed(2)}</span>
        </li>
    `).join('');

    const total = getTotal();
    document.getElementById('checkoutTotal').textContent = '₱' + total.toFixed(2);

    // Quick amount buttons
    const quickAmts = [50, 100, 200, 500, 1000].filter(a => a >= total || a >= 50);
    document.getElementById('quickAmounts').innerHTML = quickAmts.map(a =>
        `<button class="quick-btn" onclick="setQuickAmount(${a})">₱${a}</button>`
    ).join('') + `<button class="quick-btn" onclick="setQuickAmount(${Math.ceil(total/50)*50})">Exact (₱${(Math.ceil(total/50)*50).toFixed(2)})</button>`;

    // Cash vs other
    const cashSection = document.getElementById('cashSection');
    cashSection.style.display = paymentMethod === 'cash' ? 'block' : 'none';
    document.getElementById('cashInput').value = '';
    document.getElementById('changeVal').textContent = '₱0.00';
    document.getElementById('changeDisplay').className = 'change-display';
    document.getElementById('confirmBtn').disabled = paymentMethod !== 'cash' ? false : true;

    document.getElementById('checkoutModal').classList.add('open');
}

function closeCheckout() {
    document.getElementById('checkoutModal').classList.remove('open');
}

function setQuickAmount(amt) {
    document.getElementById('cashInput').value = amt;
    computeChange();
}

function computeChange() {
    const cash  = parseFloat(document.getElementById('cashInput').value) || 0;
    const total = getTotal();
    const change = cash - total;
    const display = document.getElementById('changeDisplay');
    const confirmBtn = document.getElementById('confirmBtn');

    if (cash <= 0) {
        document.getElementById('changeVal').textContent = '₱0.00';
        display.className = 'change-display';
        confirmBtn.disabled = true;
        return;
    }

    document.getElementById('changeVal').textContent = '₱' + Math.abs(change).toFixed(2);
    if (change >= 0) {
        display.className = 'change-display';
        display.querySelector('label').innerHTML = '<i class="fas fa-coins"></i> Change';
        confirmBtn.disabled = false;
    } else {
        display.className = 'change-display insufficient';
        display.querySelector('label').innerHTML = '<i class="fas fa-exclamation-circle"></i> Insufficient';
        confirmBtn.disabled = true;
    }
}

// ===== CONFIRM ORDER =====
function confirmOrder() {
    const total  = getTotal();
    const cash   = paymentMethod === 'cash' ? (parseFloat(document.getElementById('cashInput').value) || 0) : total;
    const change = cash - total;

    orderCounter++;
    localStorage.setItem('posOrderCount', orderCounter);
    const orderId = String(orderCounter).padStart(4, '0');

    // Close checkout modal
    closeCheckout();

    // Populate receipt
    document.getElementById('receiptOrderId').textContent = 'Order #' + orderId;
    document.getElementById('receiptDate').textContent    = new Date().toLocaleString();

    const itemsHtml = cart.map(i => `
        <div class="receipt-item-row">
            <span>${i.name} x${i.qty}${i.note ? ` <em style="color:#aaa">(${i.note})</em>` : ''}</span>
            <span>₱${(i.price * i.qty).toFixed(2)}</span>
        </div>
    `).join('');
    document.getElementById('receiptItems').innerHTML = itemsHtml;

    const sub = getSubtotal();
    if (discount > 0) {
        document.getElementById('receiptDiscount').style.display = 'flex';
        document.getElementById('receiptDiscVal').textContent = `-₱${(sub - total).toFixed(2)} (${discount}%)`;
    } else {
        document.getElementById('receiptDiscount').style.display = 'none';
    }

    document.getElementById('receiptTotal').textContent   = '₱' + total.toFixed(2);
    document.getElementById('receiptPayment').textContent = paymentMethod.charAt(0).toUpperCase() + paymentMethod.slice(1);

    if (paymentMethod === 'cash') {
        document.getElementById('receiptCashRow').style.display  = 'flex';
        document.getElementById('receiptChangeRow').style.display = 'flex';
        document.getElementById('receiptCash').textContent   = '₱' + cash.toFixed(2);
        document.getElementById('receiptChange').textContent = '₱' + change.toFixed(2);
    } else {
        document.getElementById('receiptCashRow').style.display   = 'none';
        document.getElementById('receiptChangeRow').style.display = 'none';
    }

    document.getElementById('receiptModal').classList.add('open');

    // Log activity (send to server silently)
    fetch('log_activity.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            action: 'Order placed',
            module: 'Sales',
            details: { order_id: orderId, total: total.toFixed(2), items: cart.length, payment: paymentMethod }
        })
    }).catch(() => {});
}

function printReceipt() {
    const content = document.getElementById('receiptContent').innerHTML;
    const w = window.open('', '_blank', 'width=400,height=600');
    w.document.write(`<html><head><title>Receipt</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>body{font-family:Poppins,sans-serif;padding:20px;font-size:13px;} .receipt-actions{display:none;} </style>
    </head><body>${content}</body></html>`);
    w.document.close();
    w.focus();
    setTimeout(() => { w.print(); w.close(); }, 500);
}

function newOrder() {
    cart     = [];
    discount = 0;
    document.getElementById('discountInput').value = '';
    document.getElementById('receiptModal').classList.remove('open');
    renderCart();
    showToast('Ready for next order!', 'success');
}

// ===== MOBILE =====
function toggleMobileCart() {
    document.getElementById('cartPanel').classList.toggle('open');
}

// ===== TOAST =====
function showToast(msg, type = '') {
    const tc   = document.getElementById('toastContainer');
    const icon = type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle';
    const t    = document.createElement('div');
    t.className = 'toast ' + type;
    t.innerHTML = `<i class="fas ${icon}"></i> ${msg}`;
    tc.appendChild(t);
    setTimeout(() => { t.style.opacity = '0'; t.style.transition = 'opacity .4s'; setTimeout(() => t.remove(), 400); }, 2500);
}

// ===== LOGOUT =====
function confirmLogout() {
    if (confirm('Are you sure you want to logout?')) {
        window.location.href = 'log-out.php';
    }
}

// Close modals on overlay click
document.getElementById('checkoutModal').addEventListener('click', function(e) {
    if (e.target === this) closeCheckout();
});
document.getElementById('receiptModal').addEventListener('click', function(e) {
    if (e.target === this) document.getElementById('receiptModal').classList.remove('open');
});

// Init
renderCart();
</script>
</body>
</html>
