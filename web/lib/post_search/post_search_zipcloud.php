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


    $api_ret = @file_get_contents('http://zipcloud.ibsnet.co.jp/api/search?zipcode=' . $postNumber, false, $context);
    $address_arr = @json_decode($api_ret, true);

    if($address_arr['status'] != 200 || empty($address_arr['results'])) return false;

    $returnArray['todoufuken'] = $address_arr['results'][0]['address1'];
    $returnArray['address1'] = $address_arr['results'][0]['address2'];
    $returnArray['address2'] = $address_arr['results'][0]['address3'];


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