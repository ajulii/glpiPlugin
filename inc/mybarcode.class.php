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
        $pdf->SetMargins(5, 5,5,5);
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
        $hh = 5;
        foreach ($ids as $key) {
            $item->getFromDB($key);
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
                            $hh = $hh + 35;
                            break;
                    }
                    $type = $item->getType();
                    $query = "select f.field from field_add_type f where upper(f.itemtype)=Upper('" . $type . "') and f.items_id =" . $item->getID();
                    $result = $DB->query($query);
                    $invbuh = $DB->fetch_row($result);
                    $typeP=$type;
                     if ($type=='Printer'){
                        /*$res = $DB->query("SELECT t.name FROM glpi_printers p, glpi_printertypes t WHERE p.is_deleted = '0' AND p.`is_template` = '0' and p.printertypes_id= t.id and p.id =". $item->getID());
                        $typear=$DB->fetch_row($res);
                        $type=$typear[0];*/
                         $typeP="Принт/Скан";
                     };
                    $pdf->Write(0, "Тлф. АСУП 73-79   ".date("Y-m-d"), '', 0, $p, true, 0, false, false, 0);
                    $pdf->Write(0, $typeP . '-' . $item->getField('name'), '', 0, $p, true,0, false, true, 0);
                    $pdf->write1DBarcode($code, 'C39E', '', '', 64, 15, 1, $style, 'N');
                    $pdf->Write(0, 'Инв. Бух. №' . $invbuh[0], '', 0, $p, true, 0, false, false, 0);
                    $pdf->Write(0, 'Серийный  №' . $item->getField('serial'), '', 0, $p, true, 0, false, false, 0);
                    $pdf->SetY($hh);
                    $pdf->Line(1, $hh-1, 210, $hh-1,$style2);
                    if ($hh > 280) {
                        $hh = 5;
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