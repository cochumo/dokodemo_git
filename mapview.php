<?php
  session_start();

  //フォームの再送信の確認 回避　あまり効果なし
  header('Expires:-1');
  header('Cache-Control:');
  header('Pragma:');

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

  // 投稿
  if (!empty($_POST['lng'])) {
    $postContent = $db->prepare('INSERT INTO posts SET user_id=?, longitude=?, latitude=?, heading=?, pitch=?, zoom=?, message=?');
    $postContent->execute(array(
      $member['id'],
      $_POST['lng'],
      $_POST['lat'],
      $_POST['head'],
      $_POST['pitch'],
      $_POST['zoom'],
      $_POST['comment']
    ));
    header('Location: index.php'); exit();
  }

  // 検索先をgeoコーディングAPIで取得
  if (!empty($_POST['address'])) {
     $address = $_POST['address'];

     // geoコーディングapiにアクセス
     $json = file_get_contents("https://maps.googleapis.com/maps/api/geocode/json?address=". $address ."&key=AIzaSyDUWlSb2vMOje2PkAwL9ki4o082gNlhrqM");
     $posted_data = json_decode($json);
     // 必要なデータを取り出す
     $posted_lat = $posted_data->results[0]->geometry->location->lat;
     $posted_lng = $posted_data->results[0]->geometry->location->lng;

     //echo '<pre>';
     //var_dump($data->results[0]->geometry->location->lat);
     //echo '</pre>';
     //exit;
  } else {
     $address = "";
  }

  // 行き先をランダムで決めてgeoコーディング
  if (!empty($_POST['area'])){
    //var_dump($_POST['area']);
    //exit;
    $postArea = $_POST['area'];

    // 一度に100件までしか結果を返してくれないのですべての観光地に当たるようにするための処理

    // とりあえず選択したエリアの情報の先頭10件(初期値)を取ってくる
    $xml_dataCount = simplexml_load_file("https://webservice.recruit.co.jp/ab-road/spot/v1/?area=". $postArea ."&key=c9454c39fc9f4bd2");
    // 取得した情報の中にそのエリアの観光地が何箇所あるか出す
    $results_available = $xml_dataCount->results_available;
    // 1度に最大100件しか取得できないのでピッタリ最後まで表示させる時はマイナス100
    $startPoint = $results_available - 100;
    // 1からピッタリ最後まで表示させる数値の乱数を作成
    $startSelect = mt_rand(1,$startPoint);

    //var_dump($startSelect);
    //exit();

    // 先程出した乱数を使ってすべての観光地を表示できるようにした処理
    $xml_data = simplexml_load_file("https://webservice.recruit.co.jp/ab-road/spot/v1/?area=". $postArea . "&start=". $startSelect ."&count=100&key=c9454c39fc9f4bd2");

    // 一度に100件返ってくるようにしたがエリアによっては100以下もあるかもしれないのでspotの数を数えます
    // 処理が遅くなってしまう場合はエリアのスポットの数を確認し、100以下がなければmt_rand(1,100)に変更するといいかも
    $max = count($xml_data->spot);

    //$rand = mt_rand(0,$max-1);

    //$travel_name = $xml_data->spot[$rand]->name;

    //$json_area = file_get_contents("https://maps.googleapis.com/maps/api/geocode/json?address=". $travel_name ."&key=AIzaSyDUWlSb2vMOje2PkAwL9ki4o082gNlhrqM");
    //$rand_data = json_decode($json_area);

    //if (!empty($rand_data->results)){
      //$posted_lat = $rand_data->results[0]->geometry->location->lat;
      //$posted_lng = $rand_data->results[0]->geometry->location->lng;
    //}

    while (empty($rand_data->results)){
      $rand = mt_rand(0,$max-1);
      $travel_name = $xml_data->spot[$rand]->name;
      $json_area = file_get_contents("https://maps.googleapis.com/maps/api/geocode/json?address=". $travel_name ."&key=AIzaSyDUWlSb2vMOje2PkAwL9ki4o082gNlhrqM");
      $rand_data = json_decode($json_area);
    }

    $posted_lat = $rand_data->results[0]->geometry->location->lat;
    $posted_lng = $rand_data->results[0]->geometry->location->lng;

    //echo '<pre>';
    //echo '<br>';
    //echo '<br>';
    //echo '<br>';
    //echo '<br>';
    //var_dump($travel_name);
    //var_dump($rand_data);
    //var_dump($json_area);
    //echo '</pre>';
    //exit;
  }

  if (!empty($_POST['array_select'])) {
    if ($_POST['array_select'] = "japan_best"){
      //var_dump("if文入ったよ！");
      $jpn_best_arr = array(
        "富士山",
        "軍艦島",
        "道頓堀",
        "海地獄",
        "横浜赤レンガパーク",
        "東京ステーションホテル",
        "原爆ドーム",
        "黒部ダム",
        "瀬戸大橋",
        "首都圏外郭放水路",
        "日本科学未来館",
        "ANA機体メンテナンスセンター",
        "富士スピードウェイ",
        "東京国立博物館",
        "佐川醤油蔵"
      );

      $array_count = count($jpn_best_arr);
      $rand = mt_rand(0,$array_count - 1);
      $selected_point = $jpn_best_arr[$rand];
      //var_dump($selected_point);
      $json_array = file_get_contents("https://maps.googleapis.com/maps/api/geocode/json?address=". $selected_point ."&key=AIzaSyDUWlSb2vMOje2PkAwL9ki4o082gNlhrqM");
      $rand_data = json_decode($json_array);

      $posted_lat = $rand_data->results[0]->geometry->location->lat;
      $posted_lng = $rand_data->results[0]->geometry->location->lng;

      // 重複なしランダム
      //$array_count = count($jpn_best_arr);
      //$array_number = range(0,$array_count - 1);
      //$order_number = shuffle($array_number);
      //var_dump($array_count);

      //for ($i = 0 ; $i < $array_count ; $i++){
        //$rand = $order_number[$i];
        //var_dump($rand);
        //exit();
        //$travel_name = $jpn_best_arr[$rand];
        //echo $travel_name;
      //}
    }
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
</head>

<body class="select">

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
            <a class="nav-link js-scroll-trigger" href="#">利用規約</a>
          </li>
          <li class="nav-item">
            <a class="nav-link js-scroll-trigger" href="logout.php">ログアウト</a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <section class="container">
    <div class="flexbox vh-100 row">
      <div class="nav_margin"></div>
      <div class="mapview col-lg-12 col-md-12 col-sm-12">
        <div id="pano"></div>
        <!-- modal -->
        <div>
          <div class="flexbox modal_bt">
            <button type="button" class="btn btn-primary btn-sm control" data-toggle="modal" data-target="#myModal-data2">投稿する</button>
          </div>

          <div class="modal fade" id="myModal-data2" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h4 class="modal-title">コメントを追加</h4>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                  <form method="post" action="">
                    <div class="form-group">
                      <input type="hidden" id="lat" name="lat" value="">
                      <input type="hidden" id="lng" name="lng" value="">
                      <input type="hidden" id="head" name="head" value="">
                      <input type="hidden" id="pitch" name="pitch" value="">
                      <input type="hidden" id="zoom" name="zoom" value="">
                      <input type="text" name="comment" class="form-control" placeholder="コメントを入力してください">
                    </div>
                    <div class="modal-footer">
                      <!-- DBに挿入する -->
                      <input type="submit" class="btn btn-primary" value="投稿する">
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div id="map"></div>
        <div id="res" class="flexbox"></div>
      </div>
    </div>
  </section>

  <script>
    // グローバルな変数作成
    var _svp = "";
    //var js_postAddress = JSON.parse('<?php print_r ($address); ?>');
    var posted_lat = JSON.parse('<?php print_r ($posted_lat); ?>');
    var posted_lng = JSON.parse('<?php print_r ($posted_lng); ?>');
console.log(posted_lat);
console.log(posted_lng);

    var fenway = {
      lat: Number(posted_lat),
      lng: Number(posted_lng)
    };

    // function initialize() {
    //   // Use the Street View service to find a pano ID on Pirrama Rd, outside the
    //   // Google office.
    //   var streetviewService = new google.maps.StreetViewService;
    //   streetviewService.getPanorama(
    //       {location: {lat: -33.867386, lng: 151.195767}},
    //       function(result, status) {
    //         if (status === 'OK') {
    //           outsideGoogle = result;
    //           initPanorama();
    //         }
    //       });
    // }

    function initialize() {
      var fenway = {
        lat: Number(posted_lat),
        lng: Number(posted_lng)
      };

      // 取得してきた緯度経度にストリートビューがあるか検査
      var streetviewService = new google.maps.StreetViewService;
      streetviewService.getPanorama(
        {location: {lat: Number(posted_lat), lng: Number(posted_lng)}},
        function(result, status) {

          // リロード処理
          var reload = function(){
            location.reload();
          };

          if (status === 'OK') {
            // ある場合
            console.log(result);
            console.log(status);
            // 単体テスト
            setTimeout(reload, 10000);
          } else {
            // ない場合(UNKNOWN_ERROR or ZERO_RESULTS)
            console.log('else入ったよ');
            console.log(result);
            console.log(status);
            setTimeout(reload, 0);
          }
      });

      var map = new google.maps.Map(document.getElementById('map'), {
        center: fenway,
        zoom: 14
      });
      var panorama = new google.maps.StreetViewPanorama(
        document.getElementById('pano'), {
          position: fenway,
          pov: {
            heading: 34,
            pitch: 10
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
        "緯度：" + _svp.getPosition().lat() + "<br>" +
        "経度" + _svp.getPosition().lng() + "<br>" +
        "方角:" + pov["heading"] + "<br>" +
        "角度:" + pov["pitch"] + "<br>" +
        "ズーム:" + pov["zoom"];

      // ストリートビューを動かしたときにinputのvalueも更新
      document.getElementById("lat").value = _svp.getPosition().lat();
      document.getElementById("lng").value = _svp.getPosition().lng()
      document.getElementById("head").value = pov["heading"]
      document.getElementById("pitch").value = pov["pitch"]
      document.getElementById("zoom").value = pov["zoom"];
    }


  </script>
  <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCWCuLqhEGIXn3k-oCmaw-bJrcKA08vCIU&callback=initialize">
  </script>

</body>

</html>
