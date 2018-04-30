<?php

class Model_reports extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
	}

	/*getting the total months*/
	private function months()
	{
		return array('01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12');
	}

	/* getting the year of the orders */
	public function getOrderYear()
	{
		$sql = "SELECT * FROM orders WHERE paid_status = ?";
		$query = $this->db->query($sql, array(1));
		$result = $query->result_array();

		$return_data = array();
		foreach ($result as $k => $v) {
			$date = date('Y', $v['date_time']);
			$return_data[] = $date;
		}

		$return_data = array_unique($return_data);

		return $return_data;
	}

	// getting the order reports based on the year and moths
	public function getOrderData($year)
	{
		if($year) {
			$months = $this->months();

			$sql = "SELECT * FROM orders WHERE paid_status = ?";
			$query = $this->db->query($sql, array(1));
			$result = $query->result_array();

			$final_data = array();
			foreach ($months as $month_k => $month_y) {
				$get_mon_year = $year.'-'.$month_y;

				$final_data[$get_mon_year][] = '';
				foreach ($result as $k => $v) {
					$month_year = date('Y-m', $v['date_time']);

					if($get_mon_year == $month_year) {
						$final_data[$get_mon_year][] = $v;
					}
				}
			}


			return $final_data;

		}
	}

  public function get_current_annual_income()
  {
  $sql ="SELECT SUM(gross_amount) as total,(YEAR(CURRENT_DATE)) as current_year FROM `orders` WHERE YEAR("date('Y',date_time)")=(YEAR(CURRENT_DATE))";

	$query = $this->db->query($sql);
print_r($this->db->last_query());exit;
  if ($query) {
  return $query->result();
  } else {
  return FALSE;
  }
  }

  public function get_previous_annual_income()
  {
    $sql ="SELECT SUM(amount) as total,(YEAR(CURRENT_DATE)-1) as previous_year FROM `income` WHERE YEAR(date)=(YEAR(CURRENT_DATE)-1)";
  $query = $this->db->query($sql);

  if ($query) {
  return $query->result();
  } else {
  return FALSE;
  }
  }

  public function get_todays_income()
  {
    $sql ="SELECT SUM(amount) as total,(CURRENT_DATE) as currentdate FROM `income` WHERE date=CURRENT_DATE";
  $query = $this->db->query($sql);

  if ($query) {
  return $query->result();
  } else {
  return FALSE;
  }
  }

  public function get_yesterdays_income()
  {
    $sql = "SELECT SUM(amount) as total,date(CURRENT_DATE)- INTERVAL 1 DAY as currentdate FROM `income` WHERE date=(CURRENT_DATE)- INTERVAL 1 DAY";
    $query = $this->db->query($sql);

  if ($query) {
  return $query->result();
  } else {
  return FALSE;
  }
  }

  public function get_todays_expense()
  {
    $sql ="SELECT SUM(amount) as total,(CURRENT_DATE) as currentdate FROM `expense` WHERE date=CURRENT_DATE";
  $query = $this->db->query($sql);

  if ($query) {
  return $query->result();
  } else {
  return FALSE;
  }
  }

  public function get_yesterdays_expense()
  {
    $sql = "SELECT SUM(amount) as total,date(CURRENT_DATE)- INTERVAL 1 DAY as currentdate FROM `expense` WHERE date=(CURRENT_DATE)- INTERVAL 1 DAY";
    $query = $this->db->query($sql);

  if ($query) {
  return $query->result();
  } else {
  return FALSE;
  }
  }


  public function get_current_month_income()
  {
    $sql = "SELECT SUM(amount) as total,MONTHNAME(STR_TO_DATE(MONTH(CURRENT_DATE), '%m')) as currentmonth FROM `income` WHERE MONTH(date)=MONTH(CURRENT_DATE)";

    $query = $this->db->query($sql);

  if ($query) {
  return $query->result();
  } else {
  return FALSE;
  }
  }

  public function get_last_six_month_income()
  {
    $sql = "SELECT SUM(amount) as total FROM `income` WHERE date < NOW()and date > DATE_ADD(Now(), INTERVAL- 6 MONTH)";

    $query = $this->db->query($sql);

  if ($query) {
  return $query->result();
  } else {
  return FALSE;
  }
  }

  public function get_last_one_year_groupby_month_income()
  {
    $sql="SELECT amount, MONTHNAME(date) as month, YEAR(date) as year FROM income GROUP BY YEAR(date), MONTH(date) LIMIT 5";
    $query = $this->db->query($sql);

  if ($query) {
  return $query->result();
  } else {
  return FALSE;
  }
  }

  public function get_last_one_year_groupby_month_expense()
  {
    $sql="SELECT amount, MONTHNAME(date) as month, YEAR(date) as year FROM expense GROUP BY YEAR(date), MONTH(date) LIMIT 5";
    $query = $this->db->query($sql);

  if ($query) {
  return $query->result();
  } else {
  return FALSE;
  }
  }

  public function get_current_week_income()
  {
    $sql = "SELECT SUM(amount) as total FROM income WHERE YEARWEEK(date, 0) = YEARWEEK(CURDATE(), 0)";
    $query = $this->db->query($sql);

  if ($query) {
  return $query->result();
  } else {
  return FALSE;
  }
  }

  public function get_current_week_expense()
  {
    $sql = "SELECT SUM(amount) as total FROM expense WHERE YEARWEEK(date, 0) = YEARWEEK(CURDATE(), 0)";
    $query = $this->db->query($sql);

  if ($query) {
  return $query->result();
  } else {
  return FALSE;
  }
  }

  public function get_last_week_income()
  {
    $sql = "SELECT SUM(amount) as total FROM income WHERE WEEK (date) = WEEK(CURRENT_DATE) - 1 AND YEAR( date) = YEAR(CURRENT_DATE)";
     $query = $this->db->query($sql);

  if ($query) {
  return $query->result();
  } else {
  return FALSE;
  }
  }

   public function get_last_week_expense()
  {
    $sql = "SELECT SUM(amount) as total FROM expense WHERE WEEK (date) = WEEK(CURRENT_DATE) - 1 AND YEAR( date) = YEAR(CURRENT_DATE)";
     $query = $this->db->query($sql);

  if ($query) {
  return $query->result();
  } else {
  return FALSE;
  }
  }


}
