<?php

    $_KB_GLOBAL_BUFF = "";
    $_KB_SQL_GLOBAL_BUFF = "";
    $_KB_SQL_GLOBAL_BUFF_SET_CNT = 0;
    
    // ******************************
    // kdebugger用（Object型を配列に）
    // ******************************
    function kdebugger($allVars){
        global $_KB_GLOBAL_BUFF;
        global $_KB_SQL_GLOBAL_BUFF;

        ksort($allVars);


        $varsData = array();
        $lv=0;
        kdebuggerInfoMake($allVars,$varsData,$lv);
        //_print_r($varsData);

// ****************************************************** 
// ****************************************************** 
// ****************************************************** 
// ****************************************************** 
// ****************************************************** 
?>

<style>
#kdebugger {
    position: fixed;
    bottom:0px; /*場所を右下に移動*/
    right:0px; /*場所を右下に移動*/
<?php 
if($_KB_GLOBAL_BUFF==""){
    echo "width:40px;\n";
}else{
    echo "width:100%;\n";
}
?>
    height: 40px;
    background-color: #ff4444;
    z-index: 99999;
}
#kdeb_foot {
    position: fixed;
    bottom:0px; /*場所を左下に移動*/
    left:0px; /*場所を左下に移動s*/
}
#kdeb_vars {
    position: fixed;
    bottom:2px; /*場所を左下に移動*/
    left:20px; /*場所を左下に移動s*/
<?php 
if($_KB_GLOBAL_BUFF==""){
    echo "display:none;\n";
}
?>
}
#kdeb_vars a {
    color:white;
    font-size:22px;
    font-weight:bold;
}
#kdeb_icon {
    position: fixed;
    bottom:2px; /*場所を右下に移動*/
    right:2px; /*場所を右下に移動s*/
    width:36px;
    height:36px;
    opacity:0.7;
}
#kdeb_vars_info {
    position: fixed;
    bottom:40px;
    left:0px;
    background-color:#EEEEEE;
    width:100%;
    height:100%;
    overflow: scroll;
    padding-top: 40px;
    display:none;
}
#kdeb_vars_request {
    position: fixed;
    bottom:40px;
    left:0px;
    background-color:#EEEEEE;
    width:100%;
    height:100%;
    overflow: scroll;
    padding-top: 40px;
    display:none;
}
#kdeb_vars_session {
    position: fixed;
    bottom:40px;
    left:0px;
    background-color:#EEEEEE;
    width:100%;
    height:100%;
    overflow: scroll;
    padding-top: 40px;
    display:none;
}
#kdeb_print {
    position: fixed;
    bottom:40px;
    left:0px;
    background-color:#EEEEEE;
    width:100%;
    height:100%;
    overflow: scroll;
    padding-top: 40px;
<?php 
if($_KB_GLOBAL_BUFF==""){
    echo "display:none;\n";
}
?>

}
#kdeb_sql {
    position: fixed;
    bottom:40px;
    left:0px;
    background-color:#EEEEEE;
    width:100%;
    height:100%;
    overflow: scroll;
    padding-top: 40px;
    display:none;
}

.kdeb_ul li {
    width:100%;
    background-color: #ff9999;
    margin-top: 1px;
    margin-bottom: 1px;
}
</style>
<div id="kdebugger">
<!-- ******************************** -->
<div id="kdeb_vars_info">
<span style="font-size:21px;font-weight:bold;">全ての変数一覧（処理実行後の値）</span>
<div style="float:right;">
    <img class="kdeb_close" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAACbElEQVRYR82XX3LTMBDGv31IXgk3iLgA5QSkJ8B9sKdvpCegnID2BuUEwKvUGcwJCCfAuQAuNwiv8XSWWVt2nMS2NqVDo5dkxit9P62k/UM4YORJEoH5DYApgAmITsrpzBmAFYA7MKfm9vabdlkKGebn51Pc338AEIFoErL3QAKToijemzSV/71jECBPkiswv1ML78owr0B0Y6y97iPoBMijaILR6HvjYtW2B4zkiIritMsbewB5HMu5irjO3Vo48QZwapyT+9KMLQC/8/zRxWs5gSgK0/ZEA/Dobu/zDHNmnHtVf94AyIUD5Lb/j3FtrBU9lAD+qf0MuZ6Z/xDRsyFCjQ1aR1EBJMlnAG+DCwMzACcg+tRpy3wBIGNgEQIF8MVYOy8BfsXxamhCuStgVt/gPI7nexDMF8Y52QjkJQUhmFfGuedUhlfga/+d2Rav7bYgWuKt72EI4EwAgu5Hh4Df6Vx+6523N9Hppf1I+ZHyOF6A6HXw6vdAdM1TictE5h/iAYlML4MA1YTmnPvs1eLVepkAsEp8E816IQ4S9+sdBcDTHQGwPIpL+MTPMBCIyrjdkcc1gUhRV5ypQvEuhCYUh8QlvL9wbqJORjWEJhmFxP0L3CQjKUZ4NLoLZrCqyBwu1RQ2ZXIriqlURsdRkDQZ7JCwfFD43DJeGmurhqauiBoA7VE8ULzt+k4AdTHxAABm/k1ANFiWtz2B8XihzpJhoCXW65mqMdkqKpLkipkvg6+jB8AXqNKalRVw1wg3p9Kmjcc3zBxpQbxwivX68p+a011i357XlbHEg7qQWfpAlYFoYaxNw6dSWfwFz76VcZY91wEAAAAASUVORK5CYII=">
