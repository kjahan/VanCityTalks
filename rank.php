<?php
class Rank{
	//score array
	public $scores = array();
	//DB parameters
	// open connection to MongoDB server
	public $conn;
	
	// access database
	public $db;
	
	// access collection
	public $col_schl;
	public $col_lib;
	public $col_park;
	public $col_crime;
	public $col_busn;
	public $col_prptx;
	
	//weights for classifier
	public $weights;
	
	function __construct() {
		//print "constructor\n";
		$this->conn = new Mongo('localhost');
		$this->db = $this->conn->test;
		$this->col_schl = $this->db->school;
		$this->col_lib = $this->db->library;
		$this->col_park = $this->db->parks;
		$this->col_crime = $this->db->crime;
		$this->col_busn = $this->db->business_licences;
		$this->col_prptx = $this->db->property_tax;

		$this->weights = array(
				"school" => 1.0,
				"library" => 1.0,
				"parks"   => 1.0,
				"crime"  => -1.0,
				"business" => 1.0,
				"property_tax" => 1.0
		);
	}

	//use this function to calculate score.
	//pass an array where features = number of schools, parks, etc in your area
	//and weights = weight given to each school, library, etc - for calculating weighted average
	function computeRank($features)
	{
		$total = $features["school"] * $this->weights["school"] +
		$features["library"] * $this->weights["library"] +
		$features["parks"] * $this->weights["parks"] +
		$features["crime"] * $this->weights["crime"] +
		$features["business"] * $this->weights["business"] +
		$features["property_tax"] * $this->weights["property_tax"];
		//echo $total;
		return $total/6.0;
	}
	
	// Create mongo's geo-spatial queries
	function get_query($lat, $lon, $rad) {
		//echo "rad: $rad";
		return array('location' =>
				array('$within' =>
						array('$centerSphere' =>
								array(
										array(floatval($lon), floatval($lat)), floatval($rad/3963.192)
								)
						)
				)
		);
	}
	
	function getPrpAvg($prpRecords) {
		return array_sum($prpRecords) / count(array_filter($prpRecords));
	}
	
	function computeScores($testData) {
		try {	
			$max_schl = 0;
			$max_lib = 0;
			$max_park = 0;
			$max_crime = 0;
			$max_busn = 0;
			$max_prptx = 0;
	
			$results = array();
			$cnt = 0;
			
			foreach ($testData as $td) {
				$res = array();
				
				$qschl = $this->col_schl->find($this->get_query($td['lat'], $td['lon'], $td['radius']))->count();
				$qlib    = $this->col_lib->find($this->get_query($td['lat'], $td['lon'], $td['radius']))->count();				
				$qpark   = $this->col_park->find($this->get_query($td['lat'], $td['lon'], $td['radius']))->count();				
				$qcrime  = $this->col_crime->find($this->get_query($td['lat'], $td['lon'], $td['radius']))->count();				
				$qbusn   = $this->col_busn->find($this->get_query($td['lat'], $td['lon'], $td['radius']))->count();				
				$qtax = $this->col_prptx->find($this->get_query($td['lat'], $td['lon'], $td['radius']));
				$qprptx = $this->getPrpAvg($qtax);
	
				if ($qschl > $max_schl)
					$max_schl = $qschl;
				if ($qlib > $max_lib)
					$max_lib = $qlib;
				if ($qpark > $max_park)
					$max_park = $qpark;
				if ($qcrime > $max_crime)
					$max_crime = $qcrime;
				if ($qbusn > $max_busn)
					$max_busn = $qbusn;
				if ($qprptx > $max_prptx)
					$max_prptx = $qprptx;
	
				$res["lat"] = $td['lat'];
				$res["lon"] = $td['lon'];
				$res["school"] = $qschl;
				$res["library"] = $qlib;
				$res["parks"] = $qpark;
				$res["crime"] = $qcrime;
				$res["business"] = $qbusn;
				$res["property_tax"] = $qprptx;
	
				$results[$cnt] = $res;
				$cnt += 1;
			}			
			$cnt = 0;
			foreach ($results as $value) {
				$value["school"] /= $max_schl;
				$value["library"] /= $max_lib;
				$value["parks"] /= $max_park;
				$value["crime"] /= $max_crime;
				$value["business"] /= $max_busn;
				$value["property_tax"] /= $max_prptx;

				$rank = $this->computeRank($value);
				$score = array(
						"lat" => $value["lat"],
						"lon" => $value["lon"],
						"score" => $rank
				);
				$this->scores[$cnt] = $score;
				$cnt+=1;
			}
			 //var_dump($this->scores);
			// disconnect from server
			$this->conn->close();
		} catch (MongoConnectionException $e) {
			die('Error connecting to MongoDB server');
		} catch (MongoException $e) {
			die('Error: ' . $e->getMessage());
		}
		//return $scores;
	}
	//accessor for scores attribute
	function getScores(){
		return $this->scores;
	}	
}
?>