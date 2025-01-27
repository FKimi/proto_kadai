<?php
// DB接続関数
function connect_to_db() {
    // 開発環境（XAMPP）と本番環境（さくらサーバー）の切り替え
    $is_local = false;  // trueなら開発環境、falseなら本番環境 ／ GitHubにアップする際は false にする
    
    if ($is_local) {
        // ローカル環境（XAMPP）用の設定
        $dbn = 'mysql:dbname=balubo_db;charset=utf8mb4;port=3306;host=localhost';
        $user = 'root';
        $pwd = '';
    } else {
        // さくらサーバー用の設定

    }
 
    try {
        return new PDO($dbn, $user, $pwd, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    } catch (PDOException $e) {
        echo json_encode(["db error" => "{$e->getMessage()}"]);
        exit();
    }
}

// セッションチェック用の関数
function check_session_id() {
    if (!isset($_SESSION) || !isset($_SESSION["session_id"]) || $_SESSION["session_id"] !== session_id()) {
        header('Location: ../login/login.php');  // パスを正しく指定
        exit();
    }
}


// URL情報取得関数（変更なし）
function fetch_url_metadata($url) {
    try {
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => 'User-Agent: Mozilla/5.0',
                'timeout' => 10
            ]
        ]);

        $html = @file_get_contents($url, false, $context);
        if ($html === false) {
            return null;
        }

        $doc = new DOMDocument();
        @$doc->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        
        $meta_tags = array();
        
        // titleタグからタイトルを取得
        $titles = $doc->getElementsByTagName('title');
        $title = $titles->length > 0 ? $titles->item(0)->nodeValue : '';

        // metaタグから情報を取得
        foreach($doc->getElementsByTagName('meta') as $meta) {
            $property = $meta->getAttribute('property') ?: $meta->getAttribute('name');
            if($property) {
                $meta_tags[$property] = $meta->getAttribute('content');
            }
        }

        return array(
            'title' => $meta_tags['og:title'] ?? $title,
            'description' => $meta_tags['og:description'] ?? $meta_tags['description'] ?? '',
            'image' => $meta_tags['og:image'] ?? '',
            'url' => $url
        );

    } catch (Exception $e) {
        return null;
    }
}

// 画像アップロード処理関数（統合）
function handleImageUpload($file, $old_image = null) {
    // エラーログ追加
    error_log('File info: ' . print_r($file, true));

    $upload_dir = __DIR__ . '/uploads/';  // 絶対パスを使用
    error_log('Upload directory: ' . $upload_dir);
    
    if (!file_exists($upload_dir)) {
        if (!mkdir($upload_dir, 0777, true)) {
            error_log('Failed to create directory: ' . $upload_dir);
            throw new Exception('アップロードディレクトリの作成に失敗しました');
        }
    }
    
    if (!is_writable($upload_dir)) {
        error_log('Directory not writable: ' . $upload_dir);
        throw new Exception('アップロードディレクトリに書き込み権限がありません');
    }
    
    if (!empty($file['name'])) {
        // 許可する拡張子
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($file_ext, $allowed_types)) {
            throw new Exception('許可されていないファイル形式です');
        }
        
        $new_filename = uniqid() . '_' . bin2hex(random_bytes(8)) . '.' . $file_ext;
        $upload_path = $upload_dir . $new_filename;
        
        if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
            throw new Exception('ファイルのアップロードに失敗しました');
        }
    
        if ($old_image && file_exists($old_image)) {
            unlink($old_image);
        }
        
        return 'uploads/' . $new_filename;
    }
    
    return $old_image;
}

// 画像URL取得関数（変更なし）
function getImageUrl($path, $default = 'img/default-avatar.png') {
    if (empty($path)) {
        return $default;
    }
    
    // パスの正規化
    $normalized_path = ltrim($path, '/');
    
    // ファイルの存在確認
    if (file_exists($normalized_path)) {
        return $normalized_path;  // 既にmypageディレクトリからの相対パス
    }
    
    return $default;
}

// 画像パス取得関数（統合）
function getImagePath($path) {
    error_log('Original path: ' . $path);
    
    if (empty($path)) {
        return 'img/default-image.png';
    }
    
    $full_path = __DIR__ . '/' . $path;
    if (!file_exists($full_path)) {
        error_log('File not found: ' . $full_path);
    }
    
    return strpos($path, 'uploads/') === 0 ? $path : 'uploads/' . $path;
}
?>



