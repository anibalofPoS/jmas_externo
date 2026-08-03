<?php
include("include/dbcommon.php");
setlocale(LC_ALL, "es_ES", 'es_MX.utf8');
date_default_timezone_set("America/Denver");
$fecha1=$_GET['fecha1'];
$fecha2=$_GET['fecha2'];
$fecha=date('Y-m-d');
$path='/var/www/html/repojmas/libs/fpdf185/fpdf.php';
require_once($path);
class PDF extends FPDF
   {
   // Page header
   function Header() {
      global $fecha; 
      $this->SetLeftMargin(3);   
      $fecha=utf8_decode(strftime("%d-%b-%Y", strtotime($fecha)));   
      $firma=utf8_decode(strftime("%d-%b-%Y", strtotime($_GET['firma'])));       
      $inicio=utf8_decode(strftime("%d-%b-%Y", strtotime($_GET['fecha1'])));
      $termina=utf8_decode(strftime("%d-%b-%Y", strtotime($_GET['fecha2'])));      
      // Logo
      $this->SetFillColor(223,223,223);
      $this->Image('../rezagos/images/poliza1.png',10,10,40);
      $this->Image('../rezagos/images/poliza2.png',60,10,40);      
      $this->SetTextColor(0,0,0);
      $this->SetFont('Arial','B',10);

      $this->SetFont('Arial','',9);
	  $this->Cell(101,4,"",0,0,'C');      
	  $this->ln();
	  $this->Cell(101,4,"",0,0,'C');            
	  $this->Cell(140,4,"Fecha: ".$fecha,0,0,'C');
	  $this->ln();
	  $this->Cell(101,4,"",0,0,'C');            
      $this->SetFont('Arial','B',9);
	  $this->ln(12);	  
      $this->SetFont('Arial','B',11);
      $this->SetLeftMargin(20);
      $this->Cell(0,4,"Contrato: ".$_GET['contrato'],0,0,'C');
	  $this->ln(4);
      $this->Cell(0,4,"Fecha de la firma del Contrato: ".$firma,0,0,'C');
	  $this->ln(4);      	  
	  $this->Cell(0,4,"Periodo: Del ".$inicio.' al '.$termina,0,0,'C');      
	  $this->Ln(8);	  	  
	  $this->Cell(0,4,utf8_decode("Póliza para el pago limpieza de las cajas de los medidores"),0,0,'C');	  
      $this->SetFont('Arial','B',8);	  
	  $this->ln(8);
      $this->SetLeftMargin(70);	  
      $this->SetFillColor(180, 196, 231);
	  $this->Cell(10,5,"Num.",1,0,'L',true);
      $this->Cell(25,5,'Fecha asignada',1,0,'C',true);
      $this->Cell(25,5,'Fecha limpieza',1,0,'C',true);
      $this->Cell(18,5,'Cantidad',1,0,'C',true);
      $this->ln();
   }

   function Footer(){
      $this->SetLeftMargin(0);
      $this->SetY(-5);      
      $tiempo=date('Y-m-d H:i:s');
      // Print centered page number
      $this->Cell(0,5,'Hoja: '.$this->PageNo().'/{nb}'.' '.$tiempo,0,0,'C');
   }
}
$pdf = new PDF('P','mm');
$pdf->AliasNbPages();
$pdf->SetFont('Arial','',6);
$pdf->AddPage();
$pdf->SetLeftMargin(70);
$pdf->SetFillColor(180, 196, 231);
$sql="SELECT
	date_format(fecha,'%Y-%m-%d') as fecha,
	date_format(fecha_limpio,'%Y-%m-%d') as fecha_limpio,
	count(*) as cajas
FROM
	en_proceso
WHERE
    status='Limpio' and date_format(fecha_limpio,'%Y-%m-%d') between '{$fecha1}' and '{$fecha2}' 
GROUP BY
    date_format(fecha,'%Y-%m-%d'),date_format(fecha_limpio,'%Y-%m-%d')
ORDER BY
    fecha_limpio
