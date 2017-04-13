
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML>
<HEAD>
<TITLE>Login</TITLE>

<style>
BODY {	
	scrollbar-3dlight-color:595959;
	scrollbar-arrow-color:ffffff;
	scrollbar-base-color:CFCFCF;
	scrollbar-darkshadow-color:FFFFFF;
	scrollbar-face-color:CFCFCF;
	scrollbar-highlight-color:FFFFF;
	scrollbar-shadow-color:595959;
}
A:link {COLOR: #333333; FONT-FAMILY: Verdana; TEXT-DECORATION: none}
A:visited {COLOR: #333333; FONT-FAMILY: Verdana; TEXT-DECORATION: none}
A:hover {COLOR: #0000FF; FONT-FAMILY: Verdana; TEXT-DECORATION: UNDERLINE}
A:active {COLOR: #333333; FONT-FAMILY: Verdana; TEXT-DECORATION: none}
body, td {FONT-FAMILY: Verdana; FONT-SIZE: 11px;line-height: 13pt; TEXT-DECORATION: none}
input, option, select,textarea {COLOR: #000000; FONT-FAMILY: Verdana; FONT-SIZE: 11px; TEXT-DECORATION: none;}

td {
 word-break:break-all;
}

.button1 {font-family:Verdana, Arial; font-size:9pt; color:#000000;}

.border1 {border:solid 1px #BBBBBB; padding: 2px}
.border2 {border:solid 1px #CCCCCC; padding:0px}

.memuheader {
	background: #009AFF;
	color: #FFFFFF;
}

.titleheader {
	background: #9CBAEF;
	color: #FFFFFF;
}
</style>
</HEAD>

<BODY onLoad="document.loginbox.username.focus();">
<p>&nbsp;</p>
<p>&nbsp;</p>

<form name="loginbox" method="POST">
<table border=0 cellspacing=0 cellpadding=1 bgcolor="#101888" align="center" width="45%">
<tr>
<td align="right" height="25">
   <b><font size="2" color="#FFFFFF">Login&nbsp;&nbsp;</font></b>
</td></tr>
<tr><td>
   <table cellpadding=3 cellspacing=0 bgcolor="#CECED7" width="100%" style="border-collapse: collapse" bordercolor="#111111">
      <tr>
        <td rowspan="3"></td>
        <td>
<font face="Arial, Helvetica" size="2" color="#000000" style="font-family: Arial; font-size: 9pt"><b>Username:</b></font>
<br>
        <input type="text" size=40 name="username"><P></td>
      </tr>
      <tr>
        <td><B><font face="Arial, Helvetica" size="2" color="#000000" style="font-family: Arial; font-size: 9pt">Password:</font><font face="Arial, Helvetica" size="2" color="#FFFFFF" style="font-family: Arial;font-size:9pt;color:black;"><br><input type="password" size=40 name="password"></font><br><br>
		<INPUT TYPE="submit" VALUE="Logon">
		<br>&nbsp;
	   </td>
      </tr>
   </table></td></tr></table>
   </form>
</BODY>
</HTML>

