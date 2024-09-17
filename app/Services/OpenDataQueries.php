<?php
namespace App\Services;

class OpenDataQueries {

    private string $ordernumbers = "'616', '617', '618', '624', '619', '620', '626', '614', '623','621','622','625'";

    private string $endpoint = 'https://data.cityofnewyork.us/resource/wvxf-dwi5.json';

    private string $select_columns = " `buildingid`,
                `bin`,
                `councildistrict`,
                `ordernumber`,
                `boro`,
                `longitude`,
                `latitude`,
                `streetname`,
                `housenumber`,
                `apartment`,
                `zip`,
                `currentstatusdate`,
                `inspectiondate`,
                `violationid`,
                `currentstatusid`";

    private string $api_key;

    public function __construct(){
      
      $this->api_key = $_ENV['LEAD_VIOLATIONS_DATA_KEY'];

    }

    public function getViolationsCountQuery():string{
    
      return 
        "SELECT
                count(`buildingid`) as `count_buildingid`
              WHERE
                caseless_one_of(
                  `ordernumber`,
                  $this->ordernumbers
                )
              ";
    }

    public function getDates(string $timestamp){
      return "currentstatusdate > '$timestamp'";
    }

    public function getSelectedColumns():string{
      return $this->select_columns;
    }

    public function getEndpoint():string{
      return $this->endpoint;
    }

    public function getAPIKey():string {
      return $this->api_key;
    }

    public function getOrderNumbers():string{
      return $this->ordernumbers;
    }

}