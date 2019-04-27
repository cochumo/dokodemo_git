<?php

    session_start();
    
    require('../dbconnect.php');

    if (!isset($_SESSION['join'])) {
        header('Location: join_index.php');
        exit();
    }
    
    if (!empty($_POST)) {
    	// 登録処理をする
    	$statement = $db->prepare('INSERT INTO users SET name=?, email=?, password=?, created_at=NOW()');
    	$statement->execute(array(
    			$_SESSION['join']['name'],
    			$_SESSION['join']['email'],
    			sha1($_SESSION['join']['password'])
    		));
    		
    		//var_dump($_SESSION['join']['name']);
    		//var_dump($_SESSION['join']['email']);
    		//var_dump(sha1($_SESSION['join']['password']));
    		//exit();
    		
		unset($_SESSION['join']);
		header('Location: thanks.php');
		exit();
	}

?>

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
  <link href="../style.css" rel="stylesheet" type="text/css">
</head>

<body class="join">

  <!-- Navigation -->
  <nav class="navbar navbar-expand-lg navbar-light fixed-top" id="mainNav">
    <div class="container">
      <a class="t_logo navbar-brand js-scroll-trigger" href="#page-top">
        <i class="fas fa-street-view"></i> DoKoDeMo
      </a>
      <button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarResponsive">
        <ul class="navbar-nav ml-auto">
          <li class="nav-item">
            <a class="nav-link js-scroll-trigger" href="#">About</a>
          </li>
          <li class="nav-item">
            <a class="nav-link js-scroll-trigger" href="#">Services</a>
          </li>
          <li class="nav-item">
            <a class="nav-link js-scroll-trigger" href="#">Introduction</a>
          </li>
          <li class="nav-item">
            <a class="nav-link js-scroll-trigger" href="#">Contact</a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <section class="join_content container">
    <div class="flexbox row vh-100">
      <form class="formArea col-xl-4 col-lg-5 col-md-7 col-sm-10" action="" method="post">
        <div class="formArea_text">
          <input type="hidden" name="action" value="submit">
          <h2>アカウント作成</h2>
          <h3>記載情報の確認</h3>
          <h4>ユーザー名</h4>
          <p><?php echo htmlspecialchars($_SESSION['join']['name'], ENT_QUOTES,'UTF-8'); ?></p>
          <h4>メールアドレス</h4>
          <p><?php echo htmlspecialchars($_SESSION['join']['email'], ENT_QUOTES,'UTF-8'); ?></p>
          <h4>パスワード</h4>
          <p>[※表示されません]</p>
          <h5>入力事項を確認の上、登録して下さい</h5>
        </div>
        <div class="formArea_submit">
          <input type="submit" name="" value="登録する">
        </div>
        <div class="login_rewrite">
          <a href="join_index.php?action=rewrite">書き直す</a>
        </div>
      </form>
    </div>
  </section>

</body>

</html>