</div>
<?php
    kdebuggerTagDisp($varsData,false);
?>
</div>
<!-- ******************************** -->
<div id="kdeb_vars_request">
<span style="font-size:21px;font-weight:bold;">$_REQUESTの値</span>
<div style="float:right;">
    <img class="kdeb_close" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAACbElEQVRYR82XX3LTMBDGv31IXgk3iLgA5QSkJ8B9sKdvpCegnID2BuUEwKvUGcwJCCfAuQAuNwiv8XSWWVt2nMS2NqVDo5dkxit9P62k/UM4YORJEoH5DYApgAmITsrpzBmAFYA7MKfm9vabdlkKGebn51Pc338AEIFoErL3QAKToijemzSV/71jECBPkiswv1ML78owr0B0Y6y97iPoBMijaILR6HvjYtW2B4zkiIritMsbewB5HMu5irjO3Vo48QZwapyT+9KMLQC/8/zRxWs5gSgK0/ZEA/Dobu/zDHNmnHtVf94AyIUD5Lb/j3FtrBU9lAD+qf0MuZ6Z/xDRsyFCjQ1aR1EBJMlnAG+DCwMzACcg+tRpy3wBIGNgEQIF8MVYOy8BfsXxamhCuStgVt/gPI7nexDMF8Y52QjkJQUhmFfGuedUhlfga/+d2Rav7bYgWuKt72EI4EwAgu5Hh4Df6Vx+6523N9Hppf1I+ZHyOF6A6HXw6vdAdM1TictE5h/iAYlML4MA1YTmnPvs1eLVepkAsEp8E816IQ4S9+sdBcDTHQGwPIpL+MTPMBCIyrjdkcc1gUhRV5ypQvEuhCYUh8QlvL9wbqJORjWEJhmFxP0L3CQjKUZ4NLoLZrCqyBwu1RQ2ZXIriqlURsdRkDQZ7JCwfFD43DJeGmurhqauiBoA7VE8ULzt+k4AdTHxAABm/k1ANFiWtz2B8XihzpJhoCXW65mqMdkqKpLkipkvg6+jB8AXqNKalRVw1wg3p9Kmjcc3zBxpQbxwivX68p+a011i357XlbHEg7qQWfpAlYFoYaxNw6dSWfwFz76VcZY91wEAAAAASUVORK5CYII=">
</div>
<?php
    kdebuggerTagDisp($varsData,false,'_REQUEST');
