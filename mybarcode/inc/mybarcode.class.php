<?php

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

/**
 * Class to generate barcodes using PEAR Image_Barcode
 **/
class PluginMyBarcodeMyBarcode extends CommonDBTM
{
    private $docsPath;

    static $rightname = 'plugin_mybarcode_mybarcode';


    /**
     * Constructor
     **/
    function __construct()
    {
        $this->docsPath = GLPI_PLUGIN_DOC_DIR . '/mybarcode/';
    }


    /**
     * @since version 0.85
     *
     * @see CommonDBTM::showMassiveActionsSubForm()
     **/
    static function showMassiveActionsSubForm(MassiveAction $ma)
    {
        switch ($ma->getAction()) {
            case 'Generate':
                echo Html::submit(__('Generate'), array('name' => 'massiveaction')) . "</span>";
                return true;
        }
        return parent::showMassiveActionsSubForm($ma);
    }


    static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item, array $ids)
    {
        global $CFG_GLPI;
        global $DB;
        require_once(GLPI_ROOT . "/vendor/tecnickcom/tcpdf/tcpdf.php");
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Andreyz');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf->SetMargins(5,4,5,5);
        $pdf->SetHeaderMargin(5);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        $pdf->SetAutoPageBreak(false, PDF_MARGIN_BOTTOM);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        $pdf->setBarcode(date('Y-m-d H:i:s'));
        $pdf->SetFont('dejavuserifcondensed', '', 10);
        $style = array('position' => '', 'align' => 'C', 'stretch' => false, 'fitwidth' => true, 'cellfitalign' => '', 'border' => false, 'hpadding' => 0,
                       'vpadding' => 1, 'fgcolor' => array(0, 0, 0), 'bgcolor' => false, 'text' => true, 'font' => 'dejavuserifcondensed', 'fontsize' => 10, 'stretchtext' => 1);
        $style2 = array('width' => 0.6, 'cap' => 'round', 'join' => 'round', 'dash' => '2,10');
        $style3 = array('width' => 0.5, 'cap' => 'round', 'join' => 'round', 'dash' => '2,10');
        $pdf->AddPage();
        $pdf->Line(71, 1, 71, 290,$style3);
        $pdf->Line(139, 1, 139, 290,$style3);
        $n = 0;
        $hh = 1;
        foreach ($ids as $key) {
            $item->getFromDB($key);
echo $item->isField('otherserial');
            if ($item->isField('otherserial')) {
                $code = $item->getField('otherserial');
                $style['position'] = '';
                if ($code != '') {
                    $n = $n + 1;
                    switch ($n) {
                        case 1 :
                            $style['position'] = 'L';
                            $p = 'L';
                            $x=5;
                            break;
                        case 2 :
                            $style['position'] = 'C';
                            $p = 'C';
                            break;
                        case 3 :
                            $style['position'] = 'R';
                            $p = 'R';
                            $n = 0;
                            $hh = $hh + 37;
                            break;
                    }
						
                    $query = 'SELECT LOWER(name) FROM glpi_plugin_fields_containers';
                    $result = $DB->query($query);
                    $fields_containers = $DB->fetch_row($result);
					
                    $type = strtolower($item->getType());
                    $query = 'select f.field from glpi_plugin_fields_'. $type .$fields_containers[0].'s f where f.items_id =' . $item->getID();
                    $result = $DB->query($query);
                    $invbuh = $DB->fetch_row($result);
                    $typeP=$type;
                     if ($type=='Printer'){
                        /*$res = $DB->query("SELECT t.name FROM glpi_printers p, glpi_printertypes t WHERE p.is_deleted = '0' AND p.`is_template` = '0' and p.printertypes_id= t.id and p.id =". $item->getID());
                        $typear=$DB->fetch_row($res);
                        $type=$typear[0];*/
                         $typeP="Принт/Скан";
                     };

		    $str_zg="Тлф. ОИТ 28-33,20-70";
		    if ($typeP=='computer') {
                        $trimtxt=$typeP;
			$str_zg="Тлф. ОИТ 28-33,20-70";
		    }
		    elseif ($typeP=='networkequipment')
		    {
                        $res = $DB->query("SELECT glpi_networkequipments.name, gm.name,  gn.name, glpi_networkequipments.comment,glpi_ipaddresses.name
			  FROM glpi_networkequipments, glpi_networkequipmentmodels gn, glpi_manufacturers gm ,glpi_ipaddresses
		          WHERE glpi_networkequipments.networkequipmentmodels_id = gn.id  AND gm.id = glpi_networkequipments.manufacturers_id
		          and glpi_networkequipments.id = glpi_ipaddresses.mainitems_id AND glpi_ipaddresses.mainitemtype = 'NetworkEquipment' AND glpi_ipaddresses.is_deleted = 0
		          AND glpi_networkequipments.is_deleted = 0 AND glpi_networkequipments.is_template = 0
		          AND glpi_networkequipments.id =". $item->getID());
                        $typear=$DB->fetch_row($res);
                        $type=$typear[0];
                        $type1=$typear[1];
                        $type2=$typear[2];
                        $type3=$typear[3];
                        $type4=$typear[4];
			$trimtxt=$type1." ".$type2;
//			$type3= strip_tags(preg_replace("/('|\"|\r?\n)/", '',$type3));
			$type3= mb_eregi_replace("/[^ \na-zа-я0-9`~\!@#\$%\^&\*\(\)_\+\-\=\[\]\{\}\\\|;\:'\",\.\/\<\>\?]+/ui",'',$type3);
//			$type3=trim(preg_replace("/(\s*[\r\n]+\s*|\s+)/", ' ', $type3));
			$type3=str_replace (array("\r\n", "\n", "\r"), ' ', $type3);
		        $str_zg=mb_substr($type3, 0, 21, 'UTF-8')." ".$type4;
		    }
		    else
		    {
			$trimtxt=$typeP . '-' . $item->getField('name');
  		        $str_zg="Тлф. ОИТ 28-33,20-70";
		    };

                    $pdf->Write(0,$str_zg, '', 0, $p, true, 0, false, false, 0);
                    $pdf->SetFont('dejavuserifcondensed', 'B', 12);

                    if (strlen($trimtxt)> 24){
                        $trimtxt=substr($trimtxt,strpos($trimtxt,'-')+1);
                        $trimtxt=substr($trimtxt,0,26);
                    }
                    $pdf->Write(0, $trimtxt, '', 0, $p, true,0, false, true, 0);
                    $pdf->SetFont('dejavuserifcondensed', '', 10);
                    $pdf->write1DBarcode($code, 'C39E', '', '', 62, 15, 1, $style, 'N');
                    $pdf->Write(0, 'Инв.№ ' . $invbuh[0]." Д: ".date("Y-m"), '', 0, $p, true, 0, false, false, 0);
                    $pdf->Write(0, 'Серийный  № ' . $item->getField('serial'), '', 0, $p, true, 0, false, false, 0);
                    $pdf->SetY($hh+3);
                    $pdf->Line(1, $hh, 210, $hh,$style2);
                    if ($hh > 280) {
                        $hh = 1;
                        $pdf->AddPage();
                        $pdf->Line(71, 1, 71, 290,$style3);
                        $pdf->Line(139, 1, 139, 290,$style3);
                    }
                }
            }
        }
        $file = $pdf->Output('ddd.pdf', 'S');
        $pdfFile = GLPI_ROOT . "/files/_plugins/mybarcode/" . $type . '.pdf';
        if (file_exists($pdfFile)) unlink($pdfFile);
        file_put_contents($pdfFile, $file);
        $filePath = explode('/', $pdfFile);
        $filename = $filePath[count($filePath) - 1];
        $msg = "<a href='".$CFG_GLPI['root_doc'].'/plugins/mybarcode/front/send.php?file='.urlencode($filename)."'>".__('Generated file', 'barcode')."</a >";
        Session::addMessageAfterRedirect($msg);
        $ma->itemDone($item->getType(), 0, MassiveAction::ACTION_OK);

        parent::processMassiveActionsForOneItemtype($ma, $item, $ids);
    }
}