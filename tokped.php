<?php
error_reporting(0);
function getStr($start, $end, $string) {
    if (!empty($string)) {
    $setring = explode($start,$string);
    $setring = explode($end,$setring[1]);
    return $setring[0];
    }
}

$productList = array();
$page = 1;

echo "Tokopedia Product Scrapper v1.2.1
by willhendyan (willhendyan.lim@gmail.com)

";

do{
echo date("[h:i:s A]") . " => " . "Tokopedia User? ";
$userTokped = trim(fgets(STDIN));
echo date("[h:i:s A]") . " => " . "Result File (*.csv)? ";
$resultFile = trim(fgets(STDIN));
echo date("[h:i:s A]") . " => " . "Image Folder (folder must exist)? ";
$folderName = trim(fgets(STDIN));
echo date("[h:i:s A]") . " => " . "Delim (; or ,)? ";
$delim = trim(fgets(STDIN));
}while($userTokped=="" OR $resultFile=="" OR $folderName=="");
$handle = fopen(dirname(__FILE__)."/$resultFile", 'a');


    do{
    echo date("[h:i:s A]") . " => " . "Scrapping $userTokped's page $page products...\n";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://www.tokopedia.com/$userTokped/page/$page");
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.87 Safari/537.36");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                                        "Host: www.tokopedia.com",
                                        "Connection: keep-alive",
                                        "Upgrade-Insecure-Requests: 1",
                                        "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.87 Safari/537.36",
                                        "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3",
                                        "Sec-Fetch-Site: none",
                                        "Sec-Fetch-Mode: navigate",
                                        "Accept-Language: en-US,en;q=0.9,id;q=0.8"
    ));
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

    $result = curl_exec($ch);

    $checkNextPage = $page+1;

    if(!strpos($result, '<a href="/' . $userTokped . '/page/' . $checkNextPage)){
        $noNextPage = 1;
    }else{
        $noNextPage = 0;
        $page++;
    }

    $needle = "https://www.tokopedia.com/$userTokped/";
    $lastPos = 0;
    $positions = array();

    while (($lastPos = strpos($result, $needle, $lastPos))!== false) {
        $positions[] = $lastPos;
        $lastPos = $lastPos + strlen($needle);
    }

    foreach($positions as $productPos){
        $product = substr($result, $productPos, 1000);
        $product = getStr($needle, '"', $product);
        $productURL = $needle . $product;
        if(!strpos($productURL, "/page")){
            array_push($productList, $productURL);
        }
    }

    }while($noNextPage!=1);

    echo date("[h:i:s A]") . " => " . count($productList) ." products scrapped!\n";
    fwrite($handle, count($productList) . " PRODUCTS ($needle)\n");

    $productNo = 1;

    foreach($productList as $productURL){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $productURL);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.87 Safari/537.36");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                                            "Host: www.tokopedia.com",
                                            "Connection: keep-alive",
                                            "Upgrade-Insecure-Requests: 1",
                                            "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.87 Safari/537.36",
                                            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3",
                                            "Sec-Fetch-Site: none",
                                            "Sec-Fetch-Mode: navigate",
                                            "Accept-Language: en-US,en;q=0.9,id;q=0.8"
        ));
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    
        $result = curl_exec($ch);

        $productName = str_replace($delim, "", getStr('name="title" content="', '"', $result));
        $productDesc = str_replace($delim, "", getStr('"description": "', '"', $result));
        $productPrice = str_replace($delim, "", getStr('property="twitter:data1" content="', '"', $result));
        $productImage = str_replace($delim, "", getStr('img alt="Product" src="', '"', $result));
        fwrite($handle, "$productNo$delim$productName$delim$productDesc$delim$productPrice$delim$productURL$delim$productImage\n");
        echo date("[h:i:s A]") . " => " . "Product [$productNo] has been saved to $resultFile!\n";

        set_time_limit(0);

        $folderHandle = fopen(dirname(__FILE__)."/$folderName/$productNo.jpg", 'a');

        $ch = curl_init(str_replace(" ","%20",$productImage));

        curl_setopt($ch, CURLOPT_TIMEOUT, 50);
        curl_setopt($ch, CURLOPT_FILE, $folderHandle);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        $data = curl_exec($ch);
        echo date("[h:i:s A]") . " => " . "Product image [$productNo] has been downloaded to /$folderName!\n";
        $productNo++;
    }

?>
