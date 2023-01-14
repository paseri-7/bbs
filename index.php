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

$db = dbconnect();

//メッセージの投稿
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_SPECIAL_CHARS);

    $stmt = $db->prepare('insert into posts (message, member_id)VALUES(:message,:id)');
    if (!$stmt) {
        die($db->errorInfo());
    }

    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->bindValue(':message', $message, PDO::PARAM_STR);
    $success = $stmt->execute();
    if (!$success) {
        die($db->errorInfo());
    }

    //POSTの内容をクリア
    header('Location: index.php');
    exit();
}

?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>ひとこと掲示板</title>

    <link rel="stylesheet" href="css/modaal.css">
    <link rel="stylesheet" href="css/style.css" />
</head>

<body>
    <div class="wrapper">
        <div id="head">
            <h1>ひとこと掲示板</h1>
        </div>
        <div id="wrap">
            <div class="content home">
                <form action="" method="post">
                    <dl>
                        <dt><?php echo h($name); ?>さん、メッセージをどうぞ</dt>
                        <dd>
                            <input type="text" name="message" required size="35" maxlength="255" value="" />
                        </dd>
                    </dl>
                    <div>
                        <p>
                            <input type="submit" value="投稿する" />
                        </p>
                    </div>
                </form>

                <!-- 一覧表示（DB取得） -->
                <?php
                $stmt = $db->prepare('select p.id, p.member_id, p.message, p.created, m.name, m.picture from posts p, members m where m.id=p.member_id order by id desc');
                if (!$stmt) {
                    die($db->errorInfo());
                };
                $success = $stmt->execute();
                if (!$success) {
                    die($db->errorInfo());
                }
                $all = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($all as $result) :
                ?>

                    <div class="msg fadein">
                        <?php if ($result['picture']) : ?>
                            <a href="user.php?id=<?php echo h($result['member_id']); ?>" class="modal"><img src="member_picture/<?php echo h($result['picture']); ?>" class="icon" width="125" height="125" alt="" /></a>
                        <?php endif; ?>
                        <p><a href="view.php?id=<?php echo h($result['id']); ?>"><?php echo h($result['message']); ?> </a><span class="name">（<?php echo h($result['name']); ?>)</span></p>
                        <p class="day"><?php echo h($result['created']); ?>
                            <?php if ($_SESSION['id'] == $result['member_id']) : ?>
                                [<a href="delete.php?id=<?php echo h($result['id']); ?>" onclick="return confirm('本当に削除しますか?')" style="color: #F33;">削除</a>]
                            <?php endif; ?>
                        </p>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- ユーザ情報（DB取得） -->
            <?php
            $stmt = $db->prepare('select id, name, picture from members where id=:id');
            if (!$stmt) {
                die($db->errorInfo());
            };

            $stmt->bindValue(':id', $id, PDO::PARAM_INT);

            $success = $stmt->execute();
            if (!$success) {
                die($db->errorInfo());
            }
            // $stmt->bind($id, $member_id, $message, $created, $name, $picture);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            ?>
            <div class="user">
                <h4>ログインユーザ</h4>
                <a href="user.php?id=<?php echo $user['id']; ?>" class="modal"><img src="member_picture/<?php echo h($user['picture']); ?>" class="icon" width="200" height="200" alt="" /></a>
                <p><span class="name"><?php echo h($user['name']); ?></span></p>
                <div><a href="logout.php">ログアウト</a></div>
            </div>
        </div>
    </div>
    <script src="js/jquery-3.6.3.min.js"></script>
    <script src="js/modaal.js"></script>
    <script src="js/common.js"></script>
</body>

</html>