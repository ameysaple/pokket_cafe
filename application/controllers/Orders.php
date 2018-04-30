<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH.'third_party/escpos/autoload.php';
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer;
use Mike42\Escpos\EscposImage;

class Orders extends Admin_Controller 
{
	public function __construct()
	{
		parent::__construct();

		$this->not_logged_in();

		$this->data['page_title'] = 'Orders';

		$this->load->model('model_orders');
		$this->load->model('model_menus');
		$this->load->model('model_company');
	}

	/* 
	* It only redirects to the manage order page
	*/
	public function index()
	{
		if(!in_array('viewOrder', $this->permission)) {
            redirect('dashboard', 'refresh');
        }

		$this->data['page_title'] = 'Manage Orders';
		$this->render_template('orders/index', $this->data);		
	}

	/*
	* Fetches the orders data from the orders table 
	* this function is called from the datatable ajax function
	*/
	public function fetchOrdersData()
	{
		$result = array('data' => array());

		$data = $this->model_orders->getOrdersData();

		foreach ($data as $key => $value) {

			$count_total_item = $this->model_orders->countOrderItem($value['id']);
			$date = date('d-m-Y', $value['date_time']);
			$time = date('h:i a', $value['date_time']);

			$date_time = $date . ' ' . $time;

			// button
			$buttons = '';

			if(in_array('viewOrder', $this->permission)) {
				$buttons .= '<a href="'.base_url('orders/printDiv/'.$value['id']).'" class="btn btn-default"><i class="fa fa-print"></i></a>';
			}

			if(in_array('updateOrder', $this->permission)) {
				$buttons .= ' <a href="'.base_url('orders/update/'.$value['id']).'" class="btn btn-default"><i class="fa fa-pencil"></i></a>';
			}

			if(in_array('deleteOrder', $this->permission)) {
				$buttons .= ' <button type="button" class="btn btn-default" onclick="removeFunc('.$value['id'].')" data-toggle="modal" data-target="#removeModal"><i class="fa fa-trash"></i></button>';
			}

			if($value['paid_status'] == 1) {
				$paid_status = '<span class="label label-success">Paid</span>';	
			}
			else {
				$paid_status = '<span class="label label-warning">Not Paid</span>';
			}

			$result['data'][$key] = array(
				$value['bill_no'],
				$value['customer_name'],
				$value['customer_phone'],
				$date_time,
				$count_total_item,
				$value['net_amount'],
				$paid_status,
				$buttons
			);
		} // /foreach

		echo json_encode($result);
	}

	/*
	* If the validation is not valid, then it redirects to the create page.
	* If the validation for each input field is valid then it inserts the data into the database 
	* and it stores the operation message into the session flashdata and display on the manage group page
	*/
	public function create()
	{
		if(!in_array('createOrder', $this->permission)) {
            redirect('dashboard', 'refresh');
        }

		$this->data['page_title'] = 'Add Order';

		$this->form_validation->set_rules('menu[]', 'Menu name', 'trim|required');
		
	
        if ($this->form_validation->run() == TRUE) {        	
        	
        	$order_id = $this->model_orders->create();
        	
        	if($order_id) {
        		$this->session->set_flashdata('success', 'Successfully created');
        		redirect('orders/update/'.$order_id, 'refresh');
        	}
        	else {
        		$this->session->set_flashdata('errors', 'Error occurred!!');
        		redirect('orders/create/', 'refresh');
        	}
        }
        else {
            // false case
        	$company = $this->model_company->getCompanyData(1);
        	$this->data['company_data'] = $company;
        	$this->data['is_vat_enabled'] = ($company['vat_charge_value'] > 0) ? true : false;
        	$this->data['is_service_enabled'] = ($company['service_charge_value'] > 0) ? true : false;
        	$this->data['is_gst_enabled'] = ($company['gst_value'] > 0) ? true : false;

        	$this->data['menus'] = $this->model_menus->getActiveMenuData();      	

            $this->render_template('orders/create', $this->data);
        }	
	}

	/*
	* It gets the product id passed from the ajax method.
	* It checks retrieves the particular product data from the product id 
	* and return the data into the json format.
	*/
	public function getMenuValueById()
	{
		$menu_id = $this->input->post('menu_id');
		if($menu_id) {
			$menu_data = $this->model_menus->getMenuData($menu_id);
			echo json_encode($menu_data);
		}
	}

