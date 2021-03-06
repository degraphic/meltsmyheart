<h1>Login to your account</h1>

<div class="clearfix">
  <?php getTemplate()->display('partials/paragraphRight.php'); ?>

  <p>
    <form method="post" action="/login" id="loginForm">
      <div data-role="fieldcontain">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required="required" tabindex="1">
      </div>

      <div data-role="fieldcontain">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required="required" tabindex="2">
      </div>

      <input type="hidden" name="r" value="<?php echo $r; ?>">
      <button type="submit" tabindex="3" data-theme="b"><div>Login</div></button>
      <br>
      <em>Need an account? <a href="/join?r=<?php echo $r; ?>" tabindex="3">Join here</a>.</em>
      <br>
      <em>Forgot your password? <a href="/forgot" tabindex="4">Reset it here</a>.</em>
    </form>
  </p>
</div>
