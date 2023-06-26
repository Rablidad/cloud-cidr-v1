<?php

namespace App\Controllers;

use  WpOrg\Requests\Requests;

class AwsController extends \Leaf\Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * aws ip ranges endpoint
     */
    private $endpoint = "https://ip-ranges.amazonaws.com/ip-ranges.json";


    /**
     * AWS regions mapped to their actual location name
     */
    private $zones_map = [
        "global" => "Global",
        "us-east-2" => "City of Dublin (Ohio)",
        "us-east-1" => "N. Virginia",
        "us-west-1" => "San Francisco",
        "us-west-2" => "Oregon",
        "af-south-1" => "Cape Town",
        "ap-east-1" => "Hong Kong",
        "ap-south-2" => "Hyderabad",
        "ap-southeast-3" => "Jakarta",
        "ap-southeast-4" => "Melbourne",
        "ap-south-1" => "Mumbai",
        "ap-northeast-3" => "Osaka",
        "ap-northeast-2" => "Seoul",
        "ap-southeast-1" => "Singapore",
        "ap-southeast-2" => "Sydney",
        "ap-northeast-1" => "Tokyo",
        "ap-southeast-6" => "Auckland",
        "ca-central-1" => "Montreal",
        "ca-west-1" => "Calgary",
        "cn-north-1" => "Beijing",
        "cn-northwest-1" => "Yinchuan",
        "eu-central-1" => "Frankfurt",
        "eu-west-1" => "Dublin",
        "eu-west-2" => "London",
        "eu-south-1" => "Milan",
        "eu-east-1" => "Madrid",
        "eu-west-3" => "Paris",
        "eu-south-2" => "Spain",
        "eu-north-1" => "Stockholm",
        "eu-central-2" => "Zurich",
        "me-west-1" => "Tel Aviv",
        "me-south-1" => "Manama (Bahrain)",
        "me-central-1" => "United Arab Emirates",
        "sa-east-1" => "São Paulo",
        "il-central-1" => "Tel Aviv",
        "us-gov-east-1" => "US GovCloud East",
        "us-gov-west-1" => "US GovCloud West"
    ];

    /**
     * Fetch the ip ranges from aws endpoint
     */
    public function get()
    {
        return Requests::get($this->endpoint);
    }

    /**
     * Sort aws json prefixes
     */
    public function aws_sort($data, $query)
    {
        $prefixes = $data[$query['prefix']['collection']];
        $prefixes = $this->filter_prefixes_by_service($prefixes, $query['service']);
        $regions = $this->group_prefixes_into_regions($prefixes, $query['prefix']['key']);
        $this->sort_prefixes_ascending_and_remove_duplicates($regions);
//        ksort($regions);
        return $regions;
    }

    /**
     * filter prefixes by service
     */
    private function filter_prefixes_by_service($ip_ranges, $service)
    {
        return array_filter($ip_ranges, function ($range) use ($service) {
            if ($service) return strcmp(strtolower($range["service"]), strtolower($service)) === 0;
            else return true;
        });
    }

    /**
     * map each prefix into it's specific region
     */
    private function group_prefixes_into_regions($prefixes, $prefix_type)
    {
        $regions = [];
        foreach ($prefixes as $prefix) {
            $regions[$prefix['region']][] = [
                'prefix' => $prefix[$prefix_type],
                'service' => $prefix['service'],
                'location' => $this->zones_map[strtolower($prefix['region'])]
            ];
        }
        return $regions;
    }


    /**
     * sort the prefixes in ascending order and remove duplicates
     */
    private function sort_prefixes_ascending_and_remove_duplicates(&$regions)
    {
        foreach ($regions as $region => $prefix) {

            // sort the prefixes in ascending order
            usort($regions[$region], function ($prefix1, $prefix2) {
                return version_compare($prefix1['prefix'], $prefix2['prefix']);
            });

            // remove objects with duplicate prefixes
            $regions[$region] = array_intersect_key($regions[$region], array_unique(array_column($regions[$region], 'prefix')));
        }
    }

    /**
     * get zone name from zone id
     */
    public function get_zone_name($zone_id)
    {
        return $this->zones_map[strtolower($zone_id)];
    }
}
