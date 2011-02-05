<?php

require './Scrapt.php';

function label_to_property($label, $strip_divs_spans=true)
{
   $prop = $label;
   
   if ($strip_divs_spans) {
      // Remove embedded divs and spans.
      $prop = preg_replace('/<(span|div).+<\/\\1>/U', '', $label);
   }
   
   $prop = strtolower(strip_tags($prop));
   $prop = preg_replace('/[^a-z\s]/i', '', $prop);
   $prop = trim($prop);
   $prop = preg_replace('/\s+/', '_', $prop);
   return $prop;
}

function get_thesaurus_entry($word)
{
   $scraper = new Scrapt;
   $scraper->setURL('http://thesaurus.com/browse/'.$word);
   $data = $scraper->cache();
   $page = $scraper->getPage();
   
   $properties = $page->findBySelector('table.the_content tr');
   
   $main_entry = '';
   $part_of_speech = '';
   $definition = '';
   $entries = array();
   
   foreach($properties as $p) {
      $name = str_replace(':', '', strip_tags($p->find('td', 0)->innertext));
      $value = trim(str_replace("\n", " ", strip_tags($p->find('td', 1)->innertext)));
      
      $prop = label_to_property($name);
      
      if (isset($$prop)) {
         $$prop = $value;
         continue;
      }

      if (!isset($entries[$main_entry])) {
         $entries[$main_entry] = array();
      }   
      if (!isset($entries[$main_entry][$part_of_speech])) {
         $entries[$main_entry][$part_of_speech] = array();
      }
      
      $entries[$main_entry][$part_of_speech][$definition][$prop] = $value;
   }
   return $entries; 
}

$entries = get_thesaurus_entry('sweet');
print_r($entries);