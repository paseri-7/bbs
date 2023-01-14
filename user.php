<?php
session_start();
require('library.php');

if (isset($_SESSION['id']) && isset($_SESSION['name'])) {
  $id = $_SESSION['id'];
  $name = $_SESSION['name'];
} else {
  header('Location: login.php');
  exit();
}

$member_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
if (!$member_id) {
  header('Location: index.php');
  exit();
}

$db = dbconnect();
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
      <div class="content user">
        <?php
        $stmt = $db->prepare('select m.id, m.name, m.email, m.picture, mi.member_id, mi.age, mi.hobby, mi.comment from members m,member_info mi where m.id = :member_id and mi.member_id = :member_id');
        if (!$stmt) {
          die($db->errorInfo());
        };
        $stmt->bindValue(':member_id', $member_id, PDO::PARAM_INT);
        $success = $stmt->execute();
        if (!$success) {
          die($db->errorInfo());
        }
        $all = $stmt->fetch(PDO::FETCH_ASSOC);
        ?>
        <dl class="user_msg">
          <dt>ユーザネーム</dt>
          <dd>
            <p><span class="name"><?php echo h($all['name']); ?></span></p>
          </dd>
          <dt>メールアドレス</dt>
          <dd>
            <p><span class="name"><?php echo h($all['email']); ?></span></p>
          </dd>
          <dt>年齢</dt>
          <dd>
            <?php if ($all['age'] != 0) : ?>
              <p><span class="name"><?php echo h($all['age']); ?></span></p>
            <?php else : ?>
              <p class="error">情報がありません</p>
            <?php endif; ?>
          </dd>
          <dt>趣味</dt>
          <dd>
            <?php if ($all['hobby']) : ?>
              <p><span class="name"><?php echo h($all['hobby']); ?></span></p>
            <?php else : ?>
              <p class="error">情報がありません</p>
            <?php endif; ?>
          </dd>
          <dt>一言</dt>
          <dd>
            <?php if ($all['comment']) : ?>
              <p><span class="name"><?php echo h($all['comment']); ?></span></p>
            <?php else : ?>
              <p class="error">情報がありません</p>
            <?php endif; ?>
          </dd>

          <?php if ($_SESSION['id'] == $all['member_id']) :  ?>
            <a href="user_edit.php" style="color: #F33;">編集</a>
          <?php endif; ?>
        </dl>

        <dl class="user_img">
          <dt>トップ画像</dt>
          <dd>
            <img src="member_picture/<?php echo h($all['picture']); ?>" class="icon" width="300" height="300" alt="" />
          </dd>
        </dl>
      </div>
    </div>
  </div>
</body>

</html>