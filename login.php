<?php
session_start();
require('library.php');

$error = [];
$name = '';
$password = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_SPECIAL_CHARS);
    if ($name === '' || $password === '') {
        $error['login'] = 'blank';
    } else {
        //ログインチェック
        $db = dbconnect();
        $stmt = $db->prepare('select id, name, password as hash from members where name=:name limit 1');
        if (!$stmt) {
            die($db->errorInfo());
        }

        // $stmt->bind_param('s', $name);
        $stmt->bindValue(':name', $name, PDO::PARAM_STR);

        $success = $stmt->execute();
        if (!$success) {
            die($db->errorInfo());
        }

        $all = $stmt->fetch(PDO::FETCH_ASSOC);
        // $stmt->bind_result($id, $name, $hash);
        // $stmt->fetch();
        if ($all) {
            if (password_verify($password, $all['hash'])) {
                //ログイン成功
                session_regenerate_id();
                $_SESSION['id'] = $all['id'];
                $_SESSION['name'] = $all['name'];
                header('Location: index.php');
                exit();
            } else {
                $error['login'] = 'pass_failed';
            }
        } else {
            $error['login'] = 'user_failed';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <link rel="stylesheet" type="text/css" href="css/style.css" />
    <title>ログインする</title>
</head>

<body>
    <div class="wrapper">
        <div id="head">
            <h1>ログインする</h1>
        </div>
        <div class="wrap login">
            <div class="content login">
                <div class="lead">
                    <p>ユーザ名とパスワードを記入してログインしてください。</p>
                    <p>入会手続きがまだの方はこちらからどうぞ。</p>
                    <p>&raquo;<a href="join/">入会手続きをする</a></p>
                </div>
                <form action="" method="post">
                    <dl class="login_form">
                        <dt>ユーザ名</dt>
                        <dd>
                            <input type="text" name="name" size="35" maxlength="255" value="<?php echo h($name); ?>" />
                            <?php if (isset($error['login']) && $error['login'] === 'blank') : ?>
                                <p class="error">* ユーザ名とパスワードをご記入ください</p>
                            <?php endif; ?>
                            <?php if (isset($error['login']) && $error['login'] === 'user_failed') : ?>
                                <p class="error">* ユーザ名が間違っています。正しくご記入ください。</p>
                            <?php endif; ?>
                        </dd>
                        <dt>パスワード</dt>
                        <dd>
                            <input type="password" name="password" size="35" maxlength="255" value="<?php echo h($password); ?>" />
                            <?php if (isset($error['login']) && $error['login'] === 'pass_failed') : ?>
                                <p class="error">* パスワードが間違っています。正しくご記入ください。</p>
                            <?php endif; ?>
                        </dd>
                    </dl>
                    <div>
                        <input type="submit" value="ログインする" />
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>