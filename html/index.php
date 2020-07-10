<?php

echo <<<EOT
<!doctype html>
<html lang="en">
<!-- bootstrap boilerplate from website -->
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">

  </head>
  <body>
    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous"></script>
   <!-- Custom css -->
   <link rel="stylesheet" href="css/product_demo.css">

   <!-- Placeholders for some variables -->
   <script>
   let categories = [];
   let categoryMap = {};
   </script>

EOT;

# Read in configuration
$config_json = file_get_contents("../config/config.json");
$config = json_decode($config_json, true);

# Pass some non-secret settings to Javascript
printf("<script>const TIMEOUT_MSG_INFO_MS = %d; const TIMEOUT_MSG_ERROR_MS = %d;</script>", $config['TIMEOUT_MSG_INFO_MS'], $config['TIMEOUT_MSG_ERROR_MS']);

?>
<div class="container-fluid">
  <div class="row">
    <div class="col-xs-12 page_header">
      All Products, Grouped by Category
    </div>
  </div>

  <!-- error caption -->
  <div class="row">
    <div class="col-xs-12">
      <div class="alert alert-danger hidden" role="alert" id="msg_error"></div>
    </div>
  </div>

  <!-- info caption -->
  <div class="row">
    <div class="col-xs-12">
      <div class="alert alert-primary hidden" role="alert" id="msg_info"></div>
    </div>
  </div>

  <!-- main product window -->
  <div class="hidden" id="mainArea"></div>

</div>
<script>

function showMsg(message, timeoutMs) {
    $('#msg_info').text(message);
    $('#msg_info').show();

    if (timeoutMs > 0) {
        setTimeout(function() {
            $('#msg_info').hide();
        }, timeoutMs);
    }
}

function showErr(message, timeoutMs) {
    $('#msg_error').text(message);
    $('#msg_error').show();

    if (timeoutMs > 0) {
        setTimeout(function() {
            $('#msg_error').hide();
        }, timeoutMs);
    }
}

async function reloadProducts() {
    showMsg('Reloading Product data', TIMEOUT_MSG_INFO_MS);

    let uri = 'fetch_products.php';

    try {
    let response = await fetch(uri);
    // success

    if (response.ok) {
        showMsg('Received Product data', TIMEOUT_MSG_INFO_MS);
        response.json().then(function(data) {
            parseProducts(data.response);
        });
    } else {
        showErr('Error while fetching products', TIMEOUT_MSG_ERROR_MS);
    }

    } catch (e) {
        showErr(err, TIMEOUT_MSG_ERROR_MS);
    }
}

function parseProducts(products) {
    categoryMap = {};
    products.forEach(function(item) {

        let categoryName = item.category;
        if (categoryName === null || categoryName ==='') {
            categoryName = 'Everything Else';
        }

        if (!(categoryName in categoryMap)) {
            categoryMap[categoryName] = [];
        }

        categoryMap[categoryName].push(item);
    });

    categories = Object.keys(categoryMap).sort();

    // Clear out the display area
    $('#mainArea').addClass('hidden');
    $('#mainArea').empty();

    // Now show them
    categories.forEach(function(item) {
        let childCount = categoryMap[item].length;
        showOneCategory(item, categoryMap);
    });

    $('#mainArea').removeClass('hidden');
}

function composeOneRow(data, rowIndex, totalItems) {
    let result = '<div class="row product_row">';

    for (let i = rowIndex * 4; i < (rowIndex + 1)  * 4; i++) {
        if (i < totalItems) {
            let images = data[i].images;
            if (images.length == 0) {
                // No image
                result += '<div class="col-xs-3 single_item"><figure><img class="item_photo" src="img/no_image.png"><figcaption>ID: ' + data[i].product_id + '</figcaption></figure></div>';
            } else {
                // Has image
                // Arbitrarily choose the first image.  TODO: Revisit
                result += '<div class="col-xs-3 single_item"><figure><img class="item_photo" src="' + images[0].img+ '"><figcaption>' + data[i].description + '</figcaption></figure></div>';
            }
        }
    }

    result += '</div>';

    return result;
}

function showOneCategory(oneCategory, categoryMap) {
    $('#mainArea').append('<div class="row"><div class="col-xs-12 category_header">' + oneCategory + '</div></div>');

    let itemCount = categoryMap[oneCategory].length;
    let itemRows = Math.floor(itemCount / 4);;

    if ((itemCount % 4) !== 0) {
        itemRows++;
    }

    for (let i = 0; i < itemRows; i++) {
        $('#mainArea').append(composeOneRow(categoryMap[oneCategory], i, itemCount));
    }
}

<!-- execute on ready -->
$(document).ready(function(){
    reloadProducts(); /* We could put this on setInterval */
});
</script>
  </body>
</html>
