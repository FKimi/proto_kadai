<?php
// ===================================================================
// profile_update.php - プロフィール更新処理
// 目的：プロフィール編集フォームからの送信データを処理し、DBを更新
// 作成日：2024-01-18
// ===================================================================

session_start();
include('../functions.php');
check_session_id();  // セッションチェック（セキュリティ対策）

// デバッグ表示（開発時のみ）
// echo '<pre>';
// print_r($_POST);
// echo '</pre>';

// POSTデータのバリデーション
// username（表示名）は必須、他は任意項目
if (!isset($_POST['username']) || $_POST['username'] === '') {
    $_SESSION['update_error'] = '表示名は必須項目です。';
    header('Location: profile_edit.php');
    exit();
}

// POSTデータの受け取り
$username = $_POST['username'];
$occupation = $_POST['occupation'] ?? '';  // 任意項目は空文字をデフォルト値に
$bio = $_POST['bio'] ?? '';
$skills = $_POST['skills'] ?? '';

// DB接続
$pdo = connect_to_db();

// SQLの準備（更新日時も自動的に更新）
$sql = 'UPDATE users_table 
        SET username = :username,
            occupation = :occupation,
            bio = :bio,
            skills = :skills,
            updated_at = NOW()
        WHERE id = :id';

// SQL実行の準備
$stmt = $pdo->prepare($sql);
// バインド変数の設定
$stmt->bindValue(':username', $username, PDO::PARAM_STR);
$stmt->bindValue(':occupation', $occupation, PDO::PARAM_STR);
$stmt->bindValue(':bio', $bio, PDO::PARAM_STR);
$stmt->bindValue(':skills', $skills, PDO::PARAM_STR);
$stmt->bindValue(':id', $_SESSION['user_id'], PDO::PARAM_INT);

try {
    // SQLの実行
    $status = $stmt->execute();

    // 成功時：セッションにメッセージを保存してマイページへリダイレクト
    $_SESSION['update_success'] = 'プロフィールを更新しました！';
    header('Location: mypage.php');
    exit();

} catch (PDOException $e) {
    // エラー時：エラーメッセージをセッションに保存して編集ページへ戻る
    $_SESSION['update_error'] = 'データベースエラーが発生しました。';
    header('Location: profile_edit.php');
    exit();
}
// ===================================================================
// 【実装のポイント】
//
// 1. 基本的なセキュリティ対策
//    - セッションチェック
//    - SQLインジェクション対策（プリペアドステートメント）
//    - 入力値のバリデーション
//
// 2. エラーハンドリング
//    - try-catchによるDB処理のエラー捕捉
//    - ユーザーフレンドリーなエラーメッセージ
//    - 適切なリダイレクト処理
//
// 3. ユーザー体験の向上
//    - 処理結果のフィードバック（成功/エラーメッセージ）
//    - 直感的な画面遷移
//
// 4. データの整合性
//    - 更新日時の自動記録
//    - NULL値の適切な処理
//
// 【改善予定】
// 1. より詳細なバリデーション
//    - 文字数制限
//    - 禁止文字のチェック
//    - スキルの形式チェック
//
// 2. トランザクション処理の追加
//    - データの整合性担保
//    - 複数テーブルの更新対応
//
// 3. ログ機能の実装
//    - 更新履歴の記録
//    - エラーログの保存
//
// 4. プロフィール画像の処理
//    - 画像アップロード機能
//    - 画像のリサイズ処理
// ===================================================================
?>