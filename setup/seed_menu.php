<?php
require_once dirname(__FILE__) . '/../includes/db.php';

try {
    $conn->exec("TRUNCATE TABLE menu_items RESTART IDENTITY CASCADE");

    $menu_items = [
        ['Classic Barako', 'Matapang at mabango mula sa Batangas', 65.00, 'Coffee'],
        ['Kape at Pandesal', 'Ang paboritong agahan ng bawat Pinoy', 45.00, 'Combo'],
        ['Rich Tsokolate', 'Gawa sa purong tablea', 85.00, 'Hot Drinks'],
        ['Iced Sagada', 'Malamig na kape mula sa bundok', 95.00, 'Cold Drinks'],
        ['Bibingka Special', 'May itlog na maalat at keso', 75.00, 'Pastry'],
        ['Puto Bumbong', 'Paborito tuwing pasko (o araw-araw!)', 70.00, 'Pastry'],
        ['Kapeng Matamis', 'Nilagyan ng kondensada', 75.00, 'Coffee'],
        ['Ensaimada', 'Punong-puno ng mantikilya at keso', 55.00, 'Pastry']
    ];

    $stmt = $conn->prepare("INSERT INTO menu_items (name, description, price, category, stock_quantity) VALUES (?, ?, ?, ?, 100)");

    foreach ($menu_items as $item) {
        $stmt->execute($item);
    }

    echo "Menu items seeded successfully!";
} catch (PDOException $e) {
    echo "Seed failed: " . $e->getMessage();
}
