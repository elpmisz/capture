<?php
ini_set("display_errors", "1");
ini_set("display_startup_errors", "1");
error_reporting(E_ALL);

require_once(__DIR__ . "/vendor/autoload.php");

use Spatie\Browsershot\Browsershot;

$token = (isset($_POST["token"]) ? $_POST["token"] : "");
$text = (isset($_POST["text"]) ? $_POST["text"] : "");
$url = (isset($_POST["url"]) ? $_POST["url"] : "");
$type = (isset($_POST["type"]) ? $_POST["type"] : "");
$width = (isset($_POST["width"]) ? $_POST["width"] : 1280);
$height = (isset($_POST["height"]) ? $_POST["height"] : 720);

if (!empty($url)) {
  if (intval($type) === 1) {
    $data = Browsershot::url("{$url}")
      ->fullPage()
      ->setDelay(6000)
      ->base64Screenshot();
  } else {
    $data = Browsershot::url("{$url}")
      ->windowSize($width, $height)
      ->setDelay(6000)
      ->base64Screenshot();
  }


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

    echo line_notify($arr, $token);
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
  <title>Capture</title>
  <style>
    @font-face {
      font-family: "K2D";
      font-style: normal;
      font-weight: normal;
      src: url("/fonts/K2D-Medium.woff") format("woff");
    }

    body {
      font-family: "K2D";
      background: #f6f9ff;
      color: #444444;
    }
  </style>
</head>

<body>
  <div class="container mt-5">
    <div class="row">
      <div class="col-12">
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) ?>" method="post" class="needs-validation" novalidate>
          <div class="row">
            <div class="col-10">
              <div class="input-group input-group-sm mb-3">
                <span class="input-group-text">TOKEN</span>
                <input type="text" class="form-control form-control-sm" name="token" value="XyaQ9gT5s5XxeGW7e8o3sX0A51VPL6mBmWynnfgsdsO" required>
                <div class="invalid-feedback">
                  Please fill out this field.
                </div>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-8">
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
            <div class="col-6">
              <label class="pe-3">Image Size</label>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="type" id="fullscreen" value="1" required>
                <label class="form-check-label" for="fullscreen">Fullscreen</label>
              </div>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="type" id="custom" value="2" required>
                <label class="form-check-label" for="custom">Custom Size</label>
              </div>
            </div>
          </div>
          <div class="row div_custom">
            <div class="col-6">
              <div class="input-group input-group-sm mb-3">
                <span class="input-group-text">WIDTH</span>
                <input type="text" class="form-control form-control-sm" name="width" placeholder="1280">
                <span class="input-group-text">HEIGHT</span>
                <input type="text" class="form-control form-control-sm" name="height" placeholder="720">
                <div class="invalid-feedback">
                  Please fill out this field.
                </div>
              </div>
            </div>
          </div>

          <div class="row py-3">
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
      "use strict"

      const forms = document.querySelectorAll(".needs-validation")

      Array.from(forms).forEach(form => {
        form.addEventListener("submit", event => {
          if (!form.checkValidity()) {
            event.preventDefault()
            event.stopPropagation()
          }

          form.classList.add("was-validated")
        }, false)
      })
    })()

    $(document).on("click", "button[type='submit']", function() {
      $("img").prop("src", "");
    })

    $(".div_custom").hide();
    $(document).on("click", "input[name='type']", function() {
      let type = parseInt($(this).val());
      if (type === 2) {
        $(".div_custom").show();
        $("input[name='width'],input[name='height']").prop("required", true);
      } else {
        $(".div_custom").hide();
        $("input[name='width'],input[name='height']").prop("required", false);
      }
    });
  </script>
</body>

</html>