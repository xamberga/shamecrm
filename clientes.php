<?
session_start();
if ($_SESSION['acceso']=='') header("location:index.php"); 

include("conexion.php");
date_default_timezone_set('Atlantic/Canary');

if ($_POST['boton5']=="Ir a sus Visitas") header("Location: visitas?idcliente=".$_POST['idcliente']);
if ($_POST['boton7']=="Ir a sus Contratos") header("Location: contratos?idcliente=".$_POST['idcliente']);

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

if ($_POST['boton3']=="Guardar" && $_POST['nombres']<>'' && $_POST['apellidos']<>'' && $_POST['empresa']<>'' && ($_POST['movil']<>'' || $_POST['telefono']<>'')) {
	$nombres=ucwords(strtolower($_POST['nombres']));$apellidos=ucwords(strtolower($_POST['apellidos']));$calle=ucwords(strtolower($_POST['calle']));$municipio=ucwords(strtolower($_POST['municipio']));$empresa=ucwords(strtolower($_POST['empresa']));$piso=ucwords(strtolower($_POST['piso']));
	$email=strtolower($_POST['email']);$actividad=ucwords(strtolower($_POST['actividad']));
	$nif=str_replace("-","",$_POST['nif']);
	if (mysql_fetch_assoc(mysql_query("SELECT idcliente FROM cliente WHERE idcliente='".$_POST['idcliente']."'"))) {
		if ($_POST['observaciones'] <> 'ERROR') {
			mysql_query("UPDATE cliente SET nombres='$nombres',apellidos='$apellidos',telefono='".$_POST['telefono']."',movil='".$_POST['movil']."',email='$email',calle='$calle',numero='".$_POST['numero']."',piso='$piso',municipio='$municipio',codpostal='".$_POST['codpostal']."',empresa='$empresa',actividad='$actividad',nif='$nif',observaciones='".$_POST['observaciones']."' ,idusuario='".$_POST['idusuario']."' WHERE idcliente='".$_POST['idcliente']."'");
			if ($_POST['comercial']<>'' && $_POST['comercial']<>$_SESSION['usuario']) {
			mysql_query("UPDATE cliente SET idusuario=".$_POST['comercial']." WHERE idcliente='".$_POST['idcliente']."'",$clinica) or die(mysql_error());
			}
			$msj="Cliente modificado";
		} elseif ($_SESSION['acceso']=='Administrador') {
			mysql_query("DELETE FROM cliente WHERE idcliente='".$_POST['idcliente']."'");
			$msj="Cliente eliminado";
		}		
	}
	elseif ($existente=mysql_fetch_assoc(mysql_query("SELECT empresa,idcliente,idusuario FROM cliente WHERE movil='".$_POST['movil']."' and movil<>'' or telefono='".$_POST['telefono']."' and telefono<>'' or nif='".$_POST['nif']."' and nif<>''"))) {
		$comerciales=mysql_query("SELECT nombre FROM usuario WHERE idusuario=".$existente['idusuario']."");$comercial=mysql_fetch_assoc($comerciales);
		$msj="Cliente ya existe, asignado a ".$comercial['nombre'].". Empresa: ".$existente['empresa'];
	} else {
	mysql_query("INSERT INTO cliente (nombres,apellidos,telefono,movil,email,calle,numero,piso,municipio,codpostal,empresa,actividad,nif,observaciones,idusuario,provincia,fecha_alta) VALUES ('$nombres','$apellidos','".$_POST['telefono']."','".$_POST['movil']."','$email','$calle','".$_POST['numero']."','$piso','$municipio','".$_POST['codpostal']."','$empresa','$actividad','$nif','".$_POST['observaciones']."','".$_POST['idusuario']."','".$_SESSION['provincia']."',SYSDATE())");
	$msj="Cliente creado";
	$_POST['idcliente']=mysql_insert_id();
	}
	$clientes = mysql_query("SELECT * FROM cliente WHERE idcliente='".$_POST['idcliente']."'",$clinica) or die(mysql_error());
	$cliente = mysql_fetch_assoc($clientes);
	$host = $_SERVER['REMOTE_ADDR'];
	mysql_query("INSERT INTO cambio (fecha_hora,accion,ip,idcliente,idusuario) VALUES (SYSDATE(),'$msj','$host','".$_POST['idcliente']."','".$_SESSION['usuario']."')");
} elseif ($_POST['boton3']=="Guardar") $msj="Faltan completar datos";

