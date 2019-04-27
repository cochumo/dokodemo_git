<?php

// sessionをスタートする

session_start();

// requireファンクションでdbconnect.phpを呼び出す

require('dbconnect.php');

if (isset($_COOKIE['email']) && $_COOKIE['email'] != '') {
    $_POST['email'] = $_COOKIE['email'];
    $_POST['password'] = $_COOKIE['password'];
    $_POST['save'] = 'on';
}



// ログインの処理
if (!empty($_POST)) {
  if ($_POST['action'] == 'normal'){
  	if ($_POST['email'] != '' && $_POST['password'] != '') {
  		$login = $db->prepare('SELECT * FROM users WHERE email=? AND password=?');
  		$login->execute(array(
  		$_POST['email'],
  		sha1($_POST['password'])
  		));

  		$member = $login->fetch();
  		if ($member) {
  			// ログイン成功
  			$_SESSION['id'] = $member['id'];
  			$_SESSION['time'] = time();

  			// ログイン情報を記録する
  			if ($_POST['save'] == 'on') {
  			setcookie('email', $_POST['email'], time()+60*60*24*14);
  			setcookie('password', $_POST['password'], time()+60*60*24*14);
  			}

  			header('Location: index.php'); exit();
  			} else {
  				$error['login'] = 'failed';
  			}
  		} else {
  		$error['login'] = 'blank';
  	}
  }
  if ($_POST['action'] == 'guest'){
    $_SESSION['id'] = 1;
    $_SESSION['time'] = time();
    header('Location: index.php'); exit();
  }
}
?>

<!doctype html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>ログイン DoKoDeMo - ストリートビューで旅しよう</title>
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

<body class="join">

  <!-- Navigation -->
  <nav class="navbar navbar-expand-lg navbar-light fixed-top" id="mainNav">
    <div class="container">
      <a class="t_logo navbar-brand js-scroll-trigger" href="LP/index.html">
        <i class="fas fa-street-view"></i> DoKoDeMo
      </a>
      <button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarResponsive">
        <ul class="navbar-nav ml-auto">
          <li class="nav-item">
            <a class="nav-link js-scroll-trigger" href="LP/index.html#about">About</a>
          </li>
          <li class="nav-item">
            <a class="nav-link js-scroll-trigger" href="LP/index.html#services">Services</a>
          </li>
          <li class="nav-item">
            <a class="nav-link js-scroll-trigger" href="LP/index.html#introduction">Introduction</a>
          </li>
          <li class="nav-item">
            <a class="nav-link js-scroll-trigger" href="LP/index.html#contact">Contact</a>
          </li>
        </ul>
      </div>
    </div>
  </nav>


  <section class="join_content container">
    <div class="flexbox row vh-100">
      <form class="formArea col-xl-4 col-lg-5 col-md-7 col-sm-10" action="" method="post">
        <div class="formArea_text">
          <h2>ログイン</h2>
          <input type="text" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? "", ENT_QUOTES); ?>" placeholder="メールアドレス" autocomplete="off" maxlength="255">
          <input type="password" name="password" value="<?php echo htmlspecialchars($_POST['password'] ?? "", ENT_QUOTES); ?>" placeholder="パスワード" autocomplete="off" maxlength="20">
          <?php if (isset($error['login'])): ?>
            <?php if ($error['login'] == 'blank'): ?>
              <p class="error">* メールアドレスとパスワードをご記入ください</p>
            <?php endif; ?>
            <?php if ($error['login'] == 'failed'): ?>
              <p class="error">* ログインに失敗しました。正しくご記入ください。</p>
            <?php endif; ?>
          <?php endif; ?>
          <h5 class="login_h5">入力事項を確認の上、次に進んで下さい</h5>
        </div>
        <div class="login_move">
          <div class="loginbox">
            <input id="save" type="checkbox" name="save" value="on">
            <p>自動的にログインする</p>
          </div>
          <button type="submit" name="action" value="normal">ログインする</button>
          <button type="submit" name="action" value="guest" class="mt-2">ゲストログインする</button>
          <p>会員登録がお済みではないですか？</p>
          <a href="join/join_index.php">会員登録</a>
        </div>
      </form>
    </div>
  </section>

</body>

</html>
