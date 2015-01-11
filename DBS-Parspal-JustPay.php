<?php

/*
Plugin Name: DBS-Parspal-JustPay
Plugin URI: http://www.dbstheme.com/?s=parspal
Description: اضافه کردن فرم پرداخت وجه با مبلغ تعیین شده توسط کاربر.
Author: دی بی اس تم
Author URI: http://dbstheme.com/
Version: 1.0
*/

! defined( 'ABSPATH' ) and exit;
@ session_start();

include 'lib/DBS-Funcy.php';
include 'lib/DBS-Parspal.class.php';

$DBSfuncy = new PP_JustPay_DBSfuncy();
$DBSparspal = new PP_JustPay_DBSParspal();

# Adding Options Panel

add_action( 'admin_menu', 'DBS_PP_JP_AddMenuPage' );

function DBS_PP_JP_AddMenuPage(){
    add_menu_page( 'افزوه پرداخت آزاد پارس پال', 'پرداخت آزاد', 'install_plugins', 'DBSParspalJustPay', 'DBS_PP_JP_AdminPage' );
	function DBS_PP_JP_AdminPage(){
		$DBSfuncy = new PP_JustPay_DBSfuncy();
		$DBSfuncy -> OptionsPanel(array(
			array('name'=>'مشخصات درگاه پارس پال شما','type'=>'headline'),
			'DBS_PP_JP_Merchant' => 'مرچنت درگاه پرداخت پارس پال',
			'DBS_PP_JP_Password' => 'پسورد درگاه پرداخت پارس پال'
		));
	}
}

# Adding the Shortcode

add_action( 'init', 'DBS_PP_JP_RegisterShortcode');

function DBS_PP_JP_RegisterShortcode(){
	add_shortcode('DBSParspalJustPay', 'DBS_PP_JP_Shortcode');
}

function DBS_PP_JP_Shortcode() {
	$DBSparspal = new PP_JustPay_DBSParspal();

	$DBSparspal -> Set(get_option('DBS_PP_JP_Merchant'),get_option('DBS_PP_JP_Password'));
	$dbs_err = false;

	if (isset($_POST['refnumber']) && isset($_POST['resnumber'])) {

		$price = $_SESSION['DBS_PP_JP_Go']['amount'];
		$ref = $_POST['refnumber'];

		$res = $DBSparspal -> Check( $price, $ref );

		if ($res == 'success') {
			return '<div class="msgbox allow box">عملیات پرداخت وجه به مبلغ ' . $price . ' تومان و با شماره رسید ' . $ref . ' با موفقیت انجام شد.</div>';
		} else {
			return '<div class="msgbox alert box">متاسفانه مشکلی در بازگشت تراکنش وجود داشته است. لطفا با مدیر تارنما تماس بگیرید. خطا شماره: ' . $res . '</div>';
		}

	} else {

		if( isset($_POST) && isset($_POST['DBS_PP_JP_FormSend']) && $_POST['DBS_PP_JP_FormSend'] == 'go' ){
			if( $_POST['DBS_PP_JP_Amount'] != '' && $_POST['DBS_PP_JP_Name'] != '' && $_POST['DBS_PP_JP_eMail'] != '' && $_POST['DBS_PP_JP_Desc'] != '' ){
				$_SESSION['DBS_PP_JP_Go'] = array(
					'amount'=>$_POST['DBS_PP_JP_Amount'],
					'name'=>$_POST['DBS_PP_JP_Name'],
					'email'=>$_POST['DBS_PP_JP_eMail'],
					'phone'=>$_POST['DBS_PP_JP_Phone'],
					'desc'=>$_POST['DBS_PP_JP_Desc']
				);
				$DBSparspal -> Go(array(
					"price"       => $_POST['DBS_PP_JP_Amount'],
					"return"  => get_permalink(), 
					"resnum"   => md5(rand(99999,9999999999)), 
					"desc" => $_POST['DBS_PP_JP_Desc'], 
					"payer"   => $_POST['DBS_PP_JP_Name'],
					"mail"       => $_POST['DBS_PP_JP_eMail'],
					"mob"      => $_POST['DBS_PP_JP_Phone']
				));
			} else {
				$dbs_err = true;
			}
		}
		if($dbs_err == true) { $dbs_out = '<div class="box alert msgbox">لطفا از کامل بودن موارد ضروری خواسته شده اطمینان حاصل کنید.</div>'; }
		@$dbs_out .=  '
			<form method="post" class="DBS-JustPay">
				<input type="hidden" name="DBS_PP_JP_FormSend" value="go" /> 

				<label for="DBS_PP_JP_Amount">مبلغ مورد نظر به تومان*</label>
				<input type="text" name="DBS_PP_JP_Amount" placeholder="مبلغ مورد نظر به تومان..." style="width: 100%;" value="' . @$_POST['DBS_PP_JP_Amount'] . '" /> 

				<label for="DBS_PP_JP_Name">نام و نام خانوادگی*</label>
				<input type="text" name="DBS_PP_JP_Name" placeholder="نام و نام خانوادگی..." style="width: 100%;" value="' . @$_POST['DBS_PP_JP_Name'] . '" /> 

				<label for="DBS_PP_JP_eMail">ایمیل*</label>
				<input type="text" name="DBS_PP_JP_eMail" placeholder="ایمیل..." style="width: 100%;" value="' . @$_POST['DBS_PP_JP_eMail'] . '" /> 

				<label for="DBS_PP_JP_Phone">شماره تماس</label>
				<input type="text" name="DBS_PP_JP_Phone" placeholder="شماره تماس..." style="width: 100%;" value="' . @$_POST['DBS_PP_JP_Phone'] . '" />

				<label for="DBS_PP_JP_Desc">توضیحات پرداخت*</label>
				<input type="text" name="DBS_PP_JP_Desc" placeholder="توضیحات پرداخت..." style="width: 100%;" value="' . @$_POST['DBS_PP_JP_Desc'] . '" />

				<input type="submit" value="اتصال به درگاه و پرداخت"  name="submit" /> 
			</form>
		';
	}
	return $dbs_out;
}

?>