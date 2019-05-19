<?php

	require('../dbconnect.php');

	session_start();

	//フォームが送信されてこのページが表示されたか確認
	if (!empty($_POST)) {


		// エラー項目の確認
		if ($_POST['name'] == '') {
			$error['name'] = 'blank';
		}
		if ($_POST['email'] == '') {
			$error['email'] = 'blank';
		}
		if (strlen($_POST['password']) < 4) {
			$error['password'] = 'length';
		}
		if ($_POST['password'] == '') {
			$error['password'] ='blank';
		}


		if (empty($error)){
        $member = $db->prepare('SELECT * FROM users WHERE email=? OR name=?');
        $member->execute(array(
					$_POST['email'],
					$_POST['name']
				));
        $record = $member->fetch();
        // if ($record['cnt'] > 0){
        // 	$error['email'] = 'duplicate';
      	// }

				if ($_POST['email'] === $record['email']) {
					$error['email'] = 'duplicate';
				}

				if ($_POST['name'] === $record['name']) {
					$error['name'] = 'duplicate';
				}
	   }

		// もしエラーが起きなかったら
		if (empty($error)) {
			// セッションに$_POSTを代入してcheck.phpへ
			$_SESSION['join'] = $_POST;
			header('Location: check.php');
			exit();
		}
	}

	// 書き直し
    if (isset($_REQUEST['action']) == 'rewrite'){
    $_POST = $_SESSION['join'];
    $error['rewrite'] = true;
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
	<link rel="shortcut icon" href="../dokodemo.ico" type="image/x-icon">
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
      <form class="formArea col-xl-5 col-lg-5 col-md-7 col-sm-10" action="" method="post" enctype="multipart/form-data">
        <div class="formArea_text">
          <h2>アカウント作成</h2>
          <input type="text" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? "", ENT_QUOTES); ?>" placeholder="ユーザー名" autocomplete="off" maxlength="255">
          <?php if (isset($error['name']) && $error['name'] == 'blank'): ?>
						<p class="error">※ ユーザー名を入力してください</p>
		　			<?php endif; ?>
					<?php if ($error['name'] == 'duplicate'): ?>
						<p class="error">※ 入力されたユーザー名は既に登録されています</p>
					<?php endif; ?>
          <input type="text" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? "", ENT_QUOTES); ?>" placeholder="メールアドレス" autocomplete="off" maxlength="255">
          <?php if (isset($error['email'])): ?>
            <?php if ($error['email'] == 'blank'): ?>
              <p class="error">※ メールアドレスを入力してください</p>
		    		<?php endif; ?>
		    		<?php if ($error['email'] == 'duplicate'): ?>
		      		<p class="error">※ 入力されたメールアドレスは既に登録されています</p>
		    		<?php endif; ?>
		  		<?php endif; ?>
          <input type="password" name="password" value="" placeholder="パスワード" autocomplete="off" maxlength="20">
          <?php if (isset($error['password'])): ?>
            <?php if ($error['password'] == 'blank'): ?>
			  <p class="error">※ パスワードを入力してください</p>
			<?php endif; ?>
			<?php if ($error['password'] == 'length'): ?>
			  <p class="error">※ パスワードは4文字以上で入力してください</p>
		    <?php endif; ?>
		　<?php endif; ?>
		　<?php if (!empty($error)): ?>
			<p class="error">※ もう一度入力してください</p>
		　<?php endif; ?>
          <h5>入力事項を確認の上、次に進んで下さい</h5>
        </div>
        <div class="formArea_submit">
          <input type="submit" name="" value="入力内容を確認する">
        </div>
        <div class="login_move">
          <p>すでにアカウントをお持ちですか？</p>
          <a href="../login.php">ログイン</a>
        </div>
      </form>
    </div>
  </section>

</body>

</html>
