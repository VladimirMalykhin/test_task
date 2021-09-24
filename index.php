<?php

class ResultItem
{
	private $values;
	
	public function __construct($values = [])
	{
		$this->values = $values;
	}
	
	
	public function define_count_unique($value) :void
	{
		if(!in_array($value, $this->values))
		{
			$this->values[] = $value;
		}
	}
	
	
	public function define_unique_values($value) :void
	{
		$valueSearched = strtolower($value);
		if(in_array($valueSearched, $this->values))
		{
			$this->values[] = $value;
			$this->values[$value] = 1;
		} else {
			$this->values[$value] += 1;
		}
	}
	
	
	public function define_count_values($value) :void
	{
		foreach($this->values as $key_item=>$value_item)
		{
			$valueSearched = strtolower($key_item);
			if(strpos($value, $valueSearched)){
				$this->values[$key_item] += 1;
				break;
			}
		}
	}
	
	
	public function values() :array
	{
		return $this->values;
	}
}


function get_url($text)
{
	$url_array = explode(' /', $text);
	$current_urls = explode(' HTTP', $url_array[1]);
	return $current_urls[0];
}


function get_code($text)
{
	$url_array = explode('"', $text);
	$code_array = explode(" ", $url_array[2]);
	return $code_array[1];
}


function get_traffic($text)
{
	$url_array = explode('"', $text);
	$code_array = explode(" ", $url_array[2]);
	return (int)$code_array[2];
}

$file_path = "access_log"; 
$pattern = "/^(\S+) (\S+) (\S+) \[([^:]+):(\d+:\d+:\d+) ([^\]]+)\] ^(\S+) \"(\S+) (.*?) (\S+)\" (\S+) (\S+) (\".*?\") (\".*?\")$/";
$handle = fopen($file_path, "r");
if ($handle) {
	$result = [];
	$result['traffic'] = 0;
	$result_url = new ResultItem();
	$result_code = new ResultItem();
	$result_referer = new ResultItem([
		  "Google" => 0,
		  "Bing" => 0,
		  "Baidu" => 0,
		  "Yandex" => 0]);
	$count_string = 0;
	$count_views = 0;
    while (($line = fgets($handle))) {
        if (preg_match('/^(\S+) \S+ \S+ \[(.*?)\] "(\S+).*?" \d+ \d+ "(.*?)" "(.*?)"/', $line, $parameters)) {
		  $url_value = get_url($line);
		  $result_url->define_count_unique($url_value);
		  $code_value = get_code($line);
		  $result_code->define_unique_values($code_value);
		  $result_referer->define_count_values($parameters[4]);
		  $count_views++;
		  $result['traffic'] += get_traffic($line);
		} 
		$count_string++;
    }
    fclose($handle);
	$result['views'] = $count_views;
	$result['strings'] = $count_string;
	$result['urls'] = count($result_url->values());
	$result['statusCodes'] = $result_code->values();
	$result['crawlers'] = $result_referer->values();
	echo json_encode($result);
} else {
    echo 'No such file';
} 


