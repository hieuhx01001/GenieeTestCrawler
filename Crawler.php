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
    private $source_url = "http://exam.geniee.info/web/";
    private $login_url = "http://exam.geniee.info/web/login";
    private $cookie_file = "tmp/cookie";
    private $crawler_data; // store scraped data from $source_url
    private $date_arr; // store only date data

    /**
     * __construct
     * scrape data and save to variable
     */
    function __construct()
    {
        $this->loginToTargetWeb();
        $this->scrapeInformation();
    }

    /**
     * Login to $login_url and then save cookie to $cookie_file
     * make crawler can scrape the information in $source_url
     * @return none
     */
    private function loginToTargetWeb()
    {
        $html = file_get_html($this->login_url);

        //BEGIN STEP 1: Gather the information to login: username, password, token
        $token_input = $html->find('form', "0")->find('input[name=token]', "0");
        $token = $token_input->value;

        $username = '';
        $password = '';

        $login_input_info = $html->find('dl');
        foreach ((array)$login_input_info as $dl_lii) {
            $count_tmp = 0;
            foreach ((array)$dl_lii->find('dt') as $dt_lii) {
                switch ($dt_lii->plaintext) {
                    case 'Username':
                        $username = $dl_lii->find('dd', $count_tmp)->plaintext;
                        break;
                    case 'Password':
                        $password = $dl_lii->find('dd', $count_tmp)->plaintext;
                        break;
                    default:
                        break;
                }
                $count_tmp++;
            }
        }
        //END STEP 1

        //BEGIN STEP 2: Prepare data to POST with format: key1=value1&key2=value2&key3=value3
        $fields = array(
            'username' => $username, // For Usename textbox (input[name=username])
            'password' => $password, // For Password textbox (input[name=password])
            'token' => $token, // For token hidden field (input[name=token])
            'login' => '' // For button "Login" (input[type=submit]) default is ''
        );

        $fields_string = '';
        foreach ((array)$fields as $key => $value) {
            $fields_string .= $key . '=' . $value . '&';
        }
        $fields_string = rtrim($fields_string, '&'); //remove the last '&'
        //END STEP 2


        //BEGIN STEP 3: Start CURL to login
        $handle = curl_init();

        curl_setopt($handle, CURLOPT_URL, $this->login_url);
        curl_setopt($handle, CURLOPT_POST, 1);
        curl_setopt($handle, CURLOPT_POSTFIELDS, $fields_string);

         //Save cookie after login success
        curl_setopt($handle, CURLOPT_COOKIEJAR, $this->cookie_file);

        ob_start();      // prevent any output make the page not reload
        curl_exec($handle);
        ob_end_clean();  // stop preventing output
        curl_close($handle);
        //END STEP 3

        return;
    }

    /**
     * Get the information from $source_url
     * @return none
     */
    private function scrapeInformation()
    {
        //BEGIN STEP 1: Access to $source_url with saved cookie
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie_file);
        curl_setopt($ch, CURLOPT_URL, $this->source_url);
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
            $this->crawler_data[] = $tmp_arr;
        }
        //END STEP 2

        //BEGIN STEP 3: Sort crawler_data by date
        usort($this->crawler_data, function ($a1, $a2) {
                $v1 = strtotime($a1['date']);
                $v2 = strtotime($a2['date']);
                return $v1 - $v2; // $v2 - $v1 to reverse direction
            });
        //END STEP 3
        return;
    }

    /**
     * Get scraped data
     * @return mixed
     */
    public function getCrawlerData()
    {
        return $this->crawler_data;
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
