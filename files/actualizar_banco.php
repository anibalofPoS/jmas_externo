<?php
include('include/dbcommon.php');
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
    $sql="select *,
         (select SUBSTRING_INDEX(direccion,'Col.',1) from pulsodelagua.padron where pulsodelagua.padron.cuenta=pulsodelagua.rezagos.cuenta limit 1) as direccion
         from pulsodelagua.rezagos where cuenta={$cuenta} limit 1";
    $rs=DB::Query($sql);
    $row=$rs->fetchAssoc();
    $sql="select * from banco where cuenta={$cuenta} limit 1";
    $rs=DB::Query($sql);
    $ban=$rs->fetchAssoc();
    if ($ban['cuenta']==$cuenta){
       // ya se limpio alguna vez
       agregar(2,$row);
    }else{
       agregar(1,$row);    
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
    $datos['fecha']=$res['periodo'];
    $datos['nombre']=$res['nombre'];
    $datos['localizacion']=$res['localizacion'];
    $datos['colonia']=$res['colonia'];
    $datos['direccion']=$res['direccion'];
    $datos['lat']=$res['lat'];
    $datos['lon']=$res['lon'];
    $datos['status']='DCR';
    if ($agrega==1){
        DB::Insert("banco",$datos);
    }else{
        echo 'funcion<br>';
        DB::Update("banco",$datos,$key);
    }
    
}


?>