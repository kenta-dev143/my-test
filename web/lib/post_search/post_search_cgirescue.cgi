#!/usr/bin/perl --

#####################################################
#     接続プログラム v.1.00  (c)2005 CGI-RESCUE
#####################################################

read(STDIN, $buffer, $ENV{'CONTENT_LENGTH'});

@pairs = split(/&/,$buffer);
foreach $pair (@pairs) {

	($name,$value) = split(/=/,$pair);
	$value =~ tr/+/ /;
	$value =~ s/%([a-fA-F0-9][a-fA-F0-9])/pack("C", hex($1))/eg;

	$in{$name} = $value;
}

print "Content-type: text/html; charset=utf-8\n\n";

print <<"EOF";
<HTML>
<HEAD>
<TITLE>郵便番号検索</TITLE>
<META NAME="ROBOTS" CONTENT="NOINDEX, NOFOLLOW">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<SCRIPT LANGUAGE="JavaScript">
<!--
function setPost()
{
    if(window.opener.closed){
        window.self.close();
    }else{
        window.opener.setPostcode(document.forms[0].post.value, document.forms[0].addr.value);
        window.self.close();
    }
}
//-->
</SCRIPT>
</HEAD>
<BODY onLoad="setPost();">

<form>
<input type=hidden name="post" value="$in{'zip'}">
<input type=hidden name="addr" value="$in{'addr'}">
</form>

エラーが発生しました。
</BODY>
</HTML>
EOF

exit;
