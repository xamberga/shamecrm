<?
#USO DE VARIABLES DE SESION
session_start();

if ($_REQUEST['idcliente']<>'') $_POST['idcliente']=$_REQUEST['idcliente'];
if ($_REQUEST['idvisita']<>'') $_POST['idvisita']=$_REQUEST['idvisita'];

#SI LA VARIABLE DE SESION ACCESO ESTA EN BLANCO REDIRECCIONA A FORMULARIO DE LOGIN
if ($_SESSION['acceso']=='') header("location:index.php"); 

#FUNCION PARA CORTAR EL TEXTO EN UNA CANTIDAD DE CARACTERES, SE USA PARA MOTIVOS DE VISITA
function corta_palabra($palabra,$num){
$largo=strlen($palabra);//indicarme el largo de una cadena
$cadena=substr($palabra,0,$num);
return $cadena;
}

#FUNCION PARA DAR FORMATO A CAMPOS DE FECHA QUE SE VAN A MOSTRAR EN PANTALLA
function cambiarFormatoFecha($fecha){ 
    list($dia,$mes,$anio)=explode("-",$fecha); 
    return $anio."/".$mes."/".$dia; 
}

#FUNCION PARA DAR FORMATO A FECHAS ANTES DE GUARDAR EN LA BASE DE DATOS
function cambiarFormatoFecha2($fecha){
    list($anio,$mes,$dia)=explode("/",$fecha);
    return $dia."-".$mes."-".$anio;
}

#CONEXION A LA BASE DE DATOS
include("conexion.php");

#ABRE LA PAGINA DE PACIENTES CON UNO SELECCIONADO
if ($_POST['boton4']=="Ir al Cliente") {
header("Location: clientes.php?idcliente=".$_POST['idcliente']."&boton=Buscar");
}

#SI PULSA NUEVA VISITA BLANQUEA TODOS LOS CAMPOS DEL FORMULARIO
if ($_POST['boton2']=="Nueva Visita") {
$_POST['idvisita']='';$_POST['motivo']='';$_POST['observaciones']='';
}

#SI PULSA EN GUARDAR LA VISITA
if ($_POST['boton']=="Guardar") {
	#SI SE SUBE UN FICHERO EN EL FORMULARIO
   	if (is_uploaded_file($_FILES['archivo']['tmp_name'])) { 
   	  $ext = strrchr($_FILES['archivo']['name'],'.');
	  $fecha2=str_replace("/","",$_POST['fecha']);
	  $hora2=str_replace(":","",$_POST['hora']);
   	  $nombre_fichero=$fecha2.$hora2.$ext;
      copy($_FILES['archivo']['tmp_name'], "documentos/".$nombre_fichero); 
    	}
	#SI ES UNA VISITA YA GUARDADA EN BASE	
	if ($_POST['idvisita']<>'') {
		if ($_POST['observaciones'] <> 'ERROR') {
			if ($_SESSION['nivel']==1 || $_SESSION['nivel']==0 || $_SESSION['usuario']==$_POST['idusuario']) {
			mysql_query("UPDATE visita SET documento='$nombre_fichero',motivo='".$_POST['motivo']."',observaciones='".$_POST['observaciones']."',idusuario='".$_POST['idusuario']."' WHERE idvisita='".$_POST['idvisita']."'");
			$msj="Visita modificada";
			} else $msj="Usuario incorrecto";
		} elseif ($_SESSION['acceso']=='Administrador') {
			mysql_query("DELETE FROM visita WHERE idvisita='".$_POST['idvisita']."'");
			$msj="Visita eliminada";
		}
	} 
	#SI ES UNA VISITA NUEVA
	else {
		#INSERTA EL REGISTRO NUEVO EN LA BASE DE DATOS
		mysql_query("INSERT INTO visita (timestamp,fecha,hora,idcliente,documento,motivo,idusuario,observaciones,provincia) VALUES (SYSDATE(),'".$_POST['fecha']."','".$_POST['hora']."',".$_POST['idcliente'].",'$nombre_fichero','".$_POST['motivo']."','".$_POST['idusuario']."','".$_POST['observaciones']."','".$_SESSION['provincia']."')");
    	$msj="Visita creada";
		$_POST['idvisita']=mysql_insert_id();
		#ELIMINA LA CITA DE ESE PACIENTE
		mysql_query("DELETE FROM cita WHERE idcliente=".$_POST['idcliente']." and fecha_hora < SYSDATE()");
	}
	#ACTUALIZA EL HISTORIAL EN TABLA CAMBIO
	$host = $_SERVER['REMOTE_ADDR'];
	mysql_query("INSERT INTO cambio (fecha_hora,accion,ip,idcliente,idusuario) VALUES (SYSDATE(),'$msj','$host','".$_POST['idcliente']."','".$_SESSION['usuario']."')");
}

