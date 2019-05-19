<?php

  session_start();

  require('dbconnect.php');

  ini_set("display_errors", 1);
  error_reporting(E_ALL);

  // もっと見るボタン
  $offset = (int)$_POST['offset'];
  $limit = 5;

  // 論理削除した投稿以外を取得
  $posts = $db->prepare('SELECT u.name, p.* FROM users u, posts p WHERE u.id=p.user_id AND p.is_deleted = 0 ORDER BY p.created_at DESC LIMIT ? OFFSET ?');
  $posts->bindParam(1, $limit, PDO::PARAM_INT);
  $posts->bindParam(2, $offset, PDO::PARAM_INT);
  $posts->execute();
  $post_ = $posts->fetchAll();

  // 表示していない投稿の件数を取得
  $count = $db->query('SELECT FOUND_ROWS()');
  $data['count'] = $count->fetch(PDO::FETCH_ASSOC);
  $data['count'] = $data['count']['FOUND_ROWS()'] - $limit;

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

  $php_json = json_encode($post_);

?>

<!doctype html>
<html lang="ja">

<head>

</head>

<body class="index" onload="initialize()">
    <!--投稿の繰り返し表示 ここから-->
    <?php foreach ($post_ as $post): ?>
    <div class="index_content" id="<?php echo $post['id']; ?>">
      <div class="content_header flexbox">
        <a href="#" class="flexbox">
          <i class="fas fa-user-circle fa-2x"></i>
          <p>
              <?php echo (mb_substr(h($post['name']),0,8)); ?>
              <?php echo ((mb_strlen($post['name']) > 8 ? '...' : '')); ?>
          </p>
        </a>
        <div class="flexbox">
          <i class="far fa-clock fa-lg"></i>
          <p><?php echo h(relative_time($post['created_at'])); ?></p>
        </div>
      </div>
      <div class="content_main">
        <div id="pano_list_<?php echo $post['id']; ?>" class="pano_list"></div>
      </div>
      <div class="content_footer flexbox">
        <p class="msg">
          <?php echo (mb_substr(h($post['message']),0,20)); ?>
          <?php echo ((mb_strlen($post['message']) > 20 ? '...' : '')); ?>
        </p>

        <!-- 編集モーダル ここから -->
        <div class="">
          <div class="flexbox modal_bt">
            <button type="button" class="btn btn-primary btn-sm control" data-toggle="modal" data-target="#modal_editPostId_<?php echo $post['id']; ?>">
              <i class="fas fa-pencil-alt fa-lg"></i>
            </button>
          </div>

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

        </div>
        <!-- 編集モーダル ここまで -->


        <div class="">
          <button type="button" name="button">
            <i class="fas fa-heart fa-lg"></i>
          </button>
          <button type="button" name="button">
            <i class="fas fa-comment fa-lg"></i>
          </button>
          <!-- 削除モーダル ここから -->
          <div class="">
            <div class="flexbox modal_bt">
              <button type="button" class="btn btn-primary btn-sm control" data-toggle="modal" data-target="#modal_deletedPostId_<?php echo $post['id']; ?>">
                <i class="far fa-trash-alt fa-lg"></i>
              </button>
            </div>

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
          </div>
          <!-- 削除モーダル ここまで -->
        </div>
      </div>
    </div>
  <?php endforeach; ?>
  <!--投稿の繰り返し表示 ここまで-->

  <div id="add"></div>


  <script>
  // グローバルな変数作成
  // var _svp = "";
  var js_post = JSON.parse('<?php echo $php_json; ?>');
  console.log(js_post);

  function initialize() {
      // ループ
      for(let val in js_post) {

          //var fenway = { lat: Number(js_post["latitude"]),lng: Number(js_post["longitude"])};
          var fenway = { lat: Number(js_post[val]["latitude"]),lng: Number(js_post[val]["longitude"])};
          var id = 'pano_list_' + js_post[val]['id'];
          console.log(id);
          console.log(fenway);

          var panorama = new google.maps.StreetViewPanorama(

              document.getElementById(id), {
               position: fenway,
               pov: {
                 heading: Number(js_post[val]["heading"]),
                 pitch: Number(js_post[val]["pitch"])
              }
          });
      }


   }

  </script>

</body>

</html>
