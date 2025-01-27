<?php
// mypage/portfolio_input.php
session_start();
include('../functions.php');
check_session_id();
?>

<?php if (isset($_SESSION['error_message'])): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
        <?= htmlspecialchars($_SESSION['error_message']) ?>
        <?php unset($_SESSION['error_message']); ?>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['success_message'])): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4" role="alert">
        <?= htmlspecialchars($_SESSION['success_message']) ?>
        <?php unset($_SESSION['success_message']); ?>
    </div>
<?php endif; ?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>作品登録 - balubo</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<!-- URL取得用スクリプト -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const urlInput = document.getElementById('url');
    if (!urlInput) return;

    urlInput.addEventListener('blur', async function() {
        const url = this.value;
        if (!url) return;

        // URL形式チェック
        try {
            new URL(url);
        } catch (e) {
            console.error('Invalid URL format');
            return;
        }

        // ローディング表示
        const preview = document.getElementById('image-preview');
        if (preview) {
            preview.innerHTML = '<div class="text-center py-4">データを取得中...</div>';
        }

        try {
            const response = await fetch('fetch_metadata.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ url: url })
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            // タイトルの自動入力
            if (data.title && !document.getElementById('title').value) {
                document.getElementById('title').value = data.title;
            }

            // 説明の自動入力
            if (data.description && !document.getElementById('description').value) {
                document.getElementById('description').value = data.description;
            }

            // 画像プレビューの表示
            if (data.image) {
                preview.innerHTML = `
                    <div class="relative">
                        <img src="${data.image}" 
                             alt="Preview" 
                             class="w-full h-48 object-cover rounded"
                             onerror="this.parentElement.style.display='none'">
                        <p class="text-sm text-gray-500 mt-1">※URLから自動取得した画像</p>
                    </div>`;
            }
        } catch (error) {
            console.error('Error fetching metadata:', error);
            preview.innerHTML = '<div class="text-red-500 text-center py-4">URLからの情報取得に失敗しました</div>';
        }
    });
});
</script>
<body class="bg-gray-50">
    <header class="bg-white shadow">
        <nav class="container mx-auto px-6 py-4">
            <div class="flex justify-between items-center">
                <div class="text-xl font-bold">balubo</div>
            </div>
        </nav>
    </header>
    <main class="container mx-auto px-6 py-8">
        <h1 class="text-2xl font-bold mb-8">作品を登録</h1>
        <form action="portfolio_create.php" method="POST" enctype="multipart/form-data" class="max-w-2xl mx-auto">
            <div class="bg-white shadow rounded-lg p-6 mb-6">
                <!-- 作品タイトル入力欄 -->
                <div class="mb-4">
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-1">作品タイトル</label>
                    <input type="text" name="title" id="title" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                <!-- 作品説明入力欄 -->
                <div class="mb-4">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">作品の説明</label>
                    <textarea name="description" id="description" rows="4"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md"></textarea>
                </div>
                <!-- portfolio_input.phpのフォーム内に追加 -->
                 <div class="mb-4">
                   <label for="url" class="block text-sm font-medium text-gray-700 mb-1">URL（任意）</label>
                   <!-- プレビュー表示領域 -->
                   <div id="image-preview" class="mb-4"></div>
                   <input type="url" 
                   name="url" 
                   id="url" 
                   placeholder="https://example.com"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md">
                   <p class="text-sm text-gray-500 mt-1">作品のURL（Webサイト、GitHub等）があれば入力してください</p>
                </div>
                <!-- 画像アップロード欄 -->
                <div class="mb-4">
                    <label for="image" class="block text-sm font-medium text-gray-700 mb-1">画像</label>
                    <input type="file" name="image" id="image" accept="image/*"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                <!-- カテゴリー入力欄 -->
                <div class="mb-4">
                    <label for="category" class="block text-sm font-medium text-gray-700 mb-1">カテゴリー</label>
                    <input type="text" name="category" id="category"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
            </div>

            <!-- ボタン類 -->
            <div class="flex justify-between">
                <a href="mypage.php" class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600">
                    キャンセル
                </a>
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                    登録する
                </button>
            </div>
        </form>
    </main>
</body>
</html>