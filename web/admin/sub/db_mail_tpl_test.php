<?php
      $msm = new UserBlade();
      $msm->assign('test',"テストの差し込みデータです");

      $mail_tpl = "test.tpl"; //拡張子「.tpl」はなくてもOK ※基本名部分でselectするため

      $ret = _bladeFetchFromMailTplDB( $msm, $mail_tpl );
      $title = $ret['subject'];
      $body = $ret['body'];

      $attach = array();
      _accessSendMail( _SYSTEM_INFO_MAIL, _SYSTEM_INFO_MAIL_NAME, "test@k-creation.co.jp", "テスト 様", $title, $body,$attach );

      echo "end";
      exit();