#CARGA LOS DATOS DEL CLIENTE DE LA BASE
if ($_POST['idcliente']<>'') {$clientes = mysql_query("SELECT * FROM cliente WHERE idcliente='".$_POST['idcliente']."'",$clinica) or die(mysql_error());
$cliente = mysql_fetch_assoc($clientes);}

#CARGA LOS DATOS DE VISITA DE LA BASE
if ($_POST['idvisita']<>'') {$visitas = mysql_query("SELECT * FROM visita WHERE idvisita='".$_POST['idvisita']."'",$clinica) or die(mysql_error());
$visita = mysql_fetch_assoc($visitas);}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>VISITAS - CRM</title>
<script type="text/javascript" src="js/si.files.js"></script>
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

.Estilo3 {color: #FF0000}

.SI-FILES-STYLIZED label.cabinet
{
    width: 45px;
    height: 31px;
    background: url(imagen/noescaner.gif) 0 0 no-repeat;

    display: block;
    overflow: hidden;
    cursor: pointer;
}

.SI-FILES-STYLIZED label.cabinet input.file
{
    position: relative;
    height: 100%;
    width: auto;
    opacity: 0;
    -moz-opacity: 0;
    filter:progid:DXImageTransform.Microsoft.Alpha(opacity=0);
}

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
.Estilo12 {color: #FFFFFF}
-->
</style>

<link rel="stylesheet" type="text/css" href="estilo.css" />
<style type="text/css">
<!--
.Estilo14 {
	color: #333333;
	font-weight: bold;
}
.Estilo15 {font-size: 15px}
.Estilo16 {color: #6aac39}
.Estilo17 {color: #FF9900}
.Estilo18 {color: #FF6600}
.Estilo19 {color: #79C143}
-->
</style>
</head>

<body style="font-family:Arial, Helvetica, sans-serif; font-size:17px" onload="maximizar()" id="tab4">
<table width="100%" border="0">
  <tr>
    <td width="49%"><img src="imagen/logo.jpg" width="262" height="120" /></td>
    <td width="51%"><p align="right" class="Estilo6 Estilo15 Estilo19" style="padding-left:13px"><b><?php setlocale(LC_ALL,"es_ES@euro","es_ES","esp");echo ucfirst(strftime("%A %d de %B del %Y"));?> 
    </b></p>
	<p align="right" class="Estilo6 Estilo15 Estilo18" style="padding-left:13px"><b><? echo $_SESSION['acceso'];?></b><b><a href="index.php?accion=salir" class="Estilo6 Estilo15 Estilo18" style="padding-left:13px">Cerrar Sesión</a></b></p><p align="right" class="Estilo6 Estilo15 Estilo18" style="padding-left:13px"><a href="https://webmail.1and1.es/Webmail_Login" target="_blank"><img src="imagen/email.gif" width="81" height="33" border="0" /></a></p></td>
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

<span class="Estilo11">
<? if($msj<>'') echo "   ----------->".$msj;?>
</span>
<? if ($_POST['idcliente']!='') {?>
<form ACTION="visitas.php" METHOD="post" target="_self" enctype="multipart/form-data">
<p align="center" class="Estilo3" style=" line-height:31px">
<? if ($visita['documento']<>'') {?>
<a href="documentos/<? echo $visita['documento'];?>" target="_blank"><img src="imagen/escaner.gif" width="45" height="31" hspace="10" border="0" align="absmiddle" /></a>
<? } else {?>
<label class="cabinet">
    <input name="archivo" type="file" class="file" id="archivo" />
</label>
<? } ?></p>

<span class="Estilo9 Estilo17">
<? if ($visita['idvisita']=='') echo "REGISTRE UNA NUEVO VISITA"; else echo "DATOS DE LA VISITA"?>
</span>
<br />
<br />
<span class="Estilo9">CLIENTE</span>
<div id="recuadro">
<p align="center"><span class="Estilo14">Nombres</span> 
<input name="nombres" type="text" id="nombres" value="<? echo $cliente['nombres'];?>" size="30" readonly="" style="background-color:#F7D346"/> 
<strong class="Estilo14">Apellidos</strong> 
<input name="apellidos" type="text" id="apellidos" value="<? echo $cliente['apellidos'];?>" size="30" readonly="" style="background-color:#F7D346"/></p>
  <p align="center"><strong class="Estilo14">Empresa</strong> 
    <input name="empresa" type="text" id="empresa" value="<? echo $cliente['empresa'];?>" size="30" readonly="" style="background-color:#F7D346"/> 
    <input name="boton4" type="submit" id="boton4" style="background-color:#79C143;color:#FFFFFF;font:bolder" value="Ir al Cliente"/>
  </p>
  </div>
  <span class="Estilo9">DATOS DE LA VISITA</span>
<div id="recuadro">
  <p align="center"><strong class="Estilo14"> Motivo </strong><br />
    <textarea name="motivo" cols="83" rows="2" id="motivo"><? if ($visita['motivo']<>'') echo $visita['motivo']; else echo $_POST['motivo'];?></textarea>
  </p>
  <p align="center"><strong class="Estilo14"> Observaciones </strong><br />
    <textarea name="observaciones" cols="83" rows="7" id="observaciones"><? if ($visita['observaciones']<>'') echo $visita['observaciones']; else echo $_POST['observaciones'];?></textarea>
  </p>
  </div>
    <span class="Estilo9">FECHA/HORA</span>
<div id="recuadro">
  <p align="center"><strong class="Estilo14">Fecha</strong>
    <input name="fecha" type="text" id="fecha" value="<? if ($visita['fecha']<>'') echo $visita['fecha']; else echo date("d/m/Y");?>" size="6" readonly="" <? if ($_POST['idvisita']<>'') echo "style=\"background-color:#F7D346\""; ?> style="background-color:#F7D346"/>
    <strong class="Estilo14">Hora</strong>
    <input name="hora" type="text" id="hora" value="<? if ($visita['hora']<>'') echo $visita['hora']; else {date_default_timezone_set('Europe/London');echo date("G:i:s");}?>" size="4" readonly="" <? if ($_POST['idvisita']<>'') echo "style=\"background-color:#F7D346\""; ?> style="background-color:#F7D346"/>
    <strong class="Estilo14"> Usuario</strong>
    <input name="usuario" type="text" id="usuario" value="<? if ($visita['idusuario']<>'') {$comerciales=mysql_query("SELECT nombre FROM usuario WHERE idusuario=".$visita['idusuario']."");
$comercial=mysql_fetch_assoc($comerciales);echo $comercial['nombre'];} else echo $_SESSION['acceso'];?>" size="10" readonly="" style="background-color:#F7D346" />

    <? if ($_POST['idvisita']<>'') {?>
<input name="boton2" type="submit" id="boton2" style="background-color:#79C143;color:#FFFFFF;font:bolder" value="Nueva Visita"/>
  <? } ?>  
  </p>
  </div>
  <p align="center">
    <label>
	<input name="idcliente" type="hidden" id="idcliente" value="<? echo $_POST['idcliente'];?>"/>
	<input name="idvisita" type="hidden" id="idvisita" value="<? echo $_POST['idvisita'];?>"/>
    <input name="idusuario" type="hidden" id="idusuario" value="<? echo $_SESSION['usuario'];?>"/>
    <input name="boton" type="submit" id="boton3" style="background-color:#79C143;color:#FFFFFF;font:bolder; padding:5px" value="Guardar"/>
    </label>
  </p>
</form>
<?
$visitas=mysql_query("SELECT fecha,hora,motivo,idvisita,documento FROM visita WHERE idcliente='".$_POST['idcliente']."' ORDER BY idvisita DESC");
?>
<div align="center">
<p>&nbsp;</p>
  <p>&nbsp;</p>
  <p class="Estilo6 Estilo15 Estilo16">Listado de visitas al cliente ordenado por fecha y hora</p>
  <table width="800" border="0">
    <tr>
      <td width="115" bgcolor="#79C143"><div align="center" class="Estilo5 Estilo15 Estilo12">Fecha</div></td>
      <td width="81" bgcolor="#79C143"><div align="center" class="Estilo5 Estilo15 Estilo12">Hora</div></td>
      <td width="541" bgcolor="#79C143"><div align="center" class="Estilo5 Estilo15 Estilo12">Motivo de la Visita </div></td>
      <td width="45"><div align="center" class="Estilo5"></div></td>
    </tr>
<? while ($visita=mysql_fetch_assoc($visitas)) {?>	
    <tr>
      <td bgcolor="D8EDC9"><div align="center" class="Estilo15"><? echo $visita['fecha'];?></div></td>
      <td bgcolor="D8EDC9"><div align="center" class="Estilo15"><? echo $visita['hora'];?></div></td>
      <td bgcolor="D8EDC9"><div align="left" class="Estilo15"><? echo $visita['motivo'];?><? if ($visita['documento']<>'') {?><img src="imagen/foto.gif" width="15" height="20" align="top" /><? } ?></div></td>
      <td bgcolor="D8EDC9"><div align="center"><input name="boton3" type="submit" id="boton3" style="background-color:#79C143;color:#FFFFFF;font:bolder" value="Ver" onclick="window.open('visitas.php?idcliente=<? echo $_POST['idcliente'];?>&idvisita=<? echo $visita['idvisita'];?>','_self')"/></div></td>
    </tr>
<? } ?>
  </table>
</div>  
<p>
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
<? } else {?>
<?
if ($_SESSION['nivel']=='1' || $_SESSION['nivel']=='0') {
	if ($_SESSION['nivel']=='1')
$visitas=mysql_query("SELECT fecha,hora,motivo,idvisita,idcliente,idusuario FROM visita WHERE provincia='".$_SESSION['provincia']."' ORDER BY timestamp DESC LIMIT 200");
	else
$visitas=mysql_query("SELECT fecha,hora,motivo,idvisita,idcliente,idusuario,provincia FROM visita ORDER BY timestamp DESC LIMIT 200");	
 }
else {
$visitas=mysql_query("SELECT fecha,hora,motivo,idvisita,idcliente,idusuario FROM visita WHERE idusuario='".$_SESSION['usuario']."' ORDER BY timestamp DESC LIMIT 200");
 }

$hoy=date("d/m/Y");

?>
<table width="990" border="0" align="center">
  <tr>
    <td width="80" bgcolor="#79C143"><div align="center" class="Estilo5 Estilo12 Estilo15">Fecha</div></td>
    <td width="47" bgcolor="#79C143"><div align="center" class="Estilo5 Estilo12 Estilo15">Hora</div></td>
    <td width="300" bgcolor="#79C143"><div align="center" class="Estilo5 Estilo12 Estilo15">Motivo de la Visita </div></td>
    <td width="143" bgcolor="#79C143"><div align="center" class="Estilo5 Estilo12 Estilo15">Cliente</div></td>
    <td width="188" bgcolor="#79C143"><div align="center" class="Estilo5 Estilo12 Estilo15">Empresa</div></td>
    <td width="119" bgcolor="#79C143"><div align="center" class="Estilo5 Estilo12 Estilo15">Usuario</div></td>
<? if ($_SESSION['nivel']==0) {?>
<td width="83" bgcolor="#79C143"><div align="center" class="Estilo5 Estilo12 Estilo15">Provincia</div></td>
<? } ?>	  
  </tr>
<? while ($visita=mysql_fetch_assoc($visitas)) {
$_POST['idcliente']=$visita['idcliente'];
if ($_POST['idcliente']!=0) $clientes = mysql_query("SELECT nombres,apellidos,idcliente,empresa FROM cliente WHERE idcliente='".$_POST['idcliente']."'",$clinica) or die(mysql_error());
$cliente = mysql_fetch_assoc($clientes);
?>	
  <tr>
    <td bgcolor="<? if (date("d/m/Y")==$visita['fecha']) echo "F7D346"; else echo "D8EDC9";?>"><div align="center" class="Estilo15"><? echo $visita['fecha'];?></div></td>
    <td bgcolor="<? if (date("d/m/Y")==$visita['fecha']) echo "F7D346"; else echo "D8EDC9";?>"><div align="right" class="Estilo15" style="padding-right:10px"><? $hora_corta=substr($visita['hora'],0,5);if (substr($hora_corta,0,1)=="1" || substr($hora_corta,0,1)=="2") echo $hora_corta; else echo "0".substr($hora_corta,0,4);?></div></td>
	<td bgcolor="<? if (date("d/m/Y")==$visita['fecha']) echo "F7D346"; else echo "D8EDC9";?>"><div align="left" class="Estilo15"><a href="visitas.php?idcliente=<? echo $_POST['idcliente'];?>&idvisita=<? echo $visita['idvisita'];?>" style="color:#000000" title="Ir a la Visita"><? if ($visita['motivo']<>'') echo corta_palabra(strtolower($visita['motivo']),40)."..."; else echo "FALTA MOTIVO";?></a></div></td>
    <td bgcolor="<? if (date("d/m/Y")==$visita['fecha']) echo "F7D346"; else echo "D8EDC9";?>"><div align="left" class="Estilo15"><a href="clientes.php?idcliente=<? echo $cliente['idcliente'];?>&boton=Buscar" style="color:#000000" title="Ir al Cliente"><? echo $cliente['nombres'];?></a></div></td>
    <td bgcolor="<? if (date("d/m/Y")==$visita['fecha']) echo "F7D346"; else echo "D8EDC9";?>"><div align="center" class="Estilo15"><? echo $cliente['empresa'];?></div></td>   
    <td bgcolor="<? if (date("d/m/Y")==$visita['fecha']) echo "F7D346"; else echo "D8EDC9";?>"><div align="center" class="Estilo15"><? if ($visita['idusuario']<>'') {$comerciales=mysql_query("SELECT nombre FROM usuario WHERE idusuario=".$visita['idusuario']."");
$comercial=mysql_fetch_assoc($comerciales);echo $comercial['nombre'];};?></div>
    </div></td>
<? if ($_SESSION['nivel']==0) {?>
	<td bgcolor="<? if (date("d/m/Y")==$visita['fecha']) echo "F7D346"; else echo "D8EDC9";?>"><div align="center" class="Estilo15"><? echo $visita['provincia'];?></div></td> 
<? } ?>	
  </tr>
<? } ?>
</table>
  <p align="center" class="Estilo7 Estilo15 Estilo16"><? echo mysql_num_rows($visitas);?> registro(s)</p>
  </div>  
<p>
<? } ?>
</p>
</body>
</html>
