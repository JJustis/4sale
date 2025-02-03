<?php
// functions.php
function checkSkuUnique($sku) {
    global $conn;
    $stmt = $conn->prepare("SELECT COUNT(*) FROM items WHERE sku = ?");
    $stmt->bind_param("s", $sku);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_row()[0];
    return $count === 0;
}

function generateUniqueSku($sku) {
    $counter = 1;
    $newSku = $sku;
    while (!checkSkuUnique($newSku)) {
        $newSku = $sku . '_' . $counter;
        $counter++;
    }
    return $newSku;
}
