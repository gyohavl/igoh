<?php
function parse($html) {
	$result = array();
	$doc = new DomDocument();
	$doc->loadHTML($html);
	$finder = new DomXPath($doc);
	$rows = $doc->getElementById("mainContext")->getElementsByTagName("table")->item(0)->getElementsByTagName("tr");
	foreach ($rows as $key => $row) {
		$result[$key]["date"] = substr($row->getElementsByTagName("div")->item(0)->getAttribute("id"), 4);
		$result[$key]["meals"] = array();
		$meals = $finder->query("//tr[".($key + 1)."]//*[contains(concat(' ', normalize-space(@class), ' '), ' jidelnicekItem ')]");
		foreach ($meals as $mealNum => $meal) {
			//$result[$key]["meals"][$mealNum]["name"] = trim($finder->query("//tr[".($key + 1)."]//*[contains(concat(' ', normalize-space(@class), ' '), ' jidelnicekItem ')][".($mealNum + 1)."]/span/span[2]")->item(0)->textContent);
			// $theSpan = $finder->query("//tr[".($key + 1)."]//*[contains(concat(' ', normalize-space(@class), ' '), ' jidelnicekItem ')][".($mealNum + 1)."]/span/span[2]")->item(0);
			$theSpan = $finder->query("//tr[".($key + 1)."]//*[contains(concat(' ', normalize-space(@class), ' '), ' jidelnicekItem ')][".($mealNum + 1)."]/div/div[2]")->item(0);
			$result[$key]["meals"][$mealNum]["name"] = trim($theSpan->childNodes[0]->textContent);
			if(is_object($theSpan->childNodes[1])) {
				$result[$key]["meals"][$mealNum]["allergens"] = trim($theSpan->ownerDocument->saveHTML($theSpan->childNodes[1]));
			} else {
				$result[$key]["meals"][$mealNum]["allergens"] = "";
			}
			$result[$key]["meals"][$mealNum]["disabled"] = (strpos($meal->getElementsByTagName("a")->item(0)->getAttribute("class"), "disabled") !== false);
			$result[$key]["meals"][$mealNum]["ordered"] = ($finder->query("//tr[".($key + 1)."]//*[contains(concat(' ', normalize-space(@class), ' '), ' jidelnicekItem ')][".($mealNum + 1)."]//*[contains(concat(' ', normalize-space(@class), ' '), ' fa-check ')]")->length === 1);
			$result[$key]["meals"][$mealNum]["url"] = explode("'", $meal->getElementsByTagName("a")->item(0)->getAttribute("onclick"), 3)[1];
			$result[$key]["meals"][$mealNum]["url"] = preg_replace("/&week=&terminal=false&keyboard=false&printer=false/", "", $result[$key]["meals"][$mealNum]["url"]);
			$result[$key]["meals"][$mealNum]["url"] = preg_replace("/db\/dbProcessOrder\.jsp\?/", "", $result[$key]["meals"][$mealNum]["url"]);
		}
	}
	return $result;
}

/*
→ what to get (for each day):
	date (+ day name)
	meal name
	if is disabled
	which is ordered (contains fa-check icon)
	onclick url for order

structure:
	date (string)					div(0) – delete first 4 chars from id								done
	meals (array)					.jidelnicekItem → foreach											done
		0
			meal name (string)		span(0) > span(1)													done
			disabled (boolean)		a(0) – one of classes is "disabled"									done
			ordered (boolean)		is available .fa-check												done
			url (string)			a(0) – split onclick by ' to 3 parts → splitted[1] – second part	done
		1 – optional
			meal name (string)
			disabled (boolean)
			ordered (boolean)
			url (string)
*/