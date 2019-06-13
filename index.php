<?php

  session_start();

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
    header('Location: login.php');
    exit();
  }

  // 論理削除処理
  if (!empty($_POST['is_deleted'])){
    $postIsDeleted = $db->prepare('UPDATE posts SET is_deleted=1 WHERE id=?');
    $postIsDeleted->execute(array($_POST['is_deleted']));
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit();
  }

  // 編集処理
  if (!empty($_POST['editMessage'])){
    $postEditMessage = $db->prepare('UPDATE posts SET message=? WHERE id=?');
    $postEditMessage->execute(array(
      $_POST['editMessage'],
      $_POST['editPostId']
    ));

    header('Location: index.php');
    exit();
  }

  // 投稿の取得

  // ページング
  if (isset($_GET['page'])) {
    $page = $_GET['page'];
  } else {
    $page = 1;
  }

  // 取得件数
  $limit = 10;
  // オフセット
  $offset = $limit * ($page - 1);

  // 論理削除した投稿以外を取得
  $posts = $db->prepare('SELECT SQL_CALC_FOUND_ROWS u.name, u.id AS user_id, p.* FROM users u, posts p WHERE u.id=p.user_id AND p.is_deleted = 0 ORDER BY p.created_at DESC LIMIT ?, ?');
  $posts->bindParam(1, $offset, PDO::PARAM_INT);
  $posts->bindParam(2, $limit, PDO::PARAM_INT);
  $posts->execute();
  $posts = $posts->fetchAll();

  // 投稿の件数を取得
  $count = $db->query('SELECT FOUND_ROWS()');
  $data['count'] = $count->fetch(PDO::FETCH_ASSOC);
  $data['count'] = $data['count']['FOUND_ROWS()'];
  $max_page = floor($data['count'] / $limit) + 1;

  // コメントを追加
  if (!empty($_POST['reply_text'])) {
    $post_comment = $db->prepare('INSERT INTO comments(user_id,post_id,message) VALUES (?,?,?)');
    $post_comment->execute(array(
      $member['id'],
      $_POST['reply_postId'],
      $_POST['reply_text']
    ));
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit();
  }

  // 経過時間
  function relative_time($value) {
    $now = time();
    $ts = strtotime($value);
    $second = (int) ($now - $ts);

    if ($second < 60) { // 60sec=1min
        return $second . '秒前';
      } elseif ($second < 3600) { // 3600sec = 1hour
        $min = (int) ($second / 60);
        return (string) $min . '分前';
      } elseif ($second < 86400) { // 86400sec = 1day
        $hour = (int) ($second / 3600);
        return (string) $hour . '時間前';
        } else {
        $day = (int) ($second / 86400);
        return (string) $day . '日前';
    }
  }

  // htmlspecialcharsのショートカット
  function h($value) {
  	return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
  }

  // 本文内のURLにリンクを設定します
  function makeLink($value) {
  	return mb_ereg_replace("(https?)(://[[:alnum:]\+\$\;\?\.%,!#~*/:@&=_-]+)", '<a href="\1\2">\1\2</a>' , $value);
  }

  $php_json = json_encode($posts);
  // var_dump($posts[0]['user_id']);
  // var_dump($member['id']);
  // exit();
  //


?>
<!doctype html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>DoKoDeMo - ストリートビューで旅しよう</title>
  <!--Bootstrap４に必要なCSSとJavaScriptを読み込み-->
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
  <script src="https://code.jquery.com/jquery-3.3.1.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
  <link href="https://use.fontawesome.com/releases/v5.0.6/css/all.css" rel="stylesheet">
  <!-- リセット CSS -->
  <link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/3.18.1/build/cssreset/cssreset-min.css">
  <!--style.cssを読み込み-->
  <link href="style.css" rel="stylesheet" type="text/css">
  <script type="text/javascript" src="common.js"></script>
  <link rel="shortcut icon" href="dokodemo.ico" type="image/x-icon">
</head>

