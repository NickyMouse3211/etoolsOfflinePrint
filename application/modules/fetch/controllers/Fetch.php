<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
use Escpos\Printer;
use Escpos\EscposImage;
class Fetch extends MX_Controller {

    function __construct() {
		parent::__construct();
		$this->load->library("Escpos.php");
	}
    
    public function printReceipt(){
        
        $input              = $this->input->post();
        $data               = json_decode($input['data']);
        // dd($data);
        $device_name = $data->device_name;
        $ip_address = getLocalIP();
        // dd($ip_address);
        $connector = new Escpos\PrintConnectors\WindowsPrintConnector('smb://'. $ip_address .'/'.$device_name);

        $printer = new Escpos\Printer($connector);
        /* Initialize */
        $printer->initialize();
        $items = array();
        foreach($data->items as $key => $value){
            $items[$key] = new item($value[0],$value[1], $value[2]);
        }
        $subtotal = new item($data->subtotal[0],$data->subtotal[1], $data->subtotal[2]);
		$discount = new item($data->discount[0],$data->discount[1], $data->discount[2]);
        $total    = new item($data->total[0],$data->total[1], $data->total[2]);

        $wildCardLink = $data->wildCardLink;

        $totalPrices   = $data->totalPrices;
        $totalDiscount = $data->totalDiscount;
        $dpp = $data->dpp;
        $ppn = $data->ppn;
        $printer->setTextSize(1, 1);
        /* Name of shop */
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->text(langText('toko')."\n");
        $printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
        $printer->text($data->get_store->ccstore_name."\n");
        $printer->selectPrintMode();
        $printer->text($data->get_store->ccstore_description."\n");
        $printer->text('----------------------');
        $printer->feed();

        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->text(langText('cabang')."\n");
        $printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
        $printer->text($data->get_store->ccbranch_name."\n");
        $printer->selectPrintMode();
        $printer->text($data->get_store->ccbranch_description."\n");
        $printer->text('----------------------');
        $printer->feed();


        /* Title of receipt */
        $printer->setEmphasis(true);
        $printer->text(strtoupper(langText('faktur_penjualan'))."\n");
        $printer->text($data->get_store->cct_code."\n");
        $printer->setEmphasis(false);
        $printer->text(date('d-m-Y H:i:s',strtotime($data->get_store->cct_insert_date))."\n");
        $printer->feed();

        /* Items */
        $printer->setJustification(Printer::JUSTIFY_LEFT);
        foreach ($items as $item) {
            $printer->text($item->getAsString(32)); // for 58mm Font A
        }
        $printer->feed();
        $printer->setEmphasis(true);
        $printer->text($subtotal->getAsString(32));
        $printer->setEmphasis(false);
        

        /* discount and total */
        if($totalDiscount > 0){
            $printer->text($discount->getAsString(32));
        }
        if($ppn > 0){
            $printer->feed();
            $printer->text('DPP:'.number_format($dpp,2,'.',',').'   PPN:'.number_format($ppn,2,'.',','));
            $printer->feed();
        }
        $printer->feed();
        $printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
        $printer->text($total->getAsString(32));
        $printer->selectPrintMode();
        

        /* Footer */
        $printer->feed(2);
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        if($data->get_store->cct_member_token_code != ''){
            $printer->text('Hi, '.$data->get_store->member_name."\n");
        }
        $printer->text(langText('terimakasih_telah_berbelanja_di_toko_kami')."\n");
        $printer->feed(2);

        // $printer->barcode('asd', Printer::BARCODE_CODE39);
        // if($data->get_store->cct_member_token_code != ''){
            // Demo that alignment QRcode is the same as text
            // $printer2 = new Printer($connector); // dirty printer profile hack !!
            // $printer2->setJustification(Printer::JUSTIFY_CENTER);
            // $printer2->qrCode($wildCardLink.$data->get_store->token_member, Printer::QR_ECLEVEL_M, 8);
            // $printer2->text(langText('anggota').' :'.$data->get_store->member_name."\n");
            // $printer2->setJustification();
            // $printer2->feed();
        // }else{
            $printer2 = new Printer($connector); // dirty printer profile hack !!
            $printer2->setJustification(Printer::JUSTIFY_CENTER);
            $printer2->qrCode($wildCardLink.$data->get_store->token_cashier, Printer::QR_ECLEVEL_M, 8);
            $printer2->text(langText('petugas_kasir').' :'.$data->get_store->cashier_name."\n");
            $printer2->setJustification();
            $printer2->feed();
        // }
        
        $printer->cut();
        sleep(2);

        /* Pulse */
        $printer->pulse();

        /* Always close the printer! On some PrintConnectors, no actual
        * data is sent until the printer is closed. */
        $printer->close();

        $datas['status'] 	= staticValue('status_request','success');
	    $datas['message']	= langText('struk_telah_dicetak');
        echo json_encode($datas);
    }

    public function printCode(){
        
        $input              = $this->input->post();
        
        // dd($data);
        $device_name = $input['deviceName'];
        $ip_address = getLocalIP();
        $connector = new Escpos\PrintConnectors\WindowsPrintConnector('smb://'. $ip_address .'/'.$device_name);

        $printer = new Escpos\Printer($connector);
        /* Initialize */
        $printer->initialize();
        $times = (int)$input['times'];
        for ($i=0; $i < $times; $i++) { 
            if($input['codeType'] == 'qr'){
                // Demo that alignment QRcode is the same as text
                $printer2 = new Printer($connector); // dirty printer profile hack !!
                $printer2->setJustification(Printer::JUSTIFY_CENTER);
                $printer2->qrCode($input['text'], Printer::QR_ECLEVEL_M, 8);
                $printer->feed();
                $printer2->text($input['text']);
                $printer2->setJustification();
                $printer2->feed();
            }else{
                $printer2 = new Printer($connector); // dirty printer profile hack !!
                $printer2->setJustification(Printer::JUSTIFY_CENTER);
                $printer->barcode($input['text'], Printer::BARCODE_CODE39);
                $printer->feed();
                $printer2->text($input['text']);
                $printer2->setJustification();
                $printer2->feed();
            }
            $printer->cut();
        }
        
        sleep(2);

        /* Pulse */
        $printer->pulse();

        /* Always close the printer! On some PrintConnectors, no actual
        * data is sent until the printer is closed. */
        $printer->close();

        $datas['status'] 	= staticValue('status_request','success');
	    $datas['message']	= langText('struk_telah_dicetak');
        echo json_encode($datas);
    }

}

class item
{
    private $name;
    private $pcs;
    private $price;
    private $dollarSign;

    public function __construct($name = '', $pcs = '', $price = '', $dollarSign = false)
    {
        $this->name = $name;
        $this->pcs = $pcs;
        $this->price = $price;
        $this->dollarSign = $dollarSign;
    }

    public function getAsString($width = 48)
    {
        $rightCols = 15;
        $leftCols = $width - $rightCols;
        if ($this->dollarSign) {
            $leftCols = $leftCols / 2 - $rightCols / 2;
        }
        $left = str_pad($this->name, $leftCols);

        $sign = ($this->dollarSign ? '$ ' : '');
		$pcs  = ($this->pcs ? $this->pcs.'   ' : '');
        $right = str_pad($pcs.$sign . $this->price, $rightCols, ' ', STR_PAD_LEFT);
        return "$left$right\n";
    }

    public function __toString()
    {
        return $this->getAsString();
    }

}