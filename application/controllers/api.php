<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Api extends CI_Controller {


    private function get_number($string)
    {
        preg_match_all('!\d+!', $string, $matches);
        $var = implode(' ', $matches[0]);

        if(strlen($var) ==0)
        {
            return null;
        }
        else
        {
            return $var;
        }
    }

	public function index()
	{

        $html = file_get_contents('http://bridemusic.org/');

        $doc = new DOMDocument();
        $doc->loadHtml($html);

        $xpath = new DOMXPath($doc);

        $nodes = $xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' menu_item ')]");

        $newArray = array();
        foreach($nodes as $items)
        {
            $temp_item['cat_name'] = $items->textContent;
            $temp_item['cat_id'] = $this->get_number($items->getAttribute('href'));
            $temp_item['sub_categories'] = base_url().'api/sub_cat/'.$temp_item['cat_id'];
            if($temp_item['cat_id'] != null)
            {
                array_push($newArray,$temp_item);
            }
        }
        $this->output
            ->set_content_type ( 'application/json; charset=UTF-8' )
            ->set_output ( json_encode ( $newArray ) );

	}

    private function get_sub_cat_id($cat_id, $string)
    {
        preg_match_all('!\d+!', $string, $matches);
        $var = implode(' ', $matches[0]);
        $nums = explode(' ',$var);

        if($nums[0] != $cat_id)
        {
            return null;
        }
        else
        {
            return $nums[1];
        }
    }

    public function sub_cat($cat_id = 1)
    {
        $html = file_get_contents('http://bridemusic.org/');

        $doc = new DOMDocument();
        $doc->loadHtml($html);

        $xpath = new DOMXPath($doc);

        $nodes = $xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' submenu_item ')]");

        $newArray = array();
        foreach($nodes as $items)
        {
            $temp_item['subcat_id'] = (int)$this->get_sub_cat_id($cat_id, $items->getAttribute('href'));
            $temp_item['subcat_name'] = $items->textContent;
            $temp_item['cat_id'] = (int)$cat_id;
            $temp_item['songs'] = base_url().'api/songs/'.$temp_item['cat_id'].'/'.$temp_item['subcat_id'];
            //$temp_item['cat_id'] = $this->get_number($items->getAttribute('href'));
            if($temp_item['subcat_id'] != null)
            {
                array_push($newArray,$temp_item);
            }
        }
        $this->output
            ->set_content_type ( 'application/json; charset=UTF-8' )
            ->set_output ( json_encode ( $newArray ) );

    }

    public function songs($cat_id, $sub_cat_id)
    {
        $html = file_get_contents('http://bridemusic.org/music.asp?libId='.$cat_id.'&catId='.$sub_cat_id);

        $doc = new DOMDocument();
        $doc->loadHtml($html);

        $xpath = new DOMXPath($doc);
        $nodes = $doc->getElementsByTagName('tr');
        $links = $xpath->query("//*[contains(concat(' ', normalize-space(@target), ' '), ' mediaPlayerFrame ')]");

        $newArray = array();
        $num = 0;
        foreach($nodes as $items)
        {
            $row = $items->childNodes;

            if($items->childNodes->length == 8)
            {
                $count = 0;
                foreach($row as $key=>$row_item)
                {
                    switch($count)
                    {
                        case 0: $temp_item['song_name'] = html_entity_decode($row_item->nodeValue, ENT_QUOTES, "UTF-8");break;
                        case 2: $temp_item['artist'] = html_entity_decode($row_item->nodeValue, ENT_QUOTES, "UTF-8");break;
                        case 4: $temp_item['date_recorded'] = html_entity_decode($row_item->nodeValue, ENT_QUOTES, "UTF-8");break;
                        //case 7: $temp_item['key'] = $num;break;
                        case 7: $temp_item['song_id'] = (int)$this->get_sub_cat_id($cat_id,$links->item($num)->getAttribute('href'));break;
                    }
                    $count++;

                    //print_r($row_item->nodeValue .'<br>Node type: '.$row_item->nodeType);echo '<br><br><br>';
                }
                $temp_item['download'] = 'http://bridemusic.org/download.asp?libId='. $cat_id.'&id='.$temp_item['song_id'];
                if($temp_item['date_recorded'] != 'Recorded')
                {
                    array_push($newArray,$temp_item);
                    $num++;
                }
            }
        }
        $this->output
            ->set_content_type ( 'application/json; charset=UTF-8' )
            ->set_output ( json_encode ( $newArray ) );

    }

}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */