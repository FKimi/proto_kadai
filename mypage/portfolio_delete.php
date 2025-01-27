<?php
// mypage/portfolio_delete.php
// ===================================================================
// 作品削除処理
// 目的：指定された作品の削除とそれに関連する画像ファイルの削除
// ===================================================================
session_start();
include('../functions.php');
check_session_id();

// GETパラメータのチェック
if (!isset($_GET['id'])) {
    $_SESSION['error_message'] = '削除する作品が指定されていません';
    header('Location: mypage.php');
    exit();
}

$id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// データベースに接続
$pdo = connect_to_db();

try {
    // トランザクション開始
    $pdo->beginTransaction();

    // 作品情報の取得（所有者確認のため）
    $sql = 'SELECT * FROM portfolio WHERE id = :id AND user_id = :user_id';
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $work = $stmt->fetch();

    // 作品が存在しないか、所有者が異なる場合
    if (!$work) {
        $_SESSION['error_message'] = '削除権限がないか、作品が存在しません';
        header('Location: mypage.php');
        exit();
    }

    // 画像ファイルの削除
    if (!empty($work['image_path']) && file_exists('../' . $work['image_path'])) {
        if (!unlink('../' . $work['image_path'])) {
            // 画像削除に失敗した場合
            throw new Exception('画像ファイルの削除に失敗しました');
        }
    }

    // データベースから作品を削除
    $sql = 'DELETE FROM portfolio WHERE id = :id AND user_id = :user_id';
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();

    // トランザクションのコミット
    $pdo->commit();

    // 成功メッセージをセット
    $_SESSION['success_message'] = '作品を削除しました';
    header('Location: mypage.php');
    exit();

} catch (Exception $e) {
    // エラー発生時はロールバック
    $pdo->rollBack();
    
    // エラーメッセージをセット
    $_SESSION['error_message'] = '削除中にエラーが発生しました: ' . $e->getMessage();
    header('Location: mypage.php');
    exit();
}

// ===================================================================
// 実装のポイント
// 1. セキュリティ対策
//    - セッションチェック
//    - 所有者確認
//    - SQLインジェクション対策
//
// 2. データの整合性
//    - トランザクションの使用
//    - 画像ファイルとデータベースの同期
//
// 3. エラーハンドリング
//    - 例外処理
//    - ユーザーフレンドリーなメッセージ
//    - 適切なリダイレクト
//
// 4. リソース管理
//    - 画像ファイルの適切な削除
//    - ファイルシステムのエラー処理
//
// 5. ユーザビリティ
//    - 処理結果の通知
//    - 直感的な画面遷移
// ===================================================================
?>