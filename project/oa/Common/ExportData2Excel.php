<?php

require 'PHPExcel.php';
require 'ExcelAssistant.php';

class ExportData2Excel {

    private $i = 1;
    private $field_array = array();
    private $field_width_array = array();
    private $row_height_array = 15;

    /**
     * 设置字段顺序
     * 默认是按照数据表字段顺序导出
     * @param type $field
     */
    public function set_field($field = array()) {
        $this->field_array = $field;
    }

    /**
     * 这种列的宽度,已array的形式一次排列.
     * 若不设置,所有列的宽度均默认设置为20
     * @param type $field_width
     */
    public function set_field_width($field_width = array()) {
        $this->field_width_array = $field_width;
    }

    public function set_row_height($height) {
        $this->row_height_array = $height;
    }

    /**
     * @param type $th 数据头 array
     * @param type $data 数据源 array
     * @param type $title 数据title String
     * @param type $filename 导出文件名
     */
    public function create($th = array(), $data = array(), $title = "", $filename = "excel导出") {
        $objExcel = new PHPExcel();
        $objExcel->getProperties()->setCreator("非时序集团");
        $objExcel->setActiveSheetIndex(0);
        $excelAssistant = new ExcelAssistant();
        $excelTit_array = $excelAssistant->GetExcelTit(count($th));

        if ($title !== "") {
            $mergeCells_name = $excelTit_array[0] . ($this->i) . ":" . $excelTit_array[count($excelTit_array) - 1] . $this->i;
            $objExcel->getActiveSheet()->mergeCells($mergeCells_name);
            $objExcel->getActiveSheet()->setCellValue("A1", $title);
            $objExcel->getActiveSheet()->getStyle('A1')->applyFromArray(
                    array(
                        'font' => array(
                            'bold' => true
                        ),
                        'alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                        )
                    )
            );
            $this->i = 2;
        }
        if (count($this->field_width_array) > 0) {
            foreach ($excelTit_array as $k => $v) {
                $objExcel->getActiveSheet()->getColumnDimension($v)->setWidth($this->field_width_array[$k]);
            }
        } else {
            foreach ($excelTit_array as $k => $v) {
                $objExcel->getActiveSheet()->getColumnDimension($v)->setWidth(20);
            }
        }
        foreach ($th as $k => $v) {
            $objExcel->getActiveSheet()->setCellValue(($excelTit_array[$k]) . $this->i, "$v");
            $objExcel->getActiveSheet()->getStyle(($excelTit_array[$k]) . $this->i)->applyFromArray(
                    array(
                        'alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                        )
                    )
            );
        }
        if (count($this->field_array) > 0) {
            foreach ($data as $k => $v) {
                $this->i++;
                $c_nul = 0;
                foreach ($this->field_array as $key => $value) {
                    $c_v = $excelTit_array[$c_nul++] . $this->i;
                    $objExcel->getActiveSheet()->setCellValue($c_v, $v[$value]);
                    $objExcel->getActiveSheet()->getStyle($c_v)->applyFromArray(
                            array(
                                'alignment' => array(
                                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                                )
                            )
                    );
                }
            }
        } else {
            foreach ($data as $k => $v) {
                $this->i++;
                $c_nul = 0;
                foreach ($v as $index => $d) {
                    $c_v = $excelTit_array[$c_nul++] . $this->i;
                    $objExcel->getActiveSheet()->setCellValue($c_v, $d);
                    $objExcel->getActiveSheet()->getStyle($c_v)->applyFromArray(
                            array(
                                'alignment' => array(
                                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                                )
                            )
                    );
                }
            }
        }
        for ($x = 0; $x <= count($data) + 2; $x++) {
            $objExcel->getActiveSheet()->getRowDimension($x)->setRowHeight($this->row_height_array);
        }

        $timestamp = time();
        $timestr = date("Y-m-d", $timestamp);
        header('Content-Type: application/vnd.ms-excel;charset=utf-8');
        header('Content-Disposition: attachment;filename="' . $filename . '@' . $timestr . '.xls"');
        header('Cache-Control: max-age=0');
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate,post-check=0,pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header("Content-Transfer-Encoding:binary");
        $objWriter = PHPExcel_IOFactory::createWriter($objExcel, 'Excel5');
        ob_clean();  
        $objWriter->save("php://output");
    }

}
