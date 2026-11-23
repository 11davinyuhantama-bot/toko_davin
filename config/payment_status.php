<?php

function applyPaymentStatus(mysqli $conn, string $orderId, string $newStatus): void
{
    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare('SELECT id_transaksi, payment_status FROM tbl_transaksi WHERE payment_order_id = ? LIMIT 1 FOR UPDATE');
        $stmt->bind_param('s', $orderId);
        $stmt->execute();
        $transaction = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$transaction || !in_array($transaction['payment_status'], ['pending', 'verification'], true)) {
            $conn->commit();
            return;
        }

        $idTransaksi = (int) $transaction['id_transaksi'];
        if (in_array($newStatus, ['expired', 'cancelled', 'failed'], true)) {
            $details = $conn->prepare('SELECT id_produk, jumlah FROM tbl_detail_transaksi WHERE id_transaksi = ? AND id_produk IS NOT NULL');
            $details->bind_param('i', $idTransaksi);
            $details->execute();
            $items = $details->get_result()->fetch_all(MYSQLI_ASSOC);
            $details->close();
            $stock = $conn->prepare('UPDATE tbl_produk SET stok = stok + ? WHERE id_produk = ?');
            foreach ($items as $item) {
                $quantity = (int) $item['jumlah'];
                $productId = (int) $item['id_produk'];
                $stock->bind_param('ii', $quantity, $productId);
                $stock->execute();
            }
            $stock->close();
        }

        $update = $conn->prepare("UPDATE tbl_transaksi SET payment_status = ? WHERE id_transaksi = ? AND payment_status IN ('pending', 'verification')");
        $update->bind_param('si', $newStatus, $idTransaksi);
        $update->execute();
        $update->close();
        $conn->commit();
    } catch (Throwable $exception) {
        $conn->rollback();
        throw $exception;
    }
}
