<?php
// ===================================================================
// profile_edit.php - プロフィール編集ページ
// 目的：ユーザープロフィール情報の編集機能を提供
// 作成日：2024-01-18
// ===================================================================
session_start();
include('../functions.php');
check_session_id();  // 未ログインユーザーのアクセスを防止

// ユーザー情報の取得
$pdo = connect_to_db();
$sql = 'SELECT * FROM users_table WHERE id = :id AND deleted_at IS NULL';
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':id', $_SESSION['user_id'], PDO::PARAM_INT);

try {
    $status = $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // デバッグ表示（テスト後は削除）
        // echo '<pre>';
        // print_r($user);
        // echo '</pre>';

} catch (PDOException $e) {
    echo json_encode(["error" => "{$e->getMessage()}"]);
    exit();
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>プロフィール編集 - balubo</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <!-- ヘッダー部分 -->
    <header class="bg-white shadow">
        <nav class="container mx-auto px-6 py-4">
            <div class="flex justify-between items-center">
                <div class="text-xl font-bold">balubo</div>
            </div>
        </nav>
    </header>

    <main class="container mx-auto px-6 py-8">
        <h1 class="text-2xl font-bold mb-8">プロフィール編集</h1>
        
        <!-- プロフィール編集フォーム -->
        <!-- multipart/form-data は将来的な画像アップロード機能のために準備 -->
        <form action="profile_update.php" method="POST" class="max-w-2xl mx-auto">
            <div class="bg-white shadow rounded-lg p-6 mb-6">
        <!-- 基本情報 -->
        <h2 class="text-xl font-bold mb-4">基本情報</h2>
        <!-- 表示名入力欄 -->       
        <div class="mb-4">
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-1">表示名</label>
                    <input type="text" 
                           name="username" 
                           id="username" 
                           value="<?= htmlspecialchars($user['username'] ?? '') ?>" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md"
                           required>
                </div>
                <!-- 職業入力欄 -->
                <div class="mb-4">
                    <label for="occupation" class="block text-sm font-medium text-gray-700 mb-1">職業</label>
                    <input type="text" 
                           name="occupation" 
                           id="occupation" 
                           value="<?= htmlspecialchars($user['occupation'] ?? '') ?>" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                <!-- 自己紹介入力欄 -->
                <div class="mb-4">
                    <label for="bio" class="block text-sm font-medium text-gray-700 mb-1">自己紹介</label>
                    <textarea name="bio" 
                              id="bio" 
                              rows="4" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-md"><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                </div>
                <!-- スキル入力欄 -->
                <div class="mb-4">
                    <label for="skills" class="block text-sm font-medium text-gray-700 mb-1">
                        スキル（カンマ区切りで入力）
                    </label>
                    <input type="text" 
                           name="skills" 
                           id="skills" 
                           value="<?= htmlspecialchars($user['skills'] ?? '') ?>" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md"
                           placeholder="例：ライティング, 編集, 企画, デザイン">
                </div>
                <!-- プロフィール画像アップロード（将来的な実装のために準備） -->
                <!-- <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">プロフィール画像</label>
                    <input type="file" name="profile_image" accept="image/*">
                </div> -->
                </div>
            <!-- フォームボタン -->
            <div class="flex justify-between">
                <!-- キャンセルボタン -->
                <a href="mypage.php" 
                   class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600">
                    キャンセル
                </a>
                <!-- 保存ボタン -->
                <button type="submit" 
                        class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                    保存する
                </button>
            </div>
        </form>
    </main>

    <!-- バリデーション用のJavaScript -->
    <script>
        // フォーム送信時のバリデーション
        document.querySelector('form').addEventListener('submit', function(e) {
            const username = document.getElementById('username').value.trim();
            
            if (username === '') {
                e.preventDefault();
                alert('表示名は必須項目です');
                return false;
            }
        });
    </script>
</body>
</html>

<?php
// ===================================================================
// 【実装上の注意点】
// 1. セキュリティ対策
//    - XSS対策：全ての出力にhtmlspecialchars()を使用
//    - CSRF対策：トークンの実装を検討
//    - 入力値のバリデーション
//
// 2. ユーザビリティ
//    - 必須項目の明示
//    - フォームのバリデーション（クライアント/サーバー両方）
//    - 直感的なUI/UX
//
// 3. エラーハンドリング
//    - データベースエラーの適切な処理
//    - ユーザーフレンドリーなエラーメッセージ
//
// 【今後の実装予定】
// 1. プロフィール画像のアップロード機能
// 2. リアルタイムバリデーション
// 3. 自動保存機能
// 4. 変更履歴の管理
// ===================================================================
?>