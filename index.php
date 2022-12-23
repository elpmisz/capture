<?php
ini_set("display_errors", "1");
ini_set("display_startup_errors", "1");
error_reporting(E_ALL);

require_once(__DIR__ . "/vendor/autoload.php");

use Spatie\Browsershot\Browsershot;

$token = (isset($_POST["token"]) ? $_POST["token"] : "");
$text = (isset($_POST["text"]) ? $_POST["text"] : "");
$url = (isset($_POST["url"]) ? $_POST["url"] : "");

if (!empty($url)) {
  $data = Browsershot::url("{$url}")
    ->windowSize(1280, 720)
    ->setDelay(6000)
    ->base64Screenshot();

  $name = md5(microtime(true)) . ".png";
  $path = "images/{$name}";
  $image = "data:image/png;base64, {$data}";
  $source = fopen($image, "r");
  $destination = fopen($path, "w");
  stream_copy_to_stream($source, $destination);
  fclose($source);
  fclose($destination);

  if (!empty($token)) {
    $fullpath = dirname(__FILE__) . "/images/{$name}";
    $file = curl_file_create($fullpath);

    $arr = [
      "message" => $text,
      "imageFile" => $file,
    ];

    line_notify($arr, $token);
  }
}

function line_notify($res, $token)
{
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, "https://notify-api.line.me/api/notify");
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $res);
  $headers = ["Content-type: multipart/form-data", "Authorization: Bearer {$token}",];
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  $result = curl_exec($ch);

  return $result;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="/vendor/twbs/bootstrap/dist/css/bootstrap.min.css">
  <title>Document</title>
</head>

<body>
  <div class="container mt-5">
    <div class="row">
      <div class="col-12">
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) ?>" method="post" class="needs-validation" novalidate>
          <div class="row">
            <div class="col-12">
              <div class="input-group input-group-sm mb-3">
                <span class="input-group-text">TOKEN</span>
                <input type="text" class="form-control form-control-sm" name="token" required>
                <div class="invalid-feedback">
                  Please fill out this field.
                </div>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-12">
              <div class="input-group input-group-sm mb-3">
                <span class="input-group-text">TEXT</span>
                <input type="text" class="form-control form-control-sm" name="text" required>
                <div class="invalid-feedback">
                  Please fill out this field.
                </div>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-12">
              <div class="input-group input-group-sm mb-3">
                <span class="input-group-text">URL</span>
                <input type="text" class="form-control form-control-sm" name="url" required>
                <div class="invalid-feedback">
                  Please fill out this field.
                </div>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-sm-6 col-xl-3 mb-2">
              <button type="submit" class="btn btn-success btn-sm w-100">
                <i class="fas fa-check pr-2"></i>ยืนยัน
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <?php if (isset($data) && !empty($data)) : ?>
      <div class="row">
        <img src="data:image/png;base64, <?php echo $data ?>">
      </div>
    <?php endif; ?>
  </div>

  <script src="/vendor/components/jquery/jquery.min.js"></script>
  <script src="/vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    (() => {
      'use strict'

      const forms = document.querySelectorAll('.needs-validation')

      Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
          if (!form.checkValidity()) {
            event.preventDefault()
            event.stopPropagation()
          }

          form.classList.add('was-validated')
        }, false)
      })
    })()

    $(document).on("click", "button[type='submit']", function() {
      $("img").prop("src", "");
    })
  </script>
</body>

</html>