?>
</div>
<!-- ******************************** -->
<div id="kdeb_vars_session">
<span style="font-size:21px;font-weight:bold;">$_SESSIONの値</span>
<div style="float:right;">
    <img class="kdeb_close" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAACbElEQVRYR82XX3LTMBDGv31IXgk3iLgA5QSkJ8B9sKdvpCegnID2BuUEwKvUGcwJCCfAuQAuNwiv8XSWWVt2nMS2NqVDo5dkxit9P62k/UM4YORJEoH5DYApgAmITsrpzBmAFYA7MKfm9vabdlkKGebn51Pc338AEIFoErL3QAKToijemzSV/71jECBPkiswv1ML78owr0B0Y6y97iPoBMijaILR6HvjYtW2B4zkiIritMsbewB5HMu5irjO3Vo48QZwapyT+9KMLQC/8/zRxWs5gSgK0/ZEA/Dobu/zDHNmnHtVf94AyIUD5Lb/j3FtrBU9lAD+qf0MuZ6Z/xDRsyFCjQ1aR1EBJMlnAG+DCwMzACcg+tRpy3wBIGNgEQIF8MVYOy8BfsXxamhCuStgVt/gPI7nexDMF8Y52QjkJQUhmFfGuedUhlfga/+d2Rav7bYgWuKt72EI4EwAgu5Hh4Df6Vx+6523N9Hppf1I+ZHyOF6A6HXw6vdAdM1TictE5h/iAYlML4MA1YTmnPvs1eLVepkAsEp8E816IQ4S9+sdBcDTHQGwPIpL+MTPMBCIyrjdkcc1gUhRV5ypQvEuhCYUh8QlvL9wbqJORjWEJhmFxP0L3CQjKUZ4NLoLZrCqyBwu1RQ2ZXIriqlURsdRkDQZ7JCwfFD43DJeGmurhqauiBoA7VE8ULzt+k4AdTHxAABm/k1ANFiWtz2B8XihzpJhoCXW65mqMdkqKpLkipkvg6+jB8AXqNKalRVw1wg3p9Kmjcc3zBxpQbxwivX68p+a011i357XlbHEg7qQWfpAlYFoYaxNw6dSWfwFz76VcZY91wEAAAAASUVORK5CYII=">
</div>
<?php
    kdebuggerTagDisp($varsData,false,'_SESSION');
?>
</div>
<!-- ******************************** -->
<div id="kdeb_print">
<span style="font-size:21px;font-weight:bold;">出力</span>
<div style="float:right;">
    <img class="kdeb_close" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAACbElEQVRYR82XX3LTMBDGv31IXgk3iLgA5QSkJ8B9sKdvpCegnID2BuUEwKvUGcwJCCfAuQAuNwiv8XSWWVt2nMS2NqVDo5dkxit9P62k/UM4YORJEoH5DYApgAmITsrpzBmAFYA7MKfm9vabdlkKGebn51Pc338AEIFoErL3QAKToijemzSV/71jECBPkiswv1ML78owr0B0Y6y97iPoBMijaILR6HvjYtW2B4zkiIritMsbewB5HMu5irjO3Vo48QZwapyT+9KMLQC/8/zRxWs5gSgK0/ZEA/Dobu/zDHNmnHtVf94AyIUD5Lb/j3FtrBU9lAD+qf0MuZ6Z/xDRsyFCjQ1aR1EBJMlnAG+DCwMzACcg+tRpy3wBIGNgEQIF8MVYOy8BfsXxamhCuStgVt/gPI7nexDMF8Y52QjkJQUhmFfGuedUhlfga/+d2Rav7bYgWuKt72EI4EwAgu5Hh4Df6Vx+6523N9Hppf1I+ZHyOF6A6HXw6vdAdM1TictE5h/iAYlML4MA1YTmnPvs1eLVepkAsEp8E816IQ4S9+sdBcDTHQGwPIpL+MTPMBCIyrjdkcc1gUhRV5ypQvEuhCYUh8QlvL9wbqJORjWEJhmFxP0L3CQjKUZ4NLoLZrCqyBwu1RQ2ZXIriqlURsdRkDQZ7JCwfFD43DJeGmurhqauiBoA7VE8ULzt+k4AdTHxAABm/k1ANFiWtz2B8XihzpJhoCXW65mqMdkqKpLkipkvg6+jB8AXqNKalRVw1wg3p9Kmjcc3zBxpQbxwivX68p+a011i357XlbHEg7qQWfpAlYFoYaxNw6dSWfwFz76VcZY91wEAAAAASUVORK5CYII=">
</div>
<div style="background-color:#ff9999;">
<?php
    echo $_KB_GLOBAL_BUFF;
