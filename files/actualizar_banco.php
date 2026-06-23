<?php
include('include/dbcommon.php');
setlocale(LC_ALL, "es_ES", 'es_MX.utf8');
date_default_timezone_set("America/Denver");
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
$acuentas=explode(',',$_GET['cuentas']);
foreach ($acuentas as $cuenta){
    echo $cuenta.'<br>';
    $sql="select * from padron where cuenta={$cuenta} limit 1";
    $rs=DB::Query($sql);
    $ban=$rs->fetchAssoc();
    if ($ban['cuenta']==$cuenta){
       // ya se limpio alguna vez
       agregar(2,$ban);
    }else{
       agregar(1,$ban);    
    } 
    echo str_repeat(' ', 1024);
    ob_flush();
    flush();
}

function agregar($agrega,$res){
    $datos=array();
    $key=array();
    $key['cuenta']=$res['cuenta'];
    $datos['cuenta']=$res['cuenta'];
    $datos['id_medidor']=$res['id_medidor'];
    $datos['nombre']=$res['nombre'];
    $datos['localizacion']=$res['localizacion'];
    $datos['colonia']=$res['colonia'];
    $datos['direccion']=$res['calle'];
    $datos['lat']=$res['geolat'];
    $datos['lon']=$res['geolon'];
    $datos['status']='DCR';
    if ($agrega==1){
        $datos['fecha']=date('Y-m-d');
        DB::Insert("banco",$datos);
    }else{
        DB::Update("banco",$datos,$key);
    }
    
}


?>