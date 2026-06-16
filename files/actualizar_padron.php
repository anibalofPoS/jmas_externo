<!DOCTYPE html>
<html lang="en">
<head>
  <meta http-equiv="Content-Type" content="text/html" charset="UTF-8" />
  <title>Actualizando padron</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  
<STYLE>
  body {   background-image: url("images/logo_jmas.png");
background-repeat: no-repeat;
background-size: auto;
background-position:center;
height: 600px;
//width: 300px;
//border: 2px solid black;
opacity: 0.90;
}

.center{text-align:center; padding:8px;}
		div{width:95%; height=50%; padding:0px;margin:auto;}
		center{margin:16px 0;}
</STYLE>  
</head>  
<body>
<!-- Progress bar holder -->
<div align="center">Procesar WS SIGA...</div>
<div align='center' id="progress" style="width:500px;border:1px solid #ccc;"></div>
<!-- Progress information -->
<div align='center' id="information" style="width"></div>
<?php
include("include/dbcommon.php");
// Desactivar buffering automático
ini_set('output_buffering', 'off');
ini_set('zlib.output_compression', 0);

// Cabeceras para evitar que el servidor retenga el contenido
header('Content-Encoding: none');
header('X-Accel-Buffering: no'); // útil en Nginx
header('Content-Type: text/html; charset=utf-8');

// Forzar que el navegador empiece a mostrar algo
echo str_repeat(' ', 1024); // "relleno" para que Chrome/Firefox empiecen a renderizar
flush();
//$flec="2026-04-01";
//$flec1=date('Y-m-d',strtotime($flec.' - 3 days'));
$leer_ws= new ws();
$sql="select count(*) from pulsodelagua.padron where cuenta<1000000";
$total=DBLookUp($sql);
$sql="select cuenta from pulsodelagua.padron limit 100";
$rs=DB::Query($sql);
$n=1;
while ($row=$rs->fetchAssoc()){
   echo '<script language="javascript">
        document.getElementById("information").innerHTML="'.$n.' Registros procesados de '.$total.' Actualizando.'.'"</script>';
   //echo $row['cuenta'].', ';
   $leer_ws->conectar($row['cuenta']);
   if ($leer_ws->cuenta==0){
      $n++;       
      continue;
      
   }
   $datos=array();
   $key=array();
   $key['cuenta']=$row['cuenta'];
   $datos['cuenta']=$row['cuenta'];
   $datos['anomalia']=$leer_ws->anomalia;
   $datos['anomalia_info']=$leer_ws->anomalia_info;
   $datos['id_medidor']=$leer_ws->id_medidor;
   $datos['localizacion']=$leer_ws->localizacion;
   $datos['tarifa']=$leer_ws->tarifa;
   $datos['giro']=$leer_ws->giro;
   $datos['sector']=$leer_ws->sector;
   $datos['distrito']=$leer_ws->distrito;
   $datos['clase']=$leer_ws->clase;
   $datos['colonia']=$leer_ws->colonia;
   $datos['periodos_adeudo']=$leer_ws->periodos_adeudo;
   $datos['adeudo']=$leer_ws->adeudo;
   $datos['nombre']=$leer_ws->nombre;
   $sql="select cuenta from jmas_externo.padron where cuenta={$row['cuenta']} limit 1";
   $existe=DBLookUp($sql);
   if ($existe==$row['cuenta']){
      DB::Update("jmas_externo.padron",$datos,$key);   
   }else{
      $datos[fecha]=date('Y-m-d'); 
      DB::Insert("jmas_externo.padron",$datos);
   }
   echo str_repeat(' ', 1024);
   ob_flush();
   flush();
   $n++;  
}


class ws{
   public $mes,$anomalia,$anomalia_info,$id_medidor,$cuenta;
   public $sector, $distrito, $colonia, $tarifa, $giro, $periodos_adeudo, $adeudo, $clase, $localizacion, $nombre;
   public function __construct() {
      $this->mes=array(
        "Jan"=>"01",
        "Feb"=>"02",
	    "Mar"=>"03",
	    "Apr"=>"04",
	    "May"=>"05",
	    "Jun"=>"06",
	    "Jul"=>"07",
	    "Aug"=>"08",
	    "Sep"=>"09",
	    "Oct"=>"10",
	    "Nov"=>"11",
	    "Dec"=>"12"
	    );
   }
 
   public $cdistrito=array("DISTRITO 1"=>1,
                           "DISTRITO 2"=>2,
                           "DISTRITO 3"=>3,
                           "DISTRITO 4"=>4,
                           "DISTRITO 5"=>5);
                        
   public function conectar($cuenta) {
       $url="http://172.16.70.21/jlm/apis/ora_jmas/api/get-corte-reconexion/".$cuenta;
       $response=file_get_contents($url);
       $response = json_decode($response,true);
       $response = $response['DATA'];
       $this->anomalia='';
       $this->anomalia_info='';
       $this->id_medidor='';
       $this->cuenta=0;
       $this->colonia="";
       $this->distrito=0;
       $this->sector=0;
       $this->tarifa=0;
       $this->giro=0;
       $this->periodos_adeudo=0;
       $this->adeudo=0;
       $this->clase="";
       $this->localizacion="";       
       $this->nombre=""; 
       if (count($response)>0){
          $this->cuenta=$response['CUENTA']; 
          $anomalia=$response['ANOMALIA'];
          $this->anomalia=$this->anomalia($anomalia);
          $this->anomalia_info=$response['ANOMALIA_INFO'];
          $this->id_medidor=$response['SERIE_MEDIDOR'];
          $this->distrito=$this->cdistrito[$response['DISTRITO']];
          $acolonia=explode("Col.", $response["DIRECCION"]);
          $this->colonia=$acolonia[1];
          $this->sector=$response["SECTOR"];
          $this->tarifa=$response['ID_TARIFA'];
          $this->giro=$response['ID_GIRO'];
          $this->periodos_adeudo=$response['PERIODOS_ADEUDO'];
          $this->adeudo=$response['ADEUDO_TOTAL'];
          $this->clase=$response['CLASE'];
          $this->localizacion=$response['LOCALIZACION'];
          $this->nombre=$response['NOMBRE'];
       }
   }

   public function fecha_mysql($fecha){
      $mes=$this->mes;
      if (empty($fecha)){
         return '';
      }
      $a=explode('-',$fecha);
      $a[1]=strtolower($a[1]);
      $a[1]=ucfirst($a[1]);
      $fecha="20".$a[2].'-'.$mes[$a[1]].'-'.$a[0];
      return $fecha;
   }
   
   public function anomalia($anomalia){
       $sql="select id from pulsodelagua.rezagos_cat_anomalias where estatus='ACTIVO' and anomalia='{$anomalia}' limit 1";
       return DBLookUp($sql);
   }
   
}

?>