?>
</div>
</div>
<!-- ******************************** -->
<div id="kdeb_sql">
<span style="font-size:21px;font-weight:bold;">ＳＱＬ</span>
<div style="float:right;">
    <img class="kdeb_close" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAACbElEQVRYR82XX3LTMBDGv31IXgk3iLgA5QSkJ8B9sKdvpCegnID2BuUEwKvUGcwJCCfAuQAuNwiv8XSWWVt2nMS2NqVDo5dkxit9P62k/UM4YORJEoH5DYApgAmITsrpzBmAFYA7MKfm9vabdlkKGebn51Pc338AEIFoErL3QAKToijemzSV/71jECBPkiswv1ML78owr0B0Y6y97iPoBMijaILR6HvjYtW2B4zkiIritMsbewB5HMu5irjO3Vo48QZwapyT+9KMLQC/8/zRxWs5gSgK0/ZEA/Dobu/zDHNmnHtVf94AyIUD5Lb/j3FtrBU9lAD+qf0MuZ6Z/xDRsyFCjQ1aR1EBJMlnAG+DCwMzACcg+tRpy3wBIGNgEQIF8MVYOy8BfsXxamhCuStgVt/gPI7nexDMF8Y52QjkJQUhmFfGuedUhlfga/+d2Rav7bYgWuKt72EI4EwAgu5Hh4Df6Vx+6523N9Hppf1I+ZHyOF6A6HXw6vdAdM1TictE5h/iAYlML4MA1YTmnPvs1eLVepkAsEp8E816IQ4S9+sdBcDTHQGwPIpL+MTPMBCIyrjdkcc1gUhRV5ypQvEuhCYUh8QlvL9wbqJORjWEJhmFxP0L3CQjKUZ4NLoLZrCqyBwu1RQ2ZXIriqlURsdRkDQZ7JCwfFD43DJeGmurhqauiBoA7VE8ULzt+k4AdTHxAABm/k1ANFiWtz2B8XihzpJhoCXW65mqMdkqKpLkipkvg6+jB8AXqNKalRVw1wg3p9Kmjcc3zBxpQbxwivX68p+a011i357XlbHEg7qQWfpAlYFoYaxNw6dSWfwFz76VcZY91wEAAAAASUVORK5CYII=">
</div>
<div style="background-color:#ff9999;">
<?php
    echo $_KB_SQL_GLOBAL_BUFF;
?>
</div>
</div>
<!-- ******************************** -->
<div id="kdeb_foot">
    <div id="kdeb_vars"><a id="kdeb_vars_link">全ての変数</a>　　　<a id="kdeb_vars_request_link">$_REQUEST</a>　　　<a id="kdeb_vars_session_link">$_SESSION</a>　　　<a id="kdeb_sql_link">ＳＱＬ</a>　　　<a id="kdeb_print_link">出力</a></div>
    <img id="kdeb_icon" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAADCUlEQVRYR61XQVLbQBCckTiZA+YHzg/MCyJu9ooq4AWQF8R5gckLcF4AfkGcKmvxLeIFOC+A/MAcpJs0VFO7lCyvLclCF1fJu7M9vT3TI6ZPeMIwXDLzaD6fx03DcdMN5fVKqRtmHmdZ9mWxWLw0jdcKwGAw6Pm+/yQiU631qOnhWN8KQBiG9yJykaZpL47jVRkAAHqe19VaL7eB2xvA2dlZICJ/iehHFEWT8gEAR0RXIvJfa937dADmgPMoio4durhm5jsi+pckSeBix+7ZmwErviiKPmKYd1+JqE9EXRH5qbW+2aWNDQBKKaC/IqKliMRpmj66MhgOhxee5/0WkRPcsVIqZmYcXnxWInKK/4Mg6HY6nSciuixqYgMAAqOmi8FEBCKapWn6y4KxGmDm0yzLugCzJVOAgEYQt58kyXExoZ1XgEPyPA+IKAAgA+QbMrAMEJFtPlhX9UyjKLouLqqtAQNmxswiIitm3qrsMgoReSWikdYalbH21AaAXUopADivStMB4FFr7WTICQDZZlkma1QxQ5xr9NUFsqsa1gAYmm8hlrrBq9aJiDN7dMmDg4PeOwCj6DHEhg2e563Vbp7noP6o6rDi/4gD9T88PMyK71GOh4eHOGuENayUmjDzd7RMERmVNyil+syM+m36xHmewylfbd2HYQjDGosIEp+gSQEAsn1xKRQnmk23TU93rIdZdYlomiTJyPaCyiqwLbcpgDzPL33fX6FJQVMoW1xJ2RkrAezDQJUDNmpEZuh4bsjAhwdU7atkwDQgl9E4Y4vIH1QTET2naXq6y4oRoBYA42QvNUrxvddbo9rLjrdRZsare4flEno9wMEZ7WQMeyaiI631ya5rqMVAMYDpC2hYUDfuOsbch/GsBOB9Wi7bbysz2pZJcTawDNh3KMdyc2tUBVUqLrTyNQZME4N17xzLGl+BC5CLAVs9+N1mxbWroIoFI7iNg8x4h++CjUHExmzNgBli74oCrAL8qRpQSuF7EGZWZybcwNaKgbbZt9YAZgkjsr0+TLH3DRdYyvJGf/ILAAAAAElFTkSuQmCC">