";

$rs=DB::Query($sql);
$n=1;
$total=0;
while ($row=$rs->fetchAssoc()){
   if ( $n % 2 ){
	  $sombra=false;
      $pdf->SetFillColor(218, 223, 241);
   }else{
      $sombra=true;
      $pdf->SetFillColor(180, 196, 231);         
   }    
   $pdf->Cell(10,3,$n,'LT',0,'R',$sombra);
   $fa=strftime("%a %d-%b-%Y", strtotime($row['fecha']));
   $fl=strftime("%a %d-%b-%Y", strtotime($row['fecha_limpio']));   
   $pdf->Cell(25,3,$fa,'LT',0,'C',$sombra);
   $pdf->Cell(25,3,$fl,'LT',0,'C',$sombra);
   $pdf->Cell(18,3,$row['cajas'],'LTR',0,'R',$sombra);   
   $total+=$row['cajas'];
   $pdf->Ln();
   $n++;
}
if ( $n % 2 ){
   $sombra=false;
   $pdf->SetFillColor(218, 223, 241);
}else{
   $sombra=true;
   $pdf->SetFillColor(180, 196, 231);         
}
$pdf->Cell(60,3,'Tota...',1,0,'R',$sombra);
$pdf->Cell(18,3,$total,1,0,'R',$sombra);
$sql="SELECT * FROM firmas";
$rs=DB::Query($sql);
$afirmas=array();
while ($row=$rs->fetchAssoc()){
    $afirmas[$row['id']]=array($row['nombre'],$row['puesto'],$row[firma]);
}
$pdf->Ln(20);
$pdf->SetLeftMargin(36);
$pdf->ln(0);
$pdf->Cell(70,5,str_pad("_",40,'_',STR_PAD_BOTH),0,0,'C');
$pdf->Cell(9,5,"",0,0,"C");
$pdf->Cell(70,5,str_pad("_",40,'_',STR_PAD_BOTH),0,0,'C');
$pdf->ln(4);
// elaborado 1 index 1-2
$pdf->Cell(70,5,$afirmas[1][2],0,0,"C");
$pdf->Cell(9,5,"",0,0,"C"); 
// direccion comercial 1 index 2-2
$pdf->Cell(70,5,$afirmas[2][2],0,0,"C");
$pdf->ln(3);
// elaborado 2 index 1-0
$pdf->Cell(70,5,$afirmas[1][0],0,0,"C");
$pdf->Cell(9,5,"",0,0,"C");      
$pdf->Cell(70,5,utf8_decode("Encargada despacho Dir. Comercial"),0,0,"C");
$pdf->Ln(3);
// elaborado 3 index 1-1
$pdf->Cell(70,5,utf8_decode($afirmas[1][1]),0,0,"C");
$pdf->Cell(9,5,"",0,0,"C");
$pdf->Cell(70,5,utf8_decode(''),0,0,"C");
$pdf->ln(16);
$pdf->Cell(70,5,str_pad("_",40,'_',STR_PAD_BOTH),0,0,'C');
$pdf->Cell(9,5,"",0,0,"C");
$pdf->Cell(70,5,str_pad("_",40,'_',STR_PAD_BOTH),0,0,'C');
$pdf->ln(4);      
$pdf->Cell(70,5,"Autorizado por:",0,0,"C");
$pdf->Cell(9,5,"",0,0,"C");      
$pdf->Cell(70,5,"Autorizado por:",0,0,"C");
$pdf->ln(3);
$pdf->Cell(70,5,utf8_decode("Dirección Financiera"),0,0,"C");
$pdf->Cell(9,5,"",0,0,"C");      
$pdf->Cell(70,5,"Jefa de Recursos Humanos",0,0,"C");
$pdf->Ln(3);
$pdf->Cell(70,5,utf8_decode(''),0,0,"C");
$pdf->Cell(9,5,"",0,0,"C");
$pdf->Cell(70,5,utf8_decode(''),0,0,"C");
$pdf->Output();
?>