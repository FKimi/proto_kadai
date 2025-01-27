<?php
session_start();
include('../functions.php');
check_session_id();

// POSTデータの受け取り
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// データが正しく受け取れているか確認
if (!$data || !isset($data['portfolio_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid data']);
    exit();
}

$portfolio_id = $data['portfolio_id'];
$user_id = $_SESSION['user_id'];

$pdo = connect_to_db();

try {
    // トランザクション開始
    $pdo->beginTransaction();

    // いいねの重複チェック
    $sql = 'SELECT COUNT(*) FROM like_table 
            WHERE user_id = :user_id AND portfolio_id = :portfolio_id';
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':portfolio_id', $portfolio_id, PDO::PARAM_INT);
    $stmt->execute();
    $like_count = $stmt->fetchColumn();

    if ($like_count > 0) {
        // いいね取り消し
        $sql = 'DELETE FROM like_table 
                WHERE user_id = :user_id AND portfolio_id = :portfolio_id';
        $is_liked = false;
    } else {
        // いいね追加
        $sql = 'INSERT INTO like_table (user_id, portfolio_id) 
                VALUES (:user_id, :portfolio_id)';
        $is_liked = true;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':portfolio_id', $portfolio_id, PDO::PARAM_INT);
    $stmt->execute();

    // 更新後のいいね数を取得
    $sql = 'SELECT COUNT(*) FROM like_table WHERE portfolio_id = :portfolio_id';
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':portfolio_id', $portfolio_id, PDO::PARAM_INT);
    $stmt->execute();
    $like_count = $stmt->fetchColumn();

    // トランザクションコミット
    $pdo->commit();

    echo json_encode(['like_count' => $like_count, 'is_liked' => $is_liked]);
    exit();

} catch (Exception $e) {
    // エラー時はロールバック
    $pdo->rollBack();
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>