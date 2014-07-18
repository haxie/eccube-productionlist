<?php 

require_once CLASS_REALDIR . 'mpdf/mpdf.php';

class SC_Helper_ProductionList {

    /**
     * Generate HTML for production list PDF.
     *
     * @param array $categoryMap associative array (category_id => category_name)
     * @param string $shippingDate "2014-03-14"
     * @param &string $html to store HTML
     * @return void
     */

    public function generateProductionList($categoryMap, $shippingDate, &$html) {

        $fontStyles = "font-family:Arial,sans-serif; font-size:12px; clear:both; float:left; text-align:left;"; 
        $html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> <html lang="ja"> <head> <meta http-equiv="content-type" content="text/html; charset=utf-8"> <style type="text/css"> @page { margin: 10% 10% 12% 0; margin-header: 2.4cm; margin-footer: 1.5cm; margin-left: 1cm; margin-right: 1.9cm; } </style> </head> <body>'; 

        $shippingDate = strtotime($shippingDate);
        $timestamp = SC_Utils_Ex::sfGetTimestamp(date("Y", $shippingDate), date("n", $shippingDate), date("j", $shippingDate), false);
        
        $sel = " (case ";

        foreach ($categoryMap as $id => $name) {
            $sel .= " when C.product_id IN( SELECT DISTINCT product_id FROM dtb_product_categories WHERE category_id IN ( $id )) 
                            then '$name' ";
        }

        $sel .= " else product_name end) as grouped_cats, SUM(quantity) as quantity ";

        $from = " dtb_shipping as A, dtb_order as B, dtb_order_detail as C, dtb_products as D "; 

        $where = " A.order_id = B.order_id
                          AND B.order_id        = C.order_id
                          AND A.shipping_date   = date('" . $timestamp  . "')

                          AND B.del_flg         = 0
                          AND B.status          != 3
                          AND A.del_flg         = 0
                          AND C.quantity        > 0
                          AND C.product_id      = D.product_id

                          AND D.product_id  IN (SELECT DISTINCT product_id FROM dtb_product_categories WHERE category_id NOT IN (10,29,41,27) ) ";

        $arrResults = $this->findLists($sel, $from, $where);

        $sum = 0;
        for ($i = 0; $i < count($arrResults); ++$i) {
            $sum += $arrResults[$i]['quantity'];
        }

        $html .= '<table><tr> <th>商品名</th> <th>台数</th> </tr>';

        foreach($arrResults as $result) {
            $html .= '<tr>';
            $html .= '<td style="' . $fontStyles . '">' . $result['grouped_cats'] . '</td>';
            $html .= '<td>' . $result['quantity'] . '</td>';
            $html .= '</tr>';
        }

        $html .= '<tr> <td style="'.$fontStyles.'">合計台数</td> <td>'.$sum.'</td> </tr>';
        $html .= '</table>';
        
        $html .= '</body></html>';
    }

    /**
     * Download the PDF. 
     *
     * @param string $html data to populate the PDF with
     * @param string $title name of PDF 
     * @param string $is_download download directly ('D') or save ('F')
     * @param string $path if saving, specify a path (ie /var/www/pdf/) 
     * @return void 
     */

    public function downloadMPDF($html, $title, $isDownload = 'D', $path = NULL){
        $mpdf = new mPDF( 'utf-8', array(211, 106) );
        $mpdf->useAdobeCJK = true;
        
        $mpdf->WriteHTML($html);
        if ($isDownload == 'D') {
            $mpdf->Output($title, $isDownload );
        } else {
            $mpdf->Output($path . $title, $isDownload);
        }
    }

    /**
     * Return the MySQL data.
     *
     * @param string $sel
     * @param string $from 
     * @param string $where 
     * @return array $where
     */

    private function findLists($sel, $from, $where) {
        $objQuery =& SC_Query_Ex::getSingletonInstance();
        $objQuery->setGroupBy('grouped_cats');

        return $objQuery->select($sel, $from, $where);
    }
}

?>
