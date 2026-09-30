<?php
include_once __DIR__.'/config/BaseUrl.php';
$BaseUrlConfig = new BaseUrl;
$BaseUrlConfig = $BaseUrlConfig->getBaseUrl(true);
$BaseUrl = $BaseUrlConfig->protocol."://$BaseUrlConfig->host"."/auth-login";
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Session Expaired</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        .jumbotron{
            margin-top: 50px;
        }
    </style>
  </head>
  <body>
     <section class="jumbotron text-center">
        <div class="container">
          <img src="./site-under-maintance.png" style="max-width: 800px;" />
            <h2>Website Under Maintenance</h2>
        <!-- <p>Our website has been successfully migrated to a new hosting provider for improved performance and reliability. You are now viewing old version of our website.</p> -->
        <!-- <p>SITE UNDER MAINTENANCE,WILL GET BACK TO YOU IN AN HOUR</p>
        <p>PLEASE COOPERATE ,SORRY FOR THE INCONVINENCE</p> -->
          <h5 style="line-height: 35px;">We're currently performing essential maintenance to improve the performance and security of our website. <br />
             We'll be back in approximately on Monday. We apologize for any inconvenience this may cause.</h5>
        
            <a href="<?php echo $BaseUrl ?>" class="btn btn-primary my-2">Go to Login Page</a>
          </p>
        </div>
      </section>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>