</div>
<!-- ******************************** -->
</div>
<script>
$(function() {

$('#kdeb_icon').on('click',function(){
    kdeb_allclose();
});
$('.kdeb_close').on('click',function(){
    kdeb_allclose();
});

function kdeb_allclose(){
    if ($("#kdeb_vars").is(":hidden")) {
         $('#kdebugger').width('100%');
         $('#kdeb_foot').width('100%');
         $('#kdeb_vars').show();
     }else{
         $('#kdebugger').height('40px');
         $('#kdebugger').width('40px');
         $('#kdeb_foot').width('40px');
         $('#kdeb_vars').hide();
         $('#kdeb_vars_info').hide();
         $('#kdeb_vars_request').hide();
         $('#kdeb_vars_session').hide();
         $('#kdeb_sql').hide();
         $('#kdeb_print').hide();
     }

}

$('#kdeb_vars_link').on('click',function(){
    if ($("#kdeb_vars_info").is(":hidden")) {
        $('#kdebugger').height('90%');
        $('#kdeb_vars_info').show();
         $('#kdeb_vars_request').hide();
         $('#kdeb_vars_session').hide();
         $('#kdeb_sql').hide();
         $('#kdeb_print').hide();
     }else{
         $('#kdebugger').height('40px');
         $('#kdeb_foot').width('40px');
         $('#kdeb_vars_info').hide();
         $('#kdeb_vars_request').hide();
         $('#kdeb_vars_session').hide();
         $('#kdeb_sql').hide();
         $('#kdeb_print').hide();
     }
});

$('#kdeb_vars_request_link').on('click',function(){
    if ($("#kdeb_vars_request").is(":hidden")) {
        $('#kdebugger').height('90%');
        $('#kdeb_vars_request').show();
         $('#kdeb_vars_info').hide();
         $('#kdeb_vars_session').hide();
         $('#kdeb_sql').hide();
         $('#kdeb_print').hide();
     }else{
         $('#kdebugger').height('40px');
         $('#kdeb_foot').width('40px');
         $('#kdeb_vars_request').hide();
         $('#kdeb_vars_info').hide();
         $('#kdeb_vars_session').hide();
         $('#kdeb_sql').hide();
         $('#kdeb_print').hide();
     }
});

$('#kdeb_vars_session_link').on('click',function(){
    if ($("#kdeb_vars_session").is(":hidden")) {
        $('#kdebugger').height('90%');
        $('#kdeb_vars_session').show();
         $('#kdeb_vars_info').hide();
         $('#kdeb_vars_request').hide();
         $('#kdeb_sql').hide();
         $('#kdeb_print').hide();
     }else{
         $('#kdebugger').height('40px');
         $('#kdeb_foot').width('40px');
         $('#kdeb_vars_session').hide();
         $('#kdeb_vars_info').hide();
         $('#kdeb_vars_request').hide();
         $('#kdeb_sql').hide();
         $('#kdeb_print').hide();
     }
});

$('#kdeb_print_link').on('click',function(){
    if ($("#kdeb_print").is(":hidden")) {
        $('#kdebugger').height('90%');
        $('#kdeb_print').show();
         $('#kdeb_vars_info').hide();
         $('#kdeb_vars_request').hide();
         $('#kdeb_vars_session').hide();
         $('#kdeb_sql').hide();
     }else{
         $('#kdebugger').height('40px');
         $('#kdeb_foot').width('40px');
         $('#kdeb_vars_session').hide();
         $('#kdeb_vars_info').hide();
         $('#kdeb_vars_request').hide();
         $('#kdeb_sql').hide();
         $('#kdeb_print').hide();
     }
});

$('#kdeb_sql_link').on('click',function(){
    if ($("#kdeb_sql").is(":hidden")) {
        $('#kdebugger').height('90%');
        $('#kdeb_sql').show();
         $('#kdeb_vars_info').hide();
         $('#kdeb_vars_request').hide();
         $('#kdeb_vars_session').hide();
         $('#kdeb_print').hide();
     }else{
         $('#kdebugger').height('40px');
         $('#kdeb_foot').width('40px');
         $('#kdeb_vars_session').hide();
         $('#kdeb_vars_info').hide();
         $('#kdeb_vars_request').hide();
         $('#kdeb_sql').hide();
         $('#kdeb_print').hide();
     }
});

$('.kdeb_haschild').on('click',function(){
    var obj = $(this).parent('li').next('ul');
    if (obj.is(":hidden")) {
        obj.show();
    }else{
        obj.hide();
    }
});


});
</script>
<?php
// ******************************************************
// ****************************************************** 
// ****************************************************** 
// ****************************************************** 

    }
    function kdebuggerInfoMake($vars,&$data,$lv){
        $lv++;
        //if($lv>10) return;

        foreach($vars as $k => $v){

            if($lv==1 && $k=="GLOBALS") continue;
            if($lv==1 && $k=="_KB_GLOBAL_BUFF") continue;
            if($lv==1 && $k=="_KB_SQL_GLOBAL_BUFF") continue;
            if($lv==1 && $k=="_KB_SQL_GLOBAL_BUFF_SET_CNT") continue;
            
            $type = gettype($v);
            $idx = _count($data);
            $data[$idx]['level'] = $lv;
            $data[$idx]['type'] = $type;
            $data[$idx]['name'] = $k;
            if($type=="object"){
                $data[$idx]['data'] = array();
                kdebuggerInfoMake((array)$v,$data[$idx]['data'],$lv);
            }elseif($type=="array"){
                $data[$idx]['data'] = array();
                kdebuggerInfoMake($v,$data[$idx]['data'],$lv);
            }else{
                $data[$idx]['data'] = $v;
            }
        }
    }

    function kdebuggerTagDisp(&$varsData,$hideFlg,$target=""){
        if($hideFlg) $hide = "style=\"display:none;\"";

        echo "<ul class=\"kdeb_ul\" ".$hide.">";
        for ($i=0; $i < _count($varsData); $i++) { 

            if($target!="" && $varsData[$i]['level']==1 && $target!=$varsData[$i]['name']) continue;

            $lm = $varsData[$i]['level'] * 20;
            if($varsData[$i]['type']=="object" || $varsData[$i]['type']=="array"){
                echo "<li style=\"margin-left:".$lm."px;\"><span style=\"font-weight:bold;\">".$varsData[$i]['name']."</span> (<a class=\"kdeb_haschild\" style=\"color:red;\">".$varsData[$i]['type'].":"._count($varsData[$i]['data'])."</a>)"."<a class=\"kdeb_haschild\">↓</a></li>";
                $nexthide = true;
                if($target!="" && $varsData[$i]['level']==1) $nexthide = false;
                kdebuggerTagDisp($varsData[$i]['data'],$nexthide,$target);
            }else{
                echo "<li style=\"margin-left:".$lm."px;\"><span style=\"font-weight:bold;\">".$varsData[$i]['name']."</span> (".$varsData[$i]['type'].")"." ".$varsData[$i]['data']."</li>";
            }
        }
        echo "</ul>";
    }

    function kd_print_r($arg){
        global $_KB_GLOBAL_BUFF;

        ob_start();
        print_r($arg);
        $str = ob_get_contents();
        ob_end_clean();
        $str = str_replace("\r\n","\n",$str);
        $str = str_replace("\r","\n",$str);
        //$_KB_GLOBAL_BUFF .= nl2br( _hs($str) );
        $_KB_GLOBAL_BUFF .= "<pre>". _hs($str) ."</pre>";
        $_KB_GLOBAL_BUFF .= "<hr>";
    }

    function kd_echo($arg){
        global $_KB_GLOBAL_BUFF;

        ob_start();
        echo $arg;
        $str = ob_get_contents();
        ob_end_clean();
        $str = str_replace("\r\n","\n",$str);
        $str = str_replace("\r","\n",$str);
        //$_KB_GLOBAL_BUFF .= nl2br( _hs($str) );
        $_KB_GLOBAL_BUFF .= "<pre>". _hs($str) ."</pre>";
        $_KB_GLOBAL_BUFF .= "<hr>";
    }

    function kd_var_dump($arg){
        global $_KB_GLOBAL_BUFF;

        ob_start();
        var_dump($arg);
        $str = ob_get_contents();
        ob_end_clean();
        $str = str_replace("\r\n","\n",$str);
        $str = str_replace("\r","\n",$str);
        //$_KB_GLOBAL_BUFF .= nl2br( _hs($str) );
        $_KB_GLOBAL_BUFF .= "<pre>". _hs($str) ."</pre>";
        $_KB_GLOBAL_BUFF .= "<hr>";
    }
