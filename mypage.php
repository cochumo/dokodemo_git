<?php

  session_start();
  // フラッシュメッセージリセット
  $flash = isset($_SESSION['flash']) ? $_SESSION['flash'] : array();
  unset($_SESSION['flash']);

  // flashメッセージセット
  function flash($type, $message) {
    global $flash;
    $_SESSION['flash'][$type] = $message;
    $flash[$type] = $message;
  }

  require('dbconnect.php');

  // ログインチェック
  if (isset($_SESSION['id']) && $_SESSION['time'] + 21600 > time()) { // 21600 = 6時間
    // ログインしている
    $_SESSION['time'] = time();

    $members = $db->prepare('SELECT * FROM users WHERE id=?');
    $members->execute(array($_SESSION['id']));
    $member = $members->fetch();
  } else {
    // ログインしていない
    header('Location: login.php'); exit();
  }

  // 変数定義
  $userId = $_SESSION['id'];

  // 投稿データ取得
  $userPosts = $db->prepare('SELECT * FROM posts WHERE user_id=?');
  $userPosts->execute(array($userId));
  $userPost = $userPosts->fetchAll();

  // 論理削除投稿データ取得
  $deletedPosts = $db->prepare('SELECT * FROM posts WHERE user_id=? AND is_deleted=1 ORDER BY id DESC');
  $deletedPosts->execute(array($userId));
  $deletedPost = $deletedPosts->fetchAll();

  if (!empty($_POST)) {

    // 論理削除した投稿を復元
    if (!empty($_POST['restorePostId'])) {
      // 復元する投稿の投稿者が現在ログインしているユーザーか検査
      if ($_POST['userId'] == $userId){
        $restorePost = $db->prepare('UPDATE posts SET is_deleted=0 WHERE id=?');
        $restorePost->execute(array($_POST['restorePostId']));

        // フラッシュメッセージ success
        flash('success', '投稿を復元が正常に行われました');
        header('Location: mypage.php');
        exit;
      } else {
        // 復元する投稿の投稿者が現在ログインしているユーザーが違う

        // フラッシュメッセージ error
        flash('error', '復元する投稿の投稿者が現在ログインしているユーザーが違います');
        header('Location: mypage.php');
        exit;
      }
    }

    // ゲストログインでは変更できない処理
    if (!($member['id'] == 1 && $member['name'] == "Guest")) {

      // 名前編集処理
      if (!empty($_POST['name_change'])){
        $name_change = $db->prepare('UPDATE users SET name=? WHERE id=?');
        $name_change->execute(array(
          $_POST['name_change'],
          $userId
        ));

        // フラッシュメッセージ success
        flash('success', '名前の変更が正常に行われました');
        header('Location: mypage.php');
        exit;
      }

      // パスワード変更処理
      if (!empty($_POST['current_password'] || $_POST['new_password'] || $_POST['confirm_password'])) {

        // 変数定義
        $current_password = $_POST['current_password']; // 現在のパスワード
        $new_password = $_POST['new_password']; // 新しいパスワード
        $confirm_password = $_POST['confirm_password']; // 新しいパスワードの確認

        // エラー処理

        // 現在のパスワードを取得
        $current_pass_check = $db->prepare('SELECT password FROM users WHERE id=?');
        $current_pass_check->execute(array($userId));
        $current_pass_return = $current_pass_check->fetch();

        // 3つの欄の中で空がないか検査
        if ($_POST['current_password'] == "" || $_POST['new_password'] == "" || $_POST['confirm_password'] == "") {

          // フラッシュメッセージ error
          flash('error', '未記入の欄があります。確認してください');
          header('Location: mypage.php');
          exit;
        }

        // 新しいパスワードと確認用パスワードが一緒か検査
        if ($new_password == $confirm_password) {

          // 入力された現在のパスワードと照合
          if ($current_pass_return[0] == sha1($current_password)) {
            // 照合成功
            $password_update = $db->prepare('UPDATE users SET password=? WHERE id=?');
            $password_update->execute(array(
              sha1($new_password),
              $userId
            ));

            // フラッシュメッセージ success
            flash('success', 'パスワードの変更が正常に行われました');
            header('Location: mypage.php');
            exit;

          } else {
            //照合失敗

            // フラッシュメッセージ error
            flash('error', '入力された「現在のパスワード」が一致しませんでした');
            header('Location: mypage.php');
            exit;
          }
        } else {
          // 新しいパスワードと確認用パスワードが一緒じゃない

          // フラッシュメッセージ error
          flash('error', '入力された「新しいパスワード」と「確認用パスワード」が一致しませんでした');
          header('Location: mypage.php');
          exit;
        }
      }

    } else {

      // フラッシュメッセージ error
      flash('error', 'ゲストログインでは各種変更処理はできません。新しくアカウントを作成して下さい。');
      header('Location: mypage.php');
      exit;

    }

  }




?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title><?php echo $member['name']; ?>さんのマイページ DoKoDeMo - ストリートビューで旅しよう</title>
  <!--Bootstrap４に必要なCSSとJavaScriptを読み込み-->
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
  <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
  <link href="https://use.fontawesome.com/releases/v5.0.6/css/all.css" rel="stylesheet">
  <!-- リセット CSS -->
  <link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/3.18.1/build/cssreset/cssreset-min.css">
  <!--style.cssを読み込み-->
  <link href="style.css" rel="stylesheet" type="text/css">
  <!-- common.jsの読み込み -->
  <script type="text/javascript" src="common.js"></script>
  <link rel="shortcut icon" href="dokodemo.ico" type="image/x-icon">
