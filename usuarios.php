<?
session_start();
if ($_SESSION['acceso']=='') header("location:index.php"); 

include("conexion.php");
date_default_timezone_set('Atlantic/Canary');

if ($_POST['boton3']=="Guardar" && $_POST['alias']<>'' && $_POST['nombre']<>'' && $_POST['clave']<>'' && $_POST['email']<>'') {
	$nombre=ucwords(strtolower($_POST['nombre']));
	$email=strtolower($_POST['email']);
	$alias=strtolower($_POST['alias']);
	if ($_SESSION['nivel']==1) $provincia=$_SESSION['provincia'];
	else $provincia=$_POST['provincia'];
	if (mysql_fetch_assoc(mysql_query("SELECT idusuario FROM usuario WHERE idusuario='".$_POST['idusuario']."'"))) {
		if ($_POST['observaciones'] <> 'ERROR') {
			mysql_query("UPDATE usuario SET nombre='$nombre',alias='$alias',email='$email',clave='".$_POST['clave']."',provincia='$provincia',nivel='".$_POST['nivel']."',activo='".$_POST['activo']."',fallidos='".$_POST['fallidos']."',observaciones='".$_POST['observaciones']."' WHERE idusuario='".$_POST['idusuario']."'");
			$msj="Usuario modificado";
		}
		elseif ($_SESSION['acceso']=='Administrador') {
			mysql_query("UPDATE usuario SET nombre='$nombre',alias='$alias',email='$email',clave='".$_POST['clave']."',provincia='$provincia',nivel='".$_POST['nivel']."',activo='0',fallidos='".$_POST['fallidos']."',observaciones='".$_POST['observaciones']."' WHERE idusuario='".$_POST['idusuario']."'");
			$msj="Usuario eliminado";
			$_POST['nombre']='';$_POST['alias']='';$_POST['email']='';$_POST['clave']='';$_POST['fallidos']='';
		}	
	}
	else {
	mysql_query("INSERT INTO usuario (nombre,alias,clave,email,nivel,activo,fallidos,observaciones,provincia,fecha_alta) VALUES ('$nombre','$alias','".$_POST['clave']."','$email','".$_POST['nivel']."','".$_POST['activo']."','".$_POST['fallidos']."','".$_POST['observaciones']."','$provincia',SYSDATE())");
	$msj="Usuario creado";
	}
$usuarios = mysql_query("SELECT * FROM usuario WHERE idusuario='".$_POST['idusuario']."'",$clinica) or die(mysql_error());
$usuario = mysql_fetch_assoc($usuarios);
$host = $_SERVER['REMOTE_ADDR'];
mysql_query("INSERT INTO cambio (fecha_hora,accion,ip,idcliente,idusuario) VALUES (SYSDATE(),'$msj','$host','".$_POST['idcliente']."','".$_SESSION['usuario']."')");
} elseif ($_POST['boton3']=="Guardar") $msj="Faltan completar datos";

if ($_REQUEST['idusuario']<>'' && $_REQUEST['boton']=="Buscar") {
$_POST['idusuario']=$_REQUEST['idusuario'];$_POST['boton']=$_REQUEST['boton'];
}