	/*
	* It gets the all the active product inforamtion from the product table 
	* This function is used in the order page, for the product selection in the table
	* The response is return on the json format.
	*/
	public function getTableMenuRow()
	{
		$menus = $this->model_menus->getActiveMenuData();
		echo json_encode($menus);
	}

	/*
	* If the validation is not valid, then it redirects to the edit orders page 
	* If the validation is successfully then it updates the data into the database 
	* and it stores the operation message into the session flashdata and display on the manage group page
	*/
	public function update($id)
	{
		if(!in_array('updateOrder', $this->permission)) {
            redirect('dashboard', 'refresh');
        }

		if(!$id) {
			redirect('dashboard', 'refresh');
		}

		$this->data['page_title'] = 'Update Order';

		$this->form_validation->set_rules('menu[]', 'Menu name', 'trim|required');
		
	
        if ($this->form_validation->run() == TRUE) {        	
        	
        	$update = $this->model_orders->update($id);
        	
        	if($update == true) {
        		$this->session->set_flashdata('success', 'Successfully updated');
        		redirect('orders/update/'.$id, 'refresh');
        	}
        	else {
        		$this->session->set_flashdata('errors', 'Error occurred!!');
        		redirect('orders/update/'.$id, 'refresh');
        	}
        }
        else {
            // false case
        	$company = $this->model_company->getCompanyData(1);
        	$this->data['company_data'] = $company;
        	$this->data['is_vat_enabled'] = ($company['vat_charge_value'] > 0) ? true : false;
        	$this->data['is_service_enabled'] = ($company['service_charge_value'] > 0) ? true : false;
        	$this->data['is_gst_enabled'] = ($company['gst_value'] > 0) ? true : false;

        	$result = array();
        	$orders_data = $this->model_orders->getOrdersData($id);

    		$result['order'] = $orders_data;
    		$orders_item = $this->model_orders->getOrdersItemData($orders_data['id']);

    		foreach($orders_item as $k => $v) {
    			$result['order_item'][] = $v;
    		}

    		$this->data['order_data'] = $result;

        	$this->data['menus'] = $this->model_menus->getActiveMenuData();      	

            $this->render_template('orders/edit', $this->data);
        }
	}

	/*
	* It removes the data from the database
	* and it returns the response into the json format
	*/
	public function remove()
	{
		if(!in_array('deleteOrder', $this->permission)) {
            redirect('dashboard', 'refresh');
        }

		$order_id = $this->input->post('order_id');

        $response = array();
        if($order_id) {
            $delete = $this->model_orders->remove($order_id);
            if($delete == true) {
                $response['success'] = true;
                $response['messages'] = "Successfully removed"; 
            }
            else {
                $response['success'] = false;
                $response['messages'] = "Error in the database while removing the product information";
            }
        }
        else {
            $response['success'] = false;
            $response['messages'] = "Refersh the page again!!";
        }

        echo json_encode($response); 
	}

