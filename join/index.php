<?php
session_start();
require('../library.php');

$error = [];

//書き直すボタンから遷移した際にsession等をセットする
if (isset($_GET['action']) && $_GET['action'] === 'rewrite' && isset($_SESSION['form'])) {
    $form = $_SESSION['form'];

    //画像アップロードされていた場合は、再アップロードを促す文言の表示と対象画像をフォルダから削除する
    if ($form['image']) {
        $guid = 'true';
        $up_file = '../member_picture/' . ($form['image']);
        if (file_exists($up_file) && $form['image'] !== $first_image) {
            unlink($up_file);
        }
    }
} else {
    $form = [
        'name' => '',
        'email' => '',
        'password' => ''
    ];
}

// フォームの内容をチェック(POST送信されている場合)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form['name'] = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS);
    if ($form['name'] === '') {
        $error['name'] = 'blank';
    } else {
        //nameの重複がないか
        $db = dbconnect();
        $stmt = $db->prepare('select count(*) from members where name=:name');
        if (!$stmt) {
            die($db->errorInfo());
        }

        // $stmt->bind_param('s', $form['name']);
        $stmt->bindValue(':name', $form['name'], PDO::PARAM_STR);

        $success = $stmt->execute();
        if (!$success) {
            die($db->errorInfo());
        }

        //一致したユーザ名の件数を取得
        // $stmt->bind_result($cnt);
        // $stmt->fetch();
        $cnt = $stmt->fetchColumn();

        if ($cnt > 0) {
            $error['name'] = 'duplicate';
        }
    }

    $form['email'] = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_SPECIAL_CHARS);
    if ($form['email'] === '') {
        $error['email'] = 'blank';
    } else {
        //emailの重複がないか
        $db = dbconnect();
        $stmt = $db->prepare('select count(*) from members where email=:email');
        if (!$stmt) {
            die($db->errorInfo());
        }

        // $stmt->bind_param('s', $form['email']);
        $stmt->bindValue(':email', $form['email'], PDO::PARAM_STR);

        $success = $stmt->execute();
        if (!$success) {
            die($db->errorInfo());
        }

        //一致したメール件数を取得
        // $stmt->bind_result($cnt);
        // $stmt->fetch();
        $cnt = $stmt->fetchColumn();

        if ($cnt > 0) {
            $error['email'] = 'duplicate';
        }
    }

    $form['password'] = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_SPECIAL_CHARS);
    if ($form['password'] === '') {
        $error['password'] = 'blank';
    } else if (strlen($form['password']) < 4) {
        $error['password'] = 'length';
    }

    // 画像のチェック
    $image = $_FILES['image'];
    if ($image['name'] !== '' && $image['error'] === 0) {
        $type = mime_content_type($image['tmp_name']);
        if ($type !== 'image/png' && $type !== 'image/jpeg') {
            $error['image'] = 'type';
        }
    }

    //エラーが未発生時の処理
    if (empty($error)) {
        $_SESSION['form'] = $form;

        // 画像のアップロード
        if ($image['name'] !== '') {
            $filename = date('YmdHis') . '_' . $image['name'];
            if (!move_uploaded_file($image['tmp_name'], '../member_picture/' . $filename)) {
                die('ファイルのアップロードに失敗しました。');
            }
            $_SESSION['form']['image'] = $filename;
        } else {
            //設定なしの場合は初期画像を設定
            $_SESSION['form']['image'] = $first_image;
        }

        header('Location: check.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>会員登録</title>

    <link rel="stylesheet" href="../css/style.css" />
</head>

<body>
    <div class="wrapper">
        <div id="head">
            <h1>会員登録</h1>
        </div>
        <div id="wrap">
            <div class="content join">
                <p>次のフォームに必要事項をご記入ください。</p>
                <form action="" method="post" enctype="multipart/form-data">
                    <dl>
                        <dt>ユーザ名<span class="required">必須</span></dt>
                        <dd>
                            <input type="text" name="name" size="35" maxlength="255" required value="<?php echo h($form['name']); ?>" />
                            <?php if (isset($error['name']) && $error['name'] === 'blank') : ?>
                                <p class="error">* ニックネームを入力してください</p>
                            <?php endif; ?>
                            <?php if (isset($error['name']) && $error['name'] === 'duplicate') : ?>
                                <p class="error">* 指定されたユーザ名はすでに登録されています</p>
                            <?php endif; ?>
                        </dd>
                        <dt>メールアドレス<span class="required">必須</span></dt>
                        <dd>
                            <input type="text" name="email" size="35" maxlength="255" required value="<?php echo h($form['email']); ?>" />
                            <?php if (isset($error['email']) && $error['email'] === 'blank') : ?>
                                <p class="error">* メールアドレスを入力してください</p>
                            <?php endif; ?>
                            <?php if (isset($error['email']) && $error['email'] === 'duplicate') : ?>
                                <p class="error">* 指定されたメールアドレスはすでに登録されています</p>
                            <?php endif; ?>
                        <dt>パスワード<span class="required">必須</span></dt>
                        <dd>
                            <input type="password" name="password" size="35" placeholder="半角英数字4~10文字" maxlength="10" pattern="[a-zA-Z0-9]+" required value="<?php echo h($form['password']); ?>" />
                            <?php if (isset($error['password']) && $error['password'] === 'blank') : ?>
                                <p class="error">* パスワードを入力してください</p>
                            <?php endif; ?>
                            <?php if (isset($error['password']) && $error['password'] === 'length') : ?>
                                <p class="error">* パスワードは4文字以上で入力してください</p>
                            <?php endif; ?>
                        </dd>
                        <dt>写真など</dt>
                        <dd>
                            <input type="file" name="image" size="35" value="" />
                            <?php if (isset($error['image']) && $error['image'] === 'type') : ?>
                                <p class="error">* 写真などは「.png」または「.jpg」の画像を指定してください</p>
                            <?php endif; ?>
                            <?php if (isset($guid) && empty($error['image'])) : ?>
                                <p class="error">* 恐れ入りますが、画像を改めて指定してください</p>
                            <?php endif; ?>
                        </dd>
                    </dl>
                    <div><input type="submit" value="入力内容を確認する" /></div>
                    <p>&laquo;<a href="../login.php">ログイン画面に戻る</a></p>
                </form>
            </div>
        </div>
    </div>
</body>

</html>