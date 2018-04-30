<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Reports extends Admin_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->data['page_title'] = 'Stores';
		$this->load->model('model_reports');
	}

	/*
    * It redirects to the report page
    * and based on the year, all the orders data are fetch from the database.
    */
	public function index()
	{
		if(!in_array('viewReports', $this->permission)) {
            redirect('dashboard', 'refresh');
        }

		$today_year = date('Y');

		if($this->input->post('select_year')) {
			$today_year = $this->input->post('select_year');
		}

		$parking_data = $this->model_reports->getOrderData($today_year);
		$this->data['report_years'] = $this->model_reports->getOrderYear();


		$final_parking_data = array();
		foreach ($parking_data as $k => $v) {

			if(count($v) > 1) {
				$total_amount_earned = array();
				foreach ($v as $k2 => $v2) {
					if($v2) {
						$total_amount_earned[] = $v2['gross_amount'];
					}
				}
				$final_parking_data[$k] = array_sum($total_amount_earned);
			}
			else {
				$final_parking_data[$k] = 0;
			}

		}

		$this->data['current_annual_income']=$this->model_reports->get_current_annual_income();

		// $this->data['previous_annual_income']=$this->daily_management_model->get_previous_annual_income();
		//
		// $this->data['todays_income']=$this->daily_management_model->get_todays_income();
		//
		// $this->data['yesterdays_income']=$this->daily_management_model->get_yesterdays_income();
		//
		// $this->data['todays_expense']=$this->daily_management_model->get_todays_expense();
		//
		// $this->data['yesterdays_expense']=$this->daily_management_model->get_yesterdays_expense();
		//
		// $this->data['current_month_income']=$this->daily_management_model->get_current_month_income();
		//
		// $this->data['last_six_month_income']=$this->daily_management_model->get_last_six_month_income();
		//
		// $this->data['last_one_year_groupby_month_income']=$this->daily_management_model->get_last_one_year_groupby_month_income();
		//
		// $this->data['last_one_year_groupby_month_expense']=$this->daily_management_model->get_last_one_year_groupby_month_expense();
		//
		// $this->data['current_week_income']=$this->daily_management_model->get_current_week_income();
		//
		// $this->data['current_week_expense']=$this->daily_management_model->get_current_week_expense();
		//
		// $this->data['last_week_income']=$this->daily_management_model->get_last_week_income();
		//
		// $this->data['last_week_expense']=$this->daily_management_model->get_last_week_expense();

		$this->data['selected_year'] = $today_year;
		$this->data['company_currency'] = $this->company_currency();
		$this->data['results'] = $final_parking_data;

		$this->render_template('reports/index', $this->data);
	}
}