</head>
  <body class="mypage">

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top" id="mainNav">
      <div class="container">
        <a class="t_logo navbar-brand js-scroll-trigger" href="index.php">
          <i class="fas fa-street-view"></i> DoKoDeMo
        </a>
        <button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarResponsive">
          <ul class="navbar-nav ml-auto">
            <li class="nav-item">
              <a class="nav-link js-scroll-trigger" href="travel_select/travel_index.php">旅をする</a>
            </li>
            <li class="nav-item">
              <a class="nav-link js-scroll-trigger" href="mypage.php">マイページ</a>
            </li>
            <li class="nav-item">
              <a class="nav-link js-scroll-trigger" href="terms.php">利用規約</a>
            </li>
            <li class="nav-item">
              <a class="nav-link js-scroll-trigger" href="logout.php">ログアウト</a>
            </li>
          </ul>
        </div>
      </div>
    </nav>

    <section class="nav_margin">
    </section>

    <!-- 成功ログ -->
    <?php if (isset($flash['success'])): ?>
      <div id="flash_message" class="flash_msg_success">
        <h3><?php echo $flash['success']; ?></h3>
      </div>
    <?php endif ?>
    <!-- 失敗ログ -->
    <?php if (isset($flash['error'])): ?>
      <div id="flash_message" class="flash_msg_error">
        <h3><?php echo $flash['error']; ?></h3>
      </div>
    <?php endif ?>

    <section class="mypage_main container">
      <div class="flexbox row">
        <div class="col-lg-5 col-md-8 col-sm-12">
          <div class="mypage_cotent_list">
            <div class="mypage_cotent">
              <div class="mypage_top">
                <?php if ($member['icon_img'] == 0) { ?>
                  <i class="fas fa-user-circle fa-3x"></i>
                <?php } else { ?>
                  <img src="" alt="">
                <?php } ?>
                <h3>Name</h3>
                <p><?php echo $member['name']; ?></p>
              </div>
              <div class="flex flx-juC">
                <div class="mypage_info">
                  <h3>Email</h3>
                  <p><?php echo $member['email']; ?></p>
                </div>
                <div class="mypage_info">
                  <h3>投稿数</h3>
                  <p><?php echo count($userPost);?></p>
                </div>
              </div>
            </div>
          </div>

          <div class="mypage_menu flex flx-juC flx-fdC">
            <div id="name_change" class="mypage_content">
              <h3>名前を変更</h3>
              <div class="content_inner hide">
                <form action="" method="post" class="flex flx-juSb flx-fdC">
                  <input type="hidden" name="userId" value="<?php echo $userId; ?>">
                  <input type="text" name="name_change" value="<?php echo $member['name']; ?>">
                  <input type="submit" value="変更を保存する">
                </form>
              </div>
            </div>

            <div id="password_change" class="mypage_content">
              <h3>パスワードを変更</h3>
              <div class="content_inner hide">
                <form action="" method="post" class="flex flx-juSb flx-fdC">
                  <input type="hidden" name="userId" value="<?php echo $userId; ?>">
                  <p>現在のパスワード</p>
                  <input type="password" autocomplete="off" name="current_password" value="">
                  <p>新しいパスワード</p>
                  <input type="password" autocomplete="off" name="new_password" value="">
                  <p>新しいパスワード 確認</p>
                  <input type="password" autocomplete="off" name="confirm_password" value="">
                  <input type="submit" value="変更を保存する">
                </form>
              </div>
            </div>

            <div id="deletedPost" class="mypage_content">
              <h3>削除した投稿を表示</h3>
              <div class="content_inner hide">
                <?php if (!empty($deletedPost)) { ?>
                  <?php for($i = 0; $i < count($deletedPost);$i++) : ?>
                    <?php if ($deletedPost[$i]['id'] == "") {break;} ?>
                    <div class="deletedContent">
                      <p>post_id: <?php echo $deletedPost[$i]['id']; ?></p>
                      <p>message: <?php echo $deletedPost[$i]['message']; ?></p>
                      <p>created_at: <?php echo $deletedPost[$i]['created_at']; ?></p>
                      <form class="" action="" method="post">
                        <input type="hidden" name="userId" value="<?php echo $userId; ?>">
                        <input type="hidden" name="restorePostId" value="<?php echo $deletedPost[$i]['id']; ?>">
                        <input type="submit" value="投稿の復元">
                      </form>
                    </div>
                  <?php endfor; ?>
                <?php } else { ?>
                  <p class="textC">削除された投稿はありません</p>
                <?php } ?>
              </div>
            </div>

            <!--div id="icon_change" class="mypage_content">
              <h3>アイコンイメージの設定</h3>
              <div class="content_inner hide">
                <form action="" method="post" class="flex flx-juSb">
                  <input type="hidden" name="userId" value="<?php echo $userId; ?>">
                  <input type="text" name="name_change" value="<?php echo $member['name']; ?>">
                  <input type="submit" value="変更を保存する">
                </form>
              </div>
            </div-->

          </div>
        </div>
      </div>
    </section>
  </body>
</html>
