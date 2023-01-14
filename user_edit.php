<?php
session_start();
require('library.php');

//session情報から値を挿入する
if (isset($_SESSION['id']) && isset($_SESSION['name'])) {
  $id = $_SESSION['id'];
  $name = $_SESSION['name'];
} else {
  header('Location: login.php');
  exit();
}

$error = [];
if (isset($_GET['action']) && $_GET['action'] === 'rewrite' && isset($_SESSION['user_form'])) {
  $form = $_SESSION['user_form'];

  //画像アップロードされていた場合は、再アップロードを促す文言の表示と対象画像をフォルダから削除する
  if ($form['image']) {
    $guid = 'true';
    $up_file = 'member_picture/' . ($form['image']);
    if (file_exists($up_file) && $form['image'] !== $first_image && $form['image'] !== $_SESSION['image']) {
      unlink($up_file);
    }
  }
}

$db = dbconnect();

// フォームの内容をチェック(POST送信されている場合)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $form['name'] = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS);
  if ($form['name'] === '') {
    $error['name'] = 'blank';
  } else {
    //nameの重複がないか
    $stmt = $db->prepare('select count(*) from members where name=:name');
    if (!$stmt) {
      die($db->errorInfo());
    }
    $stmt->bindValue(':name', $form['name'], PDO::PARAM_STR);
    $success = $stmt->execute();
    if (!$success) {
      die($db->errorInfo());
    }

    //一致したユーザ名の件数を取得
    $cnt = $stmt->fetchColumn();

    $stmt = $db->prepare('select name from members where name=:name');
    if (!$stmt) {
      die($db->errorInfo());
    }
    $stmt->bindValue(':name', $name, PDO::PARAM_STR);
    $success = $stmt->execute();
    if (!$success) {
      die($db->errorInfo());
    }

    $user_name = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($cnt > 0 && $form['name'] !== $user_name['name']) {
      $error['name'] = 'duplicate';
    }
  }


  $form['email'] = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_SPECIAL_CHARS);
  if ($form['email'] === '') {
    $error['email'] = 'blank';
  } else {
    //emailの重複がないか
    $stmt = $db->prepare('select count(*) from members where email=:email');
    if (!$stmt) {
      die($db->errorInfo());
    }
    $stmt->bindValue(':email', $form['email'], PDO::PARAM_STR);
    $success = $stmt->execute();
    if (!$success) {
      die($db->errorInfo());
    }
    //一致したメール件数を取得
    $cnt = $stmt->fetchColumn();

    $stmt = $db->prepare('select email from members where name=:name');
    if (!$stmt) {
      die($db->errorInfo());
    }
    $stmt->bindValue(':name', $name, PDO::PARAM_STR);
    $success = $stmt->execute();
    if (!$success) {
      die($db->errorInfo());
    }

    $user_email = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($cnt > 0 && $user_email['email'] != $form['email']) {
      $error['email'] = 'duplicate';
    }
  }

  // 画像のチェック
  $image = $_FILES['image'];
  if ($image['name'] !== '' && $image['error'] === 0) {
    $type = mime_content_type($image['tmp_name']);
    if ($type !== 'image/png' && $type !== 'image/jpeg') {
      $error['image'] = 'type';
    }
  }

  $form['age'] = filter_input(INPUT_POST, 'age', FILTER_SANITIZE_NUMBER_INT);
  $form['hobby'] = filter_input(INPUT_POST, 'hobby', FILTER_SANITIZE_SPECIAL_CHARS);
  $form['comment'] = filter_input(INPUT_POST, 'comment', FILTER_SANITIZE_SPECIAL_CHARS);


  //エラーが未発生時の処理
  if (empty($error)) {
    $_SESSION['user_form'] = $form;

    // 画像のアップロード
    if ($image['name'] !== '') {
      $filename = date('YmdHis') . '_' . $image['name'];
      if (!move_uploaded_file($image['tmp_name'], 'member_picture/' . $filename)) {
        die('ファイルのアップロードに失敗しました。');
      }
      $_SESSION['user_form']['image'] = $filename;
    } else {
      //設定なしの場合は元の画像を設定
      $_SESSION['user_form']['image'] = $_SESSION['image'];
    }

    header('Location: user_check.php');
    exit();
  }
} else {
  if (!isset($_SESSION['user_form'])) {
    //最初に遷移してきた際に対象ユーザのデータを取得
    $stmt = $db->prepare('select m.email, m.picture, mi.member_id, mi.age, mi.hobby, mi.comment from members m,member_info mi where m.id = :member_id and mi.member_id = :member_id');
    if (!$stmt) {
      die($db->errorInfo());
    };
    $stmt->bindValue(':member_id', $id, PDO::PARAM_INT);
    $success = $stmt->execute();
    if (!$success) {
      die($db->errorInfo());
    }
    $all = $stmt->fetch(PDO::FETCH_ASSOC);

    //初期情報を格納
    $form = [
      'name' => $name,
      'email' => $all['email'],
      'image' => $all['picture'],
      'age' => $all['age'],
      'hobby' => $all['hobby'],
      'comment' => $all['comment']
    ];

    $_SESSION['image'] = $form['image'];

    //年齢が0なら空文字にする
    if ($form['age'] == 0) {
      $form['age'] = '';
    }
  }
}
?>


<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>ユーザ情報</title>

  <link rel="stylesheet" href="css/style.css" />
</head>

<body>
  <div class="wrapper">
    <div id="head">
      <h1>ユーザ情報</h1>
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
            <dt>年齢</dt>
            <dd>
              <input type="text" name="age" size="35" maxlength="255" value="<?php echo h($form['age']); ?>" />
            </dd>
            <dt>趣味</dt>
            <dd>
              <input type="text" name="hobby" size="35" maxlength="255" value="<?php echo h($form['hobby']); ?>" />
            </dd>
            <dt>一言</dt>
            <dd>
              <input type="text" name="comment" size="35" maxlength="255" value="<?php echo h($form['comment']); ?>" />
            </dd>
          </dl>
          <div><input type="submit" value="入力内容を確認する" /></div>
          <p>&laquo;<a href="index.php" onclick=<?php unset($_SESSION['user_form']); ?>>掲示板に戻る</a></p>
        </form>
      </div>
    </div>
  </div>
</body>

</html>