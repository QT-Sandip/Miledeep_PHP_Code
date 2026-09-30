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
  <title>Session Expired</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    .jumbotron {
      margin-top: 50px;
    }
  </style>
</head>

<body>
  <section class="jumbotron text-center">
    <div class="container">
      <h2>:: Session Expired ::</h2>
      <!-- <p>Our website has been successfully migrated to a new hosting provider for improved performance and reliability. You are now viewing old version of our website.</p> -->
      <p>Your Session Expired Please Login Again to Continue</p>
      <p>If you encounter any issues or have questions, please don't hesitate to contact us.</p>

      <a href="<?php echo $BaseUrl ?>" class="btn btn-primary my-2">Go to login page</a>
      </p>
    </div>
  </section>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>