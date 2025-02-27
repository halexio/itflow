<?php

if(!file_exists('config.php')){
  header("Location: setup.php");
}

?>

<?php include("config.php"); ?>
<?php include("functions.php"); ?>

<?php 

$ip = get_ip();
$os = get_os();
$browser = get_web_browser();
$device = get_device();

?>

<?php

session_start();

if(isset($_POST['login'])){
  
  $username = mysqli_real_escape_string($mysqli,$_POST['username']);
  $plain_password = $_POST['password'];
  $password = md5($_POST['password']);
  if(!empty($token)){
    $current_code = mysqli_real_escape_string($mysqli,$_POST['current_code']);
  }
  $sql = mysqli_query($mysqli,"SELECT * FROM users WHERE user_email = '$username' AND user_password = '$password'");
  
  if(mysqli_num_rows($sql) == 1){
    $row = mysqli_fetch_array($sql);
    $token = $row['token'];
    $_SESSION['user_id'] = $row['user_id'];
    $_SESSION['user_name'] = $row['user_name'];
    $user_name = $row['user_name'];
    $user_id = $row['user_id'];

    if(empty($token)){
      $_SESSION['logged'] = TRUE;
      mysqli_query($mysqli,"INSERT INTO logs SET log_type = 'Login', log_action = 'Success', log_description = '$ip - $os - $browser - $device', log_created_at = NOW(), log_user_id = $user_id");
         
      header("Location: dashboard.php");
    }else{
      $token_field = "<div class='input-group mb-3'>
            <input type='text' class='form-control' placeholder='Token' name='current_code' autofocus>
            <div class='input-group-append'>
              <div class='input-group-text'>
                <span class='fas fa-key'></span>
              </div>
            </div>
          </div>";

      require_once("rfc6238.php");

      if(TokenAuth6238::verify($token,$current_code)){
        $_SESSION['logged'] = TRUE;
        mysqli_query($mysqli,"INSERT INTO logs SET log_type = 'Login 2FA', log_action = 'Success', log_description = '$ip - $os - $browser - $device', log_created_at = NOW(), log_user_id = $user_id");
        //header("Location: $config_start_page");
        header("Location: dashboard.php");
      }else{
        mysqli_query($mysqli,"INSERT INTO logs SET log_type = 'Login', log_action = '2FA Failed', log_description = '$ip - $os - $browser - $device', log_created_at = NOW(), log_user_id = $user_id");

        $response = "
          <div class='alert alert-primary'>
            Please Enter 2FA Key!
            <button class='close' data-dismiss='alert'>&times;</button>
          </div>
        ";
      }     
    }
  
  }else{
    mysqli_query($mysqli,"INSERT INTO logs SET log_type = 'Login', log_action = 'Failed', log_description = '$username - $ip - $os - $browser - $device', log_created_at = NOW()");

    $response = "
      <div class='alert alert-danger'>
        Incorrect username or password.
        <button class='close' data-dismiss='alert'>&times;</button>
      </div>
    ";
  }
}

?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo $config_app_name; ?> | Login</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="dist/css/adminlte.min.css">
    <!-- Google Font: Source Sans Pro -->
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
  </head>
  <body class="hold-transition login-page">
  <div class="login-box">
    <div class="login-logo">
      <b>IT</b>Flow
    </div>
    
    <!-- /.login-logo -->
    <div class="card">
      <div class="card-body login-card-body">
        <p class="login-box-msg"><?php if(isset($response)) { echo $response; } ?></p>
        <form method="post">
          <div class="input-group mb-3">
            <input type="text" class="form-control" placeholder="Email" name="username" value="<?php if(!empty($token_field)){ echo $username; }?>" required <?php if(empty($token_field)){ echo "autofocus"; } ?> >
            <div class="input-group-append">
              <div class="input-group-text">
                <span class="fas fa-envelope"></span>
              </div>
            </div>
          </div>
          <div class="input-group mb-3">
            <input type="password" class="form-control" placeholder="Password" name="password" value="<?php if(!empty($token_field)){ echo $plain_password; } ?>" required>
            <div class="input-group-append">
              <div class="input-group-text">
                <span class="fas fa-lock"></span>
              </div>
            </div>
          </div>
          <?php if(!empty($token_field)){ echo $token_field; } ?>
          
          <button type="submit" class="btn btn-primary btn-block mb-3" name="login">Sign In</button>
        
        </form>

      </div>
      <!-- /.login-card-body -->
    </div>
  </div>
  <!-- /.login-box -->

  <!-- jQuery -->
  <script src="plugins/jquery/jquery.min.js"></script>
  <!-- Bootstrap 4 -->
  <script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
  <!-- AdminLTE App -->
  <script src="dist/js/adminlte.min.js"></script>

  <script src="plugins/Show-Hide-Passwords-Bootstrap-4/bootstrap-show-password.min.js"></script>

  <!-- Prevents resubmit on refresh or back -->
  <script>
  
    if(window.history.replaceState){
      window.history.replaceState(null,null,window.location.href);
    }

  </script>

  </body>
</html>
