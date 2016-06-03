<?php
/**
 * Created by PhpStorm.
 * User: hieuhoang
 * Date: 6/2/16
 * Time: 5:40 PM
 */

include "lib/simple_html_dom.php";

/**
 * Class GenieeCrawler
 * Geniee crawler for Geniee PHP Developer Test
 */

class Crawler
{
    private $page_url = "http://exam.geniee.info/web/";
    private $login_url = "http://exam.geniee.info/web/login";
    private $cookie_file = "/cookie/cookie";
    private $date_arr;

    /**
     *
     */
    public function loginAndSaveCookie()
    {
        // Create DOM from URL or file
        $html = file_get_html($this->login_url);

        // get token, username and password from DOM for login
        $token = $html->find('form', "0")->find('input[name=token]', "0") != null
            ? $html->find('form', "0")->find('input[name=token]', "0")->value
            : '';
        $userName = $html->find('dl[class=dl-horizontal]', "-1")->find('dd',"0") != null
            ? $html->find('dl[class=dl-horizontal]', "-1")->find('dd',"0")->plaintext
            : '';
        $password = $html->find('dl[class=dl-horizontal]', "-1")->find('dd',"1") != null
            ? $html->find('dl[class=dl-horizontal]', "-1")->find('dd',"1")->plaintext
            : '';

        if ($token != '' && $userName != '' && $password != '' ){
        // make data to post username=value1&password=value2&token=value3
            $postData = 'username='.$userName.'&password='.$password.'&token='.$token.'&login=""';

        // start to login
            $handle = curl_init();

            curl_setopt($handle, CURLOPT_URL, $this->login_url);
            curl_setopt($handle, CURLOPT_POST, 1);
            curl_setopt($handle, CURLOPT_POSTFIELDS, $postData);

            //Save cookie after login success

            curl_setopt($handle, CURLOPT_COOKIEJAR, __DIR__.$this->cookie_file);

            ob_start();      // prevent any output make the page not reload
            curl_exec($handle);
            ob_end_clean();  // stop preventing output
            curl_close($handle);

            return true;
        }else {
            return false;
        }
    }

    /**
     *
     * @return array data
     */
    public function getScrapData()
    {
        $crawData = [];
        //BEGIN STEP 1: Access to $source_url with saved cookie
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_COOKIEFILE, __DIR__.$this->cookie_file);
        curl_setopt($ch, CURLOPT_URL, $this->page_url);
        $result = curl_exec($ch);
        curl_close($ch);
        //END STEP 1

        //BEGIN STEP 2: Gather the information
        $html = str_get_html($result);

        $table = $html->find('table[id=mainTable]', "0");

        foreach ((array)$table->find('tr[class=data]') as $row) {
            $count_tmp = 0;
            $tmp_arr = array();
            foreach ((array)$row->find('td') as $data) {
                switch ($count_tmp) {
                    case 0:
                        $tmp_arr['partner'] = $data->plaintext;
                        break;
                    case 1:
                        $tmp_arr['placement_id'] = $data->plaintext;
                        break;
                    case 2:
                        $tmp_arr['impression'] = $data->plaintext;
                        break;
                    case 3:
                        $tmp_arr['click'] = $data->plaintext;
                        break;
                    case 4:
                        $tmp_arr['revenue'] = $data->plaintext;
                        break;
                    case 5:
                        $date = str_replace('/', '-', $data->plaintext); //replace '/' to '-' to fix issue strtotime can parse d/m/y
                        $tmp_arr['date'] = $date;
                        $this->date_arr[] = $date; //Store date data
                        break;
                    default:
                        break;
                }
                $count_tmp++;
            }
            $crawData[] = $tmp_arr;
        }
        //END STEP 2

        //BEGIN STEP 3: Sort crawler_data by date
        usort($crawData, function ($a1, $a2) {
                $v1 = strtotime($a1['date']);
                $v2 = strtotime($a2['date']);
                return $v1 - $v2; // $v2 - $v1 to reverse direction
            });
        //END STEP 3
        return $crawData;
    }



    /**
     * Get date data
     * @return mixed
     */
    public function getDataArray()
    {
        return $this->date_arr;
    }

}
