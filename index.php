<?
#USO DE VARIABLES DE SESION
session_start();

#CONEXION A LA BASE DE DATOS
include("conexion.php");

if ($_REQUEST[accion]="salir") {$_SESSION['acceso']='';$_SESSION['usuario']='';$_SESSION['nivel']='';$_SESSION['provincia']='';}

#INTRODUJO USUARIO Y CLAVE, Y DIO ENTRAR
if ($_POST['boton']=="Entrar" && $_POST['usuario']<>'' && $_POST['clave']<>'') {
		$consulta=mysql_query("SELECT idusuario, alias, nombre, nivel, provincia FROM usuario WHERE alias='".$_POST['usuario']."' AND clave='".$_POST['clave']."' AND activo=1");
		#SI ENCUENTRA UN REGISTRO EN LA TABLA DE USUARIOS
		if ($registro=mysql_fetch_assoc($consulta)){
			#CARGA LAS VARIABLES DE SESION ACCESO, CENTRO Y NIVEL
			$_SESSION['acceso']=$registro['nombre'];
			$_SESSION['usuario']=$registro['idusuario'];
			$_SESSION['nivel']=$registro['nivel'];
			$_SESSION['provincia']=$registro['provincia'];
			#PONE EN CERO LOS ACCESOS FALLIDOS DEL USUARIO
			mysql_query("UPDATE usuario SET fallidos=0 WHERE alias='".$_POST['usuario']."'");
			#ACTUALIZA EL HISTORIAL EN LA TABLA CAMBIO
			$host = $_SERVER['REMOTE_ADDR'];		
			mysql_query("INSERT INTO cambio (fecha_hora,accion,ip,usuario) VALUES (SYSDATE(),'Inicio de sesión','$host','".$_SESSION['usuario']."')");
			#REDIRECCIONA A PAGINA DE CITAS
			header("location:citas.php");
		}
		#NO ENCUENTRA EL REGISTRO
		else {
			#BUSCA LOS ACCESOS FALLIDOS DE ESE USUARIO ACTIVO
			$consulta=mysql_query("SELECT alias, fallidos FROM usuario WHERE alias='".$_POST['usuario']."' AND activo=1");
			#SI ENCUENTRA EL USUARIO ACTIVO MUESTRA MENSAJE DE INTENTOS
			#BLOQUEA SI ES EL TERCER INTENTO PONIENDO ACTIVO=0
			#ACTUALIZA HISTORIAL EN TABLA CAMBIO
			#ACTUALIZA NUMERO DE INTENTOS
			if ($registro=mysql_fetch_assoc($consulta))	{
				$msj='La clave no es correcta.';
				$alias=$registro['alias'];
				$fallidos=$registro['fallidos'];
				switch($fallidos) {
					case(0):$msj=$msj." 1er intento";break;
					case(1):$msj=$msj." 2do intento";break;
					case(2):$msj=$msj." 3er intento. El usuario fue bloqueado.";mysql_query("UPDATE usuarios SET activo=0 WHERE alias='$alias'");break;
				}
				mysql_query("INSERT INTO cambio (fecha_hora,accion,ip,usuario) VALUES (SYSDATE(),'Inicio fallido, clave incorrecta','$host','".$_SESSION['usuario']."')");
				mysql_query("UPDATE usuario SET fallidos=fallidos+1 WHERE alias='$alias'");
				}
			#SI NO ENCUENTRA EL USUARIO ACTIVO
			else {
				$consulta=mysql_query("SELECT alias, activo FROM usuario WHERE alias='".$_POST['usuario']."'");
				if ($registro=mysql_fetch_assoc($consulta)) $msj='El usuario está bloqueado.';
				else $msj='El usuario no existe.';
				}	
		}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>CRM - Acceso Restringido</title>
<link rel="stylesheet" type="text/css" href="estilo.css" />

<style type="text/css">
<!--
.Estilo1 {color: #F48A36}
.Estilo2 {color: #666666}
-->
</style>
</head>

<body>
<p>&nbsp;</p>
<p>&nbsp;</p>
<form ACTION="index.php" METHOD="post" target="_self">
<table width="500" border="0" align="center">
  <tr>
    <td height="60" colspan="2"><div align="center" class="Estilo6">
      <p><img src="imagen/logo.jpg" width="262" height="120" /></p>
      <p class="Estilo2">Acceso Restringido [CRM]</p>
    </div></td>
  </tr>
  <tr>
    <td width="191"><div align="right"><span class="Estilo1"><b>Usuario</b></span></div></td>
    <td width="299" height="40"><input name="usuario" type="text" id="usuario" size="10"  style="font-size:20px;"/></td>
  </tr>
  <tr>
    <td height="40"><div align="right"><span class="Estilo1"><b>Clave</b></span></div></td>
    <td><input name="clave" type="password" id="clave" size="10"  style="font-size:20px;" /></td>
  </tr>
  <tr>
    <td height="60" colspan="2"><div align="center" class="Estilo6">
      <label>
      <input name="boton" type="submit" id="boton" style="font-size:18px" value="Entrar"/>
      </label>
    </div></td>
  </tr>
</table>
<p align="center" class="Estilo7"><? echo $msj;?></p>
</form>
</body>
</html>
