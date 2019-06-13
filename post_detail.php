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

  // 投稿取得 2回もDBからデータ取ってくるのは無駄感(後々修正)
  if (!empty($_GET['post_id'])) {
    // 論理削除された投稿じゃないか確認
    $deleted_tmp = $db->prepare('SELECT u.name, p.* FROM users u, posts p WHERE p.is_deleted AND u.id = p.user_id AND p.id = ?');
    $deleted_tmp->execute(array(
      $_GET['post_id']
    ));
    $deleted = $deleted_tmp->fetchAll();
    if (empty($deleted)) {
      // 削除されていない
      $post_tmp = $db->prepare('SELECT u.name, u.icon_img, p.* FROM users u, posts p WHERE u.id = p.user_id AND p.id = ?');
      $post_tmp->execute(array(
        $_GET['post_id']
      ));
      $post = $post_tmp->fetchAll();
      // echo '<pre>';
      // var_dump($post[0]['icon_img']);
      // echo '</pre>';
      // exit;

      //コメントを取ってくる
      $comments_tmp = $db->prepare('SELECT u.name,c.* FROM users u, posts p, comments c WHERE u.id = p.user_id AND p.id = ? AND p.id = c.post_id ORDER BY c.created_at DESC;');
      $comments_tmp->execute(array(
        $_GET['post_id']
      ));
      $comments = $comments_tmp->fetchAll();
      // echo '<pre>';
      // var_dump($comments);
      // echo '</pre>';
      // exit;

      $posted_lat = $post[0]['latitude'];
      $posted_lng = $post[0]['longitude'];
      $posted_head = $post[0]['heading'];
      $posted_pitch = $post[0]['pitch'];
      $posted_zoom = $post[0]['zoom'];
    } else {
      // 削除済
      header('Location: index.php');
      exit();
    }
  }

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

 ?>
<!doctype html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>旅してみよう DoKoDeMo - ストリートビューで旅しよう</title>
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
  <link rel="shortcut icon" href="dokodemo.ico" type="image/x-icon">
  <link href="https://fonts.googleapis.com/css?family=M+PLUS+Rounded+1c" rel="stylesheet">
</head>

<body class="post_detail">

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

  <section class="nav_margin"></section>

  <section class="container">
    <div class="flexbox row">
      <div class="post_detail_wrap flex col-lg-12 col-md-12 col-sm-12">
        <div class="map_area">
          <div id="pano"></div>
          <div id="map"></div>
        </div>
        <div class="detail_area">
          <div id="res" class="flex flx-alC flx-juC"></div>
          <div class="user_info flexbox flx-fdC">
            <div class="flex">
              <?php if ($post[0]['icon_img'] == 0) { ?>
                <i class="fas fa-user-circle fa-2x"></i>
              <?php } else { ?>
                <img src="" alt="">
              <?php } ?>
              <p class="flex flx-alC info_name"><?php echo $post[0]['name']; ?></p>
            </div>
          </div>
          <div class="contributor">
            <p><?php echo $post[0]['message']; ?></p>
          </div>
          <div class="comments_area">
            <div class="spectator">
              <?php foreach ($comments as $key => $value): ?>
                <div class="comment commetsId_<?php echo $value['id']; ?>">
                  <p class="spe_name"><?php echo $value['name']; ?></p>
                  <p class="spe_message"><?php echo $value['message']; ?></p>
                  <p class="spe_date"><?php echo $value['created_at']; ?></p>
                </div>
              <?php endforeach ?>
            </div>
          </div>
          <div class="enter_comment flexbox">
            <form action="" method="post" class="flex">
              <input type="hidden" id="reply_postId" name="reply_postId" value="<?php echo $_GET['post_id'] ?>">
              <input type="text" name="reply_text" value="" autocomplete="off" placeholder="コメントを追加">
              <input type="submit" name="" value="投稿する">
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>

  <script>
    // グローバルな変数作成
    var _svp = "";
    //var js_postAddress = JSON.parse('<?php print_r ($address); ?>');
    var posted_lat = JSON.parse('<?php print_r ($posted_lat); ?>');
    var posted_lng = JSON.parse('<?php print_r ($posted_lng); ?>');
    var posted_head = JSON.parse('<?php print_r ($posted_head); ?>');
    var posted_pitch = JSON.parse('<?php print_r ($posted_pitch); ?>');
    var posted_zoom = JSON.parse('<?php print_r ($posted_zoom); ?>');
// console.log(posted_lat);
// console.log(posted_lng);
// console.log(posted_head);
// console.log(posted_pitch);
// console.log(posted_zoom);

    function initialize() {
      var fenway = {
        lat: Number(posted_lat),
        lng: Number(posted_lng)
      };
      var map = new google.maps.Map(document.getElementById('map'), {
        center: fenway,
        zoom: 14
      });
      var panorama = new google.maps.StreetViewPanorama(
        document.getElementById('pano'), {
          position: fenway,
          pov: {
            heading: Number(posted_head),
            pitch: Number(posted_pitch),
            zoom: Number(posted_zoom)
          },
          motionTracking: false
        });
      map.setStreetView(panorama);

      // panoramaオブジェクトを_svpに代入しておくとreviewファンクション内で使用できる
      _svp = panorama

      // ストリートビューを動かした時にreviewファンクションを呼び出す
      google.maps.event.addListener(_svp, 'tilesloaded', review);
      google.maps.event.addListener(_svp, 'pano_changed', review);
      google.maps.event.addListener(_svp, 'pov_changed', review);
    }

    function getPosition() {
      var res = streetViewPanorama.getPosition(_svp);
      var response = res;
      document.getElementById('res').innerHTML = response;
    }

    function review() {
      var pov = _svp.getPov();
      document.getElementById("res").innerHTML =
        "lat: " + _svp.getPosition().lat() + "<br>" +
        "lng: " + _svp.getPosition().lng() + "<br>" +
        "heading: " + pov["heading"] + "<br>" +
        "pitch: " + pov["pitch"] + "<br>" +
        "zoom: " + pov["zoom"];
    }


  </script>
  <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCWCuLqhEGIXn3k-oCmaw-bJrcKA08vCIU&callback=initialize">
  </script>

</body>

</html>
