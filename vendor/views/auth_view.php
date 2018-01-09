    <?php if (dcs\vendor\core\User::isAuthorized()): ?>

      <h1>Your are welcome!</h1>
      <input type="hidden" name="act" value="logout">
      <button id="submit" type="button" class="btn btn-info form-control-sm">Выход</button>    

    <?php else: ?>

      <div class="main-error alert alert-error hide"></div>
      <h2 class="form-signin-heading">Пожалуйста, авторизуйтесь</h2>
      <input class = "form-control-sm" name="username" type="text" class="input-block-level" placeholder="Логин" autofocus>
      <input class = "form-control-sm" name="password" type="password" class="input-block-level" placeholder="Пароль">
      <input class = "form-checkbox" name="remember-me" type="checkbox" value="remember-me" id="remember" checked>
      <label class = "label-control" for = "remember">Запомнить меня</label>
      <input type="hidden" name="act" value="login">
      <button id="submit" type="button" class="btn btn-info form-control-sm">Войти</button>    

    <?php endif; ?>

