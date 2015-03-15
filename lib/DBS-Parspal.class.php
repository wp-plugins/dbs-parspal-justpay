<?php

# Version 1.0.1

class PP_JustPay_DBSParspal {

	private $WSURL = "http://merchant.parspal.com/WebService.asmx?wsdl";
	private $MID;
	private $Pass;

	public function Set($MID, $Pass) {
		$this->MID = $MID;
		$this->Pass = $Pass;
	}

	public function Go($inarray = array()) {

		extract( $inarray );

		$connect = new SoapClient($this->WSURL);

		$result  = $connect->RequestPayment(array(
			"MerchantID"  => $this->MID, 
			"Password"    => $this->Pass, 
			"Price"       => $price,
			"ReturnPath"  => $return, 
			"ResNumber"   => $resnum, 
			"Description" => urlencode($desc), 
			"Paymenter"   => $payer,
			"Email"       => $mail,
			"Mobile"      => $mob
		));

		if( $result->RequestPaymentResult->ResultStatus == 'Succeed' ){
			return $result->RequestPaymentResult->PaymentPath;
		} else {
			return $result->RequestPaymentResult->ResultStatus;
		}

	}

	public function Check($price, $refnum) {

		$connect = new SoapClient($this->WSURL);

		$result  = $connect->VerifyPayment(array(
			"MerchantID" => $this->MID, 
			"Password"   => $this->Pass, 
			"Price"      => $price,
			"RefNum"     => $refnum
		));

		return $result->verifyPaymentResult->ResultStatus;

	}


}

?>