if ($_POST['boton']=="Buscar" && $_POST['idusuario']<>'') {
$clientes=mysql_query("SELECT * FROM cliente WHERE idcliente=".$_POST['idcliente']."");
$cliente = mysql_fetch_assoc($clientes);
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>USUARIOS - CRM</title>
<script language="JavaScript">
function maximizar(){
window.moveTo(0,0);
window.resizeTo(screen.width,screen.height);
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
.Estilo14 {color: #6aac39; font-size: 15px;}
.Estilo15 {
	color: #333333;
	font-weight: bold;
}
.Estilo16 {color: #FF6600}
.Estilo17 {color: #79C143}
-->
</style>
</head>

<body style="font-family:Arial, Helvetica, sans-serif; font-size:17px" onload="maximizar()" id="tab5">
<table width="100%" border="0">
  <tr>
    <td width="49%"><img src="imagen/logo.jpg" width="262" height="120" /></td>
    <td width="51%" class="Estilo9 Estilo13"><p align="right" class="Estilo6 Estilo15 Estilo20 Estilo17" style="padding-left:13px"><b><?php setlocale(LC_ALL,"es_ES@euro","es_ES","esp");echo ucfirst(strftime("%A %d de %B del %Y"));?> 
    </b></p>
	<p align="right" class="Estilo6 Estilo15 Estilo19 Estilo16" style="padding-left:13px"><b><? echo $_SESSION['acceso'];?></b><b><a href="index.php?accion=salir" class="Estilo6 Estilo15 Estilo19 Estilo16" style="padding-left:13px">Cerrar Sesión</a></b></p><p align="right" class="Estilo6 Estilo15 Estilo19 Estilo16" style="padding-left:13px"><a href="https://webmail.1and1.es/Webmail_Login" target="_blank"><img src="imagen/email.gif" width="81" height="33" border="0" /></a></p></td>
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
  <? if($msj<>'') echo "<br>   ----------->".$msj;?>
  </span>
<?
if ($_SESSION['nivel']==0) 
$usuarios=mysql_query("SELECT * FROM usuario WHERE observaciones<>'ERROR' ORDER BY nombre");
else
$usuarios=mysql_query("SELECT * FROM usuario WHERE observaciones<>'ERROR' AND provincia='".$_SESSION['provincia']."' AND nombre<>'Administrador' ORDER BY nombre");

?>
<div align="center">
  <table width="990" border="0">
    <tr>
      <td width="232" bgcolor="#79C143"><div align="center" class="Estilo5 Estilo13">Nombre</div></td>
      <td width="151" bgcolor="#79C143"><div align="center" class="Estilo5 Estilo13">Alias</div></td>      <td width="151" bgcolor="#79C143"><div align="center" class="Estilo5 Estilo13">Nivel</div></td>
      <td width="226" bgcolor="#79C143"><div align="center" class="Estilo5 Estilo13">Correo Electrónico</div></td>
      <td width="97" bgcolor="#79C143"><div align="center" class="Estilo5 Estilo13">Activo</div></td>
<? if ($_SESSION['nivel']==0) {?>
<td width="107" bgcolor="#79C143"><div align="center" class="Estilo5 Estilo13">Provincia</div></td>
<? } ?>	  
    </tr>
<? while ($usuario=mysql_fetch_assoc($usuarios)) {?>	
    <tr>
      <td bgcolor="D8EDC9"><div align="left" class="Estilo13"><a href="usuarios.php?idusuario=<? echo $usuario['idusuario'];?>" style="color:#000000" title="Ir al Usuario"><? echo $usuario['nombre'];?></a></div></td>
      <td bgcolor="D8EDC9"><div align="left" class="Estilo13"><? echo $usuario['alias'];?></div></td>
	  <td bgcolor="D8EDC9"><div align="left" class="Estilo13">
	    <div align="center"><? echo $usuario['nivel'];?></div>
	  </div>
	    <div align="center"></div><div align="center"></div></td>
	  <td bgcolor="D8EDC9"><span class="Estilo13"><? echo $usuario['email'];?></span></td>
      <td bgcolor="D8EDC9">
        <div align="center" class="Estilo13">
          <input name="checkbox" type="checkbox" value="checkbox" <? if ($usuario['activo']) echo "checked";?> disabled/>
      </div></td>
<? if ($_SESSION['nivel']==0) {?>
	<td bgcolor="D8EDC9"><div align="center"><span class="Estilo13"><? echo $usuario['provincia'];?></span></div></td>
<? } ?>	  
    </tr>
<? } ?>
  </table>
  <p class="Estilo14"><? echo mysql_num_rows($usuarios);?> registro(s)</p>
</div>
<? 
if ($_REQUEST['idusuario']<>'') {
$usuarios=mysql_query("SELECT * FROM usuario WHERE idusuario='".$_REQUEST['idusuario']."'");
$usuario=mysql_fetch_assoc($usuarios);
}
?>
<form ACTION="usuarios.php" METHOD="post" target="_self" enctype="multipart/form-data">
<br />
<span class="Estilo9">USUARIO</span>
<div id="recuadro">
<p align="center"><span class="Estilo15">Nombre</span> 
<input name="nombre" type="text" id="nombre" value="<? if ($usuario['nombre']<>'') echo $usuario['nombre']; else echo $_POST['nombre'];?>" size="30"/> 
<strong class="Estilo15">Alias</strong> 
<input name="alias" type="text" id="alias" value="<? if ($usuario['alias']<>'') echo $usuario['alias']; else echo $_POST['alias'];?>" size="30"/>
</p>
  <p align="center"><strong class="Estilo15">Clave</strong> 
    <input name="clave" type="<? if ($usuario['nombre']=='Administrador') echo "password";else echo "text";?>" id="clave" value="<? if ($usuario['clave']) echo $usuario['clave']; else echo $_POST['clave'];?>" size="10" /> 
<strong class="Estilo15">Correo electr&oacute;nico </strong><strong></strong>
    <input name="email" type="text" id="email" value="<? if ($usuario['email']) echo $usuario['email']; else echo $_POST['email'];?>" size="30"/>
  </p>
  <p align="center">
  <? if ($_SESSION['nivel']==0) {?>
  <strong class="Estilo15">Provincia</strong>
    <label>
    <select name="provincia" id="provincia">
	   <option value="Tenerife" <? if ($usuario['provincia']=='Tenerife' || $usuario['provincia']=='') echo "selected";?>>Tenerife</option>
      <option value="Las Palmas" <? if ($usuario['provincia']=='Las Palmas') echo "selected";?>>Las Palmas</option>
    </select>
    </label> 
<? } ?>	
  <strong class="Estilo15">Nivel</strong>
    <label>
    <select name="nivel" id="nivel">
      <option value="1" <? if ($usuario['nivel']=='1') echo "selected";?>>1</option>
      <option value="2" <? if ($usuario['nivel']=='2' || $usuario['nivel']=='') echo "selected";?>>2</option>
    </select>
    </label> 
<strong class="Estilo15">Activo</strong>
<label>
<input name="activo" type="checkbox" id="activo" value="1" <? if ($usuario['activo']<>0 || $usuario['activo']=='') echo "checked";?> />
</label>
<strong class="Estilo15">Fallidos</strong> 
    <input name="fallidos" type="text" id="fallidos" value="<? if ($usuario['fallidos']<>'') echo $usuario['fallidos']; else echo $_POST['fallidos'];?>" size="5" />
  </p>
  <p align="center"><textarea name="observaciones" cols="80" rows="5" id="observaciones"><? echo $usuario['observaciones'];?></textarea></p>
</div>
    

  <p align="center">
    <label>
	<input name="idusuario" type="hidden" id="idusuario" value="<? echo $usuario['idusuario'];?>" size="30"/>
<? if ($usuario['nombre']<>'Administrador') {?>	
    <input name="boton3" type="submit" id="boton3" style="background-color:#79C143;color:#FFFFFF;font:bolder; padding:5px" value="Guardar"/>
<? } ?>	
    </label>
  </p>
  <p align="center" class="Estilo14">Total de usuarios: <? echo mysql_num_rows(mysql_query("SELECT * FROM usuario"));?> </p>
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