<body class="index">
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

  <section class="index_main container">
    <div class="flexbox row">
      <div class="index_content_list col-lg-5 col-md-8 col-sm-12">
        <!--投稿の繰り返し表示 ここから-->
        <?php foreach ($posts as $post): ?>
        <div class="index_content" id="<?php echo $post['id']; ?>">
          <div class="content_header flexbox">
            <div class="flexbox">
              <i class="fas fa-user-circle fa-2x"></i>
              <p>
                <?php echo (mb_substr(h($post['name']),0,8)); ?>
                <?php echo ((mb_strlen($post['name']) > 8 ? '...' : '')); ?>
              </p>
            </div>
            <div class="flexbox">
              <i class="far fa-clock fa-lg"></i>
              <p><?php echo h(relative_time($post['created_at'])); ?></p>
            </div>
          </div>
          <div class="content_main">
            <div id="pano_list_<?php echo $post['id']; ?>" class="pano_list"></div>
          </div>
          <div class="content_footer flexbox">
            <div class="msg">
              <p class="">
                <?php echo (mb_substr(h($post['message']),0,20)); ?>
                <?php echo ((mb_strlen($post['message']) > 20 ? '...' : '')); ?>
              </p>
            </div>
            <div class="flex">

              <?php
                // ログインしているユーザーIDと投稿者のユーザーIDが一緒だったら表示
                if ($post['user_id'] === $member['id']):
              ?>
                <!-- 編集 ここから -->
                <div class="">
                  <!-- 編集ボタン -->
                  <div class="flexbox modal_bt">
                    <button type="button" class="btn btn-primary btn-sm control" data-toggle="modal" data-target="#modal_editPostId_<?php echo $post['id']; ?>">
                      <i class="fas fa-pencil-alt fa-lg"></i>
                    </button>
                  </div>

                  <!-- 編集モーダル ここから -->
                  <div class="modal fade" id="modal_editPostId_<?php echo $post['id']; ?>" tabindex="-1" role="dialog">
                    <div class="modal-dialog" role="document">
                      <div class="modal-content">
                        <div class="modal-header">
                          <h4 class="modal-title">投稿の編集</h4>
                          <button type="button" class="close u-p0" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <div class="modal-body">
                          <form method="post" action="">
                          <div class="form-group">
                            <h5>編集内容を入力して下さい</h5>
                            <!-- DBのメッセージカラムの内容を変更する -->
                            <input type="hidden" id="editPostId" name="editPostId" value="<?php echo $post['id']; ?>">
                            <input type="text" name="editMessage" id="editMessage" value="<?php echo $post['message']; ?>">
                          </div>
                          <div class="modal-footer">
                            <input type="submit" class="btn btn-primary" value="編集内容を保存">
                          </div>
                        </form>
                        </div>
                      </div>
                    </div>
                  </div>
                  <!-- 編集モーダル ここまで -->
                </div>
                <!-- 編集 ここまで -->
              <?php endif; ?>

              <!-- ファボ ここから -->
              <!-- <div class="">
                <button type="button" name="button">
                  <i class="fas fa-heart fa-lg"></i>
                </button>
              </div> -->
              <!-- ファボ ここまで -->

              <!-- コメント ここから -->
              <div class="lexbox modal_bt">
                <!-- コメントボタン -->
                <button type="button" class="btn btn-primary btn-sm control" data-toggle="modal" data-target="#modal_commentPostId_<?php echo $post['id']; ?>" name="button">
                  <i class="fas fa-comment fa-lg"></i>
                </button>

                <!-- コメントモーダル ここから -->
                <div class="modal fade" id="modal_commentPostId_<?php echo $post['id']; ?>" tabindex="-1" role="dialog">
                  <div class="modal-dialog" role="document">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h4 class="modal-title">コメント</h4>
                        <button type="button" class="close u-p0" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                      </div>
                      <div class="modal-body">
                        <div class="form-group">
                          <h5>よかったらコメント残してみませんか？</h5>
                        </div>
                        <form class="comment_box" action="" method="post">
                          <div>
                            <input type="hidden" id="reply_postId" name="reply_postId" value="<?php echo $post['id']; ?>">
                            <input type="text" name="reply_text" placeholder="コメントを入力してください">
                          </div>
                          <div class="modal-footer">
                            <input type="submit" class="btn btn-primary" value="投稿する">
                          </div>
                        </form>
                      </div>
                    </div>
                  </div>
                </div>

              </div>
              <!-- コメント ここまで -->

              <?php
                // ログインしているユーザーIDと投稿者のユーザーIDが一緒だったら表示
                if ($post['user_id'] === $member['id']):
              ?>
                <!-- 削除 ここから -->
                <div>
                  <!-- 削除ボタン -->
                  <div class="flexbox modal_bt">
                    <button type="button" class="btn btn-primary btn-sm control" data-toggle="modal" data-target="#modal_deletedPostId_<?php echo $post['id']; ?>">
                      <i class="far fa-trash-alt fa-lg"></i>
                    </button>
                  </div>

                  <!-- 削除モーダル ここから -->
                  <div class="modal fade" id="modal_deletedPostId_<?php echo $post['id']; ?>" tabindex="-1" role="dialog">
                    <div class="modal-dialog" role="document">
                      <div class="modal-content">
                        <div class="modal-header">
                          <h4 class="modal-title">投稿の削除</h4>
                          <button type="button" class="close u-p0" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <div class="modal-body">
                          <div class="form-group">
                            <h5>本当に削除しますか？</h5>
                            <small>論理削除なので投稿自体は削除されません</small>
                          </div>
                          <div class="modal-footer">
                            <form method="post" action="">
                              <!-- DBの論理削除カラムを1に変更する -->
                              <input type="hidden" id="is_deleted" name="is_deleted" value="<?php echo $post['id']; ?>">
                              <input type="submit" class="btn btn-primary" value="削除">
                            </form>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <!-- 削除モーダル ここまで -->
                </div>
                <!-- 削除 ここまで -->
              <?php endif; ?>
              <div class="">
                <button type="button" class="btn btn-primary btn-sm control">
                  <a href="post_detail.php?post_id=<?php echo $post['id']; ?>">
                    <i class="fas fa-ellipsis-h fa-lg"></i>
                  </a>
                </button>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
      <div class="pages">
        <?php if ($page >= 2): ?>
          <a href="index.php?page=<?php echo ($page - 1); ?>"><?php echo ($page - 1); ?></a>
        <?php endif; ?>
          <p><?php echo $page; ?></p>
        <?php if ($page < $max_page): ?>
          <a href="index.php?page=<?php echo ($page + 1); ?>"><?php echo ($page + 1); ?></a>
        <?php endif; ?>
      </div>
      <!--投稿の繰り返し表示 ここまで-->
      <!-- <div class="add_post"></div>
      <div class="more_btn flex flx-juC ">
        <button id="more" type="button" name="button">もっと見る</button>
      </div> -->
      </div>
    </div>
  </section>

