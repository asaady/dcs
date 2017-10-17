    <div class="container">

      <?php if (tzVendor\User::isAuthorized()): ?>
    
      <h1>Your are already registered!</h1>

      <form class="ajax" method="post" action="vendor/core/AuthorizationAjaxRequest.php">
          <input type="hidden" name="act" value="logout">
          <div class="form-actions">
              <button class="btn btn-large btn-primary" type="submit">Logout</button>
          </div>
      </form>

      <?php else: ?>

      <form class="form-signin ajax" method="post" action="vendor/core/AuthorizationAjaxRequest.php">
        <div class="main-error alert alert-error hide"></div>

        <h2 class="form-signin-heading">Please sign up</h2>
        <input name="username" type="text" class="input-block-level" placeholder="Username" autofocus>
        <input name="password1" type="password" class="input-block-level" placeholder="Password">
        <input name="password2" type="password" class="input-block-level" placeholder="Confirm password">
        <input type="hidden" name="act" value="register">
        <button class="btn btn-large btn-primary" type="submit">Register</button>
        <div class="alert alert-info" style="margin-top:15px;">
            <p>Already have account? <a href="/">Sign In.</a>
        </div>
      </form>

      <?php endif; ?>

    </div> <!-- /container -->
