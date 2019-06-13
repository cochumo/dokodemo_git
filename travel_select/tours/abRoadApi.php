<!--<?php
  session_start();
  require('../../dbconnect.php');

  if (!empty($_POST['area'])){
    //var_dump($_POST['area']);
    //exit;
    $postArea = $_POST['area'];
    $xml_data = simplexml_load_file("https://webservice.recruit.co.jp/ab-road/spot/v1/?area=". $postArea ."&key=c9454c39fc9f4bd2");

    $max = count($xml_data->spot);

    $rand = mt_rand(0,$max-1);

    $travel_name = $xml_data->spot[$rand]->name;

    //echo '<pre>';
    //var_dump($travel_name);
    //var_dump($rand);
    //echo '</pre>';
    //exit;
  }
?>-->
<!doctype html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>ストリートビューで旅しよう(仮)</title>
  <!--Bootstrap４に必要なCSSとJavaScriptを読み込み-->
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
  <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
  <link href="https://use.fontawesome.com/releases/v5.0.6/css/all.css" rel="stylesheet">
  <!-- リセット CSS -->
  <link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/3.18.1/build/cssreset/cssreset-min.css">
  <!--style.cssを読み込み-->
  <link href="../../style.css" rel="stylesheet" type="text/css">
  <link rel="shortcut icon" href="../../dokodemo.ico" type="image/x-icon">
</head>

<body class="select">

  <!-- Navigation -->
  <nav class="navbar navbar-expand-lg navbar-light fixed-top" id="mainNav">
    <div class="container">
      <a class="t_logo navbar-brand js-scroll-trigger" href="../../index.php">
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
            <a class="nav-link js-scroll-trigger" href="../../mypage.php">マイページ</a>
          </li>
          <li class="nav-item">
            <a class="nav-link js-scroll-trigger" href="../../terms.php">利用規約</a>
          </li>
          <li class="nav-item">
            <a class="nav-link js-scroll-trigger" href="../../logout.php">ログアウト</a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <section class="overseas_travel container">
    <div class="flexbox vh-100 row">
      <div class="nav_margin">
      </div>
      <!-- <a href="../mapview.php" class="rough_content col-lg-4 col-md-6 col-sm-12">
        <p>アジア 方面</p>
      </a>
      <a href="../mapview.php" class="rough_content col-lg-4 col-md-6 col-sm-12">
        <p>アメリカ 中南米 方面</p>
      </a>
      <a href="../mapview.php" class="rough_content col-lg-4 col-md-6 col-sm-12">
        <p>ヨーロッパ 方面</p>
      </a>
      <a href="../mapview.php" class="rough_content col-lg-4 col-md-6 col-sm-12">
        <p>オセアニア 方面</p>
      </a>
      <a href="../mapview.php" class="rough_content col-lg-4 col-md-6 col-sm-12">
        <p>中東 アフリカ 方面</p>
      </a>
      <a href="../mapview.php" class="rough_content col-lg-4 col-md-6 col-sm-12">
        <p>ハワイ グアム サイパン 方面</p>
      </a> -->
      <form action="../../mapview.php" method="post">
          <button type="text" name="area" value="AAS">アジア</button>
          <button type="text" name="area" value="EUR">ヨーロッパ</button>
          <button type="text" name="area" value="DUS">アメリカ</button>
          <button type="text" name="area" value="FOC">オセアニア</button>
          <button type="text" name="area" value="CAF">アフリカ</button>
      </form>
    </div>
  </section>



</body>

</html>
