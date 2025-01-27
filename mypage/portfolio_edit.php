<?php
// mypage/portfolio_edit.php
// ===================================================================
// 作品編集ページ
// 目的：既存の作品情報を編集するためのフォームを提供
// ===================================================================

session_start();
include('../functions.php');
check_session_id();

// idの受け取り
$id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// データベースに接続
$pdo = connect_to_db();

// idとuser_idを指定して作品データを取得（自分の作品のみ編集可能）
$sql = 'SELECT * FROM portfolio WHERE id = :id AND user_id = :user_id';
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
$stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);

try {
    $status = $stmt->execute();
    $work = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$work) {
        $_SESSION['error_message'] = '作品が見つからないか、編集権限がありません';
        header('Location: mypage.php');
        exit();
    }
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
    <title>作品編集 - balubo</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <header class="bg-white shadow">
        <nav class="container mx-auto px-6 py-4">
            <div class="flex justify-between items-center">
                <div class="text-xl font-bold">balubo</div>
            </div>
        </nav>
    </header>

    <main class="container mx-auto px-6 py-8">
        <h1 class="text-2xl font-bold mb-8">作品編集</h1>
        
        <form action="portfolio_update.php" method="POST" enctype="multipart/form-data" class="max-w-2xl mx-auto">
            <!-- 作品IDの非表示フィールド -->
            <input type="hidden" name="id" value="<?= $work['id'] ?>">
            
            <div class="bg-white shadow rounded-lg p-6 mb-6">
                <!-- 作品タイトル編集欄 -->
                <div class="mb-4">
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-1">作品タイトル</label>
                    <input type="text" 
                           name="title" 
                           id="title" 
                           value="<?= htmlspecialchars($work['title']) ?>" 
                           required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>

                <!-- 作品説明編集欄 -->
                <div class="mb-4">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">作品の説明</label>
                    <textarea name="description" 
                              id="description" 
                              rows="4"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md"><?= htmlspecialchars($work['description']) ?></textarea>
                </div>

                <!-- 画像関連の編集欄 -->
                <div class="mb-4">
                    <?php if ($work['image_path']): ?>
                        <div class="mb-4">
                            <p class="block text-sm font-medium text-gray-700 mb-1">現在の画像</p>
                            <img src="<?= htmlspecialchars('../' . $work['image_path']) ?>" 
                                 alt="現在の画像" 
                                 class="w-48 h-48 object-cover rounded">
                            <input type="hidden" 
                                   name="current_image" 
                                   value="<?= htmlspecialchars($work['image_path']) ?>">
                        </div>
                    <?php endif; ?>
                    
                    <label for="image" class="block text-sm font-medium text-gray-700 mb-1">
                        新しい画像（選択すると更新されます）
                    </label>
                    <input type="file" 
                           name="image" 
                           id="image"
                           accept="image/*"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>

                <!-- カテゴリー編集欄 -->
                <div class="mb-4">
                    <label for="category" class="block text-sm font-medium text-gray-700 mb-1">カテゴリー</label>
                    <input type="text" 
                           name="category" 
                           id="category" 
                           value="<?= htmlspecialchars($work['category']) ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
            </div>

            <!-- ボタン類 -->
            <div class="flex justify-between">
                <a href="mypage.php" 
                   class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600">
                    キャンセル
                </a>
                <button type="submit" 
                        class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                    更新する
                </button>
            </div>
        </form>
    </main>
</body>
</html>