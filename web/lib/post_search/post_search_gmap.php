<?php
require("../environment.php");
require("../inc.php");

$_conf_todoufuken     = array();
$_conf_todoufuken[1]  = "北海道";
$_conf_todoufuken[2]  = "青森県";
$_conf_todoufuken[3]  = "岩手県";
$_conf_todoufuken[4]  = "宮城県";
$_conf_todoufuken[5]  = "秋田県";
$_conf_todoufuken[6]  = "山形県";
$_conf_todoufuken[7]  = "福島県";
$_conf_todoufuken[8]  = "茨城県";
$_conf_todoufuken[9]  = "栃木県";
$_conf_todoufuken[10] = "群馬県";
$_conf_todoufuken[11] = "埼玉県";
$_conf_todoufuken[12] = "千葉県";
$_conf_todoufuken[13] = "東京都";
$_conf_todoufuken[14] = "神奈川県";
$_conf_todoufuken[15] = "新潟県";
$_conf_todoufuken[16] = "富山県";
$_conf_todoufuken[17] = "石川県";
$_conf_todoufuken[18] = "福井県";
$_conf_todoufuken[19] = "山梨県";
$_conf_todoufuken[20] = "長野県";
$_conf_todoufuken[21] = "岐阜県";
$_conf_todoufuken[22] = "静岡県";
$_conf_todoufuken[23] = "愛知県";
$_conf_todoufuken[24] = "三重県";
$_conf_todoufuken[25] = "滋賀県";
$_conf_todoufuken[26] = "京都府";
$_conf_todoufuken[27] = "大阪府";
$_conf_todoufuken[28] = "兵庫県";
$_conf_todoufuken[29] = "奈良県";
$_conf_todoufuken[30] = "和歌山県";
$_conf_todoufuken[31] = "鳥取県";
$_conf_todoufuken[32] = "島根県";
$_conf_todoufuken[33] = "岡山県";
$_conf_todoufuken[34] = "広島県";
$_conf_todoufuken[35] = "山口県";
$_conf_todoufuken[36] = "徳島県";
$_conf_todoufuken[37] = "香川県";
$_conf_todoufuken[38] = "愛媛県";
$_conf_todoufuken[39] = "高知県";
$_conf_todoufuken[40] = "福岡県";
$_conf_todoufuken[41] = "佐賀県";
$_conf_todoufuken[42] = "長崎県";
$_conf_todoufuken[43] = "熊本県";
$_conf_todoufuken[44] = "大分県";
$_conf_todoufuken[45] = "宮崎県";
$_conf_todoufuken[46] = "鹿児島県";
$_conf_todoufuken[47] = "沖縄県";

function _postNumber2Address($postNumber = NULL){
    $returnArray = array();
    if($postNumber === NULL || preg_match('/^\d{7}$/', $postNumber) !== 1) return false;

    //$googleMapsApiData = json_decode(@file_get_contents('http://maps.googleapis.com/maps/api/geocode/json?sensor=false&language=ja&address='.$postNumber), true);
    //2019/02/06 Mod
    $googleMapsApiData = json_decode(@file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?address='.$postNumber.'&language=ja&sensor=false&key='._GMAP_API_KEY), true);

    if($googleMapsApiData['status'] !== 'OK') return false;

    $addressArray = $googleMapsApiData['results'][0]['address_components'];
    unset($addressArray[0]);
    array_pop($addressArray);
    $addressArray = array_reverse($addressArray);
    $returnArray['todoufuken'] = $addressArray[0]['long_name'];
    unset($addressArray[0]);

    foreach($addressArray as $k=> $v){
        if($v['long_name']!=""){
            if( array_search('sublocality', $v['types']) !== FALSE){
                //住所２レベル
                $returnArray['address2'] .= $v['long_name'];
            }else{
                //住所１レベル
                $returnArray['address1'] .= $v['long_name'];
            }
        }
    }

    $returnArray['lat'] = $googleMapsApiData['results'][0]['geometry']['location']['lat'];
    $returnArray['lng'] = $googleMapsApiData['results'][0]['geometry']['location']['lng'];

    return $returnArray;
}



$no = $_REQUEST['post_no'];
$result = _postNumber2Address($no);
if( $result==false){
    $ret = "住所が見つかりませんでした";
}else{

    $todoufuken_idx = array_search($result['todoufuken'], $_conf_todoufuken);

    $ret = "";
    $ret .= $todoufuken_idx;
    $ret .= "_". $result['todoufuken'];
    $ret .= "_". $result['address1'];
    $ret .= "_". $result['address2'];

    $ret = "OK#" . $ret;
}

echo $ret;
exit();
?>