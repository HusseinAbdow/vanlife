// When order is completed
$pdo->beginTransaction();
try {
    // 1. Create order
    $stmt = $pdo->prepare(
        "INSERT INTO orders (van_id, satici_id, musteri_id, amount, status)
         VALUES (?, ?, ?, ?, 'completed')"
    );
    $stmt->execute([$van_id, $seller_id, $buyer_id, $amount]);
    
    // 2. Mark van as sold
    $pdo->exec("UPDATE vans SET is_sold = TRUE WHERE van_id = $van_id");
    
    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    // Handle error
}