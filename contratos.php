<?
#USO DE VARIABLES DE SESION
session_start();

if ($_REQUEST['idcliente']<>'') $_POST['idcliente']=$_REQUEST['idcliente'];
if ($_REQUEST['idcontrato']<>'') $_POST['idcontrato']=$_REQUEST['idcontrato'];

#SI LA VARIABLE DE SESION ACCESO ESTA EN BLANCO REDIRECCIONA A FORMULARIO DE LOGIN
if ($_SESSION['acceso']=='') header("location:index.php"); 

#FUNCION PARA SUMAR MESES A UNA FECHA
function sumarmeses($fecha,$meses){ 
//$fecha = date('Y-m-j');
$nuevafecha = strtotime ( '+'.$meses.' month' , strtotime ( $fecha ) ) ;
$nuevafecha = date ( 'Y-m-j' , $nuevafecha );
return $nuevafecha;
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

#SI PULSA NUEVO CONTRATO BLANQUEA TODOS LOS CAMPOS DEL FORMULARIO
if ($_POST['boton2']=="Nuevo Contrato") {
$_POST['idcontrato']='';$_POST['observaciones']='';$_POST['otro']='';$_POST['servicio']='';
}

#SI PULSA EN GUARDAR EL CONTRATO
if ($_POST['boton']=="Guardar") {
	#SI SE SUBE UN FICHERO EN EL FORMULARIO
   	if (is_uploaded_file($_FILES['archivo']['tmp_name'])) { 
   	  $ext = strrchr($_FILES['archivo']['name'],'.');
	  $fecha2=str_replace("/","",$_POST['fecha']);
	  $hora2=str_replace(":","",$_POST['hora']);
   	  $nombre_fichero=$fecha2.$hora2.$ext;
      copy($_FILES['archivo']['tmp_name'], "documentos/".$nombre_fichero); 
    	}
	#SI ES UN CONTRATO YA GUARDADO EN BASE	
	if ($_POST['idcontrato']<>'') {
		if ($_POST['observaciones'] <> 'ERROR') {
			if ($_SESSION['nivel']==1 || $_SESSION['nivel']==0 || $_SESSION['usuario']==$_POST['idusuario']) {
			if ($_POST['servicio']<>'Otro' && $_POST['otro']<>'') $_POST['otro']='';
			mysql_query("UPDATE contrato SET documento='$nombre_fichero',servicio='".$_POST['servicio']."',fecha_inicio='".cambiarFormatoFecha2($_POST['fecha_inicio'])."',fecha_fin='".cambiarFormatoFecha2($_POST['fecha_fin'])."',meses='".$_POST['meses']."',estado='".$_POST['estado']."',idusuario='".$_POST['idusuario']."',otro='".ucwords(strtolower($_POST['otro']))."' WHERE idcontrato='".$_POST['idcontrato']."'");
			if ($_POST['comercial']<>'' && $_SESSION['usuario']<>$_POST['comercial']) mysql_query("UPDATE contrato SET idusuario='".$_POST['comercial']."' WHERE idcontrato='".$_POST['idcontrato']."'");
			$msj="Contrato modificado";
			} else $msj="Usuario incorrecto";
		} elseif ($_SESSION['acceso']=='Administrador') {
			mysql_query("DELETE FROM contrato WHERE idcontrato='".$_POST['idcontrato']."'");
			$msj="Contrato eliminado";
		}
	} 
	#SI ES UN CONTRATO NUEVO
	else {
		#INSERTA EL REGISTRO NUEVO EN LA BASE DE DATOS
		mysql_query("INSERT INTO contrato (timestamp,fecha,hora,idcliente,documento,servicio,idusuario,observaciones,fecha_inicio,fecha_fin,estado,meses,otro,provincia) VALUES (SYSDATE(),'".$_POST['fecha']."','".$_POST['hora']."',".$_POST['idcliente'].",'$nombre_fichero','".$_POST['servicio']."','".$_POST['idusuario']."','".$_POST['observaciones']."','".cambiarFormatoFecha2($_POST['fecha_inicio'])."','".sumarmeses(cambiarFormatoFecha2($_POST['fecha_inicio']),$_POST['meses'])."','".$_POST['estado']."','".$_POST['meses']."','".ucwords(strtolower($_POST['otro']))."','".$_SESSION['provincia']."')");
    	$msj="Contrato creado";
		$_POST['idcontrato']=mysql_insert_id();
		if ($_POST['comercial']<>'' && $_SESSION['usuario']<>$_POST['comercial']) mysql_query("UPDATE contrato SET idusuario='".$_POST['comercial']."' WHERE idcontrato='".$_POST['idcontrato']."'");
	}
	#ACTUALIZA EL HISTORIAL EN TABLA CAMBIO
	$host = $_SERVER['REMOTE_ADDR'];
	mysql_query("INSERT INTO cambio (fecha_hora,accion,ip,idcliente,idusuario) VALUES (SYSDATE(),'$msj','$host','".$_POST['idcliente']."','".$_SESSION['usuario']."')");
}

#CARGA LOS DATOS DEL CLIENTE DE LA BASE
if ($_POST['idcliente']<>'') {$clientes = mysql_query("SELECT * FROM cliente WHERE idcliente='".$_POST['idcliente']."'",$clinica) or die(mysql_error());
$cliente = mysql_fetch_assoc($clientes);}

#CARGA LOS DATOS DE VISITA DE LA BASE
if ($_POST['idcontrato']<>'') {$contratos = mysql_query("SELECT * FROM contrato WHERE idcontrato='".$_POST['idcontrato']."'",$clinica) or die(mysql_error());
$contrato = mysql_fetch_assoc($contratos);}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>CONTRATOS - CRM</title>
<script type="text/javascript" src="js/si.files.js"></script>
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
.Estilo14 {font-size: 15px;}
.Estilo15 {font-size: 15px; color: #6aac39; }
.Estilo16 {
	color: #333333;
	font-weight: bold;
}
.Estilo17 {color: #FF9900}
.Estilo18 {color: #FF6600}
.Estilo19 {color: #79C143}
-->
</style>
</head>

<body style="font-family:Arial, Helvetica, sans-serif; font-size:17px" onload="document.contrato.otro.focus();" id="tab3">
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
<form ACTION="contratos.php" METHOD="post" enctype="multipart/form-data" name="contrato" target="_self" id="contrato">
<p align="center" class="Estilo3" style=" line-height:31px">
<? if ($contrato['documento']<>'') {?>
<a href="documentos/<? echo $contrato['documento'];?>" target="_blank"><img src="imagen/escaner.gif" width="45" height="31" hspace="10" border="0" align="absmiddle" /></a>
<? } else {?>
<label class="cabinet">
    <input name="archivo" type="file" class="file" id="archivo" />
</label>
<? } ?></p>
<span class="Estilo9 Estilo17">
<? if ($contrato['idcontrato']=='') echo "REGISTRE UN NUEVO CONTRATO"; else echo "DATOS DEL CONTRATO"?>
</span><br />
<br />
<span class="Estilo9">CLIENTE</span>
<div id="recuadro">
<p align="center"><span class="Estilo16">Nombres</span> 
<input name="nombres" type="text" id="nombres" value="<? echo $cliente['nombres'];?>" size="30" readonly="" style="background-color:#F7D346"/> 
<strong class="Estilo16">Apellidos</strong> 
<input name="apellidos" type="text" id="apellidos" value="<? echo $cliente['apellidos'];?>" size="30" readonly="" style="background-color:#F7D346"/></p>
  <p align="center"><strong class="Estilo16">Empresa</strong> 
    <input name="empresa" type="text" id="empresa" value="<? echo $cliente['empresa'];?>" size="30" readonly="" style="background-color:#F7D346"/> 
    <input name="boton4" type="submit" id="boton4" style="background-color:#79C143;color:#FFFFFF;font:bolder" value="Ir al Cliente"/>
  </p>
  </div>
  <span class="Estilo9">DATOS DEL CONTRATO</span>
<div id="recuadro">
  <p align="center"><strong class="Estilo16">Servicio</strong>
<? if ($_POST['servicio']<>"" && $_POST['servicio']<>$contrato['servicio']) $service=$_POST['servicio']; else $service=$contrato['servicio']; ?>
    <select name="servicio" id="servicio" onchange="this.form.submit()">
      <option value="" <? if ($service=="") echo "selected";?>></option>
      <option value="Plataformas digitales - LOW COST" <? if ($service=="Plataformas digitales - LOW COST") echo "selected";?>>Plataformas digitales - LOW COST</option>
	  <option value="Plataformas digitales - BÁSICA" <? if ($service=="Plataformas digitales - BÁSICA") echo "selected";?>>Plataformas digitales - BÁSICA</option>
	  <option value="Plataformas digitales - A MEDIDA" <? if ($service=="Plataformas digitales - A MEDIDA") echo "selected";?>>Plataformas digitales - A MEDIDA</option>
      <option value="Mantenimiento - BÁSICO" <? if ($service=="Mantenimiento - BÁSICO") echo "selected";?>>Mantenimiento - BÁSICO</option>
	  <option value="Mantenimiento - PROFESIONAL" <? if ($service=="Mantenimiento - PROFESIONAL") echo "selected";?>>Mantenimiento - PROFESIONAL</option>
	  <option value="Mantenimiento - AVANZADO" <? if ($service=="Mantenimiento - AVANZADO") echo "selected";?>>Mantenimiento - AVANZADO</option>
      <option value="Redes Sociales - OUTLET" <? if ($service=="Redes Sociales - OUTLET") echo "selected";?>>Redes Sociales - OUTLET</option>
	  <option value="Redes Sociales - BASIC" <? if ($service=="Redes Sociales - BASIC") echo "selected";?>>Redes Sociales - BASIC</option>
	  <option value="Redes Sociales - PREMIUM" <? if ($service=="Redes Sociales - PREMIUM") echo "selected";?>>Redes Sociales - PREMIUM</option>
	  <option value="Jurídicos" <? if ($service=="Jurídicos") echo "selected";?>>Jurídicos</option>
      <option value="Marketing - Campaña" <? if ($service=="Marketing - Campaña") echo "selected";?>>Marketing - Campaña</option>
	  <option value="Marketing - Otros" <? if ($service=="Marketing - Otros") echo "selected";?>>Marketing - Otros</option>
	  <option value="Publicidad - Campaña" <? if ($service=="Publicidad - Campaña") echo "selected";?>>Publicidad - Campaña</option>
	  <option value="Publicidad - Otros" <? if ($service=="Publicidad - Otros") echo "selected";?>>Publicidad - Otros</option>
	  <option value="Otro" <? if ($service=="Otro") echo "selected";?>>Otro</option>
    </select>
<? if ($_POST['servicio']=="Otro" || $contrato['otro']<>'') { ?>
	<input name="otro" type="text" id="otro" value="<? if ($contrato['otro']<>'') echo $contrato['otro']; else echo $_POST['otro'];?>" size="30"/>
<? } ?>  
  </p>
  <p align="center"><strong class="Estilo16">Inicio</strong>
    <input name="fecha_inicio" type="text" id="fecha_inicio" onkeyup="mascara(this,'/',patron2,true)" value="<? 
	if ($contrato['fecha_inicio']<>'') echo cambiarFormatoFecha($contrato['fecha_inicio']); else echo date("d/m/Y");
	?>" size="6"/>
    <strong class="Estilo16">Fin </strong>
    <input name="fecha_fin" type="text" id="fecha_fin" onkeyup="mascara(this,'/',patron2,true)" value="<? 
	if ($contrato['fecha_fin']<>'') echo cambiarFormatoFecha($contrato['fecha_fin']);
	?>" size="6" <? if ($_SESSION['nivel']==2) {?>	readonly="" <? } ?> style="background-color:#F7D346"/>
    <strong class="Estilo16">Per&iacute;odo</strong>
    <select name="meses" id="meses">
	  <option value="" <? if ($contrato['meses']=="") echo "selected";?>></option>
      <option value="1" <? if ($contrato['meses']=="1") echo "selected";?>>1 mes</option>
      <option value="2" <? if ($contrato['meses']=="2") echo "selected";?>>2 meses</option>
      <option value="3" <? if ($contrato['meses']=="3") echo "selected";?>>3 meses</option>
      <option value="4" <? if ($contrato['meses']=="4") echo "selected";?>>4 meses</option>
      <option value="5" <? if ($contrato['meses']=="5") echo "selected";?>>5 meses</option>
      <option value="6" <? if ($contrato['meses']=="6") echo "selected";?>>6 meses</option>
      <option value="7" <? if ($contrato['meses']=="7") echo "selected";?>>7 meses</option>
      <option value="8" <? if ($contrato['meses']=="8") echo "selected";?>>8 meses</option>
      <option value="9" <? if ($contrato['meses']=="9") echo "selected";?>>9 meses</option>
      <option value="10" <? if ($contrato['meses']=="10") echo "selected";?>>10 meses</option>
      <option value="11" <? if ($contrato['meses']=="11") echo "selected";?>>11 meses</option>
      <option value="12" <? if ($contrato['meses']=="12") echo "selected";?>>12 meses</option>
	  <option value="13" <? if ($contrato['meses']=="13") echo "selected";?>>13 mes</option>
      <option value="14" <? if ($contrato['meses']=="14") echo "selected";?>>14 meses</option>
      <option value="15" <? if ($contrato['meses']=="15") echo "selected";?>>15 meses</option>
      <option value="16" <? if ($contrato['meses']=="16") echo "selected";?>>16 meses</option>
      <option value="17" <? if ($contrato['meses']=="17") echo "selected";?>>17 meses</option>
      <option value="18" <? if ($contrato['meses']=="18") echo "selected";?>>18 meses</option>
      <option value="19" <? if ($contrato['meses']=="19") echo "selected";?>>19 meses</option>
      <option value="20" <? if ($contrato['meses']=="20") echo "selected";?>>20 meses</option>
      <option value="21" <? if ($contrato['meses']=="21") echo "selected";?>>21 meses</option>
      <option value="22" <? if ($contrato['meses']=="22") echo "selected";?>>22 meses</option>
      <option value="23" <? if ($contrato['meses']=="23") echo "selected";?>>23 meses</option>
      <option value="24" <? if ($contrato['meses']=="24") echo "selected";?>>24 meses</option>
    </select>
    <strong class="Estilo16">Estado</strong>
    <select name="estado" id="estado">
	  <option value="" <? if ($contrato['estado']=="") echo "selected";?>></option>
      <option value="Pendiente" <? if ($contrato['estado']=="Pendiente") echo "selected";?>>Pendiente</option>
      <option value="Presupuestado" <? if ($contrato['estado']=="Presupuestado") echo "selected";?>>Presupuestado</option>
      <option value="Activo" <? if ($contrato['estado']=="Activo") echo "selected";?>>Activo</option>
      <option value="Finalizado" <? if ($contrato['estado']=="Finalizado") echo "selected";?>>Finalizado</option>
	  <option value="Rechazado" <? if ($contrato['estado']=="Rechazado") echo "selected";?>>Rechazado</option>
    </select>
    </p>
  </p>
  <p align="center"><strong class="Estilo16"> Observaciones </strong><br />
    <textarea name="observaciones" cols="83" rows="7" id="observaciones"><? if ($contrato['observaciones']<>'') echo $contrato['observaciones']; else echo $_POST['observaciones'];?></textarea>
  </p>
  </div>
    <span class="Estilo9">FECHA/HORA Y USUARIO </span>
    <div id="recuadro">
  <p align="center"><strong class="Estilo16">Fecha</strong>
    <input name="fecha" type="text" id="fecha" value="<? if ($contrato['fecha']<>'') echo $contrato['fecha']; else echo date("d/m/Y");?>" size="6" readonly="" <? if ($_POST['idcontrato']<>'') echo "style=\"background-color:#F7D346\""; ?> style="background-color:#F7D346"/>
    <strong class="Estilo16">Hora</strong>
    <input name="hora" type="text" id="hora" value="<? if ($contrato['hora']<>'') echo $contrato['hora']; else {date_default_timezone_set('Europe/London');echo date("G:i:s");}?>" size="4" readonly="" <? if ($_POST['idvisita']<>'') echo "style=\"background-color:#F7D346\""; ?> style="background-color:#F7D346"/>
    <strong class="Estilo16"> Usuario</strong>
<? if ($_SESSION['nivel']==1 || $_SESSION['nivel']==0) {
if ($_SESSION['nivel']==1)
$personas=mysql_query("SELECT nombre,idusuario FROM usuario WHERE nombre<>'Administrador' AND provincia='".$_SESSION['provincia']."' ORDER BY nombre");
else 
$personas=mysql_query("SELECT nombre,idusuario FROM usuario WHERE nombre<>'Administrador' ORDER BY nombre");
?>
<select name="comercial" id="comercial">
	  <option value="" <? if ($comercial=="" || $contrato['idusuario']=="") echo "selected";?>></option>
<? while ($persona=mysql_fetch_assoc($personas)) {?>		  
      <option value="<? echo $persona['idusuario'];?>" <? if ($comercial==$persona['idusuario'] || $contrato['idusuario']==$persona['idusuario']) echo "selected";?>><? echo $persona['nombre'];?></option>
<? } ?>
    </select>
<? } else {?>	
    <input name="usuario" type="text" id="usuario" value="<? if ($contrato['idusuario']<>'') {$comerciales=mysql_query("SELECT nombre FROM usuario WHERE idusuario=".$contrato['idusuario']."");
$comercial=mysql_fetch_assoc($comerciales);echo $comercial['nombre'];} else echo $_SESSION['acceso'];?>" size="10" readonly="" style="background-color:#F7D346" />
<? }?>
    <? if ($_POST['idcontrato']<>'') {?>
<input name="boton2" type="submit" id="boton2" style="background-color:#79C143;color:#FFFFFF;font:bolder" value="Nuevo Contrato"/>
  <? } ?>  
  </p>
  </div>
  <p align="center">
    <label>
	<input name="idcliente" type="hidden" id="idcliente" value="<? echo $_POST['idcliente'];?>"/>
	<input name="idcontrato" type="hidden" id="idcontrato" value="<? echo $_POST['idcontrato'];?>"/>
    <input name="idusuario" type="hidden" id="idusuario" value="<? echo $_SESSION['usuario'];?>"/>
    <input name="boton" type="submit" id="boton3" style="background-color:#79C143;color:#FFFFFF;font:bolder; padding:5px" value="Guardar"/>
    </label>
  </p>
</form>
<?
$contratos=mysql_query("SELECT fecha,servicio,otro,estado,idcontrato,documento FROM contrato WHERE idcliente='".$_POST['idcliente']."' and estado<>'Rechazado' ORDER BY idcontrato DESC");
?>
<div align="center">
<p>&nbsp;</p>
  <p>&nbsp;</p>
  <p class="Estilo15">Listado de contratos del cliente ordenado por fecha</p>
  <table width="850" border="0">
    <tr>
      <td width="115" bgcolor="#79C143" class="Estilo12"><div align="center" class="Estilo5 Estilo14">Fecha</div></td>
      <td width="504" bgcolor="#79C143" class="Estilo12"><div align="center" class="Estilo5 Estilo14">Servicio</div></td>
      <td width="118" bgcolor="#79C143" class="Estilo12"><div align="center" class="Estilo5 Estilo14">Estado</div></td>
      <td width="45"><div align="center" class="Estilo5"></div></td>
    </tr>
<? while ($contrato=mysql_fetch_assoc($contratos)) {?>	
    <tr>
      <td bgcolor="D8EDC9"><div align="center" class="Estilo14"><? echo $contrato['fecha'];?></div></td>
      <td bgcolor="D8EDC9"><div align="left" class="Estilo14"><? if ($contrato['otro']<>'') echo $contrato['otro']; else echo $contrato['servicio'];?>          
	  <? if ($contrato['documento']<>'') {?>
        <img src="imagen/foto.gif" width="15" height="20" align="top" />
        <? } ?></div></td>
      <td bgcolor="D8EDC9"><div align="center" class="Estilo14"><? echo $contrato['estado'];?>
      </div></td>
      <td bgcolor="D8EDC9"><div align="center"><input name="boton3" type="submit" id="boton3" style="background-color:#79C143;color:#FFFFFF;font:bolder" value="Ver" onclick="window.open('contratos.php?idcliente=<? echo $_POST['idcliente'];?>&idcontrato=<? echo $contrato['idcontrato'];?>','_self')"/></div></td>
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
$contratos=mysql_query("SELECT fecha,fecha_inicio,fecha_fin,servicio,otro,estado,idcontrato,idcliente,idusuario FROM contrato WHERE estado<>'Rechazado' AND idusuario LIKE '".$_REQUEST['agecom']."%' AND provincia='".$_SESSION['provincia']."' ORDER BY timestamp DESC");
else
$contratos=mysql_query("SELECT fecha,fecha_inicio,fecha_fin,servicio,otro,estado,idcontrato,idcliente,idusuario,provincia FROM contrato WHERE estado<>'Rechazado' AND idusuario LIKE '".$_REQUEST['agecom']."%' ORDER BY timestamp DESC");
 }
else {
$contratos=mysql_query("SELECT fecha,fecha_inicio,fecha_fin,servicio,otro,estado,idcontrato,idcliente,idusuario FROM contrato WHERE idusuario='".$_SESSION['usuario']."' and estado<>'Rechazado' ORDER BY timestamp DESC");
 }

$hoy=date("d/m/Y");

?>
<div align="center" class="Estilo13 Estilo14">
  <p class="Estilo15">
<? if ($_SESSION['nivel']==1 || $_SESSION['nivel']==0) {?>
      <strong>Comercial</strong> -&gt; 
    <?
if ($_SESSION['nivel']==1)	
$agentes=mysql_query("SELECT distinct a.nombre as nombre, b.idusuario as usuario
FROM usuario a, cliente b where a.idusuario=b.idusuario AND a.provincia='".$_SESSION['provincia']."' ORDER BY a.nombre");
else
$agentes=mysql_query("SELECT distinct a.nombre as nombre, b.idusuario as usuario
FROM usuario a, cliente b where a.idusuario=b.idusuario ORDER BY a.nombre");
while ($agente=mysql_fetch_assoc($agentes)) {
echo '<a href="contratos.php?agecom='.$agente['usuario'].'" class="Estilo15">&nbsp'.$agente['nombre'].'&nbsp</a> | ';  
}
?>
  <a href="contratos.php?iniape=%&ininom=%&iniemp=%" class="Estilo15">Todos</a> 
<? } ?>  
</p>
</div>
<table width="550" border="0" align="right">
  <tr>
    <td width="5" bgcolor="#D8EDC9">&nbsp;</td>
    <td width="44"><span class="Estilo14">Activo</span></td>
    <td width="5" bgcolor="#FFFF6A">&nbsp;</td>
    <td width="104"><span class="Estilo14">Presupuestado</span></td>
    <td width="5" bgcolor="#F9C964">&nbsp;</td>
    <td width="68"><span class="Estilo14">Pendiente</span></td>
    <td width="5" bgcolor="#FC4F45">&nbsp;</td>
    <td width="69"><span class="Estilo14">Caducar&aacute;</span></td>
    <td width="5" bgcolor="#97CEEC">&nbsp;</td>
    <td width="75"><span class="Estilo14">Finalizado</span></td>
	<td width="5" bgcolor="#CCCCCC">&nbsp;</td>
    <td width="99"><span class="Estilo14">Completado</span></td>
  </tr>
</table>
<br />
<table width="990" border="0" align="center">
    <tr>
      <td width="78" bgcolor="#79C143"><div align="center" class="Estilo5 Estilo12 Estilo14">Fecha Inicio</div></td>
	  <td width="78" bgcolor="#79C143"><div align="center" class="Estilo5 Estilo12 Estilo14">Fecha Fin</div></td>
      <td width="218" bgcolor="#79C143"><div align="center" class="Estilo5 Estilo12 Estilo14">Servicio</div></td>
	  <td width="129" bgcolor="#79C143"><div align="center" class="Estilo5 Estilo12 Estilo14">Cliente</div></td>
      <td width="115" bgcolor="#79C143"><div align="center" class="Estilo5 Estilo12 Estilo14">Estado</div></td>
	  <td width="170" bgcolor="#79C143"><div align="center" class="Estilo5 Estilo12 Estilo14">Empresa</div></td>
	  <td width="90" bgcolor="#79C143"><div align="center" class="Estilo5 Estilo12 Estilo14">Usuario</div></td>
	  <? if ($_SESSION['nivel']==0) {?>
<td width="78"  bgcolor="#79C143"><div align="center" class="Estilo5 Estilo12 Estilo14">Provincia</div></td>
<? } ?>	
    </tr>
<? while ($contrato=mysql_fetch_assoc($contratos)) {
$_POST['idcliente']=$contrato['idcliente'];
if ($_POST['idcliente']!=0) $clientes = mysql_query("SELECT nombres,apellidos,idcliente,empresa FROM cliente WHERE idcliente='".$_POST['idcliente']."'",$clinica) or die(mysql_error());
$cliente = mysql_fetch_assoc($clientes);
// activo=verde#D8EDC9, presupuestado=amarillo#FFFF6A, pendiente=naranja#F9C964, rojo#FC4F45
switch($contrato['estado']) {
	case "Activo": $color="#D8EDC9";break;
	case "Presupuestado": $color="#FFFF6A";break;
	case "Pendiente": $color="#F9C964";break;
	case "Finalizado": $color="#97CEEC";break;
	case "Completado": $color="#CCCCCC";break;
	}
//si queda un mes o menos para la fecha de fin de contrato activo se pone en rojo	
if (strtotime('- 1 month',strtotime($contrato['fecha_fin'])) < strtotime(date('Y-m-j')) && $contrato['estado']=="Activo") $color="#FC4F45";
//si pasó la fecha de fin de contrato activo se pasa a completado
if (strtotime($contrato['fecha_fin']) < strtotime(date('Y-m-j')) && $contrato['estado']=="Activo") mysql_query("UPDATE contrato SET estado='Completado' WHERE idcontrato='".$contrato['idcontrato']."'");
//si pasaron 6 meses de la fecha de fin de contrato finalizado o completado se pasa a rechazado para ocultarlo
if (strtotime($contrato['fecha_fin']) < strtotime('- 6 month',strtotime(date('Y-m-j'))) && ($contrato['estado']=="Finalizado" || $contrato['estado']=="Completado")) mysql_query("UPDATE contrato SET estado='Rechazado' WHERE idcontrato='".$contrato['idcontrato']."'");
?>	
    <tr>
      <td bgcolor="<? echo $color;?>"><div align="center" class="Estilo14"><? echo cambiarFormatoFecha($contrato['fecha_inicio']);?></div></td>
	  <td bgcolor="<? echo $color;?>"><div align="center" class="Estilo14"><? echo cambiarFormatoFecha($contrato['fecha_fin']);?></div></td>
      <td bgcolor="<? echo $color;?>"><div align="left" class="Estilo14"><a href="contratos.php?idcliente=<? echo $_POST['idcliente'];?>&idcontrato=<? echo $contrato['idcontrato'];?>" style="color:#000000" title="Ir al Contrato"><? if ($contrato['otro']<>'') echo $contrato['otro']; else echo $contrato['servicio'];?></a></div></td>
	  <td bgcolor="<? echo $color;?>"><div align="left" class="Estilo14"><a href="clientes.php?idcliente=<? echo $cliente['idcliente'];?>&boton=Buscar" style="color:#000000" title="Ir al Cliente"><? echo $cliente['nombres'];?></a></div></td>
      <td bgcolor="<? echo $color;?>"><div align="center" class="Estilo14"><? echo $contrato['estado'];?></div></td>
	  <td bgcolor="<? echo $color;?>"><div align="center" class="Estilo14"><? echo $cliente['empresa'];?></div>
	  <div align="center" class="Estilo14"></div><div align="center" class="Estilo14"></div></td>   
	  <td bgcolor="<? echo $color;?>"><div align="center" class="Estilo14"><? if ($contrato['idusuario']<>'') {$comerciales=mysql_query("SELECT nombre FROM usuario WHERE idusuario=".$contrato['idusuario']."");
$comercial=mysql_fetch_assoc($comerciales);echo $comercial['nombre'];};?></div>
    </div></td>
	<? if ($_SESSION['nivel']==0) {?>
	  <td bgcolor="<? echo $color;?>"><div align="center" class="Estilo14"><? echo $contrato['provincia'];?></div></td>
<? } ?>	
    </tr>
<? } ?>
</table>
  <p align="center" class="Estilo15"><? echo mysql_num_rows($contratos);?> registro(s)</p>
  </div>  
<p>
<? } ?>
</p>
</body>
</html>
