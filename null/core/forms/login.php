
<div id='loginMain'>
<?  global $CAPTION;
          print "<h1> $CAPTION </h1>";
?>
<form id="loginForm" method="post" action="<?php print call("Auth", "login"); ?>" >
<label for="edtLogin"> Имя пользователя </label>
<input name="login" id="edtLogin" value="" autofocus>
<label for="edtPassword"> Пароль </label>
<input type="password" name="password" id="edtPassword">
<input type="submit" value= "Войти">
</form>
</div>
