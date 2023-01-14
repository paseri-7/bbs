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

$post_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
if (!$post_id) {
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
    <title>ひとこと掲示板</title>

    <link rel="stylesheet" href="css/style.css" />
</head>

<body>
    <div class="wrapper">
        <div id="head">
            <h1>ひとこと掲示板</h1>
        </div>
        <div id="wrap">
            <div class="content view">
                <p>&laquo;<a href="index.php">一覧にもどる</a></p>
                <?php
                $stmt = $db->prepare('select p.id, p.member_id, p.message, p.created, m.name, m.picture from posts p, members m where p.id=:post_id and p.member_id = m.id');
                if (!$stmt) {
                    die($db->errorInfo());
                };
                $stmt->bindValue(':post_id', $post_id, PDO::PARAM_INT);
                $success = $stmt->execute();
                if (!$success) {
                    die($db->errorInfo());
                }
                // $stmt->bind($id, $member_id, $message, $created, $name, $picture);
                $all = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($all) :
                ?>
                    <div class="msg fadein">
                        <?php if ($all['picture']) : ?>
                            <img src="member_picture/<?php echo h($all['picture']); ?>" class="icon" width="125" height="125" alt="" />
                        <?php endif; ?>
                        <p><?php echo h($all['message']); ?><span class="name">（<?php echo h($all['name']); ?>)</span></p>
                        <p class="day"><?php echo h($all['created']); ?></a>
                            <?php if ($_SESSION['id'] == $all['member_id']) : ?>
                                [<a href="delete.php?id=<?php echo h($all['id']); ?>" onclick="return confirm('本当に削除しますか?')" style="color: #F33;">削除</a>]
                            <?php endif; ?>
                        </p>
                    </div>
                <?php else : ?>
                    <p>その投稿は削除されたか、URLが間違えています</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script src="js/jquery-3.6.3.min.js"></script>
    <script src="js/common.js"></script>
</body>

</html>