<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Debug extends Controller {
	
	
	public function action_index() {
		$this->response->body("debug method and class");
	}	
	
	
	public function action_certify() {
		$objDataCertify = new Model_Data_Certify();
		$strUserName = "xucongbin";
		$strPass = "19850106Xcb";
		$strDyn = $this->request->query("dyn"); 
		print_r($objDataCertify->certify($strUserName, $strPass, $strDyn));
	}
	
	public function action_bulletin() {
		$rawResult = RPC::call("bulletin", "/channel/publishbulletin");
		var_dump($rawResult);
	}
	
	public function action_gearman() {
//		$client= new GearmanClient();
//		$client->addServer('127.0.0.1', 14730);
//		echo $client->do('reverse', 'Hello World!'), "\n";
		# Create our client object.
		$gmclient= new GearmanClient();
		
		# Add default server (localhost).
		$gmclient->addServer('localhost', 14730);
		
		echo "Sending job\n";
		
		# Send reverse job
		do
		{
		  $result = $gmclient->do("reverse", "Hello!");
		
		  # Check for various return packets and errors.
		  switch($gmclient->returnCode())
		  {
		    case GEARMAN_WORK_DATA:
		      echo "Data: $result\n";
		      break;
		    case GEARMAN_WORK_STATUS:
		      list($numerator, $denominator)= $gmclient->doStatus();
		      echo "Status: $numerator/$denominator complete\n";
		      break;
		    case GEARMAN_WORK_FAIL:
		      echo "Failed\n";
		      exit;
		    case GEARMAN_SUCCESS:
		      break;
		    default:
		      echo "RET: " . $gmclient->returnCode() . "\n";
		      exit;
		  }
		}
		while($gmclient->returnCode() != GEARMAN_SUCCESS);
	}
	
	public function action_cwbgearman() {
		# create our client object
		$gmclient= new GearmanClient();
		
		# add the default server (localhost)
		$gmclient->addServer('10.34.7.166', 14730);
		
		# run reverse client in the background
		$job_handle = $gmclient->doBackground("reverseP", "this is a test");
		
		if ($gmclient->returnCode() != GEARMAN_SUCCESS)
		{
		  echo "bad return code\n";
		  exit;
		}
		print_r($job_handle);
		echo "done!\n";
	}
	
	public function action_cwstgearman() {
		# create our client object
		$gmclient= new GearmanClient();
		
		# add the default server (localhost)
		$gmclient->addServer('10.34.7.166', 14730);
		
		echo "Sending job\n";
//		$gmclient->setTimeout(10000);
		# Send reverse job
		do
		{
		  $result = $gmclient->do("wireless", "shui0.5miao");
		
		  # Check for various return packets and errors.
		  switch($gmclient->returnCode())
		  {
		    case GEARMAN_WORK_DATA:
		      echo "Data: $result\n";
		      break;
		    case GEARMAN_WORK_STATUS:
		      list($numerator, $denominator)= $gmclient->doStatus();
		      echo "Status: $numerator/$denominator complete\n";
		      break;
		    case GEARMAN_WORK_FAIL:
		      echo "Failed\n";
		      exit;
		    case GEARMAN_SUCCESS:
		      break;
		    default:
		      echo "RET: " . $gmclient->returnCode() . "\n";
		      exit;
		  }
		}
		while($gmclient->returnCode() != GEARMAN_SUCCESS);
	}
}