<script type="text/javascript">

  // var set = 5;
  // var ck = 0;
  // var total = <?php echo $data['count'] ?>;
  // var offset;
  //
  // if (set + total > set) {
  //   console.log('残り' + total + '件');
  //   console.log('次の' + set + '件を見る');
  // } else {
  //   $('.more_btn').hide();
  // }
  //
  // $('#more').click(function() {
  //   console.log('クリック感知したよ');
  //
  //   ck ++;
  //   offset = set * ck;
  //   console.log(offset);
  //
  //   if (total >= 0) {
  //     total -= set;
  //     console.log(total);
  //   }
  //
  //   if (total < set) {
  //     console.log(total + '件');
  //   }
  //
  //   $.ajax({
  //     url: 'get_data.php', //送信先
  //     type: 'POST',
  //     data: { 'offset': offset },
  //     timeout: 10000,
  //     dataType: 'text'
  //   })
  //   .done(function( data ) {
  //     $('.add_post').append(data);
  //     initialize();
  //     if(total <= 0){
  //       console.log('0件');
  //     }else{
  //       console.log(total + '件');
  //     }
  //   });
  //
  //   if(total <= 0){
  //     $('#more').hide();
  //   }
  //
  //   return false;
  //
  // });


</script>

<script>

  // グローバルな変数作成
  // var _svp = "";
  var js_post = JSON.parse('<?php echo $php_json; ?>');
  // console.log(js_post);

  function initialize() {
    // ループ
    for(let val in js_post) {

      //var fenway = { lat: Number(js_post["latitude"]),lng: Number(js_post["longitude"])};
      var fenway = { lat: Number(js_post[val]["latitude"]),lng: Number(js_post[val]["longitude"])};
      var id = 'pano_list_' + js_post[val]['id'];
      // console.log(id);
      // console.log(fenway);

      var panorama = new google.maps.StreetViewPanorama(

          document.getElementById(id), {
           position: fenway,
           pov: {
             heading: Number(js_post[val]["heading"]),
             pitch: Number(js_post[val]["pitch"]),
             zoom: Number(js_post[val]["zoom"])
          }
      });
    }
  }

</script>
<script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCWCuLqhEGIXn3k-oCmaw-bJrcKA08vCIU&callback=initialize">
</script>

</body>

</html>
