<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
include('../functions.php');
check_session_id();

// デバッグログ
error_log('Request Method: ' . $_SERVER['REQUEST_METHOD']);
error_log('POST data: ' . print_r($_POST, true));
error_log('FILES data: ' . print_r($_FILES, true));

// アップロードディレクトリのチェックと作成
$upload_dir = __DIR__ . '/uploads/';
if (!file_exists($upload_dir)) {
   error_log('Creating upload directory: ' . $upload_dir);
   if (!mkdir($upload_dir, 0777, true)) {
       error_log('Failed to create upload directory');
       $_SESSION['error_message'] = 'アップロードディレクトリの作成に失敗しました';
       header('Location: portfolio_input.php');
       exit();
   }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
   try {
       // 必須項目のチェック
       if (empty($_POST['title'])) {
           throw new Exception('タイトルは必須です');
       }

       $title = $_POST['title'];
       $description = $_POST['description'] ?? '';
       $url = $_POST['url'] ?? '';
       $category = $_POST['category'] ?? '';
       $user_id = $_SESSION['user_id'];
       
       // 画像パスの初期化
       $image_path = '';
       
       // ファイルアップロードの処理
       if (!empty($_FILES['image']['name'])) {
           error_log('Processing file upload...');
           try {
               $image_path = handleImageUpload($_FILES['image']);
               error_log('File upload successful: ' . $image_path);
           } catch (Exception $e) {
               error_log('File upload failed: ' . $e->getMessage());
               throw new Exception('画像のアップロードに失敗しました: ' . $e->getMessage());
           }
       } 
       // URLからの画像取得
       elseif (!empty($url)) {
           error_log('Fetching image from URL...');
           $metadata = fetch_url_metadata($url);
           if (!empty($metadata['image'])) {
               try {
                   $image_content = file_get_contents($metadata['image']);
                   if ($image_content !== false) {
                       $ext = pathinfo(parse_url($metadata['image'], PHP_URL_PATH), PATHINFO_EXTENSION);
                       $ext = $ext ?: 'jpg';
                       
                       $filename = uniqid() . '_feed.' . $ext;
                       $save_path = $upload_dir . $filename;
                       
                       if (file_put_contents($save_path, $image_content)) {
                           $image_path = 'uploads/' . $filename;
                           error_log('URL image saved: ' . $image_path);
                       } else {
                           error_log('Failed to save URL image');
                       }
                   }
               } catch (Exception $e) {
                   error_log('URL image download failed: ' . $e->getMessage());
               }
           }
       }

       // データベース接続
       error_log('Connecting to database...');
       $pdo = connect_to_db();

       // データベースに保存
       $sql = 'INSERT INTO portfolio (title, description, image_path, url, category, user_id, created_at) 
               VALUES (:title, :description, :image_path, :url, :category, :user_id, NOW())';
       
       $stmt = $pdo->prepare($sql);
       $stmt->bindValue(':title', $title, PDO::PARAM_STR);
       $stmt->bindValue(':description', $description, PDO::PARAM_STR);
       $stmt->bindValue(':image_path', $image_path, PDO::PARAM_STR);
       $stmt->bindValue(':url', $url, PDO::PARAM_STR);
       $stmt->bindValue(':category', $category, PDO::PARAM_STR);
       $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);

       error_log('Executing database insert...');
       if ($stmt->execute()) {
           error_log('Database insert successful');
           $_SESSION['success_message'] = '作品を登録しました！';
           header('Location: mypage.php');
           exit();
       } else {
           error_log('Database insert failed');
           throw new Exception('データベースへの保存に失敗しました');
       }

   } catch (Exception $e) {
       error_log('Error occurred: ' . $e->getMessage());
       $_SESSION['error_message'] = $e->getMessage();
       header('Location: portfolio_input.php');
       exit();
   }
}

// POST以外のリクエストの場合
header('Location: portfolio_input.php');
exit();
?>