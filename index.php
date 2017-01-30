<?php
	session_start();

	require_once "classes/Conexao.class.php";
	require_once "classes/Usuario.class.php";

	if (isset($_POST['ok'])):

		$login = filter_input(INPUT_POST, "login", FILTER_SANITIZE_MAGIC_QUOTES);
		$senha = filter_input(INPUT_POST, "senha", FILTER_SANITIZE_MAGIC_QUOTES);

		$l = new Login;
		$l->setLogin($login);
		$l->setSenha($senha);

		if($l->logar()):
			header("Location: dashboard.php");
		else:
			$erro = "Erro ao logar";
		endif;
	endif;


	if(isset($_SESSION['logado'])):
		header("Location: dashboard.php");
	else:
?>
<html lang="pt-br">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<title>ProjetoJ</title>
		<!-- Tell the browser to be responsive to screen width -->
		<meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
		<!-- Bootstrap 3.3.6 -->
		<link rel="stylesheet" href="./bootstrap/css/bootstrap.min.css">
		<!-- Font Awesome -->
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css">
		<!-- Ionicons -->
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css">
		<!-- Theme style -->
		<link rel="stylesheet" href="./dist/css/AdminLTE.min.css">
		<!-- iCheck -->
		<link rel="stylesheet" href="./plugins/iCheck/square/blue.css">

		<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
		<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
		<!--[if lt IE 9]>
		<script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
		<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
		<![endif]-->
	</head>
	<body class="hold-transition login-page">
	<div class="login-box">
	  <div class="login-logo">
		<b>Projeto</b>J
	  </div>
	  <!-- /.login-logo -->
	  <div id="login" class="login-box-body">
		<p class="login-box-msg">Faça login</p>

				<div id="login">
				<form action="" method="POST" class="formulario">
					<input class="form-control" type="text" name="login" placeholder="Login">
					<br />
					<input class="form-control" type="password" name="senha" placeholder="Senha">
					<br />
					<input type="submit" name="ok" value="Logar">
				</form>
				<?php echo isset($erro) ? $erro : ''; ?>
			</div>		


		<!--<a href="#">I forgot my password</a><br>
		<a href="register.html" class="text-center">Register a new membership</a>-->

	  </div>
	  <!-- /.login-box-body -->
	</div>
	<!-- /.login-box -->

	<!-- jQuery 2.2.3 -->
	<script src="./plugins/jQuery/jquery-2.2.3.min.js"></script>
	<!-- Bootstrap 3.3.6 -->
	<script src="./bootstrap/js/bootstrap.min.js"></script>
	</body>
<?php
	endif;
?>
</html>
