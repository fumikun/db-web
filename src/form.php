<?php

/**
 * データ入力フォームの表示、POSTデータのCSV保存、CSV閲覧リンクの表示を
 * 1ファイルで行うPHPスクリプト
 */

// --- ▼ 設定項目 ▼ ---

// 1. 保存先のCSVファイル名
$csvFile = getenv('PHP_WRITE_DIR') . '/data.csv';

// 2. CSVに保存したいカラム (フォームの name 属性) を指定
//    ※このリストの順番通りにCSVに保存されます
$formColumns = [
    'name',
    'email',
    'message',
];

// 3. CSVに自動で追加したいカラム (例: タイムスタンプ)
//    ※ $formColumns の「後」に追加されます
$autoColumns = [
    'timestamp'
];

// --- ▲ 設定項目 ▲ ---


// --- ▼ スクリプト本体 ▼ ---

// CSVのヘッダー行 (フォーム項目 + 自動追加項目)
$csvHeader = array_merge($formColumns, $autoColumns);

// 処理結果を格納する変数
$message = '';
$messageType = ''; // 'success' または 'error'

// 1. POSTリクエストの場合 (フォームが送信された場合) の処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {
        // 2. ファイルが存在しない、または空の場合にヘッダーを書き込む
        $writeHeaders = !file_exists($csvFile) || filesize($csvFile) === 0;

        // 3. ファイルを追記モード ('a') で開く
        $fileHandle = @fopen($csvFile, 'a');
        if ($fileHandle === false) {
            throw new Exception('ファイルを開けませんでした。サーバーの書き込み権限（パーミッション）を確認してください。');
        }

        // 4. ヘッダーの書き込み (必要な場合のみ)
        if ($writeHeaders) {
            fputcsv($fileHandle, $csvHeader);
        }

        // 5. POSTデータから $formColumns で指定した順序でデータを準備
        $dataRow = [];
        foreach ($formColumns as $column) {
            $value = $_POST[$column] ?? ''; // POSTになければ空文字
            $value = preg_replace("/\r\n|\r|\n/", ' ', $value); // 改行コードをスペースに置換
            $dataRow[] = $value;
        }

        // 6. 自動追加カラムのデータを準備
        //    (今回はタイムスタンプのみ)
        if (in_array('timestamp', $autoColumns)) {
            $dataRow[] = date('Y-m-d H:i:s'); // 現在日時
        }

        // 7. データの書き込み
        fputcsv($fileHandle, $dataRow);

        // 8. ファイルを閉じる
        fclose($fileHandle);

        $message = 'データを正常に保存しました。';
        $messageType = 'success';
    } catch (Exception $e) {
        $message = 'エラーが発生しました: ' . $e->getMessage();
        $messageType = 'error';
    }
}

// 9. ファイルが存在するかどうかをチェック (リンク表示用)
$csvExists = file_exists($csvFile);

?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>データ登録フォーム</title>
    <style>
        body {
            font-family: sans-serif;
            margin: 20px;
        }

        form {
            border: 1px solid #ccc;
            padding: 20px;
            border-radius: 8px;
            max-width: 500px;
        }

        div {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        input[type="text"],
        input[type="email"],
        textarea {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        button {
            padding: 10px 15px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }

        /* 処理結果メッセージ */
        .message {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            max-width: 500px;
        }

        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* 閲覧セクション */
        .csv-link-section {
            margin-top: 30px;
            padding: 20px;
            background-color: #f4f4f4;
            border-radius: 8px;
            max-width: 500px;
        }
    </style>
</head>

<body>

    <h1>データ登録</h1>

    <?php if ($message): ?>
        <div class="message <?php echo htmlspecialchars($messageType, ENT_QUOTES, 'UTF-8'); ?>">
            <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>


    <h2>入力フォーム</h2>
    <form method="POST" action="">
        <div>
            <label for="name">お名前:</label>
            <input type="text" id="name" name="name" required>
        </div>
        <div>
            <label for="email">メールアドレス:</label>
            <input type="text" id="email" name="email" required>
        </div>
        <div>
            <label for="message">メッセージ:</label>
            <textarea id="message" name="message" rows="4"></textarea>
        </div>
        <div>
            <button type="submit">送信</button>
        </div>
    </form>


    <div class="csv-link-section">
        <h2>保存済みデータ</h2>
        <?php if ($csvExists): ?>
            <p>保存されたデータをCSVファイルで確認できます。</p>
            <a href="/data/data.csv" target="_blank">
                CSVファイルをダウンロード
            </a>
        <?php else: ?>
            <p>現在、保存されているデータはありません。</p>
        <?php endif; ?>
    </div>

</body>

</html>