if ($_REQUEST['idcliente']<>'' && $_REQUEST['boton']=="Buscar") {
$_POST['idcliente']=$_REQUEST['idcliente'];$_POST['boton']=$_REQUEST['boton'];
}

if ($_POST['boton']=="Buscar" && ($_POST['nombres']<>'' || $_POST['apellidos']<>'' || $_POST['idcliente']<>'')) {
if ($_POST['idcliente']=='') {
	$terminos=$_POST['nombres']." ".$_POST['apellidos']." ".$_POST['idcliente'];
	$clientes=mysql_query("SELECT *, MATCH(nombres,apellidos,idcliente) AGAINST('$terminos') as ratio FROM cliente WHERE MATCH(nombres,apellidos,idcliente) AGAINST('$terminos') ORDER by ratio DESC LIMIT 1");
	}
	else $clientes=mysql_query("SELECT * FROM cliente WHERE idcliente=".$_POST['idcliente']."");
	$cliente = mysql_fetch_assoc($clientes);
}

if ($_POST['boton6']=="Citar" && $_POST['cita']<>date("d/m/Y").' 10:00') {
    $fecha_cita=cambiarFormatoFecha3($_POST['cita']);
	if ($_POST['movil']<>'') $contacto=$_POST['movil']; else $contacto=$_POST['telefono']; 
	if ($_POST['comercial']=="") $_POST['comercial']=$_SESSION['usuario']; 
	mysql_query("INSERT INTO cita (fecha_hora,nombres,empresa,motivo,telefono,idusuario,idcliente,provincia) VALUES ('$fecha_cita','".$_POST['nombres']."','".$_POST['empresa']."','".$_POST['motivo']."','".$contacto."','".$_POST['comercial']."','".$_POST['idcliente']."','".$_SESSION['provincia']."')",$clinica) or die(mysql_error());
	$clientes = mysql_query("SELECT * FROM cliente WHERE idcliente='".$_POST['idcliente']."'",$clinica) or die(mysql_error());
	$cliente = mysql_fetch_assoc($clientes);
	$msj="Cita creada";
	if ($_POST['comercial']<>'' && $_POST['comercial']<>$_SESSION['usuario']) {
	$comerciales=mysql_query("SELECT nombre,email FROM usuario WHERE idusuario=".$_POST['comercial']."");
	$comercial=mysql_fetch_assoc($comerciales);
	$nombre=$comercial['nombre'];
	$email=$comercial['email'];
	$cita=substr(cambiarFormatoFecha(substr($fecha_cita,0,10)),0,5)." ".substr($fecha_cita,11,5);
	$mensaje = $nombre." tienes una nueva cita, los datos son los siguientes<br><br>";
	$mensaje .="<b>Fecha y hora</b>: ".$cita."hs<br><b>Cliente:</b> ".$_POST['nombres']."<br><b>Empresa:</b> ".$_POST['empresa']."<br><b>Motivo:</b> ".$_POST['motivo']."<br><b>Teléfono:</b> ".$contacto."<br><br>";
	$mensaje .= "La cita fue creada por ".$_SESSION['acceso'].". El cliente ya está registrado en el sistema.";
	$mail = new PHPMailer();
	$mail->IsSendmail() ;
	$mail->SMTPAuth = true;
    $mail->Host = "da.host";
	$mail->Username = "crm@dahost"; 
	$mail->Password = "dapasswd";
	$mail->From     = "lefrom";
	$mail->FromName = "FROMNAME";
	$mail->AddAddress($email);
	$mail->WordWrap = 50;
	$mail->IsHTML(true);
	$mail->Subject  =  "Nueva Cita";
	$mail->Body     =  $mensaje;
	if ($mail->Send()) $msj .=" - Enviada por email"; else $msj .=" - No enviada por email";
	mysql_query("UPDATE cliente SET idusuario=".$_POST['comercial']." WHERE idcliente='".$_POST['idcliente']."'",$clinica) or die(mysql_error());
	}
	if ($_POST['administrador']<>'' && $_POST['administrador']<>$_SESSION['usuario']) {
	$administradores=mysql_query("SELECT nombre,email FROM usuario WHERE idusuario=".$_POST['administrador']."");
	$administrador=mysql_fetch_assoc($administradores);
	$nombre=$administrador['nombre'];
	$email=$administrador['email'];
	$cita=substr(cambiarFormatoFecha(substr($fecha_cita,0,10)),0,5)." ".substr($fecha_cita,11,5);
	$mensaje = $nombre." tienes una nueva cita, los datos son los siguientes<br><br>";
	$mensaje .="<b>Fecha y hora</b>: ".$cita."hs<br><b>Cliente:</b> ".$_POST['nombres']."<br><b>Empresa:</b> ".$_POST['empresa']."<br><b>Motivo:</b> ".$_POST['motivo']."<br><b>Teléfono:</b> ".$contacto."<br><br>";
	$mensaje .= "La cita fue creada por ".$_SESSION['acceso'].". El cliente ya está registrado en el sistema.";
	$mail = new PHPMailer();
	$mail->IsSendmail() ;
	$mail->SMTPAuth = true;
    $mail->Host = "the.host";
	$mail->Username = "the@crm.crm"; 
	$mail->Password = "lepass";
	$mail->From     = "address@addrezz.coum";
	$mail->FromName = "FROM";
	$mail->AddAddress($email);
	$mail->WordWrap = 50;
	$mail->IsHTML(true);
	$mail->Subject  =  "Nueva Cita";
	$mail->Body     =  $mensaje;
	if ($mail->Send()) $msj .=" - Enviada por email"; else $msj .=" - No enviada por email";
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>CLIENTES - CRM</title>
<script language="JavaScript">
function maximizar(){
window.moveTo(0,0);
window.resizeTo(screen.width,screen.height);
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
<script type="text/javascript">
document.onkeypress = KeyPressed;
function KeyPressed(e)
{ return ((window.event) ? event.keyCode : e.keyCode) != 13; }
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


.Estilo5 {color: #FFFFFF}

#recuadro{ 
border: 1px solid #008300; 
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
	color: #008000;
	padding-left:103px;
}
.Estilo11 {color: #FF0000; font-weight: bold; }
-->
</style>
<link rel="stylesheet" type="text/css" href="estilo.css" />
<style type="text/css">
<!--
.Estilo13 {font-size: 15px}
.Estilo14 {color: #6AAC39}
.Estilo16 {color: #333333; font-size: 15px; }
.Estilo17 {
	color: #333333;
	font-weight: bold;
}
.Estilo18 {color: #FF9900}
.Estilo19 {color: #FF6600}
.Estilo20 {color: #79C143}
-->
</style>
</head>

<body style="font-family:Arial, Helvetica, sans-serif; font-size:17px" onload="maximizar()" id="tab2">
<table width="100%" border="0">
  <tr>
    <td width="49%"><img src="imagen/logo.jpg" width="262" height="120" /></td>
    <td width="51%" class="Estilo9 Estilo13"><p align="right" class="Estilo6 Estilo15 Estilo20" style="padding-left:13px"><b><?php setlocale(LC_ALL,"es_ES@euro","es_ES","esp");echo ucfirst(strftime("%A %d de %B del %Y"));?> 
    </b></p>
	<p align="right" class="Estilo6 Estilo15 Estilo19" style="padding-left:13px"><b><? echo $_SESSION['acceso'];?></b><b><a href="index.php?accion=salir" class="Estilo6 Estilo15 Estilo19" style="padding-left:13px">Cerrar Sesión</a></b></p><p align="right" class="Estilo6 Estilo15 Estilo19" style="padding-left:13px"><a href="https://webmail.1and1.es/Webmail_Login" target="_blank"><img src="imagen/email.gif" width="81" height="33" border="0" /></a></p></td>
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
<div align="center" class="Estilo13 Estilo14">
      <p><strong>Empresa</strong> -&gt; 
          <?
for($i=65; $i<=90; $i++) {  
    $letra = chr($i);  
    echo '<a href="clientes.php?ininom='.$_REQUEST['ininom'].'&iniemp='.$letra.'&agecom='.$_REQUEST['agecom'].'" class="Estilo14">&nbsp'.$letra.'&nbsp</a> | ';  
}
?>
              <a href="clientes.php?ininom=%&iniemp=%" class="Estilo14">Todos</a>        </p>
      <p><strong>Nombres</strong> -&gt; 
          <?
for($i=65; $i<=90; $i++) {  
    $letra = chr($i);  
    echo '<a href="clientes.php?ininom='.$letra.'&iniemp='.$_REQUEST['iniemp'].'&agecom='.$_REQUEST['agecom'].'" class="Estilo14">&nbsp'.$letra.'&nbsp</a> | ';  
}
?> 
            <a href="clientes.php?ininom=%&iniemp=%" class="Estilo14">Todos</a> 
        <? if ($_SESSION['nivel']==1 || $_SESSION['nivel']==0) {?>
        <br />
        <br />
          <strong>Comercial</strong> -&gt; 
          <?
if ($_SESSION['nivel']==1)
$agentes=mysql_query("SELECT distinct a.nombre as nombre, b.idusuario as usuario
FROM usuario a, cliente b where a.idusuario=b.idusuario AND a.provincia='".$_SESSION['provincia']."' ORDER BY a.nombre");
else
$agentes=mysql_query("SELECT distinct a.nombre as nombre, b.idusuario as usuario
FROM usuario a, cliente b where a.idusuario=b.idusuario ORDER BY a.nombre");
while ($agente=mysql_fetch_assoc($agentes)) {
echo '<a href="clientes.php?ininom='.$_REQUEST['ininom'].'&iniemp='.$_REQUEST['iniemp'].'&agecom='.$agente['usuario'].'" class="Estilo14">&nbsp'.$agente['nombre'].'&nbsp</a> | ';  
}
?>
              <a href="clientes.php?ininom=%&iniemp=%" class="Estilo14">Todos</a> 
        <? } ?>  
            </p>
</div>
  <span class="Estilo11">
  <? if($msj<>'') echo "<br>   ----------->".$msj;?>
  </span>
<?

if ($_REQUEST['ininom']<>'' || $_REQUEST['iniemp']<>'' || $_REQUEST['agecom']<>'') {
if ($_SESSION['nivel']=='1' || $_SESSION['nivel']=='0') {
	if ($_SESSION['nivel']=='1') 
$clientes=mysql_query("SELECT idcliente,nombres,fecha_alta,telefono,movil,empresa,idusuario FROM cliente WHERE nombres LIKE '".$_REQUEST['ininom']."%' AND empresa LIKE '".$_REQUEST['iniemp']."%' AND idusuario LIKE '".$_REQUEST['agecom']."%' AND provincia='".$_SESSION['provincia']."' ORDER BY fecha_alta");
	else  
$clientes=mysql_query("SELECT idcliente,nombres,fecha_alta,telefono,movil,empresa,idusuario,provincia FROM cliente WHERE nombres LIKE '".$_REQUEST['ininom']."%' AND empresa LIKE '".$_REQUEST['iniemp']."%' AND idusuario LIKE '".$_REQUEST['agecom']."%' ORDER BY fecha_alta");
}
else $clientes=mysql_query("SELECT idcliente,nombres,fecha_alta,telefono,movil,empresa,idusuario FROM cliente WHERE nombres LIKE '".$_REQUEST['ininom']."%' AND empresa LIKE '".$_REQUEST['iniemp']."%' AND idusuario=".$_SESSION['usuario']." ORDER BY fecha_alta");

?>
<div align="center">
  <p class="Estilo13 Estilo14"><? if ($_REQUEST['ininom']<>'' && $_REQUEST['ininom']<>'%')echo "<b>Nombre</b> comienza con <b>".$_REQUEST['ininom']."</b>";?>  <? if ($_REQUEST['iniemp']<>'' && $_REQUEST['iniemp']<>'%')echo "<br><b>Empresa</b> comienza con <b>".$_REQUEST['iniemp']."</b>";?><? if ($_REQUEST['agecom']<>'' && $_REQUEST['agecom']<>'%') {$nom_agentes=mysql_query("SELECT nombre FROM usuario WHERE idusuario=".$_REQUEST['agecom']."");$nom_agente=mysql_fetch_assoc($nom_agentes);echo "<br><b>Comercial</b> es <b>".$nom_agente['nombre']."</b>";}?> </p>
  <table width="990" border="0">
    <tr>
      <td width="92" bgcolor="#79C143"><div align="center" class="Estilo5 Estilo13">Fecha Alta </div></td>
	  <td width="242" bgcolor="#79C143"><div align="center" class="Estilo5 Estilo13">Empresa</div></td>
      <td width="178" bgcolor="#79C143"><div align="center" class="Estilo5 Estilo13">Nombres</div></td>
      <td width="118" bgcolor="#79C143"><div align="center" class="Estilo5 Estilo13">M&oacute;vil</div></td>
      <td width="115" bgcolor="#79C143"><div align="center" class="Estilo5 Estilo13">Fijo</div></td>
<? if ($_SESSION['nivel']==1 || $_SESSION['nivel']==0) {?>
	  <td width="137" bgcolor="#79C143"><div align="center" class="Estilo5 Estilo13">Comercial</div></td>
<? } ?>
<? if ($_SESSION['nivel']==0) {?>
<td width="78" bgcolor="#79C143"><div align="center" class="Estilo5 Estilo13">Provincia</div></td>
<? } ?>	 
    </tr>
<? while ($cliente=mysql_fetch_assoc($clientes)) {?>	
    <tr>
      <td bgcolor="D8EDC9"><div align="center" class="Estilo16"><? echo cambiarFormatoFecha(substr($cliente['fecha_alta'],0,10));?></div><div align="center"></div></td>
	  <td bgcolor="D8EDC9"><div align="left" class="Estilo16"><a href="clientes.php?idcliente=<? echo $cliente['idcliente'];?>&boton=Buscar" title="Ir al Cliente" class="Estilo16"><? echo $cliente['empresa'];?></a></div></td>
      <td bgcolor="D8EDC9"><div align="left" class="Estilo16"><a href="clientes.php?idcliente=<? echo $cliente['idcliente'];?>&boton=Buscar" title="Ir al Cliente" class="Estilo16"><? echo $cliente['nombres'];?></a></div></td>
	  <td bgcolor="D8EDC9"><div align="center" class="Estilo16"><? echo $cliente['movil'];?></div></td>
      <td bgcolor="D8EDC9"><div align="center" class="Estilo16"><? echo $cliente['telefono'];?></div></td>
<? if ($_SESSION['nivel']==1 || $_SESSION['nivel']==0) {?>
	  <td bgcolor="D8EDC9"><div align="center" class="Estilo16">
      <? $comerciales=mysql_query("SELECT nombre FROM usuario WHERE idusuario=".$cliente['idusuario']."");$comercial=mysql_fetch_assoc($comerciales);echo $comercial['nombre'];?></div></td>
<? } ?>
<? if ($_SESSION['nivel']==0) {?>
<td bgcolor="D8EDC9"><div align="center" class="Estilo16"><? echo $cliente['provincia'];?></div></td>
<? } ?>	 
    </tr>
<? } ?>
  </table>
  <p class="Estilo13 Estilo14"><? echo mysql_num_rows($clientes);?> registro(s)</p>
</div>
<? } ?>
<form ACTION="clientes.php" METHOD="post" target="_self" enctype="multipart/form-data">
<br />
<span class="Estilo9 Estilo18"><? if ($cliente['idcliente']=='') echo "REGISTRE UN NUEVO CLIENTE"; else echo "DATOS DEL CLIENTE"?></span>
<br />
<br />
<span class="Estilo9">CONTACTO</span>
<div id="recuadro">
<p align="center"><span class="Estilo17">Nombres</span> 
<input name="nombres" type="text" id="nombres" value="<? if ($cliente['nombres']<>'') echo $cliente['nombres']; else echo $_POST['nombres'];?>" size="30"/> 
<strong class="Estilo17">Apellidos</strong> 
<input name="apellidos" type="text" id="apellidos" value="<? if ($cliente['apellidos']<>'') echo $cliente['apellidos']; else echo $_POST['apellidos'];?>" size="30"/></p>
  <p align="center"><strong class="Estilo17">M&oacute;vil</strong> 
    <input name="movil" type="text" id="movil" onkeyup="mascara(this,' ',patron,true)" value="<? if ($cliente['movil']) echo $cliente['movil']; else echo $_POST['movil'];?>" size="10" /> 
<strong class="Estilo17">Correo electr&oacute;nico </strong>
    <input name="email" type="text" id="email" value="<? if ($cliente['email']) echo $cliente['email']; else echo $_POST['email'];?>" size="30"/>
  </p>
  <div align="center">
  <? if ($cliente['idcliente']<>'' || $_POST['idcliente']<>'') {?>
  <strong class="Estilo17">Fecha y Hora</strong>
  <input name="cita" type="text" id="cita" value="<? if ($_POST['cita']<>'') echo $_POST['cita']; else echo date("d/m/Y H:i");?>" size="12"/>
  <img src="imagen/calendario.gif" title="Calendario" width="23" height="23" border="0" align="absmiddle" onclick="window.open('calendar/view_calendar.php?month=<? echo date(n);?>&amp;year=<? echo date(Y);?>', 'calendario' , 'width=350,height=300,scrollbars=NO') "/>
  <input name="motivo" type="text" id="motivo" value="<? if ($_POST['motivo']<>'') echo $_POST['motivo']; else echo "motivo";?>" size="18"/>
  
  
  <? if ($_SESSION['nivel']==1 || $_SESSION['nivel']==0) {
  if ($_SESSION['nivel']==1)
$personas=mysql_query("SELECT nombre,idusuario FROM usuario WHERE nombre<>'Administrador' AND activo=1 AND provincia='".$_SESSION['provincia']."' ORDER BY nombre");
  else
$personas=mysql_query("SELECT nombre,idusuario FROM usuario WHERE nombre<>'Administrador' AND activo=1 ORDER BY nombre");  
?>
<select name="comercial" id="comercial">
<option value="" selected></option>
<? while ($persona=mysql_fetch_assoc($personas)) {?>		  
      <option value="<? echo $persona['idusuario'];?>" <? if ($comercial==$persona['idusuario'] || $cliente['idusuario']==$persona['idusuario']) echo "selected";?>><? echo $persona['nombre'];?></option>
<? } ?>
    </select>
<? } else {
$personas=mysql_query("SELECT nombre,idusuario FROM usuario WHERE idusuario<>".$_SESSION['usuario']." AND nombre<>'Administrador' AND activo=1 AND provincia='".$_SESSION['provincia']."' ORDER BY nombre");
?>	
<select name="administrador" id="administrador">
	  <option value="" selected></option>
<? while ($persona=mysql_fetch_assoc($personas)) {?>		  
      <option value="<? echo $persona['idusuario'];?>" <? if ($administrador==$persona['idusuario'] || $cliente['idusuario']==$persona['idusuario']) echo "selected";?>><? echo $persona['nombre'];?></option>
<? } ?>
    </select>
<?  } ?>
  <input name="boton6" type="submit" id="boton6" style="background-color:#79C143;color:#FFFFFF;font:bolder" value="Citar"/>
  <? } ?>
    </div>
</div>
    <span class="Estilo9">EMPRESA</span>
  <div id="recuadro">
    <p align="center"><strong class="Estilo17">Empresa</strong>
      <input name="empresa" type="text" id="empresa" value="<? if ($cliente['empresa']<>'') echo $cliente['empresa']; else echo $_POST['empresa'];?>" size="20"/>
    <strong class="Estilo17">Actividad</strong>
    <input name="actividad" type="text" id="actividad" value="<? if ($cliente['actividad']<>'') echo $cliente['actividad']; else echo $_POST['actividad'];?>" size="20"/>
    </p>
    <p align="center">      <strong class="Estilo17">NIF</strong> 
      <input name="nif" type="text" id="nif" value="<? if ($cliente['nif']<>'') echo $cliente['nif']; else echo $_POST['nif'];?>" size="10"/>
      <strong class="Estilo17">Tel&eacute;fono</strong> 
      <input name="telefono" type="text" id="telefono" onkeyup="mascara(this,' ',patron,true)" value="<? if ($cliente['telefono']<>'') echo $cliente['telefono']; else echo $_POST['telefono'];?>" size="10"/>
    </p>
    <p align="center">
      <strong class="Estilo17">Calle</strong>
    <input name="calle" type="text" id="calle" value="<? if ($cliente['calle']<>'') echo $cliente['calle']; else echo $_POST['calle'];?>" size="30"/>
    <strong class="Estilo17">Número</strong>
    <input name="numero" type="text" id="numero" value="<? if ($cliente['numero']<>'') echo $cliente['numero']; else echo $_POST['numero'];?>" size="5"/> 
    <strong class="Estilo17">Piso</strong> 
    <input name="piso" type="text" id="piso" value="<? if ($cliente['piso']<>'') echo $cliente['piso']; else echo $_POST['piso'];?>" size="20"/>
  </p>
  <p align="center"><strong class="Estilo17">Municipio</strong> 
    <input name="municipio" type="text" id="municipio" value="<? if ($cliente['municipio']<>'') echo $cliente['municipio']; else echo $_POST['municipio'];?>" size="20"/> 
    <strong class="Estilo17">C&oacute;digo Postal</strong> 
    <input name="codpostal" type="text" id="codpostal" value="<? if ($cliente['codpostal']<>'') echo $cliente['codpostal']; else echo $_POST['codpostal'];?>" size="10"/> 
</p>
  <p align="center">
    <label>
    <textarea name="observaciones" cols="90" rows="5" id="observaciones"><? echo $cliente['observaciones'];?></textarea>
    </label>
  </p>
  </div>
<? if ($cliente['idcliente']<>'') {?>  
 <span class="Estilo9">FECHA/HORA DE ALTA </span>
    <div id="recuadro">
  <p align="center"><strong class="Estilo16">Fecha</strong>
    <input name="fecha" type="text" id="fecha" value="<? echo cambiarFormatoFecha(substr($cliente['fecha_alta'],0,10));?>" size="6" readonly="" style="background-color:#F7D346"/>
    <strong class="Estilo16">Hora</strong>
    <input name="hora" type="text" id="hora" value="<? echo substr($cliente['fecha_alta'],11,8);?>" size="4" readonly="" style="background-color:#F7D346"/>
  </p>
  </div>
<? } ?>  
  <p align="center">
    <label>
	<input name="idcliente" type="hidden" id="idcliente" value="<? echo $cliente['idcliente'];?>" size="30"/>
	<input name="idusuario" type="hidden" id="idusuario" value="<? echo $_SESSION['usuario'];?>" size="30"/>
    <input name="boton3" type="submit" id="boton3" style="background-color:#79C143;color:#FFFFFF;font:bolder; padding:5px" value="Guardar"/>
    <? if ($cliente['idcliente']<>'') {?>
	<br />
	<br />
	<input name="boton5" type="submit" id="boton5" style="background-color:#79C143;color:#FFFFFF;font:bolder; padding:5px" value="Ir a sus Visitas"/>
	<br />
	<br />
	<input name="boton7" type="submit" id="boton7" style="background-color:#79C143;color:#FFFFFF;font:bolder; padding:5px" value="Ir a sus Contratos"/>
	<? 
	}?>
    </label>
  </p>
  <p align="center">&nbsp;</p>
  <p align="center" class="Estilo14 Estilo13">Total de registros en la base de clientes: <? echo mysql_num_rows(mysql_query("SELECT * FROM cliente"));?> </p>
</form>

<script type="text/javascript" language="javascript"> 
// <![CDATA[
 
SI.Files.stylizeAll();
 
/*
--------------------------------
Known to work in:
--------------------------------
- IE 5.5+
- Firefox 1.5+
- Safari 2+
                          
--------------------------------
Known to degrade gracefully in:
--------------------------------
- Opera
- IE 5.01
 
--------------------------------
Optional configuration:
 
Change before making method calls.
--------------------------------
SI.Files.htmlClass = 'SI-FILES-STYLIZED';
SI.Files.fileClass = 'file';
SI.Files.wrapClass = 'cabinet';
 
--------------------------------
Alternate methods:
--------------------------------
SI.Files.stylizeById('input-id');
SI.Files.stylize(HTMLInputNode);
 
--------------------------------
*/
 
// ]]>
</script>
</body>
</html>
