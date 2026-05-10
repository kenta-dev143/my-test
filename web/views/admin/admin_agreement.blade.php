<!DOCTYPE html>
<html lang="ja">
  <head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>展示会来場者管理システム - 個人情報の取り扱いについて</title class="login">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" />
  <script type="text/javascript" src="js/jquery-3.2.1.min.js"></script>
  <script type="text/javascript" src="js/jquery-ui/jquery-ui.js"></script>
  <script type="text/javascript" src="js/jquery-ui/datepicker-ja.js"></script>
  @verbatim
  <style>
  .loginTitle {
      max-width: 800px;
      padding: 0px 50px 0px 50px;
      background: #0370c0;
      box-shadow: 2px 2px 10px #ccc;
      height: 40px;
      margin:50px auto 0px auto;
      display: block;
      text-align:center;
      color:white;
      font-size:20px;
  }

  .agreebtn {
    padding:10px 22px;
    border-radius:30px;
    margin-top:20px;
  }

  </style>
  @endverbatim
  </head>

  <div class="row">
    <div class="col-md-12">
      <div class="card card-user">

        <input type="hidden" name="page" value="{{ $page }}">
        <input type="hidden" name="exec" value="agree">

        <div style="margin:20px 0 0 50px">
          @include('tpl.agreement')
          <br>
          <br>
          <br>
          <br>
          <br>
          <br>
        </div>
      </div>
    </div>
  </div>


