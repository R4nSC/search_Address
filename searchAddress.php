<?php
/*接続*/
$link = mysqli_connect("localhost", "my_user", "my_password", "my_db");
if (!$link) {
    echo "Error: Unable to connect to MySQL." . PHP_EOL;
    echo nl2br("\n");
    echo "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
    echo nl2br("\n");
    echo "Debugging error: " . mysqli_connect_error() . PHP_EOL;
    echo nl2br("\n");
    exit;
}
/*
echo "Success!" . PHP_EOL;
echo nl2br("\n");
echo "Host information: " . mysqli_get_host_info($link) . PHP_EOL;
echo nl2br("\n");
*/
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>検索結果</title>
    <link rel="stylesheet" href="./address.css" type="text/css">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- BootstrapのCSS読み込み -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <!-- jQuery読み込み -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <!-- BootstrapのJS読み込み -->
    <script src="js/bootstrap.min.js"></script>
</head>
<body>
    <div class = "container">
    <div class = "row">

        <div class="col-sm-6">
            <div class="title">
                <p>Address<br>Search</p>
            </div>
        </div>

        <div class="col-sm-6">
            <form action="address1.php" method="post" class="form-inline">
                <div class = "form-group">
                    <?php
                    if (isset($_REQUEST['address'])) {
                        echo "<div class = \"addressSearch1\">";
                        //echo "<label for=\"address-text\">Address:</label>";
                        echo "<input type='text' class='form-control' id='address-text' name='address' value='" . $_REQUEST['address'] . "' />";
                        echo "<input type=\"submit\" value=\"Send\" class=\"btn btn-defalt\"/>";
                        echo "</div>";
                    } else {
                        echo "<div class = \"addressSearch0\">";
                        //echo "<label for=\"address-text\">Address:</label>";
                        echo "<input type='text' class='form-control' id='address-text' name='address' />";
                        echo "<input type=\"submit\" value=\"Send\" class=\"btn btn-defalt\"/>";
                        echo "</div>";
                    }
                    ?>
                </div>
            </form>
            <div class = "result">
                <?php
                if (isset($_REQUEST['address'])) {
                    if (isset($_REQUEST['page'])) {
                        $page = $_REQUEST['page'];
                    } else {
                        $page = 1;
                    }
                    $search_address = $_REQUEST['address'];
                    $flag = 0;
                   
                    if (ctype_digit($search_address)) {//半角数字かどうか
                        $search_address = str_replace('日本、', '', $search_address);
                        $sql_query = "SELECT COUNT(*) FROM zipALL where zip like '%" . $search_address . "%';";
                        $flag = 1;
                        $search_counts = mysqli_fetch_assoc(mysqli_query($link, $sql_query));
                        echo "<p>[ " . $search_address . " ]の結果一覧</p>" . PHP_EOL;
                    } else {
                        if (preg_match("/^[一-龠]+$/u", $search_address)) {//漢字かどうか
                            $search_address = str_replace('日本、', '', $search_address);
                            $sql_query = "SELECT COUNT(*) FROM zipALL where CONCAT(addr1,addr2,addr3) like '%" . $search_address . "%';";
                            $flag = 2;
                            $search_counts = mysqli_fetch_assoc(mysqli_query($link, $sql_query));
                            echo "<p>[ " . $search_address . " ]の結果一覧</p>" . PHP_EOL;
                        } elseif (preg_match("/^[ァ-ヶー]+$/u", $search_address)) {//カタカナかどうか
                            $search_address = str_replace('日本、', '', $search_address);
                            $sql_query = "SELECT COUNT(*) FROM zipALL where CONCAT(kana1,kana2,kana3) like '%" . $search_address . "%';";
                            $flag = 3;
                            $search_counts = mysqli_fetch_assoc(mysqli_query($link, $sql_query));
                            echo "<p>[ " . $search_address . " ]の結果一覧</p>" . PHP_EOL;
                        }
                    }

                    //$search_address = str_replace('日本、', '', $search_address);
                    //$sql_query = "SELECT COUNT(*) FROM zipALL where (CONCAT(addr1,addr2,addr3) like '%" . $search_address . "%') OR (CONCAT(kana1,kana2,kana3) like '%" . $search_address . "%') OR (zip like '%" . $search_address . "%');";
                    //$search_counts = mysqli_fetch_assoc(mysqli_query($link, $sql_query));
                    //echo "<p>[ " . $search_address . " ]の結果一覧</p>" . PHP_EOL;
                    if ($flag != 0 && $search_counts["COUNT(*)"] != "0") {
                        $address_array = array();
                        $zip_array = array();

                        if ($flag == 1) {
                            $sql_query = "SELECT * FROM zipALL where zip like '%" . $search_address . "%' LIMIT " . (($page-1)*5) . ", 5;";
                        } elseif ($flag == 2) {
                            $sql_query = "SELECT * FROM zipALL where CONCAT(addr1,addr2,addr3) like '%" . $search_address . "%' LIMIT " . (($page-1)*5) . ", 5;";
                        } else {
                            $sql_query = "SELECT * FROM zipALL where CONCAT(kana1,kana2,kana3) like '%" . $search_address . "%' LIMIT " . (($page-1)*5) . ", 5;";
                        }

                        //$sql_query = "SELECT * FROM zipALL where (CONCAT(addr1,addr2,addr3) like '%" . $search_address . "%') OR (CONCAT(kana1,kana2,kana3) like '%" . $search_address . "%') OR (zip like '%" . $search_address . "%') LIMIT " . (($page-1)*5) . ", 5;";
                        $search_results = mysqli_query($link, $sql_query);
                        echo "<table id=\"result_table\" class=\"table table-striped\">" . PHP_EOL;
                        echo "<tr><th>住所</th><th>郵便番号</th></tr>";
                        $count = 0;
                        while ($row = mysqli_fetch_assoc($search_results)) {
                            $address_array[$count] = $row["addr1"] . $row["addr2"] . $row["addr3"];
                            $zip_array[$count] = substr($row["zip"], 0, 3) . "-" . substr($row["zip"], 3, 4);
                            echo "<tr><td>" . $row["addr1"] . $row["addr2"] . $row["addr3"] . "</td><td>" . substr($row["zip"], 0, 3) . "-" . substr($row["zip"], 3, 4) . "</td></tr>" . PHP_EOL;
                            $count++;
                        }
                        echo "</table>" . PHP_EOL;
                        echo "<div>" . PHP_EOL;
                        $page_counts = (int)((int)$search_counts["COUNT(*)"]/5);
                        if ((int)((int)$search_counts["COUNT(*)"]%5) == 0) {
                            $page_counts--;
                        }

                        if (1<= $page && $page <= 4) {
                            for ($i = 0; $i <= $page_counts; $i++) {
                                if ($i+1 == $page) {
                                    echo "<span>" . ($page) . "</span>" . PHP_EOL;
                                } else {
                                    echo " <a href='./address1.php?page=" . ($i+1) . "&address=" . $_REQUEST['address'] . "'>" . ($i+1) . "</a> " . PHP_EOL;
                                }
                                if ($i == 4) {
                                    $i = $page_counts - 1;
                                    echo " ... " . PHP_EOL;
                                }
                            }
                        } elseif ($page_counts-2 <= $page && $page <= $page_counts+1) {
                            for ($i = 0; $i <= $page_counts; $i++) {
                                if ($i+1 == $page) {
                                    echo "<span>" . ($page) . "</span>" . PHP_EOL;
                                } else {
                                    echo " <a href='./address1.php?page=" . ($i+1) . "&address=" . $_REQUEST['address'] . "'>" . ($i+1) . "</a> " . PHP_EOL;
                                }
                                if ($i == 0) {
                                    $i = $page_counts - 5;
                                    echo " ... " . PHP_EOL;
                                }
                            }
                        } else {
                            for ($i = 0; $i <= $page_counts; $i++) {
                                if ($i+1 == $page) {
                                    echo "<span>" . ($page) . "</span>" . PHP_EOL;
                                } else {
                                    echo " <a href='./address1.php?page=" . ($i+1) . "&address=" . $_REQUEST['address'] . "'>" . ($i+1) . "</a> " . PHP_EOL;
                                }
                                if ($i == 0) {
                                    $i = $page - 3;
                                    echo " ... " . PHP_EOL;
                                }
                                if ($i == $page) {
                                    $i = $page_counts - 1;
                                    echo " ... " . PHP_EOL;
                                }
                            }
                        }
                        echo " (全". $search_counts["COUNT(*)"] . "件)</div>" . PHP_EOL;
                        echo "<div id='map' style='width: 100%; height:35vh;'></div>";
                    } elseif ($flag != 0) {
                        echo "1件も見つかりませんでした。" . PHP_EOL;
                    } else {
                        echo "<p>検索単語は[郵便番号(半角数字)]もしくは[地名(漢字)]もしくは[地名(カタカナ)]で入力してください</p>";
                    }
                }
                ?>

                <script>
                    var map;
                    var marker = [];
                    var infoWindow = [];
                    var address_data;
                    var zip_data;
                    var geocoder;
                    var autocomplete;

                    function init(){
                        if(document.getElementById("result_table") != null) initMap();
                        initAutocomplete();
                    }

                    function initMap(){
                        address_data = JSON.parse('<?php if (isset($address_array)) {
                    echo json_encode($address_array);
                } ?>');
                        zip_data = JSON.parse('<?php if (isset($zip_array)) {
                    echo json_encode($zip_array);
                } ?>');
                        geocoder = new google.maps.Geocoder();
                        geocoder.geocode({
                            'address': address_data[0]
                        }, function(results,status) {
                            if(status === google.maps.GeocoderStatus.OK){
                                console.log(results);
                                map = new google.maps.Map(document.getElementById('map'),{
                                    center: results[0].geometry.location,
                                    zoom: 11
                                });
                                marker[0] = new google.maps.Marker({
                                    position: results[0].geometry.location,
                                    map: map
                                });
                                infoWindow[0] = new google.maps.InfoWindow({
                                    content : '<div class="sample">'+ zip_data[0] + ' ' + address_data[0] + '</div>'
                                });
                                marker[0].addListener('click', function() {
                                    infoWindow[0].open(map,marker[0]);
                                });
                            }
                        });
                        for(var i=1;i<zip_data.length;i++){
                            markAddress(i,geocoder);
                        }
                    }

                    function markAddress(num,geocoder){
                        geocoder.geocode({
                            'address': address_data[num]
                        }, function(results,status) {
                            if(status === google.maps.GeocoderStatus.OK){
                                console.log(results);
                                marker[num] = new google.maps.Marker({
                                    position: results[0].geometry.location,
                                    map: map
                                });
                                infoWindow[num] = new google.maps.InfoWindow({
                                    content : '<div class="sample">'+ zip_data[num] + ' ' + address_data[num] + '</div>'
                                });
                                marker[num].addListener('click', function() {
                                    infoWindow[num].open(map,marker[num]);
                                });
                            }
                        });
                    }

                    function initAutocomplete(){
                        var input = document.getElementById('address-text');
                        var options = {
                            types: ['(regions)'],
                            componentRestrictions: {country: 'jp'}
                        };
                        autocomplete = new google.maps.places.Autocomplete(input,options);
                    }
                </script>
                <script src="https://maps.googleapis.com/maps/api/js?libraries=places&key=AIzaSyBFVyrpbkJqWj1RG21Qz_y0K0ZEm6_z2nY&callback=init"></script>
            </div>
        </div>

    </div>
    </div>
</body>
</html>
<?php
mysqli_close($link);
?>