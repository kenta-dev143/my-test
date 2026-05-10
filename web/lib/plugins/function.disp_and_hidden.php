<?php

function smarty_function_disp_and_hidden($params, &$smarty)
{
    $output = "";

    $v_name = $params['name'];
    $v_val  = $params['value'];

    if( $params['type'] == "text" ){
        //
        // TEXT
        //
        $v_disp_val = $v_val;
    }elseif( $params['type'] == "select" ){
        //
        // SELECT
        //
        for( $i=0 ; $i<count($params['value_list']) ; $i++ ){
            if( $params['value_list'][$i] == $v_val ){
                $v_disp_val = $params['disp_list'][$i];
                break;
            }
        }

    }elseif( $params['type'] == "radio" ){
        //
        // RADIO
        //
        for( $i=0 ; $i<count($params['value_list']) ; $i++ ){
            if( $params['value_list'][$i] == $v_val ){
               $v_disp_val = $params['disp_list'][$i];
               break;
            }
        }
    }elseif($params['type'] == "checkbox" ){
        //
        // CHECKBOX
        //
       if( $v_val != "" ){
           $v_disp_val = $params['disp_list'][1];
       }else{
           $v_disp_val = $params['disp_list'][0];
       }
    }

    $output = $v_disp_val . "<input type=hidden name=\"" . $v_name . "\" value=\"" . $v_val . "\">";

    return $output;

}

?>