	/*
	* It gets the product id and fetch the order data. 
	* The order print logic is done here 
	*/
	public function printDiv($id)
	{
		if(!in_array('viewOrder', $this->permission)) {
            redirect('dashboard', 'refresh');
        }

        if ($id) {
        	# Check if order id available.
    		$order_data = $this->model_orders->getOrdersData($id);
			$orders_items = $this->model_orders->getOrdersItemData($id);
			$company_info = $this->model_company->getCompanyData(1);
			$order_date = date('d/m/Y', $order_data['date_time']);
			$paid_status = ($order_data['paid_status'] == 1) ? "Paid" : "Unpaid";

			$gross_amount = $order_data['gross_amount'];
			$bill_no = $order_data['bill_no'];
			$customer_name = $order_data['customer_name'];
			$customer_address = $order_data['customer_address'];
			$phone_number = $order_data['customer_phone'];
			$sur_charge = ($order_data['service_charge'] > 0)?$order_data['service_charge']:0;
			$vat_charge = ($order_data['vat_charge'] > 0)?$order_data['vat_charge']:0;
			$gst_charge = ($order_data['gst_charge'] > 0)?$order_data['gst_charge']:0;
			$discount = ($order_data['discount'] > 0)?$order_data['discount']:0;
			$net_amount = ($order_data['net_amount'] > 0)?$order_data['net_amount']:0;
			$payment_type = $order_data['payment_type'];

			$company_name = !empty($company_info['company_name'])?$company_info['company_name']:'';
			$company_address = !empty($company_info['address'])?$company_info['address']:'';
			$company_phone = !empty($company_info['phone'])?$company_info['phone']:'';

				foreach ($orders_items as $k => $v) {
					$menu_data = $this->model_menus->getMenuData($v['menu_id']);  
					$items[] = new Item($menu_data['name'],$v['qty'].' x '.$menu_data['price'],$v['amount'],true);
				}

			$subtotal = new Item('Sub Total','',$gross_amount,true);
			$surcharge = new Item('Sur Charge','',$sur_charge);
			$vatcharge = new Item('Vat Charge','',$vat_charge);
			$gstcharge = new Item('GST (5%)','',$gst_charge,true);
			$discount = new Item('Discount (%) ','',$discount);
			$netamount = new Item('Net Total','',$net_amount,true);

			$date = date("F j, Y, g:i a");

        	 try {
				// Enter the share name for your USB printer here
				$connector = new WindowsPrintConnector("POS_58");

				$printer = new Printer($connector);

				$printer->initialize();

				/* Print top logo */
				

				/* Name of shop */
				$printer -> selectPrintMode(Printer::MODE_DOUBLE_HEIGHT);
				$printer -> setJustification(Printer::JUSTIFY_CENTER);
				$printer -> text($company_name."\n");
				$printer -> selectPrintMode();
				$printer -> text($company_address."\n");
				$printer -> feed();

				$printer -> setJustification(Printer::JUSTIFY_CENTER);

				/* Title of receipt */
				$printer -> setEmphasis(true);
				$printer -> text("POKKET CAFE\n");
				$printer -> text("Mobile No: ".$company_phone."\n");
				$printer -> text($date . "\n");
				$printer -> setEmphasis(false);

				$printer -> feed();

				$printer -> selectPrintMode(Printer::MODE_DOUBLE_HEIGHT);
				$printer -> setEmphasis(true);
				$printer -> text("Your Bill No is ".$bill_no."\n");

				$printer -> feed();

				/* Items */
				$printer -> selectPrintMode();
				$printer -> setJustification(Printer::JUSTIFY_LEFT);
				$printer -> setEmphasis(true);
				$printer -> text(new Item('Menu Name', 'Qty','Price'));
				$printer -> setEmphasis(false);
				foreach ($items as $item) {
				    $printer -> text($item);
				}
				
				$printer -> setEmphasis(true);
				$printer -> text($subtotal);
				$printer -> setEmphasis(false);
				$printer -> feed();

				$printer -> setEmphasis(true);
				$printer -> text("Payment By ".$payment_type."\n\n");
				$printer -> text($discount."% \n\n");
				$printer -> setEmphasis(false);

				/* Tax and total */
				// if(isset($gstcharge)){
				// 	$printer -> text($gstcharge);
				// }
				
				$printer -> selectPrintMode(Printer::MODE_DOUBLE_HEIGHT);
				$printer -> text($netamount);
				$printer -> selectPrintMode();
				$printer -> feed();

				/* Bar-code at the end */
				// $printer -> setJustification(Printer::JUSTIFY_CENTER);
				// $printer -> barcode("987654321");
				// $printer -> feed();

				/* Footer */
				
				$printer -> setJustification(Printer::JUSTIFY_CENTER);
				$printer -> text("Thank you for visiting here.\n");
				$printer -> text("Opening hours 11 AM to 11.30PM, please visit AGAIN.\n\n");
				$printer -> feed();

				$printer -> setJustification(Printer::JUSTIFY_CENTER);
				$printer -> text("---------------------");
				
				/* Cut the receipt and open the cash drawer */
				$printer -> cut();
				$printer -> pulse();
				$printer -> close();

				redirect('orders/index', 'refresh');
			} catch(Exception $e) {
				echo "Couldn't print to this printer: " . $e -> getMessage() . "\n";
			}
        }
	}

}

/**
* Menu Item Class
*/
class Item
{
	private $name;
	private $price;
	private $qty;
	private $rupeeSign;
	
	public function __construct($name = '',$qty = '',$price = '',$rupeeSign = false)
	{
		$this -> name = $name;
		$this -> qty = $qty;
		$this -> price = $price;
		$this -> rupeeSign = $rupeeSign;
	}

	public function __toString()
	{
		$rightCols = 10;
		$leftCols = 10;
        if ($this -> rupeeSign) {
            $leftCols = $leftCols / 2 - $rightCols / 2;
        }
        $left = str_pad($this -> name, $leftCols) ;
        
        $sign = ($this -> rupeeSign ? ' Rs. ' : '');
        $right = str_pad($sign . $this -> price, $rightCols, ' ', STR_PAD_LEFT);
        return "$left $right\n";
	}
}