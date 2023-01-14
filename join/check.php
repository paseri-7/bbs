<?php
session_start();
require('../library.php');

if (isset($_SESSION['form'])) {
	$form = $_SESSION['form'];
} else {
	header('Location: index.php');
	exit();
}

//DB接続+insert
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

	$db = dbconnect();



	//insert文生成(member)
	$stmt = $db->prepare('insert into members (name, email,password,picture)VALUES(:name,:email,:password,:image)');
	if (!$stmt) {
		die($db->errorInfo());
	}

	//passwordをハッシュ化する
	$password = password_hash($form['password'], PASSWORD_DEFAULT);

	//valuesに挿入する変数を指定
	$stmt->bindValue(':name', $form['name'], PDO::PARAM_STR);
	$stmt->bindValue(':email', $form['email'], PDO::PARAM_STR);
	$stmt->bindValue(':password', $password, PDO::PARAM_STR);
	if ($form['image']) {
		$stmt->bindValue(':image', $form['image'], PDO::PARAM_STR);
	}

	$success = $stmt->execute();
	if (!$success) {
		die($db->errorInfo());
	}


	//登録したユーザのidを取得
	$stmt = $db->prepare('select id from members where name = :name');
	if (!$stmt) {
		die($db->errorInfo());
	};
	$stmt->bindValue(':name', $form['name'], PDO::PARAM_STR);
	$success = $stmt->execute();
	if (!$success) {
		die($db->errorInfo());
	}
	$user_id = $stmt->fetch(PDO::FETCH_ASSOC);


	//insert文生成(member_info)
	$stmt = $db->prepare('insert into member_info (member_id)VALUES(:user_id)');
	if (!$stmt) {
		die($db->errorInfo());
	}

	//valuesに挿入する変数を指定
	$stmt->bindValue(':user_id', implode(',', $user_id), PDO::PARAM_STR);

	$success = $stmt->execute();
	if (!$success) {
		die($db->errorInfo());
	}

	// sessionを削除して重複登録を防ぐ
	unset($_SESSION['form']);
	header('Location: thanks.php');
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

	<link rel="stylesheet" href="../css/style.css" />
</head>

<body>
	<div class="wrapper">
		<div id="head">
			<h1>会員登録</h1>
		</div>
		<div id="wrap">
			<div class="content check">
				<p>記入した内容を確認して、「登録する」ボタンをクリックしてください</p>
				<form action="" method="post">
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
							<img src="../member_picture/<?php echo h($form['image']) ?>" width="200" height="200" alt="" />
						</dd>
						<?php if ($form['image'] == $first_image) : ?>
							<dd class="error">* 画像が未設定の場合は上記画像が設定されます</dd>
						<?php endif; ?>
					</dl>
					<!-- action=rewrite というURLパラメーターを張り、入力画面でsession情報を復元できるようにする -->
					<div><a href="index.php?action=rewrite">&laquo;&nbsp;書き直す</a> | <input type="submit" value="登録する" /></div>
				</form>
			</div>
		</div>
	</div>
</body>

</html>