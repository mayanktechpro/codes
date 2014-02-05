<?php
App::uses('AppController', 'Controller');
/**
 * Students Controller
 *
 */
class CodesController extends AppController {
	/** -------------------------------------------------------------
	*codingStandard() Mayank Testing
	*
	* @author: Chetu Dev Team
	* @purpose: Function for checking Code Standards
	**/
	function codingStandard() {
		$this->layout = false;
		$this->render(false);
		$filename = "../../result";
		$logs = $this->file2array($filename);
		//pr($logs); die;
		# Get all commits pushed on to GIT (Uncomment below line for shell execution of GIT commands)
		// exec('git log -p --after="2014-01-30"', $logs);
		$final_logs = array();
		$temp_array = array();
		# Break the output in each commit as an associative array
		foreach ($logs as $key => $value) {
			if ($this->checkCommit($value)) {
				if($key == 0) {
					$temp[] = $value;
				} else {
					$final_logs[] = $temp;
					$temp = array();
					$temp[] = $value;
				}
				continue;
			} else if($value == end($logs)){
					$final_logs[] = $temp;
				} else {
				$temp[] = $value;
			}
		}
		//pr($final_logs); die;
		$final_array = array();
		foreach ($final_logs as $output) { //For each Commit as $output
			foreach($output as $key => $out) { //Process Each Commit to check standard
				if(substr($out, 0, 1) == '+' && substr($out, 1, 1) != '+') {
					/*if(empty($final_array)) {
						# Get a line before + into array
						$final_array[] = ltrim($output[$key-1], ' ');
					} */
					$final_array[] = ltrim($out,'+');
				}
			}
			
			// Pop out last element from array, as it is not the exact change
			//array_pop($final_array);
			$this->checkBlankLines($final_array);
			$positions = array();
			$final_vals = array();
			foreach ($final_array as $key => $value) {
				preg_match('/[^\t^\s]/', $value, $matches);
				if(trim($value) != '') {
					$pos = strpos($value, $matches[0]);
					$positions[] = $pos;
					$final_vals[] = $value;
				}
			}
			$indent_message = $this->checkIndentation($final_vals, $positions);
			pr($indent_message);
			$comment_message = $this->checkCommenting($final_vals);
			pr($comment_message);
		}
	}

	/** -------------------------------------------------------------
	* checkBlankLines() Mayank Testing
	* @param $final_array
	* @param $count
	* @param $key
	* 
	* @return $message
	* 
	* @purpose To check Extra Blank Lines for coding standards
	**/
	function checkBlankLines($final_array, $count = 0, $key = 0) {
		if(array_key_exists($count, $final_array)) {
			if($final_array[$count] == "") {
				$count++;
				$key++;
				if($key >= 2) {
					echo "More than 1 Blank Lines found below - ".$final_array[$count-3];
				} else {
					$this->checkBlankLines($final_array, $count, $key);
				}
			} else {
				$count++;
				$this->checkBlankLines($final_array, $count);
			}
		} else {
			echo "No Extra Blank Lines Found";
		}
	}

	/** -------------------------------------------------------------
	* checkIndentation() Mayank Testing
	* @param $final_vals
	* @param $positions
	* 
	* @return $message
	* 
	* @purpose To check Indentation for coding standards
	**/
	function checkIndentation($final_vals, $positions) {
		$message = true;
		$initial_tab = $positions[0];
		$indent_increasers = array('{', 'if(', 'if (', 'else', 'switch', 'case');
		$indent_decreasers = array('}');
		$ignore_list = array("preg_");
		foreach ($final_vals as $fkey => $fvalue) {
			$valid_add_indent = false;
			$valid_remove_indent = false;
			foreach ($indent_decreasers as $indent_decreaser) {
				if($this->checkIgnoreList($ignore_list, $fvalue) == false) {
					if(strpos($fvalue, $indent_decreaser) !== false) {
						$valid_remove_indent = true;
						$initial_tab--;
						break;
					}
				}
			}
			foreach ($indent_increasers as $indent_increaser) {
				if($this->checkIgnoreList($ignore_list, $fvalue) == false) {
					if(strpos($fvalue, $indent_increaser) !== false) {
						$valid_add_indent = true;
						break;
					}
				}
			}
			if($valid_add_indent) {
				if($positions[$fkey] == $initial_tab) {
					$initial_tab++;
				} else {
					//echo "Position - ".$positions[$fkey]." Initial Tab - ".$initial_tab." Line - ".$fkey." - ".$fvalue."<br>";
					echo "Check Indentation near ".$fvalue."<br>";
					$message = false;
				}
			} else if($valid_remove_indent) {
				if($positions[$fkey] != $initial_tab) {
					//echo "Position - ".$positions[$fkey]." Initial Tab - ".$initial_tab." Line - ".$fkey." - ".$fvalue."<br>";
					echo "Check Indentation near ".$fvalue."<br>";
					$message = false;
				}
			} else {
				$initial_tab = $positions[$fkey];
			}
		}

		return ($message) ? "Indentation OK" : "Indentation Problem";
	}

	/** -------------------------------------------------------------
	* checkCommenting() Mayank Testing
	* @param $final_vals
	* 
	* @return message
	* 
	* @purpose To check Function Commenting for coding standards
	**/
	function checkCommenting($final_vals) {
		$message = true;
		$search_fn = "function";
		$not_found = array();
		foreach ($final_vals as $fkey => $fvalue) {
			if(strpos($fvalue, "{") !== false) {
				if(strpos($fvalue, $search_fn) !== false) {
					$message = (strpos($final_vals[$fkey-1], "*/") !== false) ? true :false;
					if(!$message) {
						$not_found = $fvalue;
						break;
					}
				}
			}
		}

		return ($message) ? "Commenting Found for Function(s)" : "Commenting Not Found for Function - ".$not_found;
	}

	/** -------------------------------------------------------------
	* file2array()
	* @param $filename
	* 
	* @return $result
	* 
	* @purpose Read file and convert it to array
	**/
	function file2array($filename) {
		$result = array();
		$array_of_lines = file($filename);
		foreach ($array_of_lines as $line) {
			$result[] = $line;
		}
		return $result;
	}

	/** -------------------------------------------------------------
	* checkCommit()
	* @param $value
	* 
	* @return boolean
	* 
	* @purpose To check Commit line with hash key
	**/
	function checkCommit($value) {
		$commit = split(" ", $value);
		if($commit[0] == "commit" && preg_match("/^[a-z0-9]{40}$/", $commit[1])) {
			return true;
		} else {
			return false;
		}
	}

	/** -------------------------------------------------------------
	* checkIgnoreList()
	* @param ignore_list
	* @param subject
	* 
	* @return boolean
	* 
	* @purpose Match each ignore_list element in subject
	**/
	function checkIgnoreList($ignore_list, $subject) {
		foreach ($ignore_list as $ig_key => $ig_value) {
			if(strpos($subject, $ig_value) !== false) {
				return true;
			} else {
				return false;
			}
		}
	}

	/** -------------------------------------------------------------
	* testpreg()
	* @param 
	* 
	* @return Match message
	* 
	* @purpose Preg_Match for commit hash key for git
	**/
	function testpreg() {
		$this->render(false);
		$this->layout = false;
		$commit = array(0 => "commit", 1 => "5afc2b9f8f3134617663d5929efff658f2cf4276");
		if(preg_match("/^[a-z0-9]{40}$/", $commit[1]) == true) {
			echo "Match";
		} else {
			echo "Not Match";
		}
	}
}