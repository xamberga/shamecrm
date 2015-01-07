<?
session_start();
if ($_SESSION['acceso']=='') header("location:index.php"); 

function dia_semana($fecha) {
list($anio,$mes,$dia)=explode("-",$fecha); 
$dias = array('Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado');
return $dias[date("w", mktime(0, 0, 0, $mes, $dia, $anio))];
}

function cambiarFormatoFecha($fecha){ 
    list($dia,$mes,$anio)=explode("-",$fecha); 
    return $anio."/".$mes."/".$dia; 
}

function cambiarFormatoFecha2($fecha){
    list($anio,$mes,$dia)=explode("/",$fecha);
    return $dia."-".$mes."-".$anio;
}

function cambiarFormatoFecha3($fecha_hora){
	list($fecha,$hora)=explode(" ",$fecha_hora);
    list($dia,$mes,$anio)=explode("/",$fecha);
    return $anio."-".$mes."-".$dia." ".$hora;
}

include("conexion.php");

if ($_REQUEST['eliminar']<>'') {
mysql_query("DELETE FROM cita WHERE idcita=".$_REQUEST['eliminar']."");
$msj="Cita eliminada";
$eliminar='';
}
if ($_POST['boton6']=="Citar" && $_POST['cita']<>date("d/m/Y").' 10:00' && $_POST['nombres']<>'' && $_POST['empresa']<>'') {
    $fecha_cita=cambiarFormatoFecha3($_POST['cita']);
	$nombres=ucwords(strtolower($_POST['nombres']));
	$empresa=ucwords(strtolower($_POST['empresa']));
	if ($_POST['comercial']=="") $_POST['comercial']=$_SESSION['usuario']; 
	mysql_query("INSERT INTO cita (fecha_hora,nombres,empresa,motivo,telefono,idusuario,provincia) VALUES ('$fecha_cita','$nombres','$empresa','".$_POST['motivo']."','".$_POST['telefono']."','".$_POST['comercial']."','".$_SESSION['provincia']."')",$clinica) or die(mysql_error());
	if ($_POST['comercial']<>'' && $_POST['comercial']<>$_SESSION['usuario']) {
	$comerciales=mysql_query("SELECT nombre,email FROM usuario WHERE idusuario=".$_POST['comercial']."");
	$comercial=mysql_fetch_assoc($comerciales);
	$nombre=$comercial['nombre'];
	$email=$comercial['email'];
	$cita=substr(cambiarFormatoFecha(substr($fecha_cita,0,10)),0,5)." ".substr($fecha_cita,11,5);
	$mensaje = $nombre." tienes una nueva cita, los datos son los siguientes<br><br>";
	$mensaje .="<b>Fecha y hora</b>: ".$cita."hs<br><b>Cliente:</b> ".$nombres."<br><b>Empresa:</b> ".$empresa."<br><b>Motivo:</b> ".$_POST['motivo']."<br><b>Teléfono:</b> ".$_POST['telefono']."<br><br>";
	$mensaje .= "Es un nuevo cliente, hay que registrarlo en el sistema";
	$msj="Cita creada";$nombres='';$empresa='';
	require_once('class.phpmailer.php');
	require_once('class.smtp.php');
	$mail = new PHPMailer();
	$mail->IsSendmail() ;
	$mail->SMTPAuth = true;
    $mail->Host = "aqui.estaba.elhost.a.pelo";
	$mail->Username = "ladire@a.pelo"; 
	$mail->Password = "elPassWordApelo";
	$mail->From     = "dire@a.pelo";
	$mail->FromName = "from hair";
	$mail->AddAddress($email);
	$mail->WordWrap = 50;
	$mail->IsHTML(true);
	$mail->Subject  =  "Nueva Cita";
	$mail->Body     =  $mensaje;
	if ($mail->Send()) $msj .=" - Enviada por email"; else $msj .=" - No enviada por email";
	} else {$msj="Cita creada";$nombres='';$empresa='';}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>CITAS - CRM</title>
<script language="JavaScript">
function maximizar(){
window.moveTo(0,0);
window.resizeTo(screen.width,screen.height);
}
</script>
<script language="JavaScript" type="text/javascript"> 
function confirmar ( mensaje ) {
return confirm( mensaje );
} 
</script> 

<script type="text/javascript">
var patron = new Array(3,3,3)
var patron2 = new Array(2,2,4)
function mascara(d,sep,pat,nums){
if(d.valant != d.value){
	val = d.value
	largo = val.length
	val = val.split(sep)
	val2 = ''
	for(r=0;r<val.length;r++){
		val2 += val[r]	
	}
	if(nums){
		for(z=0;z<val2.length;z++){
			if(isNaN(val2.charAt(z))){
				letra = new RegExp(val2.charAt(z),"g")
				val2 = val2.replace(letra,"")
			}
		}
	}
	val = ''
	val3 = new Array()
	for(s=0; s<pat.length; s++){
		val3[s] = val2.substring(0,pat[s])
		val2 = val2.substr(pat[s])
	}
	for(q=0;q<val3.length; q++){
		if(q ==0){
			val = val3[q]
		}
		else{
			if(val3[q] != ""){
				val += sep + val3[q]
				}
		}
	}
	d.value = val
	d.valant = val
	}
}
</script>
<style type="text/css">
<!--

body {
	margin-left: 0px;
	margin-top: 0px;
}

input { font-family: Arial, Helvetica, sans-serif; font-size: 17px; color: #333; background-color: #D8EDC9; border: #79C143; border-style: solid; border-top-width: 1px; border-right-width: 1px; border-bottom-width: 1px; border-left-width: 1px}

textarea { font-family: Arial, Helvetica, sans-serif; font-size: 17px; color: #333; background-color: #D8EDC9; border: #79C143; border-style: solid; border-top-width: 1px; border-right-width: 1px; border-bottom-width: 1px; border-left-width: 1px}

select { font-family: Arial, Helvetica, sans-serif; font-size: 17px; color: #333; background-color: #D8EDC9; border: #79C143; border-style: solid; border-top-width: 1px; border-right-width: 1px; border-bottom-width: 1px; border-left-width: 1px}
.Estilo6 {
	color: #79C143;
	font-size: 15px;
}

#recuadro{ 
border: 1px solid #79C143; 
width: 777px;
padding:5px;
margin-right: auto;    
margin-left: auto;
margin-bottom:20px;
} 
.Estilo9 {
	font-family: Geneva, Arial, Helvetica, sans-serif;
	font-weight: bold;
	font-size: 12px;
	color: #79C143;
	padding-left:103px;
}
.Estilo11 {color: #FF0000; font-weight: bold; }
-->
</style>
<script type="text/javascript">
document.onkeypress = KeyPressed;
function KeyPressed(e)
{ return ((window.event) ? event.keyCode : e.keyCode) != 13; }
</script>

<link rel="stylesheet" type="text/css" href="estilo.css" />
<style type="text/css">
<!--
.Estilo13 {color: #FFFFFF; font-size: 15px; }
.Estilo14 {color: #333333;font-size: 15px}
.Estilo15 {color: #FF6600}
-->
</style>
</head>

<body style="font-family:Arial, Helvetica, sans-serif; font-size:17px" onload="maximizar()" id="tab1">
<table width="100%" border="0">
  <tr>
    <td width="49%"><img src="imagen/logo.jpg" width="262" height="120" /></td>
    <td width="51%">  <p class="Estilo6" align="right" style="padding-left:13px"><b><?php setlocale(LC_ALL,"es_ES@euro","es_ES","esp");echo ucfirst(strftime("%A %d de %B del %Y"));?> 
    </b></p>
      <p align="right" class="Estilo6 Estilo15" style="padding-left:13px"><b><? echo $_SESSION['acceso'];?></b><b><a href="index.php?accion=salir" class="Estilo6 Estilo15" style="padding-left:13px">Cerrar Sesión</a></b></p>
      <p align="right" class="Estilo6 Estilo15" style="padding-left:13px"><a href="https://webmail.1and1.es/Webmail_Login" target="_blank"><img src="imagen/email.gif" width="81" height="33" border="0" /></a></p></td>
  </tr>
</table>
<ul id="tabnav">
	<li class="tab1"><a href="citas.php">Citas</a></li>
	<li class="tab2"><a href="clientes.php">Clientes</a></li>
	<li class="tab4"><a href="visitas.php">Visitas</a></li>
	<li class="tab3"><a href="contratos.php">Contratos</a></li>
<? if ($_SESSION['nivel']==1 || $_SESSION['nivel']==0) {?>	
	<li class="tab5"><a href="usuarios.php">Usuarios</a></li>
<? } ?>
</ul>


<p><span class="Estilo11">
  <? if($msj<>'') echo "   ----------->".$msj;?>
  </span>
  <?
$medianoche=date("Y-m-d").' 00:00';
mysql_query("DELETE FROM cita WHERE fecha_hora  < '$medianoche'");
if ($_SESSION['nivel']=='1' || $_SESSION['nivel']=='0') {
	if ($_SESSION['nivel']=='1') 
	$citas=mysql_query("SELECT nombres,empresa,fecha_hora,motivo,idcita,telefono,idusuario,idcliente FROM cita WHERE provincia='".$_SESSION['provincia']."' ORDER BY fecha_hora");
	else
	$citas=mysql_query("SELECT nombres,empresa,fecha_hora,motivo,idcita,telefono,idusuario,idcliente,provincia FROM cita ORDER BY fecha_hora");
	}
else{
$citas=mysql_query("SELECT nombres,empresa,fecha_hora,motivo,idcita,telefono,idusuario,idcliente FROM cita WHERE idusuario='".$_SESSION['usuario']."' ORDER BY fecha_hora");}
?>
</p>
<div align="center">
<br />
  <table width="990" border="0">
    <tr>
	  <td width="75" bgcolor="#79C143"><div align="center" class="Estilo13">Día/Sem</div></td>
      <td width="56" bgcolor="#79C143"><div align="center" class="Estilo13">Fecha</div></td>
      <td width="52" bgcolor="#79C143"><div align="center" class="Estilo13">Hora</div></td>
      <td width="157" bgcolor="#79C143"><div align="center" class="Estilo13">Nombre</div></td>
	  <td width="216" bgcolor="#79C143"><div align="center" class="Estilo13">Motivo</div></td>
	  <td width="89" bgcolor="#79C143"><div align="center" class="Estilo13">Teléfono</div></td>
	  <td width="144" bgcolor="#79C143"><div align="center" class="Estilo13">Empresa</div></td>
<? if ($_SESSION['nivel']==1 || $_SESSION['nivel']==0) {?>	  
	  <td width="83" bgcolor="#79C143"><div align="center" class="Estilo13">Comercial</div></td>
<? } ?>    
<? if ($_SESSION['nivel']==0) {?>
<td width="80" bgcolor="#79C143"><div align="center" class="Estilo5 Estilo13">Provincia</div></td>
<? } ?>	
	</tr>
<? while ($cita=mysql_fetch_assoc($citas)) {?>	
    <tr>
	  <td bgcolor="<? if (date("Y-m-d")==substr($cita['fecha_hora'],0,10)) echo "F7D346"; else echo "D8EDC9";?>"><div align="center" class="Estilo14"><? echo dia_semana(substr($cita['fecha_hora'],0,10));?></div></td>
      <td bgcolor="<? if (date("Y-m-d")==substr($cita['fecha_hora'],0,10)) echo "F7D346"; else echo "D8EDC9";?>"><div align="center" class="Estilo14"><? echo substr(cambiarFormatoFecha(substr($cita['fecha_hora'],0,10)),0,5);?></div></td>
      <td bgcolor="<? if (date("Y-m-d")==substr($cita['fecha_hora'],0,10)) echo "F7D346"; else echo "D8EDC9";?>"><div align="center" class="Estilo14"><? echo substr($cita['fecha_hora'],11,5);?></div></td>
      <td bgcolor="<? if (date("Y-m-d")==substr($cita['fecha_hora'],0,10)) echo "F7D346"; else echo "D8EDC9";?>"><div align="left" class="Estilo14"><? if ($cita['idcliente']<>0) {?><a href="clientes.php?idcliente=<? echo $cita['idcliente'];?>&boton=Buscar" style="color:#000000" title="Ir al Cliente"><? echo $cita['nombres'];?></a><? } else echo $cita['nombres'];?></div></td>
	  <td bgcolor="<? if (date("Y-m-d")==substr($cita['fecha_hora'],0,10)) echo "F7D346"; else echo "D8EDC9";?>"><div align="left" class="Estilo14"><a href="citas.php?eliminar=<? echo $cita['idcita'];?>" onclick="return confirmar('¿Deseas eliminar la cita de <?php print($cita['nombres']);?> ?')" style="text-decoration:none; color:#FF0000">[X]</a><? echo strtolower($cita['motivo']);?></div></td>
	<td bgcolor="<? if (date("Y-m-d")==substr($cita['fecha_hora'],0,10)) echo "F7D346"; else echo "D8EDC9";?>"><div align="center" class="Estilo14"><? echo $cita['telefono'];?></div></td>
	<td bgcolor="<? if (date("Y-m-d")==substr($cita['fecha_hora'],0,10)) echo "F7D346"; else echo "D8EDC9";?>"><div align="center" class="Estilo14"><? echo $cita['empresa'];?></div></td>
<? if ($_SESSION['nivel']==1 || $_SESSION['nivel']==0) {?>		
	<td bgcolor="<? if (date("Y-m-d")==substr($cita['fecha_hora'],0,10)) echo "F7D346"; else echo "D8EDC9";?>"><div align="center" class="Estilo14"><? $comerciales=mysql_query("SELECT nombre FROM usuario WHERE idusuario=".$cita['idusuario']."");$comercial=mysql_fetch_assoc($comerciales);echo $comercial['nombre'];?></div></td>
<? } ?>
<? if ($_SESSION['nivel']==0) {?>
	<td bgcolor="<? if (date("Y-m-d")==substr($cita['fecha_hora'],0,10)) echo "F7D346"; else echo "D8EDC9";?>"><div align="center" class="Estilo14"><? echo $cita['provincia'];?></div></td>
<? } ?>	 
    </tr>
<? } ?>
  </table>
  <p class="Estilo6"><? echo mysql_num_rows($citas);?> cita(s)</p>
  <p class="Estilo6">&nbsp;</p>
</div>

<form ACTION="citas.php" METHOD="post" target="_self">
<span class="Estilo9">Citar un cliente no registrado </span>
<div id="recuadro">
<p align="center" class="Estilo6"><strong>Nombre</strong> 
  <input name="nombres" type="text" id="nombres" value="<? echo $nombres;?>" size="30"/> 
<strong>Empresa</strong> 
<input name="empresa" type="text" id="empresa" value="<? echo $empresa;?>" size="30"/></p>
  <p align="center">
    <input name="cita" type="text" id="cita" value="<? if ($cita<>'') echo $cita; else echo date("d/m/Y")." 10:00";?>" size="12"/>
	<img src="imagen/calendario.gif" title="Calendario" width="23" height="23" border="0" align="absmiddle" onclick="window.open('calendar/view_calendar.php?month=<? echo date(n);?>&amp;year=<? echo date(Y);?>', 'calendario' , 'width=350,height=300,scrollbars=NO') "/>
<input name="motivo" type="text" id="motivo" value="<? if ($motivo<>'') echo $motivo; else echo "motivo";?>" size="30"/>
<input name="telefono" type="text" id="telefono" onkeyup="mascara(this,' ',patron,true)" value="<? if ($telefono<>'') echo $telefono; else echo "telefono";?>" size="7"/>
<? if ($_SESSION['nivel']==1 || $_SESSION['nivel']==0) {
if ($_SESSION['nivel']==1)
$comerciales=mysql_query("SELECT nombre,idusuario FROM usuario WHERE nivel=2 AND provincia='".$_SESSION['provincia']."' ORDER BY nombre");
else
$comerciales=mysql_query("SELECT nombre,idusuario FROM usuario WHERE nivel=2 ORDER BY nombre");
?>
<select name="comercial" id="comercial">
	  <option value="" <? if ($comercial=="") echo "selected";?>></option>
<? while ($comercial=mysql_fetch_assoc($comerciales)) {?>		  
      <option value="<? echo $comercial['idusuario'];?>" <? if ($comercial==$comercial['idusuario']) echo "selected";?>><? echo $comercial['nombre'];?></option>
<? } ?>
    </select>
<? } ?>	
<input name="boton6" type="submit" id="boton6" style="background-color:#79C143;color:#FFFFFF;font:bolder" value="Citar"/>
</p></div>
</form>

</body>
</html>
