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

//前画面のsession情報を格納する
if (isset($_SESSION['user_form'])) {
  $form = $_SESSION['user_form'];
} else {
  header('Location: index.php');
  exit();
}

//DB接続+insert
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $db = dbconnect();

  //update文生成(member)
  $stmt = $db->prepare('update members set name = :name, email= :email, picture = :picture where id = :id');
  if (!$stmt) {
    die($db->errorInfo());
  }

  //valuesに挿入する変数を指定
  $stmt->bindValue(':id', $id, PDO::PARAM_INT);
  $stmt->bindValue(':name', $form['name'], PDO::PARAM_STR);
  $stmt->bindValue(':email', $form['email'], PDO::PARAM_STR);
  $stmt->bindValue(':picture', $form['image'], PDO::PARAM_STR);

  $success = $stmt->execute();
  if (!$success) {
    die($db->errorInfo());
  }


  ////update文生成(member_info)
  $stmt = $db->prepare('update member_info set age = :age, hobby= :hobby, comment = :comment where member_id = :id');
  if (!$stmt) {
    die($db->errorInfo());
  };
  $stmt->bindValue(':id', $id, PDO::PARAM_INT);
  $stmt->bindValue(':age', $form['age'], PDO::PARAM_INT);
  $stmt->bindValue(':hobby', $form['hobby'], PDO::PARAM_STR);
  $stmt->bindValue(':comment', $form['comment'], PDO::PARAM_STR);

  $success = $stmt->execute();
  if (!$success) {
    die($db->errorInfo());
  }

  //初期設定画像と異なる画像が設定された場合は初期画像を削除する
  if ($form['image'] !== $_SESSION['image']) {
    $up_file = 'member_picture/' . ($_SESSION['image']);
    if (file_exists($up_file) && $_SESSION['image'] !== $first_image) {
      unlink($up_file);
    }
  }

  // sessionを削除して重複登録を防ぐ
  unset($_SESSION['user_form']);
  unset($_SESSION['image']);
  header('Location: user_edit_thanks.php');
  exit();
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>会員登録</title>

  <link rel="stylesheet" href="css/style.css" />
</head>

<body>
  <div class="wrapper">
    <div id="head">
      <h1>ユーザ情報</h1>
    </div>
    <div id="wrap">
      <div class="content user_check">
        <p>記入した内容を確認して、「登録する」ボタンをクリックしてください</p>
        <form action="" method="post" class="user_check_form">
          <dl>
            <dt>ニックネーム</dt>
            <dd><?php echo h($form['name']) ?></dd>
            <dt>メールアドレス</dt>
            <dd><?php echo h($form['email']) ?></dd>
            <dt>パスワード</dt>
            <dd>
              【表示されません】
            </dd>
            <dt>写真など</dt>
            <dd>
              <img src="member_picture/<?php echo h($form['image']) ?>" width="200" height="200" alt="" />
            </dd>
          </dl>
          <dl>
            <?php if ($form['image'] == $first_image) : ?>
              <dd class="error">* 画像が未設定の場合は上記画像が設定されます</dd>
            <?php endif; ?>
            <dt>年齢</dt>
            <dd><?php echo h($form['age']) ?></dd>
            <dt>趣味</dt>
            <dd><?php echo h($form['hobby']) ?></dd>
            <dt>一言</dt>
            <dd><?php echo h($form['comment']) ?></dd>
          </dl>
          <!-- action=rewrite というURLパラメーターを張り、入力画面でsession情報を復元できるようにする -->
          <div><a href="user_edit.php?action=rewrite">&laquo;&nbsp;書き直す</a> | <input type="submit" value="登録する" /></div>
        </form>
      </div>
    </div>
  </div>
</body>

</html>