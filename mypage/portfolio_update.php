<?php
// mypage/portfolio_update.php
// ===================================================================
// 作品更新処理
// 目的：編集された作品情報をデータベースに更新
// ===================================================================
session_start();
include('../functions.php');
check_session_id();

// POSTリクエストの確認
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 必要なデータの取得
    $id = $_POST['id'];
    $title = $_POST['title'];
    $description = $_POST['description'] ?? '';
    $category = $_POST['category'] ?? '';
    $current_image = $_POST['current_image'] ?? '';
    $user_id = $_SESSION['user_id'];

    // 画像の処理
    $image_path = $current_image;  // デフォルトは現在の画像のまま
    if (!empty($_FILES['image']['name'])) {
        $upload_dir = '../uploads/';  // アップロードディレクトリ
        
        // 新しいファイル名を生成（日時 + オリジナルファイル名）
        $image_name = date('YmdHis') . '_' . $_FILES['image']['name'];
        $new_image_path = $upload_dir . $image_name;
        
        // 画像のアップロード
        if (move_uploaded_file($_FILES['image']['tmp_name'], $new_image_path)) {
            // 古い画像の削除（存在する場合）
            if ($current_image && file_exists('../' . $current_image)) {
                unlink('../' . $current_image);
            }
            // データベース保存用のパスを設定
            $image_path = 'uploads/' . $image_name;
        } else {
            $_SESSION['error_message'] = '画像のアップロードに失敗しました';
            header('Location: portfolio_edit.php?id=' . $id);
            exit();
        }
    }

    // データベースに接続
    $pdo = connect_to_db();

    // 作品の所有者確認（セキュリティ対策）
    $check_sql = 'SELECT user_id FROM portfolio WHERE id = :id';
    $check_stmt = $pdo->prepare($check_sql);
    $check_stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $check_stmt->execute();
    $work = $check_stmt->fetch(PDO::FETCH_ASSOC);

    if (!$work || $work['user_id'] !== $user_id) {
        $_SESSION['error_message'] = '更新権限がありません';
        header('Location: mypage.php');
        exit();
    }

    // 更新用SQL作成
    $sql = 'UPDATE portfolio SET 
            title = :title,
            description = :description,
            image_path = :image_path,
            category = :category,
            updated_at = NOW()
            WHERE id = :id AND user_id = :user_id';

    $stmt = $pdo->prepare($sql);

    // パラメータのバインド
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->bindValue(':title', $title, PDO::PARAM_STR);
    $stmt->bindValue(':description', $description, PDO::PARAM_STR);
    $stmt->bindValue(':image_path', $image_path, PDO::PARAM_STR);
    $stmt->bindValue(':category', $category, PDO::PARAM_STR);
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);

    try {
        $status = $stmt->execute();
        if ($status) {
            $_SESSION['success_message'] = '作品を更新しました！';
            header('Location: mypage.php');
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error_message'] = '更新に失敗しました：' . $e->getMessage();
        header('Location: portfolio_edit.php?id=' . $id);
        exit();
    }
}

// POST以外のリクエストは編集ページにリダイレクト
header('Location: mypage.php');
exit();

// ===================================================================
// 実装のポイント
// 1. セキュリティ対策
//    - セッションチェック
//    - 作品所有者の確認
//    - SQLインジェクション対策
//
// 2. 画像処理
//    - 新規アップロード時の一意なファイル名生成
//    - 古い画像の削除
//    - アップロードエラーのハンドリング
//
// 3. エラーハンドリング
//    - データベースエラーの処理
//    - 権限エラーの処理
//    - ユーザーフレンドリーなエラーメッセージ
//
// 4. ユーザー体験
//    - 処理結果のフィードバック
//    - 適切なリダイレクト
// ===================================================================
?>