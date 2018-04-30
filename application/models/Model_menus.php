<?php 

class Model_menus extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
	}

	/* get the brand data */
	public function getMenuData($id = null)
	{
		if($id) {
			$sql = "SELECT * FROM menus where id = ?";
			$query = $this->db->query($sql, array($id));
			return $query->row_array();
		}

		$sql = "SELECT * FROM menus ORDER BY id DESC";
		$query = $this->db->query($sql);
		return $query->result_array();
	}

	public function getActiveMenuData()
	{
		$sql = "SELECT * FROM menus WHERE availability = ? ORDER BY id DESC";
		$query = $this->db->query($sql, array(1));
		return $query->result_array();
	}

	public function create($data)
	{
		if($data) {
			$insert = $this->db->insert('menus', $data);
			return ($insert == true) ? true : false;
		}
	}

	public function update($data, $id)
	{
		if($data && $id) {
			$this->db->where('id', $id);
			$update = $this->db->update('menus', $data);
			return ($update == true) ? true : false;
		}
	}

	public function remove($id)
	{
		if($id) {
			$this->db->where('id', $id);
			$delete = $this->db->delete('menus');
			return ($delete == true) ? true : false;
		}
	}

	public function countTotalMenus()
	{
		$sql = "SELECT * FROM menus";
		$query = $this->db->query($sql);
		return $query->num_rows();
	}

}